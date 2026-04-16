<?php 
require_once __DIR__ . '/../../path.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Beranda'; ?> - Trafa Coffee | Tempat Cafe kekinian di Indonesia</title>
    <link href="<?= BASE_URL; ?>assets/css/output.css" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <!-- Logo app -->
     <link rel="icon" href="<?= BASE_URL ?>assets/image/logo.svg" type="image/x-icon" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f3f4f6; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        @keyframes slideUp { from { transform: translateX(-50%) translateY(100%); opacity: 0; } to { transform: translateX(-50%) translateY(0); opacity: 1; } }
        @keyframes fadeIn  { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .toast-animate { animation: slideUp 0.3s ease-out forwards; }
        .menu-card { animation: fadeIn 0.25s ease-out forwards; }
    </style>
</head>
<body class="antialiased text-gray-800">