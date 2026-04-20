<?php
session_start();

// Pastikan hanya admin/user yang sudah login yang bisa menghapus
if (!isset($_SESSION['username'])) {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../../config.php';

// Cek apakah ada parameter 'id' yang dikirim melalui URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        // Siapkan query untuk menghapus data berdasarkan ID
        $sql = "DELETE FROM menu WHERE id = ?";
        
        // Ubah $kon menjadi $pdo
        $stmt = $pdo->prepare($sql);
        
        // Eksekusi penghapusan
        if ($stmt->execute([$id])) {
            // Jika berhasil, kembalikan ke halaman menu dengan status success
            header("Location: manajemenmenu.php?status=success");
            exit;
        }
    } catch (PDOException $e) {
        // Jika gagal (misalnya ID masih dipakai di tabel pesanan), lempar pesan error
        header("Location: manajemenmenu.php?status=error&msg=" . urlencode("Gagal menghapus: " . $e->getMessage()));
        exit;
    }
} else {
    // Jika file diakses langsung tanpa ID
    header("Location: manajemenmenu.php");
    exit;
}
?>