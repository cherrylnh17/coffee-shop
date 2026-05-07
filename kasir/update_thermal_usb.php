<?php
session_start();
require_once '../config.php'; 
require_once '../path.php';   

if (!isset($_SESSION['username']) || $_SESSION['role'] != 1) {
    header("Location: " . BASE_URL . "auth/login");
    exit;
}

// ============================================================
//  KONFIGURASI PRINTER THERMAL USB
//
//  Cara cek device path:
//    $ ls /dev/usb/       → biasanya /dev/usb/lp0
//    $ ls /dev/lp*        → alternatif: /dev/lp0
//
//  Pastikan www-data punya izin akses:
//    $ sudo usermod -aG lp www-data
//    $ sudo systemctl restart apache2
//
//  Test manual:
//    $ echo "test" > /dev/usb/lp0
// ============================================================
define('PRINTER_USB_PATH', '/dev/usb/lp0'); // <-- GANTI INI setelah cek dengan: ls /dev/usb/


// ============================================================
//  FUNGSI HELPER ESC/POS
// ============================================================

/**
 * Format angka ke Rupiah (tanpa simbol)
 */
function fmt($angka) {
    return number_format((float)$angka, 0, ',', '.');
}

/**
 * Teks rata kiri-kanan dalam satu baris (kertas 58mm = 32 karakter)
 * Contoh: justify("Subtotal", "Rp 10.000") → "Subtotal     Rp 10.000"
 */
function justify($left, $right, $width = 32) {
    $space = $width - strlen($left) - strlen($right);
    if ($space < 1) $space = 1;
    return $left . str_repeat(' ', $space) . $right;
}

/**
 * Teks rata tengah dalam lebar 32 karakter
 */
function center($text, $width = 32) {
    $len = strlen($text);
    if ($len >= $width) return $text;
    $pad = intval(($width - $len) / 2);
    return str_repeat(' ', $pad) . $text;
}

/**
 * Build raw ESC/POS bytes untuk struk 58mm
 */
function buildEscPos($order, $order_items) {
    $ESC       = "\x1B";
    $GS        = "\x1D";
    $INIT      = $ESC . "@";          // Initialize printer
    $BOLD_ON   = $ESC . "E\x01";      // Bold on
    $BOLD_OFF  = $ESC . "E\x00";      // Bold off
    $ALIGN_L   = $ESC . "a\x00";      // Align left
    $ALIGN_C   = $ESC . "a\x01";      // Align center
    $SIZE_2X   = $GS  . "!\x11";      // Double width + height
    $SIZE_NORM = $GS  . "!\x00";      // Normal size
    $LF        = "\n";                // Line feed
    $CUT       = $GS  . "V\x41\x03"; // Partial cut (feed 3 baris lalu potong)

    $data = "";

    // -- Init --
    $data .= $INIT;

    // -- Header --
    $data .= $ALIGN_C;
    $data .= $BOLD_ON . $SIZE_2X . "TRAFFA COFFEE" . $SIZE_NORM . $BOLD_OFF . $LF;
    $data .= "Jl. Nama Jalan Anda No. 123" . $LF;
    $data .= "Telp: 0812-3456-7890" . $LF;
    $data .= $LF;

    // -- Divider --
    $data .= $ALIGN_L;
    $data .= str_repeat('-', 32) . $LF;

    // -- Info Transaksi --
    $data .= justify("No",    ": " . $order['code'])                                      . $LF;
    $data .= justify("Tgl",   ": " . date('d-m-Y H:i', strtotime($order['created_at']))) . $LF;
    $data .= justify("Kasir", ": " . $order['user_name'])                                 . $LF;
    $data .= justify("Meja",  ": " . $order['table_name'])                                . $LF;
    $data .= justify("Info",  ": " . $order['customer_name'])                             . $LF;

    // -- Divider --
    $data .= str_repeat('-', 32) . $LF;

    // -- Item Pesanan --
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

    // -- Divider --
    $data .= str_repeat('-', 32) . $LF;

    // -- Total Section --
    $uang_bayar = $order['paid']   ?? $order['total'];
    $kembalian  = $order['change'] ?? 0;
    $metode     = ($order['payment'] == 1) ? 'Kasir' : 'Online';

    $data .= justify("Subtotal",        "Rp " . fmt($order['subtotal'])) . $LF;
    $data .= justify("Pajak (12%)",     "Rp " . fmt($order['tax']))      . $LF;
    $data .= str_repeat('-', 32) . $LF;
    $data .= $BOLD_ON;
    $data .= justify("TOTAL",           "Rp " . fmt($order['total']))    . $LF;
    $data .= $BOLD_OFF;
    $data .= justify("Tunai ($metode)", "Rp " . fmt($uang_bayar))        . $LF;
    $data .= $BOLD_ON;
    $data .= justify("KEMBALIAN",       "Rp " . fmt($kembalian))         . $LF;
    $data .= $BOLD_OFF;

    // -- Divider --
    $data .= str_repeat('-', 32) . $LF;

    // -- Footer --
    $data .= $ALIGN_C;
    $data .= $LF;
    $data .= "Terima kasih atas kunjungannya!" . $LF;
    $data .= "Layanan Kritik & Saran:"         . $LF;
    $data .= "IG: @traffacoffee"               . $LF;
    $data .= $LF . $LF . $LF;

    // -- Cut Paper --
    $data .= $CUT;

    return $data;
}

