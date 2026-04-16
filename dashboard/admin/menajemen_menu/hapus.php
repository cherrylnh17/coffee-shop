<?php
session_start();

// Pastikan hanya admin/user yang sudah login yang bisa menghapus
if (!isset($_SESSION['username'])) {
    header("Location: ../pages/login.php");
    exit;
}

require_once '../database/koneksi.php';

// Cek apakah ada parameter 'id' yang dikirim melalui URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // Siapkan query untuk menghapus data berdasarkan ID
        $sql = "DELETE FROM menus WHERE id = ?";
        $stmt = $kon->prepare($sql);
        
        // Eksekusi penghapusan
        if ($stmt->execute([$id])) {
            // Jika berhasil, kembalikan ke halaman menu dengan status success
            header("Location: managemenu.php?status=success");
            exit;
        }
    } catch (PDOException $e) {
        // Jika gagal (misalnya ID masih dipakai di tabel pesanan), lempar pesan error
        header("Location: managemenu.php?status=error&msg=" . urlencode("Gagal menghapus: " . $e->getMessage()));
        exit;
    }
} else {
    // Jika file diakses langsung tanpa ID
    header("Location: managemenu.php");
    exit;
}
?>