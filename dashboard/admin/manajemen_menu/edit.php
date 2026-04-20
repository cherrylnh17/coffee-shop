<?php
session_start();
require_once '../../../config.php';

if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $price = max(0, (int)$_POST['price']); // Cegah harga minus
    $category = $_POST['category'];
    $image = !empty($_POST['image']) ? $_POST['image'] : 'https://placehold.co/100x100?text=No+Image';
    $description = !empty($_POST['description']) ? trim($_POST['description']) : 'Tidak ada deskripsi.';

    try {
        $sql = "UPDATE menu SET name = ?, price = ?, category = ?, image = ?, description = ? WHERE id = ?";
        
        // Ubah $kon menjadi $pdo
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $price, $category, $image, $description, $id]);

        header("Location: manajemenmenu.php?status=success");
        exit;
    } catch (PDOException $e) {
        header("Location: manajemenmenu.php?status=error&msg=" . urlencode($e->getMessage()));
        exit;
    }
}
?>