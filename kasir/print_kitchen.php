<?php
session_start();
require_once '../config.php';
require_once '../path.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 1) {
    header("Location: " . BASE_URL . "auth/login");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index");
    exit;
}

// ============================================================
//  KONFIGURASI PRINTER
// ============================================================
define('PRINTER_BT_MAC',     $_ENV['PRINTER_BT_MAC']);
define('PRINTER_BT_CHANNEL', $_ENV['PRINTER_BT_CHANNEL']);
define('PRINTER_RFCOMM_DEV', $_ENV['PRINTER_RFCOMM_DEV']);
define('PRINTER_TIMEOUT',    $_ENV['PRINTER_TIMEOUT']);

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
    $data .= "MEJA " . strtoupper($order['table_name']);
    $data .= $SIZE_NORM . $BOLD_OFF . $LF;

    // -- Info singkat --
    $data .= center("No: " . $order['code']) . $LF;
    $data .= center(date('d-m-Y H:i', strtotime($order['created_at']))) . $LF;

    if (!empty($order['customer_name'])) {
        $data .= center("Pembeli: " . $order['customer_name']) . $LF;
    }

    $data .= $ALIGN_L;
    $data .= str_repeat('=', 32) . $LF;

    // -- List Pesanan (tanpa harga) --
    $no = 1;
    foreach ($order_items as $item) {
        $nama = mb_substr($item['menu_name'], 0, 30);
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
//  FUNGSI PRINT
// ============================================================

function printViaRfcomm($escposData) {
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
            'message' => "Tidak ada izin tulis ke {$devicePath}."
        ];
    }

    $fp = @fopen($devicePath, 'wb');
    if (!$fp) {
        return [
            'success' => false,
            'message' => "Gagal membuka device {$devicePath}."
        ];
    }

    $written = fwrite($fp, $escposData);
    fclose($fp);

    if ($written === false) {
        return ['success' => false, 'message' => "Gagal menulis data ke {$devicePath}."];
    }

    return ['success' => true, 'message' => 'Tiket dapur berhasil dicetak via rfcomm.'];
}

function printViaShell($escposData) {
    $mac     = PRINTER_BT_MAC;
    $channel = PRINTER_BT_CHANNEL;
    $tmpFile = tempnam(sys_get_temp_dir(), 'escpos_') . '.bin';

    if (file_put_contents($tmpFile, $escposData) === false) {
        return ['success' => false, 'message' => 'Gagal membuat file temporary ESC/POS.'];
    }

    $cmd    = "rfcomm connect /dev/rfcomm1 {$mac} {$channel} < /dev/null & sleep 1 && cat " . escapeshellarg($tmpFile) . " > /dev/rfcomm1 ; rfcomm release /dev/rfcomm1 2>&1";
    $output = shell_exec($cmd);

    unlink($tmpFile);

    if (strpos($output, "Can't") !== false || strpos($output, 'error') !== false) {
        return [
            'success' => false,
            'message' => "Gagal koneksi Bluetooth: " . trim($output)
        ];
    }

    return ['success' => true, 'message' => 'Tiket dapur berhasil dicetak via shell.'];
}

function printToThermal($escposData) {
    $result = printViaRfcomm($escposData);

    if ($result['success']) return $result;

    // Fallback ke shell jika device belum di-bind
    if (strpos($result['message'], 'tidak ditemukan') !== false) {
        return printViaShell($escposData);
    }

    return $result;
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
    $stmtOrder = $pdo->prepare("SELECT * FROM `order` WHERE id = ?");
    $stmtOrder->execute([$order_id]);
    $orderData = $stmtOrder->fetch(PDO::FETCH_ASSOC);

    $stmtItems = $pdo->prepare("SELECT * FROM order_item WHERE order_id = ?");
    $stmtItems->execute([$order_id]);
    $orderItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $_SESSION['swal_msg'] = [
        'icon'  => 'error',
        'title' => 'Error DB',
        'text'  => $e->getMessage()
    ];
    header("Location: index");
    exit;
}

// Build & print
$escpos      = buildKitchenTicket($orderData, $orderItems);
$printResult = printToThermal($escpos);

if ($printResult['success']) {
    $_SESSION['swal_msg'] = [
        'icon'  => 'success',
        'title' => 'Berhasil!',
        'text'  => 'Tiket dapur berhasil dicetak untuk Meja ' . $orderData['table_name']
    ];
} else {
    $_SESSION['swal_msg'] = [
        'icon'  => 'warning',
        'title' => 'Gagal Print',
        'text'  => 'Tiket GAGAL dicetak. ' . $printResult['message']
    ];
}

header("Location: index");
exit;
?>