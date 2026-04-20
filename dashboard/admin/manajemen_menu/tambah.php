<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../../../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $name = $_POST['name'];
        $category = $_POST['category'];
        
        // PENGAMAN HARGA: Ubah jadi angka (int) dan paksa minimal bernilai 0 (tidak bisa minus)
        $price = max(0, (int)$_POST['price']); 
        
        // PENGAMAN DESKRIPSI: Jika kosong, isi dengan teks default agar tidak NULL di database
        $description = !empty($_POST['description']) ? trim($_POST['description']) : 'Tidak ada deskripsi.';
        //tes
        // --- LOGIKA UPLOAD GAMBAR ---
        $image_db_path = 'https://placehold.co/100x100?text=No+Image'; // Default jika gambar gagal/tidak diupload

        // Pastikan ada file yang diunggah dan tidak ada error
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['image']['tmp_name'];
            $file_name = $_FILES['image']['name'];
            
            // Ambil ekstensi file (jpg, png, dll)
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            // Tentukan ekstensi yang diperbolehkan demi keamanan
            $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($file_ext, $allowed_ext)) {
                // Buat nama file baru yang unik (agar file dengan nama sama tidak saling menimpa)
                $new_file_name = uniqid('menu_') . '.' . $file_ext;
                
                // Tentukan lokasi folder fisik tujuan (Naik 3 tingkat ke root -> lalu ke asset/image/menu)
                $target_dir = '../../../assets/image/menu/';
                
                // Jika foldernya belum ada, PHP akan membuatkannya secara otomatis
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }

                $target_file = $target_dir . $new_file_name;

                // Pindahkan file dari penyimpanan sementara ke folder fisik tujuan
                if (move_uploaded_file($file_tmp, $target_file)) {
                    // Jika berhasil dipindah, buat string path yang AKAN DISIMPAN KE DATABASE
                    // Sesuai permintaan: asset/image/menu/....jpg
                    $image_db_path = 'assets/image/menu/' . $new_file_name;
                }
            } else {
                // Jika format bukan gambar, kembalikan dengan pesan error
                header("Location: manajemenmenu.php?status=error&msg=" . urlencode("Gagal! Format gambar harus JPG, JPEG, PNG, WEBP, atau GIF."));
                exit;
            }
        }

        // 2. Query SQL
        $sql = "INSERT INTO menu (name, price, category, image, description) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        
        // 3. Eksekusi menggunakan path gambar yang sudah diproses (asset/image/menu/...)
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