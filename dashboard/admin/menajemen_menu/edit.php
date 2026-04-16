<?php
session_start();
require_once '../database/koneksi.php';

if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $price = max(0, (int)$_POST['price']); // Cegah harga minus
    $category = $_POST['category'];
    $image = !empty($_POST['image']) ? $_POST['image'] : 'https://placehold.co/100x100?text=No+Image';
    $description = !empty($_POST['description']) ? trim($_POST['description']) : 'Tidak ada deskripsi.';

    try {
        $sql = "UPDATE menus SET name = ?, price = ?, category = ?, image = ?, description = ? WHERE id = ?";
        $stmt = $kon->prepare($sql);
        $stmt->execute([$name, $price, $category, $image, $description, $id]);

        header("Location: managemenu.php?status=success");
        exit;
    } catch (PDOException $e) {
        header("Location: managemenu.php?status=error&msg=" . urlencode($e->getMessage()));
        exit;
    }
}