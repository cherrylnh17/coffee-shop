<?php
/**
 * tripay_callback.php
 * POST /order/server/tripay_callback
 *
 * Dipanggil otomatis oleh server Tripay setelah pembayaran selesai.
 * Tidak boleh diakses langsung oleh browser/user.
 *
 * Docs: https://tripay.co.id/simulator/merchant/callback
 */

require_once __DIR__ . '/../../config.php';   // TRIPAY_PRIVATE_KEY, BASE_URL, $pdo
require_once __DIR__ . '/../../path.php';

// Hanya terima POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method Not Allowed');
}

// ── Baca & decode payload ─────────────────────────────────────────────────────
$raw     = file_get_contents('php://input');
$payload = json_decode($raw, true);

if (!$payload) {
    http_response_code(400);
    die('Invalid JSON');
}

// ── Verifikasi signature dari header ─────────────────────────────────────────
// Tripay mengirim header: X-Callback-Signature
$incoming_sig = $_SERVER['HTTP_X_CALLBACK_SIGNATURE'] ?? '';

$expected_sig = hash_hmac('sha256', $raw, TRIPAY_PRIVATE_KEY);

if (!hash_equals($expected_sig, $incoming_sig)) {
    http_response_code(401);
    die('Invalid signature');
}

// ── Cek event type ────────────────────────────────────────────────────────────
// Tripay mengirim header: X-Callback-Event (payment_status)
$event = $_SERVER['HTTP_X_CALLBACK_EVENT'] ?? '';
if ($event !== 'payment_status') {
    // Event lain (misal: payment_reminder) — abaikan
    http_response_code(200);
    die('Event ignored');
}

// ── Ambil data penting dari payload ──────────────────────────────────────────
$reference    = $payload['reference']    ?? '';   // referensi Tripay
$merchant_ref = $payload['merchant_ref'] ?? '';   // = order_code kita
$status       = $payload['status']       ?? '';   // PAID | FAILED | EXPIRED | REFUND
$paid_amount  = $payload['total_amount'] ?? 0;

if (!$reference || !$merchant_ref) {
    http_response_code(400);
    die('Missing reference');
}

// ── Cari order di DB ──────────────────────────────────────────────────────────
try {
    $stmt = $pdo->prepare("SELECT * FROM `order` WHERE code = ?");
    $stmt->execute([$merchant_ref]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    die('DB error: ' . $e->getMessage());
}

if (!$order) {
    http_response_code(404);
    die('Order not found');
}

// Jika sudah PAID sebelumnya, kembalikan 200 saja (idempoten)
if ((int)$order['status'] === 1 && $status === 'PAID') {
    echo json_encode(['success' => true, 'message' => 'Already paid']);
    exit();
}

// ── Update status berdasarkan hasil Tripay ────────────────────────────────────
try {
    if ($status === 'PAID') {
        // Tandai order sebagai lunas (status = 1)
        $pdo->prepare("UPDATE `order` SET status = 1, updated_at = NOW() WHERE id = ?")
            ->execute([$order['id']]);

        // Update tripay_transaction
        $pdo->prepare("
            UPDATE tripay_transaction
            SET status = 'PAID', updated_at = NOW()
            WHERE reference = ?
        ")->execute([$reference]);

        // Opsional: log ke tabel payment_log
        // (Tambahkan tabel ini jika ingin audit trail lebih lengkap)
        // $pdo->prepare("INSERT INTO payment_log ...")->execute([...]);

    } elseif (in_array($status, ['FAILED', 'EXPIRED', 'REFUND'], true)) {
        // Update tripay_transaction saja, order dibiarkan (kasir bisa konfirmasi manual)
        $pdo->prepare("
            UPDATE tripay_transaction
            SET status = ?, updated_at = NOW()
            WHERE reference = ?
        ")->execute([$status, $reference]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    die('DB update error: ' . $e->getMessage());
}

// ── Respons 200 wajib dikembalikan ke Tripay ─────────────────────────────────
http_response_code(200);
echo json_encode(['success' => true, 'status' => $status]);