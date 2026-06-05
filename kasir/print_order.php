<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../path.php';

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

function buildEscPos($order, $order_items, $order_fees) {
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
        $harga_satuan = $item['qty'] > 0 ? $item['subtotal'] / $item['qty'] : 0; // Mencegah division by zero
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
    foreach ($order_fees as $fee) {
        $suffix = (int)$fee['type'] === 1
            ? ' (' . rtrim(rtrim(number_format((float)$fee['rate'], 2), '0'), '.') . '%)'
            : '';
        $label = mb_substr($fee['name'] . $suffix . ' :', 0, 16);
        $data .= justify($label, "Rp " . fmt($fee['amount'])) . $LF;
    }
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


// ============================================================
//  MAIN POST
// ============================================================

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

                $stmtItems = $pdo->prepare("SELECT oi.*, m.name AS menu_name FROM order_item oi LEFT JOIN menu m ON m.id = oi.menu_id WHERE oi.order_id = ?");
                $stmtItems->execute([$order_id]);
                $orderItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

                // ── 1. Update kolom sold di tabel menu ──────────────────
                $stmtSold = $pdo->prepare("UPDATE menu SET sold = sold + ? WHERE id = ?");
                foreach ($orderItems as $item) {
                    if (!empty($item['menu_id'])) {
                        $stmtSold->execute([$item['qty'], $item['menu_id']]);
                    }
                }

                // ── 2. Insert ke report untuk laporan & export ────
                $stmtReport = $pdo->prepare("
                    INSERT INTO report
                        (order_id, menu_id, menu_name, qty, subtotal, sold_at)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $soldAt = $orderData['created_at'];

                foreach ($orderItems as $item) {
                    if (!empty($item['menu_id'])) {
                        $stmtReport->execute([
                            $order_id,
                            $item['menu_id'],
                            $item['menu_name'],
                            $item['qty'],
                            $item['subtotal'],
                            $soldAt
                        ]);
                    }
                }

                // Ambil rincian fee snapshot
                $stmtFee = $pdo->prepare("SELECT * FROM order_fee WHERE order_id = ?");
                $stmtFee->execute([$order_id]);
                $orderFees = $stmtFee->fetchAll(PDO::FETCH_ASSOC);

                // Fallback order lama sebelum migrasi
                if (empty($orderFees)) {
                    $feeStmt = $pdo->prepare("SELECT * FROM fee_setting");
                    $feeStmt->execute();
                    foreach ($feeStmt->fetchAll(PDO::FETCH_ASSOC) as $f) {
                        $amt = (int)$f['type'] === 1
                            ? (int) round($orderData['subtotal'] * ((float)$f['value'] / 100))
                            : (int) round((float)$f['value']);
                        $orderFees[] = ['name' => $f['name'], 'type' => $f['type'], 'rate' => $f['value'], 'amount' => $amt];
                    }
                }

                // 1. Format teks struk
                $escpos = buildEscPos($orderData, $orderItems, $orderFees);

                // 2. Simpan teks struk ke Session untuk dilempar ke BroadcastChannel
                $_SESSION['print_payload'] = base64_encode($escpos);

                // 3. Set notifikasi sukses
                $_SESSION['swal_msg'] = [
                    'icon'  => 'success',
                    'title' => 'Berhasil!',
                    'text'  => 'Pesanan diselesaikan & struk sedang dikirim ke printer...'
                ];

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