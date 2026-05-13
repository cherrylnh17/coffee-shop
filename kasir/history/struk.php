<?php
session_start();
require_once '../../config.php';
require_once '../../path.php';
require_once '../../key.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 1) {
    header("Location: " . BASE_URL . "auth/login");
    exit;
}
 
// ============================================================
//  HELPER ESC/POS
// ============================================================

function fmt($angka) {
    return number_format((float)$angka, 0, ',', '.');
}

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

    // Header
    $data .= $ALIGN_C;
    $data .= $BOLD_ON . $SIZE_2X . "TRAFFA COFFEE" . $SIZE_NORM . $BOLD_OFF . $LF;
    $data .= "Jl. HM Subchan ZE No.3" . $LF;
    $data .= "IG: @traffacoffee" . $LF;
    $data .= $LF;

    // Info transaksi
    $data .= $ALIGN_L;
    $data .= str_repeat('-', 32) . $LF;
    $data .= justify("No :",      $order['code'])                                          . $LF;
    $data .= justify("Tgl :",     date('d-m-Y H:i', strtotime($order['created_at'])))     . $LF;
    $data .= justify("Kasir :",   $order['user_name']     ?? '-')                          . $LF;
    $data .= justify("Meja :",    $order['table_name']    ?? '-')                          . $LF;
    $data .= justify("Pembeli :", $order['customer_name'] ?? '-')                          . $LF;
    $data .= str_repeat('-', 32) . $LF;

    // Item pesanan
    foreach ($order_items as $item) {
        $harga_satuan = $item['qty'] > 0 ? $item['subtotal'] / $item['qty'] : 0;
        $nama  = mb_substr($item['menu_name'], 0, 32);
        $left  = "  " . $item['qty'] . " x " . fmt($harga_satuan);
        $right = fmt($item['subtotal']);

        $data .= $nama . $LF;
        $data .= justify($left, $right) . $LF;

        if (!empty($item['notes'])) {
            $data .= "  *" . mb_substr($item['notes'], 0, 28) . $LF;
        }
    }

    // Total
    $uang_bayar = $order['paid']   ?? $order['total'];
    $kembalian  = $order['change'] ?? 0;
    $metode     = ($order['payment'] == 1) ? 'Kasir' : 'Online';

    $data .= str_repeat('-', 32) . $LF;
    $data .= justify("Subtotal :",    "Rp " . fmt($order['subtotal']))  . $LF;
    $data .= justify("Pajak (12%) :", "Rp " . fmt($order['tax']))       . $LF;
    $data .= str_repeat('-', 32) . $LF;
    $data .= $BOLD_ON;
    $data .= justify("TOTAL :",       "Rp " . fmt($order['total']))     . $LF;
    $data .= $BOLD_OFF;
    $data .= justify("Tunai ($metode) :", "Rp " . fmt($uang_bayar))     . $LF;
    $data .= $BOLD_ON;
    $data .= justify("KEMBALIAN :",   "Rp " . fmt($kembalian))          . $LF;
    $data .= $BOLD_OFF;

    // Footer
    $data .= str_repeat('-', 32) . $LF;
    $data .= $ALIGN_C;
    $data .= $LF;
    $data .= "Terima kasih atas kunjungannya!" . $LF;
    $data .= "Layanan Kritik & Saran:"         . $LF;
    $data .= "Telp: 0856-4195-4719"            . $LF;
    $data .= $LF . $LF . $LF;
    $data .= $CUT;

    return $data;
}


// ============================================================
//  PRINT KE BLUETOOTH THERMAL PRINTER via /dev/rfcomm0
// ============================================================

function printToThermal($escposData) {
    $devicePath = PRINTER_RFCOMM_DEV;

    if (!file_exists($devicePath)) {
        return [
            'success' => false,
            'message' => "Device {$devicePath} tidak ditemukan. Jalankan: sudo rfcomm bind 0 " . PRINTER_BT_MAC . " " . PRINTER_BT_CHANNEL
        ];
    }

    if (!is_writable($devicePath)) {
        return [
            'success' => false,
            'message' => "Tidak ada izin tulis ke {$devicePath}. Jalankan: sudo usermod -aG dialout www-data && sudo chmod 666 {$devicePath}"
        ];
    }

    $fp = @fopen($devicePath, 'wb');
    if (!$fp) {
        return [
            'success' => false,
            'message' => "Gagal membuka {$devicePath}. Pastikan printer Bluetooth menyala dan sudah di-pair."
        ];
    }

    $written = fwrite($fp, $escposData);
    fclose($fp);

    if ($written === false) {
        return ['success' => false, 'message' => "Gagal menulis data ke {$devicePath}."];
    }

    return ['success' => true, 'message' => 'Struk berhasil dicetak.'];
}


// ============================================================
//  MAIN
// ============================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: order");
    exit;
}

$order_id = (int)$_POST['order_id'];

// Ambil data order beserta nama kasir, meja, dan pelanggan
$stmtOrder = $pdo->prepare("
    SELECT o.*,
           u.name AS user_name,
           t.name AS table_name
    FROM `order` o
    LEFT JOIN `user`  u ON u.id = o.user_id
    LEFT JOIN `table` t ON t.id = o.table_id
    WHERE o.id = ?
");
$stmtOrder->execute([$order_id]);
$orderData = $stmtOrder->fetch(PDO::FETCH_ASSOC);

if (!$orderData) {
    $_SESSION['swal_msg'] = [
        'icon'  => 'error',
        'title' => 'Gagal',
        'text'  => 'Data pesanan tidak ditemukan.'
    ];
    header("Location: order");
    exit;
}

// Ambil item pesanan
$stmtItems = $pdo->prepare("
    SELECT oi.*, m.name AS menu_name
    FROM order_item oi
    LEFT JOIN menu m ON m.id = oi.menu_id
    WHERE oi.order_id = ?
");
$stmtItems->execute([$order_id]);
$orderItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

$escpos      = buildEscPos($orderData, $orderItems);
$printResult = printToThermal($escpos);

if ($printResult['success']) {
    $_SESSION['swal_msg'] = [
        'icon'  => 'success',
        'title' => 'Berhasil!',
        'text'  => 'Struk berhasil dicetak oleh ' . ($orderData['user_name'] ?? '-')
    ];
} else {
    $_SESSION['swal_msg'] = [
        'icon'  => 'warning',
        'title' => 'Transaksi Tersimpan',
        'text'  => 'Pesanan selesai, tapi struk GAGAL dicetak. ' . $printResult['message']
    ];
}

header("Location: order");
exit;