<?php
session_start();
require_once '../config.php'; 
require_once '../path.php';   

if (!isset($_SESSION['username']) || $_SESSION['role'] != 1) {
    header("Location: " . BASE_URL . "auth/login");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'];
    
    // Mengambil data sesi kasir yang sedang bertugas
    $user_id = $_SESSION['user_id'] ?? null; 
    $user_name = $_SESSION['name'] ?? null; 
    
    if (!$user_id) {
        die("Error: Sesi login tidak ditemukan. Silahkan login kembali.");
    }

    if (isset($_POST['aksi']) && $_POST['aksi'] == 'selesai') {
        try {
            $paid = isset($_POST['paid']) ? (int)$_POST['paid'] : 0;
            $total = isset($_POST['total']) ? (int)$_POST['total'] : 0;
            $change = $paid - $total;
            $sql = "UPDATE `order` SET `paid` = ?, `change` = ?, `status` = 1, `payment` = 1, `user_id` = ?, `user_name` = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            
            if ($stmt->execute([$paid, $change, $user_id, $user_name, $order_id])) {
                
                $stmtCode = $pdo->prepare("SELECT code FROM `order` WHERE id = ?");
                $stmtCode->execute([$order_id]);
                $orderCode = $stmtCode->fetchColumn();

                $_SESSION['swal_msg'] = [
                    'icon' => 'success',
                    'title' => 'Berhasil!',
                    'text' => 'Pesanan berhasil diselesaikan oleh ' . $user_name
                ];

                header("Location: check_order.php?code=" . htmlspecialchars($orderCode));
                exit;
            }

        } catch (PDOException $e) {
            $_SESSION['swal_msg'] = [
                'icon' => 'error',
                'title' => 'Gagal!',
                'text' => 'Gagal menyimpan ke database! Error: ' . $e->getMessage()
            ];
            echo "<script>window.history.back();</script>";
            exit;
        }
    } 
    else {
        try {
            $sql = "UPDATE `order` SET 
                    status = 1, 
                    user_id = ?, 
                    user_name = ? 
                    WHERE id = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id, $user_name, $order_id]);

            $_SESSION['swal_msg'] = [
                'icon' => 'success',
                'title' => 'Berhasil!',
                'text' => 'Status Pesanan Berhasil Diperbarui oleh ' . $user_name
            ];

            header("Location: index");
            exit;

        } catch(PDOException $e) {
            $_SESSION['swal_msg'] = [
                'icon' => 'error',
                'title' => 'Gagal!',
                'text' => 'Gagal memperbarui status: ' . $e->getMessage()
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