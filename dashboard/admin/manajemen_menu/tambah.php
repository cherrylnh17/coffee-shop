<?php
session_start();
require_once '../../../config.php';
require_once '../../../path.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 2) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $name = $_POST['name'];
        $category = $_POST['category'];
        
        $price = max(0, (int)$_POST['price']); 
        
        $description = !empty($_POST['description']) ? trim($_POST['description']) : 'Tidak ada deskripsi.';
        $image_db_path = 'https://placehold.co/100x100?text=No+Image';
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['image']['tmp_name'];
            $file_name = $_FILES['image']['name'];
            
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($file_ext, $allowed_ext)) {
                $new_file_name = uniqid('menu_') . '.' . $file_ext;
                
                $target_dir = ROOT_PATH . "/assets/image/menu/";
                
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }

                $target_file = $target_dir . $new_file_name;

                if (move_uploaded_file($file_tmp, $target_file)) {
                    $image_db_path = 'assets/image/menu/' . $new_file_name;
                }
            } else {
                header("Location: manajemenmenu.php?status=error&msg=" . urlencode("Gagal! Format gambar harus JPG, JPEG, PNG, WEBP, atau GIF."));
                exit;
            }
        }

        $sql = "INSERT INTO menu (name, price, category, image, description) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $name, 
            $price, 
            $category, 
            $image_db_path, 
            $description
        ]);
        
        header("Location: manajemenmenu.php?status=success");
        exit;
        
    } catch (PDOException $e) {
        die("<div style='padding: 20px; border: 2px solid red; background-color: #ffebee; color: red; font-family: sans-serif;'>
                <h2>Gagal Menyimpan ke Database!</h2>
                <p><strong>Pesan Error MySQL:</strong> " . $e->getMessage() . "</p>
                <a href='manajemenmenu.php' style='background: blue; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>Kembali</a>
             </div>");
    }
} else {
    header("Location: manajemenmenu.php");
    exit;
}
?>