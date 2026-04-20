<?php
session_start();
require_once '../../../config.php';

if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: ../../../auth/login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    try {
        if ($id == $_SESSION['user_id']) {
            header("Location: managekasir.php?status=error&msg=" . urlencode("Anda tidak bisa menghapus akun Anda sendiri!"));
            exit;
        }

        $sql = "DELETE FROM user WHERE id = ? AND username != 'admin'";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$id])) {
            if ($stmt->rowCount() > 0) {
                header("Location: manajemenkasir.php?status=success");
            } else {
                header("Location: manajemenkasir.php?status=error&msg=" . urlencode("Data kasir tidak ditemukan."));
            }
            exit;
        }
    } catch (PDOException $e) {
        header("Location: managekasir.php?status=error&msg=" . urlencode("Gagal menghapus: Kasir ini mungkin terhubung dengan data transaksi."));
        exit;
    }
} else {
    header("Location: managekasir.php");
    exit;
}
?>