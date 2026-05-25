<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../path.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 1) {
    header("Location: " . BASE_URL . "auth/login");
    exit;
}

define('PRINTER_BT_MAC',     $_ENV['PRINTER_BT_MAC']); 
define('PRINTER_BT_CHANNEL', $_ENV['PRINTER_BT_CHANNEL']);           
define('PRINTER_RFCOMM_DEV', $_ENV['PRINTER_RFCOMM_DEV']);      
define('PRINTER_TIMEOUT',    $_ENV['PRINTER_TIMEOUT']);               


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

    $data = "";

    $data .= $INIT;

    $data .= $ALIGN_C;
    $data .= $BOLD_ON . $SIZE_2X . "TRAFFA COFFEE" . $SIZE_NORM . $BOLD_OFF . $LF;
    $data .= "Jl. HM Subchan ZE No.3" . $LF;
    $data .= "IG: @traffacoffee" . $LF;
    $data .= $LF;

    $data .= $ALIGN_L;
    $data .= str_repeat('-', 32) . $LF;

    $data .= justify("No :" , "" .$order['code']  )                                . $LF;
    $data .= justify("Tgl :" , ""  . date('d-m-Y H:i', strtotime($order['created_at']))) . $LF;
    $data .= justify("Kasir :" , "" . $order['user_name'])                                 . $LF;
    $data .= justify("Meja :", "" . $order['table_name'] )                              . $LF;
    $data .= justify("Pembeli :", "" . $order['customer_name'] )                           . $LF;

    $data .= str_repeat('-', 32) . $LF;

    foreach ($order_items as $item) {
        $harga_satuan = $item['subtotal'] / $item['qty'];
        $nama  = mb_substr($item['menu_name'], 0, 32);
        $left  = "  " . $item['qty'] . " x " . fmt($harga_satuan);
        $right = fmt($item['subtotal']);

        $data .= $nama . $LF;
        $data .= justify($left, $right) . $LF;

        if (!empty($item['notes'])) {
            $data .= "  *" . mb_substr($item['notes'], 0, 28) . $LF;
        }
    }

    $data .= str_repeat('-', 32) . $LF;

    $uang_bayar = $order['paid']   ?? $order['total'];
    $kembalian  = $order['change'] ?? 0;
    $metode     = ($order['payment'] == 1) ? 'Kasir' : 'Online';

    $data .= justify("Subtotal :",        "Rp " . fmt($order['subtotal'])) . $LF;
    $data .= justify("Pajak (12%) :",     "Rp " . fmt($order['tax']))      . $LF;
    $data .= str_repeat('-', 32) . $LF;
    $data .= $BOLD_ON;
    $data .= justify("TOTAL :",           "Rp " . fmt($order['total']))    . $LF;
    $data .= $BOLD_OFF;
    $data .= justify("Tunai ($metode) :", "Rp " . fmt($uang_bayar))        . $LF;
    $data .= $BOLD_ON;
    $data .= justify("KEMBALIAN :",       "Rp " . fmt($kembalian))         . $LF;
    $data .= $BOLD_OFF;

    $data .= str_repeat('-', 32) . $LF;

    $data .= $ALIGN_C;
    $data .= $LF;
    $data .= "Terima kasih atas kunjungannya!" . $LF;
    $data .= "Layanan Kritik & Saran:"         . $LF;
    $data .= "Telp: 0856-4195-4719"               . $LF;
    $data .= $LF . $LF . $LF;

    $data .= $CUT;

    return $data;
}

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
            'message' => "Tidak ada izin tulis ke {$devicePath}. Jalankan: sudo usermod -aG dialout www-data && sudo chmod 666 {$devicePath}"
        ];
    }

    $fp = @fopen($devicePath, 'wb');
    if (!$fp) {
        return [
            'success' => false,
            'message' => "Gagal membuka device {$devicePath}. Cek apakah printer Bluetooth menyala dan sudah di-pair."
        ];
    }

    $written = fwrite($fp, $escposData);
    fclose($fp);

    if ($written === false) {
        return ['success' => false, 'message' => "Gagal menulis data ke {$devicePath}."];
    }

    return ['success' => true, 'message' => 'Struk berhasil dicetak via rfcomm.'];
}

