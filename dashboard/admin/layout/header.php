<?php 
$name = $_SESSION['name'];
$words = explode(" ", $name); 
$initials = "";

foreach ($words as $w) {
  $initials .= mb_substr($w, 0, 1);
}
$initials = strtoupper(substr($initials, 0, 2));
?>

<!doctype html>
<html lang="en" dir="ltr">
<head>
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Dashboard'; ?> | Träffa Coffee</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/flowbite@4.0.1/dist/flowbite.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="icon" href="<?= BASE_URL; ?>assets/image/favicon.svg" type="image/x-icon" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
      #reader video {
        border-radius: 12px;
        object-fit: cover !important;
      }
      #reader {
        border: none !important;
      }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">
    <div id="sidebar-overlay" class="fixed inset-0 z-[1025] bg-gray-900/50 backdrop-blur-sm hidden lg:hidden"></div>