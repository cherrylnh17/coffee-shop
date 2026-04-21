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
        $stmt_select = $pdo->prepare("SELECT image FROM menu WHERE id = ?");
        $stmt_select->execute([$id]);
        $menu = $stmt_select->fetch();

        
        $sql = "DELETE FROM menu WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$id])) {
            
            if ($menu && !empty($menu['image'])) {
                $image_path = $menu['image'];
                
                if (!preg_match('/^http/', $image_path)) {
                    $file_to_delete = '../../../' . $image_path;
                    
                    if (file_exists($file_to_delete)) {
                        unlink($file_to_delete);
                    }
                }
            }

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