function printViaShell($escposData) {
    $mac     = PRINTER_BT_MAC;
    $channel = PRINTER_BT_CHANNEL;
    $tmpFile = tempnam(sys_get_temp_dir(), 'escpos_') . '.bin';

    if (file_put_contents($tmpFile, $escposData) === false) {
        return ['success' => false, 'message' => 'Gagal membuat file temporary ESC/POS.'];
    }

    $cmd = "rfcomm connect /dev/rfcomm1 {$mac} {$channel} < /dev/null & sleep 1 && cat " . escapeshellarg($tmpFile) . " > /dev/rfcomm1 ; rfcomm release /dev/rfcomm1 2>&1";
    $output = shell_exec($cmd);

    unlink($tmpFile);

    if (strpos($output, 'Can\'t') !== false || strpos($output, 'error') !== false) {
        return [
            'success' => false,
            'message' => "Gagal koneksi Bluetooth: " . trim($output) . ". Pastikan printer menyala & sudah di-pair."
        ];
    }

    return ['success' => true, 'message' => 'Struk berhasil dicetak via rfcomm shell.'];
}

function printToThermal($escposData) {
    $result = printViaRfcomm($escposData);

    if ($result['success']) {
        return $result;
    }

    if (strpos($result['message'], 'tidak ditemukan') !== false) {
        return printViaShell($escposData);
    }

    return $result;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id  = $_POST['order_id'];
    $user_id   = $_SESSION['user_id'] ?? null;
    $user_name = $_SESSION['name']    ?? null;

    if (!$user_id) {
        die("Error: Sesi login tidak ditemukan. Silahkan login kembali.");
    }

    if (isset($_POST['aksi']) && $_POST['aksi'] == 'selesai') {
        try {
            $paid   = isset($_POST['paid'])  ? (int)$_POST['paid']  : 0;
            $total  = isset($_POST['total']) ? (int)$_POST['total'] : 0;
            $change = $paid - $total;

            $sql  = "UPDATE `order` SET `paid` = ?, `change` = ?, `status` = 1, `payment` = 1, `user_id` = ?, `user_name` = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);

            if ($stmt->execute([$paid, $change, $user_id, $user_name, $order_id])) {

                $stmtCode = $pdo->prepare("SELECT code FROM `order` WHERE id = ?");
                $stmtCode->execute([$order_id]);
                $orderCode = $stmtCode->fetchColumn();

                $stmtOrder = $pdo->prepare("SELECT * FROM `order` WHERE id = ?");
                $stmtOrder->execute([$order_id]);
                $orderData = $stmtOrder->fetch(PDO::FETCH_ASSOC);

                $stmtItems = $pdo->prepare("SELECT * FROM order_item WHERE order_id = ?");
                $stmtItems->execute([$order_id]);
                $orderItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

                $escpos      = buildEscPos($orderData, $orderItems);
                $printResult = printToThermal($escpos);

                if ($printResult['success']) {
                    $_SESSION['swal_msg'] = [
                        'icon'  => 'success',
                        'title' => 'Berhasil!',
                        'text'  => 'Pesanan diselesaikan & struk dicetak oleh ' . $user_name
                    ];
                } else {
                    $_SESSION['swal_msg'] = [
                        'icon'  => 'warning',
                        'title' => 'Transaksi Tersimpan',
                        'text'  => 'Pesanan diselesaikan, tapi struk GAGAL dicetak. ' . $printResult['message']
                    ];
                }

                header("Location: check_kitchen?code=" . htmlspecialchars($orderCode));
                exit;
            }

        } catch (PDOException $e) {
            $_SESSION['swal_msg'] = [
                'icon'  => 'error',
                'title' => 'Gagal!',
                'text'  => 'Gagal menyimpan ke database! Error: ' . $e->getMessage()
            ];
            echo "<script>window.history.back();</script>";
            exit;
        }

    } else {
        try {
            $sql  = "UPDATE `order` SET status = 1, user_id = ?, user_name = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id, $user_name, $order_id]);

            $_SESSION['swal_msg'] = [
                'icon'  => 'success',
                'title' => 'Berhasil!',
                'text'  => 'Status Pesanan Berhasil Diperbarui oleh ' . $user_name
            ];

            header("Location: index");
            exit;

        } catch (PDOException $e) {
            $_SESSION['swal_msg'] = [
                'icon'  => 'error',
                'title' => 'Gagal!',
                'text'  => 'Gagal memperbarui status: ' . $e->getMessage()
            ];
            header("Location: index");
            exit;
        }
    }

} else {
    header("Location: index.php");
    exit;
}
?>