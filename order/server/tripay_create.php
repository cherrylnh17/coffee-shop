<?php
/**
 * tripay_create.php
 * POST /order/server/tripay_create
 *
 * Body JSON:
 *   { "order_code": "ORD-...", "channel": "QRIS" }
 *
 * Response JSON:
 *   { "success": true, "payment_url": "https://..." }
 *   { "success": false, "message": "..." }
 *
 * CATATAN BIAYA:
 *   - amount yang dikirim ke Tripay = order.total (subtotal + gratuity)
 *   - Biaya gateway Tripay ditanggung MERCHANT, tidak dibebankan ke customer
 *   - order.total di DB tidak diubah sama sekali
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../path.php';

header('Content-Type: application/json');

// ── Validasi input ────────────────────────────────────────────────────────────
$raw  = file_get_contents('php://input');
$body = json_decode($raw, true);

$order_code = trim($body['order_code'] ?? '');
$channel    = strtoupper(trim($body['channel'] ?? ''));

$allowed_channels = ['QRIS', 'SHOPEPAY', 'OVO', 'DANA'];

if (!$order_code || !in_array($channel, $allowed_channels, true)) {
    echo json_encode(['success' => false, 'message' => 'Parameter tidak valid.']);
    exit();
}

// ── Ambil order dari DB ───────────────────────────────────────────────────────
try {
    $stmt = $pdo->prepare("SELECT * FROM `order` WHERE code = ?");
    $stmt->execute([$order_code]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit();
}

if (!$order) {
    echo json_encode(['success' => false, 'message' => 'Order tidak ditemukan.']);
    exit();
}

if ((int)$order['status'] === 1) {
    echo json_encode(['success' => false, 'message' => 'Order sudah dibayar.']);
    exit();
}

if (strtotime($order['expired_at']) < time()) {
    echo json_encode(['success' => false, 'message' => 'Order sudah kadaluarsa.']);
    exit();
}

// ── Cek transaksi Tripay yang sudah ada (cegah duplikat) ─────────────────────
try {
    $stmtChk = $pdo->prepare("SELECT * FROM tripay_transaction WHERE order_id = ? AND channel = ?");
    $stmtChk->execute([$order['id'], $channel]);
    $existing = $stmtChk->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $existing = null;
}

if ($existing && !empty($existing['payment_url'])) {
    echo json_encode(['success' => true, 'payment_url' => $existing['payment_url']]);
    exit();
}

// ── Ambil item pesanan ────────────────────────────────────────────────────────
try {
    $stmtItem = $pdo->prepare("SELECT * FROM order_item WHERE order_id = ?");
    $stmtItem->execute([$order['id']]);
    $order_items = $stmtItem->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Gagal ambil item: ' . $e->getMessage()]);
    exit();
}

// ── Ambil gratuity/fee (snapshot dari order_fee) ──────────────────────────────
try {
    $stmtFee = $pdo->prepare("SELECT * FROM order_fee WHERE order_id = ?");
    $stmtFee->execute([$order['id']]);
    $order_fees = $stmtFee->fetchAll(PDO::FETCH_ASSOC);

    // Fallback untuk order lama yang belum punya order_fee
    if (empty($order_fees)) {
        $feeStmt = $pdo->prepare("SELECT * FROM gratuity");
        $feeStmt->execute();
        foreach ($feeStmt->fetchAll(PDO::FETCH_ASSOC) as $f) {
            $amt = (int)$f['type'] === 1
                ? (int) round($order['subtotal'] * ((float)$f['value'] / 100))
                : (int) round((float)$f['value']);
            $order_fees[] = ['name' => $f['name'], 'type' => $f['type'], 'rate' => $f['value'], 'amount' => $amt];
        }
    }
} catch (PDOException $e) {
    $order_fees = [];
}

// ── Bangun payload Tripay ─────────────────────────────────────────────────────
$merchant_ref = $order_code;
$expired_time = strtotime($order['expired_at']);

// Biaya gateway Tripay ditanggung merchant — TIDAK ditambahkan ke amount.
// amount = order.total yang sudah include gratuity (biaya layanan + PPN).
$total_amount = (int) $order['total'];

// Signature: HMAC-SHA256( merchant_code + merchant_ref + total_amount, private_key )
$signature = hash_hmac(
    'sha256',
    TRIPAY_KODE_MERC . $merchant_ref . $total_amount,
    TRIPAY_PRIVATE_KEY
);

// ── Bangun order_items untuk Tripay ──────────────────────────────────────────
// Tripay memvalidasi: SUM(price × quantity) HARUS == amount.
// Karena amount sudah include gratuity, fee juga harus masuk sebagai
// line item tersendiri di sini — tidak mengubah order.total di DB.

$tripay_items = [];

// 1. Item menu
foreach ($order_items as $item) {
    $tripay_items[] = [
        'sku'         => 'MENU-' . $item['menu_id'],
        'name'        => $item['menu_name'],
        'price'       => (int) round($item['subtotal'] / $item['qty']),
        'quantity'    => (int) $item['qty'],
        'product_url' => BASE_URL,
        'image_url'   => BASE_URL . 'assets/images/placeholder.png',
    ];
}

// 2. Gratuity sebagai line item (supaya SUM(items) == amount)
foreach ($order_fees as $fee) {
    if ((int)$fee['amount'] <= 0) continue;
    $suffix = (int)$fee['type'] === 1
        ? ' (' . rtrim(rtrim(number_format((float)$fee['rate'], 2), '0'), '.') . '%)'
        : '';
    $tripay_items[] = [
        'sku'         => 'FEE-' . preg_replace('/\s+/', '_', strtoupper($fee['name'])),
        'name'        => $fee['name'] . $suffix,
        'price'       => (int) $fee['amount'],
        'quantity'    => 1,
        'product_url' => BASE_URL,
        'image_url'   => BASE_URL . 'assets/images/placeholder.png',
    ];
}

// 3. Koreksi selisih pembulatan (jika ada ±1 rupiah)
$items_sum = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $tripay_items));
$diff      = $total_amount - $items_sum;
if ($diff !== 0 && !empty($tripay_items)) {
    $last = count($tripay_items) - 1;
    $tripay_items[$last]['price'] += $diff;
}

// ── Susun payload lengkap ─────────────────────────────────────────────────────
$payload = [
    'method'         => $channel,
    'merchant_ref'   => $merchant_ref,
    'amount'         => $total_amount,
    'customer_name'  => $order['customer_name'],
    'customer_email' => !empty($order['customer_email']) ? $order['customer_email'] : 'customer@order.com',
    'customer_phone' => '08000000000',
    'order_items'    => $tripay_items,
    'callback_url'   => BASE_URL . 'order/server/tripay_callback',
    'return_url'     => BASE_URL . 'order/' . urlencode($order['table_name']) . '/success/' . $order_code,
    'expired_time'   => $expired_time,
    'signature'      => $signature,
];

// ── Hit Tripay API ────────────────────────────────────────────────────────────
$tripay_url = 'https://tripay.co.id/api-sandbox/transaction/create';
// Production: 'https://tripay.co.id/api/transaction/create'

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => $tripay_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer ' . TRIPAY_API_KEY,
        'Content-Type: application/json',
    ],
    CURLOPT_TIMEOUT        => 30,
]);

$response  = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_err  = curl_error($ch);
curl_close($ch);

if ($curl_err) {
    echo json_encode(['success' => false, 'message' => 'cURL error: ' . $curl_err]);
    exit();
}

$result = json_decode($response, true);

if (!isset($result['success']) || !$result['success']) {
    $msg = $result['message'] ?? 'Tripay mengembalikan error.';
    echo json_encode(['success' => false, 'message' => $msg]);
    exit();
}

$tripay_data = $result['data'];
$payment_url = $tripay_data['checkout_url'];
$reference   = $tripay_data['reference'];

// ── Simpan ke tabel tripay_transaction ───────────────────────────────────────
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `tripay_transaction` (
            `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `order_id`    INT UNSIGNED NOT NULL,
            `channel`     VARCHAR(20)  NOT NULL,
            `reference`   VARCHAR(100) NOT NULL,
            `payment_url` TEXT         NOT NULL,
            `status`      VARCHAR(20)  NOT NULL DEFAULT 'UNPAID',
            `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_order_id`  (`order_id`),
            INDEX `idx_reference` (`reference`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    $pdo->prepare("DELETE FROM tripay_transaction WHERE order_id = ? AND channel = ?")
        ->execute([$order['id'], $channel]);

    $pdo->prepare("
        INSERT INTO tripay_transaction (order_id, channel, reference, payment_url, status)
        VALUES (?, ?, ?, ?, 'UNPAID')
    ")->execute([$order['id'], $channel, $reference, $payment_url]);

} catch (PDOException $e) {
    error_log('[Tripay] Gagal simpan transaksi: ' . $e->getMessage());
}

// ── Kembalikan URL ke frontend ────────────────────────────────────────────────
echo json_encode([
    'success'     => true,
    'payment_url' => $payment_url,
    'reference'   => $reference,
]);