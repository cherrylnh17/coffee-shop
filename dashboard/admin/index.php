<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
// Jika session username tidak ada, lempar kembali ke login
if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    header("Location: ../../auth/login.php");
    exit;
}
require_once '../../config.php'; 

require_once '../../config.php';

function getUsersByRole($pdo, $role) {
    $stmt = $pdo->prepare("SELECT * FROM user WHERE role = :role");
    $stmt->execute(['role' => $role]);
    return $stmt->fetchAll();
}

$countKasir = getUsersByRole($pdo, 1);

// Ambil statistik untuk dashboard
// $countMenu = $kon->query("SELECT COUNT(*) FROM menu")->fetchColumn();

var_dump($pdo);
// function getUsersByRole($pdo, $role) {
//     $stmt = $pdo->prepare("SELECT * FROM users WHERE role = :role");
//     $stmt->execute(['role' => $role]);
//     return $stmt->fetchAll();
// }

// $countKasir = getUsersByRole($pdo, 2);

// $countOrder = $kon->query("SELECT COUNT(*) FROM order")->fetchColumn();
// $countMenu = $kon->query("SELECT COUNT(*) FROM menus")->fetchColumn();
// $countKasir = $kon->query("SELECT COUNT(*) FROM users WHERE username != 'admin'")->fetchColumn();
// $countOrder = $kon->query("SELECT COUNT(*) FROM orders")->fetchColumn();
?>

