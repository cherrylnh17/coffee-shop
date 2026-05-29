<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../path.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 1) {
    header("Location: " . BASE_URL . "auth/login");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index");
    exit;
}

// ============================================================
//  HELPER ESC/POS
// ============================================================

function center($text, $width = 32) {
    $len = strlen($text);
    if ($len >= $width) return $text;
    $pad = intval(($width - $len) / 2);
    return str_repeat(' ', $pad) . $text;
}

/**
 * Build kitchen ticket — hanya meja + list pesanan + catatan, tanpa harga
 */
function buildKitchenTicket($order, $order_items) {
    $ESC       = "\x1B";
    $GS        = "\x1D";
    $INIT      = $ESC . "@";
    $BOLD_ON   = $ESC . "E\x01";
    $BOLD_OFF  = $ESC . "E\x00";
    $ALIGN_C   = $ESC . "a\x01";
    $ALIGN_L   = $ESC . "a\x00";
    $SIZE_2X   = $GS  . "!\x11";
    $SIZE_NORM = $GS  . "!\x00";
    $LF        = "\n";
    $CUT       = $GS  . "V\x41\x03";

    $data = $INIT;

    // -- Header: Nomor Meja besar --
    $data .= $ALIGN_C;
    $data .= $BOLD_ON . $SIZE_2X;
    $data .= "MEJA " . strtoupper($order['table_name'] ?? '-');
    $data .= $SIZE_NORM . $BOLD_OFF . $LF;

    // -- Info singkat --
    $data .= center("No: " . ($order['code'] ?? '-')) . $LF;
    $data .= center(date('d-m-Y H:i', strtotime($order['created_at'] ?? 'now'))) . $LF;

    if (!empty($order['customer_name'])) {
        $data .= center("Pembeli: " . $order['customer_name']) . $LF;
    }

    $data .= $ALIGN_L;
    $data .= str_repeat('=', 32) . $LF;

    // -- List Pesanan (tanpa harga) --
    $no = 1;
    foreach ($order_items as $item) {
        $nama = mb_substr($item['menu_name'] ?? '-', 0, 30);
        $data .= $BOLD_ON;
        $data .= $no . ". [" . $item['qty'] . "x] " . $nama . $LF;
        $data .= $BOLD_OFF;

        if (!empty($item['notes'])) {
            $data .= "   >> " . mb_substr($item['notes'], 0, 26) . $LF;
        }
        $no++;
    }

    $data .= str_repeat('=', 32) . $LF;

    // -- Footer --
    $data .= $ALIGN_C;
    $data .= $LF;
    $data .= "** SEGERA DIPROSES **" . $LF;
    $data .= $LF . $LF . $LF;
    $data .= $CUT;

    return $data;
}


// ============================================================
//  MAIN
// ============================================================

$order_id   = $_POST['order_id']   ?? null;
$order_code = $_POST['order_code'] ?? '';

if (!$order_id) {
    header("Location: index");
    exit;
}

try {
    // Ambil data order
    $stmtOrder = $pdo->prepare("
        SELECT o.*, t.name AS table_name 
        FROM `order` o
        LEFT JOIN `table` t ON t.id = o.table_id 
        WHERE o.id = ?
    ");
    $stmtOrder->execute([$order_id]);
    $orderData = $stmtOrder->fetch(PDO::FETCH_ASSOC);

    if (!$orderData) {
        throw new Exception("Data order tidak ditemukan.");
    }

    // Ambil data item (join dengan menu untuk dapatkan nama)
    $stmtItems = $pdo->prepare("
        SELECT oi.*, m.name AS menu_name 
        FROM order_item oi
        LEFT JOIN menu m ON m.id = oi.menu_id 
        WHERE oi.order_id = ?
    ");
    $stmtItems->execute([$order_id]);
    $orderItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

    // 1. Format teks tiket dapur
    $escpos = buildKitchenTicket($orderData, $orderItems);

    // 2. Simpan ke sesi agar di-broadcast oleh halaman tujuan
    $_SESSION['print_payload'] = base64_encode($escpos);

    $_SESSION['swal_msg'] = [
        'icon'  => 'success',
        'title' => 'Mengirim...',
        'text'  => 'Tiket dapur sedang dikirim ke printer...'
    ];

} catch (Exception $e) {
    $_SESSION['swal_msg'] = [
        'icon'  => 'error',
        'title' => 'Gagal Print',
        'text'  => $e->getMessage()
    ];
}

header("Location: index");
exit;
?>