<?php
include_once("../../../path.php");


error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../../../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    echo "<pre>";
    echo "--- DATA POST ---\n";
    var_dump($_POST);
    echo "\n--- DATA FILES ---\n";
    var_dump($_FILES);
    echo "\n--- ROOT_PATH VALUE ---\n";
    var_dump(ROOT_PATH);
    echo "</pre>";
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
                
                // Cek apakah folder ada, jika tidak, coba buat
                if (!is_dir($target_dir)) {
                    if (!mkdir($target_dir, 0777, true)) {
                        // Jika gagal membuat folder, tampilkan pesan yang jelas
                        die("Gagal membuat folder: " . $target_dir . ". Pastikan izin folder induk benar.");
                    }
                }

                // Pastikan folder bisa ditulis (writable)
                if (!is_writable($target_dir)) {
                    die("Folder " . $target_dir . " tidak memiliki izin tulis (not writable).");
                }

                $target_file = $target_dir . $new_file_name;

                if (move_uploaded_file($file_tmp, $target_file)) {
                    $image_db_path = 'assets/image/menu/' . $new_file_name;
                } else {
                    // Jika gagal, tampilkan pesan spesifik
                    echo "Gagal memindahkan file ke: " . $target_file;
                    echo "<br>Cek apakah folder tersebut ada dan bisa ditulis (writable).";
                    die();
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