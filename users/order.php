<?php
include '../config.php';
var_dump($_SERVER['REQUEST_METHOD']);
var_dump($_POST);

function generateOrderCode($length = 10) {
    $characters = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $code;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        // --- LOGIC GENERATE CODE UNIK ---
        $isUnique = false;
        $order_code = '';

        while (!$isUnique) {
            $order_code = "TRX-" . generateOrderCode(8); // Hasilnya TRX-ABC12345 (12 Karakter)
            
            // Cek ke database apakah code sudah ada
            $checkSql = "SELECT COUNT(*) FROM `order` WHERE code = ?";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->execute([$order_code]);
            if ($checkStmt->fetchColumn() == 0) {
                $isUnique = true;
            }
        }
        // --------------------------------

        // 1. Tangkap data POST (Sama seperti sebelumnya)
        $customer_name  = $_POST['customer_name'] ?? 'Guest';
        $customer_email = $_POST['customer_email'] ?? '';
        $payment_type   = $_POST['payment'];
        $table_code     = $_POST['table_code'];
        $cart_items     = json_decode($_POST['cart_data'], true);

        if (empty($cart_items)) throw new Exception("Keranjang kosong");

        // Ambil data meja
        $id_table = null; $name_table = null;
        if ($table_code) {
            $stmt = $pdo->prepare("SELECT id, name FROM `table` WHERE name = ?");
            $stmt->execute([$table_code]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) { $id_table = $row['id']; $name_table = $row['name']; }
        }

        // 2. Hitung Total & Detail
        $subtotal = 0; $total_qty = 0; $detail_list = [];
        foreach ($cart_items as $item) {
            $subtotal += ($item['price'] * $item['qty']);
            $total_qty += $item['qty'];
            $detail_list[] = $item['qty'] . " " . $item['name'];
        }
        
        $detail = implode(", ", $detail_list);
        $tax = $subtotal * 0.12; 
        $total_bayar = $subtotal + $tax;
        $created_at = date('Y-m-d H:i:s');
        $expired_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // 3. Simpan ke tabel 'order' (Tambah kolom 'code')
        $sqlOrder = "INSERT INTO `order` (
            code, table_id, table_name, customer_name, customer_email,
            qty, subtotal, tax, total, payment, detail, status,
            created_at, expired_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmtOrder = $pdo->prepare($sqlOrder);
        $stmtOrder->execute([
            $order_code, // <--- Kode unik masuk sini
            $id_table,
            $name_table,
            $customer_name,
            $customer_email,
            $total_qty,
            $subtotal,
            $tax,
            $total_bayar,
            $payment_type,
            $detail,
            2,
            $created_at,
            $expired_at
        ]);

        $pdo->commit();
        echo json_encode([
            'status' => 'success', 
            'order_code' => $order_code, 
            'message' => 'Pesanan berhasil disimpan'
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}