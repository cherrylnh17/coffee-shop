<?php
session_start();
require_once '../../config.php';
require_once '../../path.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 2) {
    header("Location: " . BASE_URL . "auth/login");
    exit;
}

if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $price = max(0, (int)$_POST['price']);
    $category = $_POST['category'];
    $description = !empty($_POST['description']) ? trim($_POST['description']) : 'Tidak ada deskripsi.';
    
    $old_image = $_POST['old_image'];
    $image_db_path = $old_image; 

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_name = $_FILES['image']['name'];
        
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (in_array($file_ext, $allowed_ext)) {
            $new_file_name = uniqid('menu_') . '.' . $file_ext;
            $target_dir = '../../../assets/image/menu/';
            
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $target_file = $target_dir . $new_file_name;

            if (move_uploaded_file($file_tmp, $target_file)) {
                $image_db_path = 'assets/image/menu/' . $new_file_name;
                
                if (!preg_match('/^http/', $old_image) && file_exists('../../../' . $old_image)) {
                    unlink('../../../' . $old_image);
                }
            }
        } else {
            header("Location: index?status=error&msg=" . urlencode("Format gambar baru tidak valid."));
            exit;
        }
    }

    try {
        $sql = "UPDATE menu SET name = ?, price = ?, category = ?, image = ?, description = ?, updated_at = NOW() WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $price, $category, $image_db_path, $description, $id]);

        header("Location: mindexstatus=success");
        exit;
    } catch (PDOException $e) {
        header("Location: index?status=error&msg=" . urlencode($e->getMessage()));
        exit;
    }
} else {
    header("Location: index");
    exit;
}
?>