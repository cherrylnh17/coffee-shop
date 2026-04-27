<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../../auth/login.php");
    exit;
}
require_once '../../config.php'; 

//pakai left join, ambil order yang status nya sudah succes,lalu menjumlahkan yang ada di order items
// tabel kiri order dan yg kanan order item
try {
    $today_stmt = $pdo->query("SELECT SUM(o.total) as revenue, COUNT(DISTINCT o.id) as transactions, SUM(oi.qty) as items_sold 
                               FROM `order` o 
                               LEFT JOIN order_item oi ON o.id = oi.order_id 
                               WHERE o.status = 1 AND DATE(o.created_at) = CURDATE()");
    $today_data = $today_stmt->fetch(PDO::FETCH_ASSOC);

    $week_stmt = $pdo->query("SELECT SUM(o.total) as revenue, COUNT(DISTINCT o.id) as transactions, SUM(oi.qty) as items_sold 
                              FROM `order` o 
                              LEFT JOIN order_item oi ON o.id = oi.order_id 
                              WHERE o.status = 1 AND o.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $week_data = $week_stmt->fetch(PDO::FETCH_ASSOC);

    $month_stmt = $pdo->query("SELECT SUM(o.total) as revenue, COUNT(DISTINCT o.id) as transactions, SUM(oi.qty) as items_sold 
                               FROM `order` o 
                               LEFT JOIN order_item oi ON o.id = oi.order_id 
                               WHERE o.status = 1 AND MONTH(o.created_at) = MONTH(CURRENT_DATE()) AND YEAR(o.created_at) = YEAR(CURRENT_DATE())");
    $month_data = $month_stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $today_data = $week_data = $month_data = ['revenue' => 0, 'transactions' => 0, 'items_sold' => 0];
}

// Format data awal untuk tampilan
$initial_revenue = "Rp " . number_format((int)$today_data['revenue'], 0, ',', '.');
$initial_transactions = (int)$today_data['transactions'] . " Pesanan";
$initial_items_sold = (int)$today_data['items_sold'] . " Produk"; // Tambahkan ini

function getUsersByRole($pdo, $role) {
    $stmt = $pdo->prepare("SELECT * FROM user WHERE role = :role");
    $stmt->execute(['role' => $role]);
    return $stmt->fetchAll();
}

$countKasir = getUsersByRole($pdo, 1);

?>

<!doctype html>
<html lang="en" data-pc-sidebar-caption="true" data-pc-layout="vertical" data-pc-direction="ltr" dir="ltr" data-pc-theme_contrast="" data-pc-theme="light">
  <head>
    <title>Admin Dashboard | Träffa Coffee</title>

    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link rel="icon" href="../../assets/image/favicon.svg" type="image/x-icon" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  </head>
  
  <body class="bg-gray-50 text-gray-800">
    
    <div id="sidebar-overlay" class="fixed inset-0 z-[1025] bg-gray-900/50 backdrop-blur-sm hidden lg:hidden"></div>

    <nav class="fixed inset-y-0 left-0 z-[1026] w-[280px] overflow-hidden border-r border-gray-200 bg-white transition-all duration-200 ease-in-out max-lg:-left-[280px] pc-sidebar">
      <div class="h-full w-full">
        <div class="flex h-[74px] items-center px-6 py-4">
          <a href="index.php" class="flex items-center gap-3">
            <img src="../../assets/image/logo.svg" class="h-8 w-8" alt="logo" />
            <span class="inline-block rounded-md bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">Admin Panel</span>
          </a>
        </div>

        <!-- Sidebar Content -->
        <div class="h-[calc(100vh-74px)] overflow-y-auto py-3">
          
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

          <div class="w-full">
            <ul class="flex flex-col gap-1.5 px-4 py-2">
              
              <li>
                <a href="index.php" class="group flex items-center gap-3 rounded-xl bg-blue-600 px-4 py-3 text-white shadow-md shadow-blue-500/20 transition-all duration-200">
                  <span class="flex w-6 justify-center text-lg"><i class="fa-solid fa-house"></i></span>
                  <span class="font-medium">Dashboard</span>
                </a>
              </li>

              <li>
                <a href="laporan/laporan.php" class="group flex items-center gap-3 rounded-xl px-4 py-3 text-gray-600 transition-all duration-200 hover:bg-blue-50 hover:text-blue-600">
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
                <a href="manajemen_meja/manajemenmeja.php" class="group flex items-center gap-3 rounded-xl px-4 py-3 text-gray-600 transition-all duration-200 hover:bg-blue-50 hover:text-blue-600">
                  <span class="flex w-6 justify-center text-lg text-gray-400 transition-colors group-hover:text-blue-600"><i class="fa-solid fa-chair"></i></span>
                  <span class="font-medium">Manajemen Meja</span>
                </a>
              </li>

              <li>
                <a href="manajemen_kasir/manajemenkasir.php" class="group flex items-center gap-3 rounded-xl px-4 py-3 text-gray-600 transition-all duration-200 hover:bg-blue-50 hover:text-blue-600">
                  <span class="flex w-6 justify-center text-lg text-gray-400 transition-colors group-hover:text-blue-600"><i class="fa-solid fa-users-gear"></i></span>
                  <span class="font-medium">Manajemen Kasir</span>
                </a>
              </li>

              <li class="mt-5 px-4 py-2">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Authentication</span>
              </li>

              <li>
                <a href="../../auth/login.php" class="group flex items-center gap-3 rounded-xl px-4 py-3 text-gray-600 transition-all duration-200 hover:bg-red-50 hover:text-red-600">
                  <span class="flex w-6 justify-center text-lg text-gray-400 transition-colors group-hover:text-red-600"><i class="fa-solid fa-right-from-bracket"></i></span>
                  <span class="font-medium">Log Out</span>
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </nav>

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

    <div class="relative ml-0 min-h-[calc(100vh-135px)] top-[74px] transition-all duration-200 ease-in-out lg:ml-[280px]">
      <div class="p-4 sm:p-6 lg:p-8">  

          <div class="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
            <div>
              <h2 class="text-2xl font-bold text-gray-800">Dashboard</h2>
              <p class="mt-1 text-sm text-gray-500">Ringkasan performa dan penjualan</p>
            </div>
            
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

          <div class="mb-6 grid grid-cols-1 gap-6 md:grid-cols-3">
            <div class="flex items-center gap-4 rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
              <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-xl text-blue-600">
                <i class="fa-solid fa-money-bill-wave"></i>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-500">Total Pendapatan</p>
                <h4 id="val-pendapatan" class="text-2xl font-bold text-gray-800"><?php echo $initial_revenue; ?></h4>
              </div>
            </div>
            
            <div class="flex items-center gap-4 rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
              <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100 text-xl text-green-600">
                <i class="fa-solid fa-cart-shopping"></i>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-500">Total Transaksi</p>
                <h4 id="val-transaksi" class="text-2xl font-bold text-gray-800"><?php echo $initial_transactions; ?></h4>
              </div>
            </div>

            <div class="flex items-center gap-4 rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
              <div class="flex h-12 w-12 items-center justify-center rounded-full bg-purple-100 text-xl text-purple-600">
                <i class="fa-solid fa-mug-hot"></i>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-500">Menu Terjual</p>
                <h4 id="val-terjual" class="text-2xl font-bold text-gray-800"><?php echo $initial_items_sold; ?></h4>
              </div>
            </div>
          </div>

          <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
              <h3 class="mb-2 text-lg font-bold text-gray-800">Selamat Datang di Halaman Admin</h3>
              <p class="text-gray-600">Gunakan menu di sebelah kiri untuk mengelola laporan penjualan, daftar menu, dan akun kasir.</p>
          </div>
      </div>
    </div>

    <footer class="relative ml-0 mt-[74px] z-[995] py-[20px] border-t border-gray-200 bg-white transition-all duration-200 ease-in-out lg:ml-[280px]">
      <div class="mx-auto px-6">
        <div class="flex items-center justify-center gap-1.5 text-sm text-gray-500">
            <p class="m-0">© Trafa Coffee ♥ by Team Phoenixcoded</p>
        </div>
      </div>
    </footer>

    <script>
      const dataDashboard = {
          hari: { 
            pendapatan: '<?php echo $initial_revenue; ?>', 
            transaksi: '<?php echo $initial_transactions; ?>', 
            terjual: '<?php echo $initial_items_sold; ?>' 
          },
          minggu: { 
            pendapatan: 'Rp ' + (<?= (int)$week_data['revenue'] ?>).toLocaleString('id-ID'), 
            transaksi: '<?= (int)$week_data['transactions'] ?> Pesanan', 
            terjual: '<?= (int)$week_data['items_sold'] ?> Produk' 
          },
          bulan: { 
            pendapatan: 'Rp ' + (<?= (int)$month_data['revenue'] ?>).toLocaleString('id-ID'), 
            transaksi: '<?= (int)$month_data['transactions'] ?> Pesanan', 
            terjual: '<?= (int)$month_data['items_sold'] ?> Produk' 
          }
        };



      function changeFilter(periode, elementBtn) {
        document.getElementById('val-pendapatan').innerText = dataDashboard[periode].pendapatan;
        document.getElementById('val-transaksi').innerText = dataDashboard[periode].transaksi;
        document.getElementById('val-terjual').innerText = dataDashboard[periode].terjual;

        document.querySelectorAll('.filter-btn').forEach(btn => {
          btn.className = 'filter-btn rounded-lg border border-transparent px-4 py-2 text-sm font-medium text-gray-500 transition-all hover:bg-gray-200/50 hover:text-gray-700 focus:outline-none';
        });

        elementBtn.className = 'filter-btn rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-blue-600 shadow-sm transition-all focus:outline-none';
      }
    </script>
 
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.querySelector('.pc-sidebar');
        const header = document.querySelector('header');
        const mainContent = document.querySelector('header').nextElementSibling;
        const footer = document.querySelector('footer');
        const btnDesktop = document.getElementById('sidebar-hide');
        const btnMobile = document.getElementById('mobile-collapse');

        const overlay = document.createElement('div');
        overlay.id = 'sidebar-overlay';
        overlay.className = 'fixed inset-0 z-[1024] bg-gray-900/40 backdrop-blur-sm hidden transition-opacity duration-200';
        document.body.appendChild(overlay);

        const closeSidebarMobile = () => {
          sidebar.classList.add('max-lg:-left-[280px]');
          sidebar.classList.remove('max-lg:left-0');
          overlay.classList.add('hidden');
          document.body.style.overflow = '';
        };

        const openSidebarMobile = () => {
          sidebar.classList.remove('max-lg:-left-[280px]');
          sidebar.classList.add('max-lg:left-0');
          overlay.classList.remove('hidden');
          document.body.style.overflow = 'hidden';
        };

        if (btnDesktop) {
          btnDesktop.addEventListener('click', function(e) {
            e.preventDefault();
            sidebar.classList.toggle('lg:w-0');
            sidebar.classList.toggle('lg:border-r-0');
            header.classList.toggle('lg:left-[280px]');
            header.classList.toggle('lg:left-0');
            mainContent.classList.toggle('lg:ml-[280px]');
            mainContent.classList.toggle('lg:ml-0');
            footer.classList.toggle('lg:ml-[280px]');
            footer.classList.toggle('lg:ml-0');
          });
        }

        if (btnMobile) {
          btnMobile.addEventListener('click', function(e) {
            e.preventDefault();
            const isOpen = sidebar.classList.contains('max-lg:left-0');
            if (isOpen) {
              closeSidebarMobile();
            } else {
              openSidebarMobile();
            }
          });
        }

        overlay.addEventListener('click', closeSidebarMobile);

        const navLinks = sidebar.querySelectorAll('ul li a');
        navLinks.forEach(link => {
          link.addEventListener('click', function() {
            if (window.innerWidth < 1024) {
              closeSidebarMobile();
            }
          });
        });

        window.addEventListener('resize', function() {
          if (window.innerWidth >= 1024) {
            overlay.classList.add('hidden');
            document.body.style.overflow = '';
          }
        });
      });
    </script>
  </body>
</html>