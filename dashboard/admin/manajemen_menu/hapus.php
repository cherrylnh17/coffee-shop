<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../../config.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        $sql = "DELETE FROM menu WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$id])) {
            header("Location: manajemenmenu.php?status=success");
            exit;
        }
    } catch (PDOException $e) {
        header("Location: manajemenmenu.php?status=error&msg=" . urlencode("Gagal menghapus: " . $e->getMessage()));
        exit;
    }
} else {
    header("Location: manajemenmenu.php");
    exit;
}
?>