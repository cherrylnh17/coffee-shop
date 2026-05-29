<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../path.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 1) {
    header("Location: " . BASE_URL . "auth/login");
    exit;
}

// ============================================================
//  HELPER ESC/POS
// ============================================================

function fmt($angka) { return number_format((float)$angka, 0, ',', '.'); }
function justify($left, $right, $width = 32) {
    $space = $width - strlen($left) - strlen($right);
    if ($space < 1) $space = 1;
    return $left . str_repeat(' ', $space) . $right;
}
function center($text, $width = 32) {
    $len = strlen($text);
    if ($len >= $width) return $text;
    $pad = intval(($width - $len) / 2);
    return str_repeat(' ', $pad) . $text;
}

function buildEscPos($order, $order_items) {
    $ESC       = "\x1B";
    $GS        = "\x1D";
    $INIT      = $ESC . "@";
    $BOLD_ON   = $ESC . "E\x01";
    $BOLD_OFF  = $ESC . "E\x00";
    $ALIGN_L   = $ESC . "a\x00";
    $ALIGN_C   = $ESC . "a\x01";
    $SIZE_2X   = $GS  . "!\x11";
    $SIZE_NORM = $GS  . "!\x00";
    $LF        = "\n";
    $CUT       = $GS  . "V\x41\x03";

    $data  = $INIT;
    $data .= $ALIGN_C;
    $data .= $BOLD_ON . $SIZE_2X . "TRAFFA COFFEE" . $SIZE_NORM . $BOLD_OFF . $LF;
    $data .= "Jl. HM Subchan ZE No.3" . $LF;
    $data .= "IG: @traffacoffee" . $LF . $LF;

    $data .= $ALIGN_L;
    $data .= str_repeat('-', 32) . $LF;
    $data .= justify("No :",      $order['code']) . $LF;
    $data .= justify("Tgl :",     date('d-m-Y H:i', strtotime($order['created_at']))) . $LF;
    $data .= justify("Kasir :",   $order['user_name'] ?? '-') . $LF;
    $data .= str_repeat('-', 32) . $LF;

    foreach ($order_items as $item) {
        $harga_satuan = $item['qty'] > 0 ? $item['subtotal'] / $item['qty'] : 0;
        $nama  = mb_substr($item['menu_name'], 0, 32);
        $left  = "  " . $item['qty'] . " x " . fmt($harga_satuan);
        $right = fmt($item['subtotal']);

        $data .= $nama . $LF;
        $data .= justify($left, $right) . $LF;
    }

    $uang_bayar = $order['paid'] ?? $order['total'];
    $kembalian  = $order['change'] ?? 0;
    
    $data .= str_repeat('-', 32) . $LF;
    $data .= justify("Subtotal :", "Rp " . fmt($order['subtotal'])) . $LF;
    $data .= $BOLD_ON;
    $data .= justify("TOTAL :",    "Rp " . fmt($order['total'])) . $LF;
    $data .= $BOLD_OFF;
    $data .= justify("Tunai :",    "Rp " . fmt($uang_bayar)) . $LF;
    $data .= justify("KEMBALI :",  "Rp " . fmt($kembalian)) . $LF;

    $data .= str_repeat('-', 32) . $LF;
    $data .= $ALIGN_C . $LF;
    $data .= "Terima kasih atas kunjungannya!" . $LF;
    $data .= $LF . $LF . $LF . $CUT;

    return $data;
}

// ============================================================
//  MAIN
// ============================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: order");
    exit;
}

$order_id = (int)$_POST['order_id'];

$stmtOrder = $pdo->prepare("
    SELECT o.*, u.name AS user_name
    FROM `order` o
    LEFT JOIN `user` u ON u.id = o.user_id
    WHERE o.id = ?
");
$stmtOrder->execute([$order_id]);
$orderData = $stmtOrder->fetch(PDO::FETCH_ASSOC);

if (!$orderData) {
    $_SESSION['swal_msg'] = ['icon' => 'error', 'title' => 'Gagal', 'text' => 'Data tidak ditemukan.'];
    header("Location: order");
    exit;
}

$stmtItems = $pdo->prepare("
    SELECT oi.*, m.name AS menu_name
    FROM order_item oi
    LEFT JOIN menu m ON m.id = oi.menu_id
    WHERE oi.order_id = ?
");
$stmtItems->execute([$order_id]);
$orderItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

// 1. Format teks struk
$escpos = buildEscPos($orderData, $orderItems);

// 2. Simpan teks struk ke Session (di-encode base64 agar aman dari karakter aneh saat dikirim)
$_SESSION['print_payload'] = base64_encode($escpos);

// 3. Beri notifikasi sukses dan kembali ke halaman order
$_SESSION['swal_msg'] = [
    'icon'  => 'success',
    'title' => 'Disimpan!',
    'text'  => 'Pesanan berhasil disimpan. Mengirim ke printer...'
];

header("Location: order");
exit;