/**
 * Kirim raw ESC/POS bytes ke printer via USB device file
 * Return: ['success' => bool, 'message' => string]
 */
function printToThermal($escposData) {
    $devicePath = PRINTER_USB_PATH;

    // Cek apakah device file ada
    if (!file_exists($devicePath)) {
        return [
            'success' => false,
            'message' => "Device printer tidak ditemukan: $devicePath. Pastikan printer USB sudah terpasang."
        ];
    }

    // Cek izin tulis
    if (!is_writable($devicePath)) {
        return [
            'success' => false,
            'message' => "Tidak ada izin tulis ke $devicePath. Jalankan: sudo usermod -aG lp www-data"
        ];
    }

    // Buka device USB printer seperti file biasa (write-only, binary)
    $fp = @fopen($devicePath, 'wb');

    if (!$fp) {
        return [
            'success' => false,
            'message' => "Gagal membuka device printer: $devicePath"
        ];
    }

    fwrite($fp, $escposData);
    fclose($fp);

    return ['success' => true, 'message' => 'Struk berhasil dicetak.'];
}


// ============================================================
//  MAIN LOGIC
// ============================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id  = $_POST['order_id'];
    $user_id   = $_SESSION['user_id'] ?? null;
    $user_name = $_SESSION['name']    ?? null;

    if (!$user_id) {
        die("Error: Sesi login tidak ditemukan. Silahkan login kembali.");
    }

    // --------------------------------------------------------
    //  AKSI: Selesaikan Pesanan (dengan pembayaran kasir)
    // --------------------------------------------------------
    if (isset($_POST['aksi']) && $_POST['aksi'] == 'selesai') {
        try {
            $paid   = isset($_POST['paid'])  ? (int)$_POST['paid']  : 0;
            $total  = isset($_POST['total']) ? (int)$_POST['total'] : 0;
            $change = $paid - $total;

            $sql  = "UPDATE `order` SET `paid` = ?, `change` = ?, `status` = 1, `payment` = 1, `user_id` = ?, `user_name` = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);

            if ($stmt->execute([$paid, $change, $user_id, $user_name, $order_id])) {

                // Ambil kode order untuk redirect
                $stmtCode = $pdo->prepare("SELECT code FROM `order` WHERE id = ?");
                $stmtCode->execute([$order_id]);
                $orderCode = $stmtCode->fetchColumn();

                // ------------------------------------------------
                //  PRINT OTOMATIS KE PRINTER THERMAL USB
                // ------------------------------------------------
                $stmtOrder = $pdo->prepare("SELECT * FROM `order` WHERE id = ?");
                $stmtOrder->execute([$order_id]);
                $orderData = $stmtOrder->fetch(PDO::FETCH_ASSOC);

                $stmtItems = $pdo->prepare("SELECT * FROM order_item WHERE order_id = ?");
                $stmtItems->execute([$order_id]);
                $orderItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

                $escpos      = buildEscPos($orderData, $orderItems);
                $printResult = printToThermal($escpos);

                // Transaksi TETAP tersimpan meski print gagal
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

                header("Location: index.php?code=" . htmlspecialchars($orderCode));
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

    // --------------------------------------------------------
    //  AKSI LAIN: Update status saja (tanpa pembayaran)
    // --------------------------------------------------------
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