<!doctype html>
<html lang="en" data-pc-sidebar-caption="true" data-pc-layout="vertical" data-pc-direction="ltr" dir="ltr" data-pc-theme_contrast="" data-pc-theme="light">
  <head>
    <title>Admin Dashboard | Träffa Coffee</title>
    <!-- [Meta] -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- [Favicon] icon -->
    <link rel="icon" href="../assets/image/logo.svg" type="image/x-icon" />
    <!-- [Font] Family -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  </head>
  
  <body class="bg-gray-50 text-gray-800">
    <!-- [ Pre-loader ] start -->
    <!-- <div class="fixed inset-0 z-[1034] bg-white transition-opacity duration-300 loader-bg">
      <div class="absolute top-0 inline-block w-full h-[5px] overflow-hidden bg-blue-100 loader-track">
        <div class="absolute left-0 top-0 h-[5px] w-[300px] bg-blue-500 animate-pulse loader-fill"></div>
      </div>
    </div> -->

    <!-- [ Sidebar Menu ] start -->
    <nav class="fixed inset-y-0 left-0 z-[1026] w-[280px] overflow-hidden border-r border-gray-200 bg-white transition-all duration-200 ease-in-out max-lg:-left-[280px] pc-sidebar">
      <div class="h-full w-full">
        <!-- Sidebar Header -->
        <div class="flex h-[74px] items-center px-6 py-4">
          <a href="index.php" class="flex items-center gap-3">
            <img src="../../assets/image/logo.svg" class="h-8 w-8" alt="logo" />
            <span class="inline-block rounded-md bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">Admin Panel</span>
          </a>
        </div>

        <!-- Sidebar Content -->
        <div class="h-[calc(100vh-74px)] overflow-y-auto py-3">
          
          <!-- User Profile Card -->
          <div class="mx-4 mb-4 rounded-xl bg-gray-50 border border-gray-100 p-4">
            <div class="flex items-center">
              <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 text-sm font-semibold text-blue-600 shadow-sm">
                AK
              </div>
              <div class="ml-3 mr-2 grow">
                <h6 class="mb-0 text-sm font-semibold text-gray-800">Admin Kece</h6>
                <small class="text-xs text-gray-500">Administrator</small>
              </div>
            </div>
          </div>

          <!-- Menu Links -->
          <div class="w-full">
            <ul class="flex flex-col gap-1.5 px-4 py-2">
              
              <li>
                <a href="index.php" class="group flex items-center gap-3 rounded-xl bg-blue-600 px-4 py-3 text-white shadow-md shadow-blue-500/20 transition-all duration-200">
                  <span class="flex w-6 justify-center text-lg"><i class="fa-solid fa-house"></i></span>
                  <span class="font-medium">Dashboard</span>
                </a>
              </li>

              <li>
                <a href="laporan.html" class="group flex items-center gap-3 rounded-xl px-4 py-3 text-gray-600 transition-all duration-200 hover:bg-blue-50 hover:text-blue-600">
                  <span class="flex w-6 justify-center text-lg text-gray-400 transition-colors group-hover:text-blue-600"><i class="fa-solid fa-file-invoice-dollar"></i></span>
                  <span class="font-medium">Laporan Penjualan</span>
                </a>
              </li>

              <li>
                <a href="manajemen_menu/manajemenmenu.php" class="group flex items-center gap-3 rounded-xl px-4 py-3 text-gray-600 transition-all duration-200 hover:bg-blue-50 hover:text-blue-600">
                  <span class="flex w-6 justify-center text-lg text-gray-400 transition-colors group-hover:text-blue-600"><i class="fa-solid fa-mug-hot"></i></span>
                  <span class="font-medium">Manajemen Menu</span>
                </a>
              </li>
              
              <li>
                <a href="managekasir.html" class="group flex items-center gap-3 rounded-xl px-4 py-3 text-gray-600 transition-all duration-200 hover:bg-blue-50 hover:text-blue-600">
                  <span class="flex w-6 justify-center text-lg text-gray-400 transition-colors group-hover:text-blue-600"><i class="fa-solid fa-users-gear"></i></span>
                  <span class="font-medium">Manajemen Kasir</span>
                </a>
              </li>

              <li class="mt-5 px-4 py-2">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Authentication</span>
              </li>

              <li>
                <a href="../auth/login.php" class="group flex items-center gap-3 rounded-xl px-4 py-3 text-gray-600 transition-all duration-200 hover:bg-red-50 hover:text-red-600">
                  <span class="flex w-6 justify-center text-lg text-gray-400 transition-colors group-hover:text-red-600"><i class="fa-solid fa-right-from-bracket"></i></span>
                  <span class="font-medium">Log Out</span>
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </nav>

    <!-- [ Header Topbar ] start -->
    <header class="fixed inset-x-0 top-0 z-[1025] flex h-[74px] items-center bg-white/80 px-4 shadow-sm backdrop-blur-md transition-all duration-200 ease-in-out lg:left-[280px]">
      <div class="flex grow items-center sm:px-2">
        <div class="mr-auto">
          <ul class="inline-flex h-[74px] items-center">
            <li class="hidden items-center lg:inline-flex">
              <a href="#" class="flex h-11 w-11 items-center justify-center rounded-lg text-gray-600 transition-all hover:bg-gray-100" id="sidebar-hide">
                <i class="fa-solid fa-bars text-lg"></i>
              </a>
            </li>
            <li class="inline-flex items-center lg:hidden">
              <a href="#" class="flex h-11 w-11 items-center justify-center rounded-lg text-gray-600 transition-all hover:bg-gray-100" id="mobile-collapse">
                <i class="fa-solid fa-bars text-lg"></i>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </header>

    <!-- [ Main Content ] start -->
    <div class="relative ml-0 min-h-[calc(100vh-135px)] top-[74px] transition-all duration-200 ease-in-out lg:ml-[280px]">
      <div class="p-4 sm:p-6 lg:p-8">  

          <!-- Dashboard Header & Filter -->
          <div class="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
            <div>
              <h2 class="text-2xl font-bold text-gray-800">Dashboard</h2>
              <p class="mt-1 text-sm text-gray-500">Ringkasan performa dan penjualan</p>
            </div>
            
            <!-- Filter Group -->
            <div class="inline-flex rounded-xl border border-gray-200 bg-gray-100 p-1">
              <button onclick="changeFilter('hari', this)" class="filter-btn rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-blue-600 shadow-sm transition-all focus:outline-none">
                Hari Ini
              </button>
              <button onclick="changeFilter('minggu', this)" class="filter-btn rounded-lg border border-transparent px-4 py-2 text-sm font-medium text-gray-500 transition-all hover:bg-gray-200/50 hover:text-gray-700 focus:outline-none">
                Minggu Ini
              </button>
              <button onclick="changeFilter('bulan', this)" class="filter-btn rounded-lg border border-transparent px-4 py-2 text-sm font-medium text-gray-500 transition-all hover:bg-gray-200/50 hover:text-gray-700 focus:outline-none">
                Bulan Ini
              </button>
            </div>
          </div>

          <!-- Dashboard Widgets -->
          <div class="mb-6 grid grid-cols-1 gap-6 md:grid-cols-3">
            <div class="flex items-center gap-4 rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
              <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-xl text-blue-600">
                <i class="fa-solid fa-money-bill-wave"></i>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-500">Total Pendapatan</p>
                <h4 id="val-pendapatan" class="text-2xl font-bold text-gray-800">Rp 1.250.000</h4>
              </div>
            </div>
            
            <div class="flex items-center gap-4 rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
              <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100 text-xl text-green-600">
                <i class="fa-solid fa-cart-shopping"></i>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-500">Total Transaksi</p>
                <h4 id="val-transaksi" class="text-2xl font-bold text-gray-800">45 Pesanan</h4>
              </div>
            </div>

            <div class="flex items-center gap-4 rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
              <div class="flex h-12 w-12 items-center justify-center rounded-full bg-purple-100 text-xl text-purple-600">
                <i class="fa-solid fa-mug-hot"></i>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-500">Menu Terjual</p>
                <h4 id="val-terjual" class="text-2xl font-bold text-gray-800">120 Item</h4>
              </div>
            </div>
          </div>

          <!-- Welcome Banner -->
          <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
              <h3 class="mb-2 text-lg font-bold text-gray-800">Selamat Datang di Halaman Admin</h3>
              <p class="text-gray-600">Gunakan menu di sebelah kiri untuk mengelola laporan penjualan, daftar menu, dan akun kasir.</p>
          </div>
      </div>
    </div>

    <!-- [ Footer ] start -->
    <footer class="relative ml-0 mt-[74px] z-[995] py-[20px] border-t border-gray-200 bg-white transition-all duration-200 ease-in-out lg:ml-[280px]">
      <div class="mx-auto px-6">
        <div class="flex items-center justify-center gap-1.5 text-sm text-gray-500">
            <p class="m-0">© Trafa Coffee ♥ by Team Phoenixcoded</p>
        </div>
      </div>
    </footer>
 
    <!-- Script untuk Data Filter Dashboard -->
    <script>
      // Data simulasi (mock data)
      const dataDashboard = {
        hari: { pendapatan: 'Rp 1.250.000', transaksi: '45 Pesanan', terjual: '120 Item' },
        minggu: { pendapatan: 'Rp 8.750.000', transaksi: '315 Pesanan', terjual: '840 Item' },
        bulan: { pendapatan: 'Rp 35.500.000', transaksi: '1.250 Pesanan', terjual: '3.200 Item' }
      };

      function changeFilter(periode, elementBtn) {
        // 1. Ubah nilai pada widget
        document.getElementById('val-pendapatan').innerText = dataDashboard[periode].pendapatan;
        document.getElementById('val-transaksi').innerText = dataDashboard[periode].transaksi;
        document.getElementById('val-terjual').innerText = dataDashboard[periode].terjual;

        // 2. Reset tampilan semua tombol filter menjadi tidak aktif
        const semuaTombol = document.querySelectorAll('.filter-btn');
        semuaTombol.forEach(btn => {
          btn.className = 'filter-btn rounded-lg border border-transparent px-4 py-2 text-sm font-medium text-gray-500 transition-all hover:bg-gray-200/50 hover:text-gray-700 focus:outline-none';
        });

        // 3. Ubah tampilan tombol yang diklik menjadi aktif
        elementBtn.className = 'filter-btn rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-blue-600 shadow-sm transition-all focus:outline-none';
      }
    </script>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        // 1. Ambil elemen-elemen layout
        const sidebar = document.querySelector('.pc-sidebar');
        const header = document.querySelector('header');
        const mainContent = document.querySelector('header').nextElementSibling; // Mengambil div konten utama
        const footer = document.querySelector('footer');

        // 2. Logika Toggle Desktop (Layar Besar)
        const btnDesktop = document.getElementById('sidebar-hide');
        if (btnDesktop && sidebar && header && mainContent && footer) {
          btnDesktop.addEventListener('click', function(e) {
            e.preventDefault();
            // Perkecil lebar sidebar jadi 0
            sidebar.classList.toggle('lg:w-0');
            sidebar.classList.toggle('lg:border-r-0');
            
            // Geser header merapat ke kiri
            header.classList.toggle('lg:left-[280px]');
            header.classList.toggle('lg:left-0');
            
            // Geser konten utama merapat ke kiri
            mainContent.classList.toggle('lg:ml-[280px]');
            mainContent.classList.toggle('lg:ml-0');
            
            // Geser footer merapat ke kiri
            footer.classList.toggle('lg:ml-[280px]');
            footer.classList.toggle('lg:ml-0');
          });
        }

        // 3. Logika Toggle Mobile (Layar Kecil)
        const btnMobile = document.getElementById('mobile-collapse');
        if (btnMobile && sidebar) {
          btnMobile.addEventListener('click', function(e) {
            e.preventDefault();
            // Tarik sidebar masuk ke layar
            sidebar.classList.toggle('max-lg:-left-[280px]');
            sidebar.classList.toggle('max-lg:left-0');
          });
        }
      });
    </script>
  </body>
</html>