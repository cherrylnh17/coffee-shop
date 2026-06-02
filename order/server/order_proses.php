<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../path.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        // generate kode unik
        $isUnique = false;
        $order_code = '';
        while (!$isUnique) {
            $characters = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
            $randomString = '';
            for ($i = 0; $i < 8; $i++) {
                $randomString .= $characters[rand(0, strlen($characters) - 1)];
            }
            $order_code = "ORD-" . $randomString;

            $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM `order` WHERE code = ?");
            $stmtCheck->execute([$order_code]);
            if ($stmtCheck->fetchColumn() == 0) $isUnique = true;
        }

        $customer_name  = trim($_POST['customer_name']);
        $customer_email = $_POST['customer_email'];
        $payment_type   = $_POST['payment'];
        $table_code     = $_POST['table_name'];
        $cart_items     = json_decode($_POST['cart_data'], true);

        if (empty($cart_items)) throw new Exception("Keranjang kosong");

        if (empty($customer_name)) {
            throw new Exception("Nama pelanggan wajib diisi!");
        }

        if (empty($payment_type)) {
            throw new Exception("Metode pembayaran wajib diisi!");
        }
        // cari id meja
        $id_table = null;
        $name_table = null;
        if ($table_code) {
            $stmt = $pdo->prepare("SELECT id, name FROM `table` WHERE name = ?");
            $stmt->execute([$table_code]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $id_table = $row['id'];
                $name_table = $row['name'];
            }
        }


        // hitung total dan buat detail
        $subtotal = 0;
        $total_qty = 0;
        $summary_list = [];
        foreach ($cart_items as $item) {
            $subtotal += ($item['price'] * $item['qty']);
            $total_qty += $item['qty'];
            $summary_list[] = $item['qty'] . " " . $item['name'];
        }

        $detail_summary = implode(", ", $summary_list);

        // Ambil fee dari database
        $feeStmt = $pdo->prepare("SELECT * FROM fee_setting");
        $feeStmt->execute();
        $fee_settings = $feeStmt->fetchAll(PDO::FETCH_ASSOC);

        // Hitung total fee (persen atau fixed)
        $tax = 0;
        $fee_lines = []; // simpan untuk di-insert ke order_fee
        foreach ($fee_settings as $fee) {
            if ((int)$fee['type'] === 1) {
                $amount = (int) round($subtotal * ((float)$fee['value'] / 100));
            } else {
                $amount = (int) round((float)$fee['value']);
            }
            $tax += $amount;
            $fee_lines[] = [
                'name'   => $fee['name'],
                'type'   => $fee['type'],
                'rate'   => $fee['value'],
                'amount' => $amount,
            ];
        }

        $total_bayar = $subtotal + $tax;
        $created_at = date('Y-m-d H:i:s');
        $expired_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        // insert tabel order
        $sqlOrder = "INSERT INTO `order` (
            code, table_id, table_name, customer_name, customer_email,
            qty, subtotal, tax, total, payment, detail, status, paid, `change`,
            created_at, expired_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmtOrder = $pdo->prepare($sqlOrder);
        $stmtOrder->execute([
            $order_code,
            $id_table,
            $name_table,
            $customer_name,
            $customer_email,
            $total_qty,
            $subtotal,
            $tax,
            $total_bayar,
            $payment_type,
            $detail_summary,
            2,
            0,
            0,
            $created_at,
            $expired_at
        ]);

        // ambil id yg baru di insert
        $order_id = $pdo->lastInsertId();

        // insert tabel order item
        $sqlItem = "INSERT INTO order_item (
            order_id, 
            menu_id, 
            menu_name, 
            qty, 
            subtotal, 
            notes
        ) VALUES (?, ?, ?, ?, ?, ?)";

        $stmtItem = $pdo->prepare($sqlItem);

        foreach ($cart_items as $item) {
            // Hitung subtotal per item (price * qty)
            $item_subtotal = $item['price'] * $item['qty'];

            $stmtItem->execute([
                $order_id,
                $item['id'],
                $item['name'],
                $item['qty'],
                $item_subtotal,
                $item['note'] ?? ''
            ]);
        }

        // insert rincian fee ke order_fee (snapshot saat order dibuat)
        $sqlFee = "INSERT INTO order_fee (order_id, name, type, rate, amount) VALUES (?, ?, ?, ?, ?)";
        $stmtFee = $pdo->prepare($sqlFee);
        foreach ($fee_lines as $f) {
            $stmtFee->execute([
                $order_id,
                $f['name'],
                $f['type'],
                $f['rate'],
                $f['amount'],
            ]);
        }

        $pdo->commit();
        header("Location: " . BASE_URL . "order/" . $table_code . "/payment/" . $order_code);
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $table_code = $_POST['table_name'];
        header("Location: " . BASE_URL . "order/" . $table_code . "/checkout" . "&m=" . urlencode($e->getMessage()));
        exit();
    }
}