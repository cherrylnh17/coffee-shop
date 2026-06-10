<?php
// Autentikasi tetap dicek di setiap halaman
if (!isset($_SESSION['username']) || $_SESSION['role'] != 1) {
    header("Location: " . BASE_URL . "auth/login");
    exit;
}
?>
<!doctype html>
<html lang="id" dir="ltr">
<head>
    <title><?= isset($pageTitle) ? $pageTitle : 'Kasir' ?> | Träffa Coffee</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/flowbite@4.0.1/dist/flowbite.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="icon" href="<?= BASE_URL ?>assets/image/icon.png" type="image/x-icon" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        /* Halaman di dalam iframe: tidak ada pt-[74px] karena
           header sudah ada di shell (kasir/index.php) */
        body { background: #f9fafb; }

        #reader video { border-radius: 12px; object-fit: cover !important; }
        #reader { border: none !important; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">