<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../database/koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $name = $_POST['name'];
        $category = $_POST['category'];
        
        // PENGAMAN HARGA: Ubah jadi angka (int) dan paksa minimal bernilai 0 (tidak bisa minus)
        $price = max(0, (int)$_POST['price']); 
        
        // PENGAMAN DESKRIPSI: Jika kosong, isi dengan teks default agar tidak NULL di database
        $description = !empty($_POST['description']) ? trim($_POST['description']) : 'Tidak ada deskripsi.';
        
        // PENGAMAN GAMBAR: Jika URL gambar kosong, beri gambar default
        $image = !empty($_POST['image']) ? trim($_POST['image']) : 'https://placehold.co/100x100?text=No+Image';

        // 2. Query SQL
        $sql = "INSERT INTO menus (name, price, category, image, description) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $kon->prepare($sql);
        
        // 3. Eksekusi
        $stmt->execute([
            $name, 
            $price, 
            $category, 
            $image, 
            $description
        ]);
        header("Location: managemenu.php?status=success");
        exit;
        
    } catch (PDOException $e) {
        die("<div style='padding: 20px; border: 2px solid red; background-color: #ffebee; color: red; font-family: sans-serif;'>
                <h2>Gagal Menyimpan ke Database!</h2>
                <p><strong>Pesan Error MySQL:</strong> " . $e->getMessage() . "</p>
                <a href='managemenu.php' style='background: blue; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>Kembali</a>
             </div>");
    }
} else {
    header("Location: managemenu.php");
    exit;
}
?>