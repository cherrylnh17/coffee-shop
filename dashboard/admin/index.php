<?php
session_start();
// Jika session username tidak ada, lempar kembali ke login
if (!isset($_SESSION['username'])) {
    header("Location: ../../auth/login");
    exit;
}


require_once '../../config.php';

function getUsersByRole($pdo, $role) {
    $stmt = $pdo->prepare("SELECT * FROM user WHERE role = :role");
    $stmt->execute(['role' => $role]);
    return $stmt->fetchAll();
}

$countKasir = getUsersByRole($pdo, 1);

// Ambil statistik untuk dashboard
// $countMenu = $kon->query("SELECT COUNT(*) FROM menus")->fetchColumn();
// $countKasir = $kon->query("SELECT COUNT(*) FROM users WHERE username != 'admin'")->fetchColumn();
// $countOrder = $kon->query("SELECT COUNT(*) FROM orders")->fetchColumn();
?>

<!doctype html>
<html lang="en" class="preset-1" data-pc-sidebar-caption="true" data-pc-layout="vertical" data-pc-direction="ltr" dir="ltr" data-pc-theme_contrast="" data-pc-theme="light">
  <!-- [Head] start -->
  <head>
    <title>Admin Dashboard | Träffa Coffee</title>
    <!-- [Meta] -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <link href="../..//assets/css/output.css" rel="stylesheet">
    <!-- [Favicon] icon -->
    <link rel="icon" href="../assets/images/favicon.svg" type="image/x-icon" />
    <!-- [Font] Family -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- [Template CSS Files] -->

  </head>
  <body>
    <!-- [ Pre-loader ] start -->
    <div class="loader-bg fixed inset-0 bg-white dark:bg-themedark-cardbg z-[1034]">
      <div class="loader-track h-[5px] w-full inline-block absolute overflow-hidden top-0 bg-primary-500/40">
        <div class="loader-fill w-[300px] h-[5px] bg-primary-500 absolute top-0 left-0 transition-[transform_0.2s_linear] origin-left animate-[2.1s_cubic-bezier(0.65,0.815,0.735,0.395)_0s_infinite_normal_none_running_loader-animate]"></div>
      </div>
    </div>

    <!-- [ Sidebar Menu ] start -->
    <nav class="pc-sidebar">
      <div class="navbar-wrapper">
        <div class="m-header flex items-center py-4 px-6 h-header-height">
          <a href="index.php" class="b-brand flex items-center gap-3">
            <img src="../assets/images/logo-dark.svg" class="w-8 h-8" alt="logo" />
            <span class="badge bg-success-500/10 text-success-500 rounded-full theme-version">Admin Panel</span>
          </a>
        </div>
        <div class="navbar-content h-[calc(100vh_-_74px)] py-2.5">
          <div class="card pc-user-card mx-[15px] mb-[15px] bg-theme-sidebaruserbg dark:bg-themedark-sidebaruserbg">
            <div class="card-body !p-5">
              <div class="flex items-center">
                <div class="w-8 h-8 rounded-full bg-blue-200 text-blue-700 flex items-center justify-center font-semibold text-sm shadow-sm">
                </div>
                  <div class="ml-4 mr-2 grow">
                    <h6 class="mb-0">Admin Kece</h6>
                    <small>Administrator</small>
                  </div>
              </div>
            </div>
          </div>
          <div class="w-full md:w-72 bg-white border-r border-gray-100 min-h-screen shadow-sm">
            <ul class="flex flex-col gap-2 p-4">
              
              <li>
                <a href="index.php" class="flex items-center gap-3 px-4 py-3 bg-blue-600 text-white rounded-xl shadow-md shadow-blue-500/20 transition-all duration-200">
                  <span class="flex justify-center w-6 text-lg"><i class="fa-solid fa-house"></i></span>
                  <span class="font-medium">Dashboard</span>
                </a>
              </li>

              <li>
                <a href="laporan.html" class="flex items-center gap-3 px-4 py-3 text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition-all duration-200 group">
                  <span class="flex justify-center w-6 text-lg text-gray-400 group-hover:text-blue-600 transition-colors"><i class="fa-solid fa-file-invoice-dollar"></i></span>
                  <span class="font-medium">Laporan Penjualan</span>
                </a>
              </li>

              <li>
                <a href="manajemenmenu.php" class="flex items-center gap-3 px-4 py-3 text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition-all duration-200 group">
                  <span class="flex justify-center w-6 text-lg text-gray-400 group-hover:text-blue-600 transition-colors"><i class="fa-solid fa-mug-hot"></i></span>
                  <span class="font-medium">Manajemen Menu</span>
                </a>
              </li>

              <li>
                <a href="managekasir.html" class="flex items-center gap-3 px-4 py-3 text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition-all duration-200 group">
                  <span class="flex justify-center w-6 text-lg text-gray-400 group-hover:text-blue-600 transition-colors"><i class="fa-solid fa-users-gear"></i></span>
                  <span class="font-medium">Manajemen Kasir</span>
                </a>
              </li>

              <li class="px-4 py-2 mt-4">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Authentication</span>
              </li>

              <li>
                <a href="../auth/login.php" class="flex items-center gap-3 px-4 py-3 text-gray-600 hover:bg-red-50 hover:text-red-600 rounded-xl transition-all duration-200 group">
                  <span class="flex justify-center w-6 text-lg text-gray-400 group-hover:text-red-600 transition-colors"><i class="fa-solid fa-right-from-bracket"></i></span>
                  <span class="font-medium">Log Out</span>
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </nav>

    <!-- [ Header Topbar ] start -->
    <header class="pc-header bg-white shadow-sm">
      <div class="header-wrapper flex max-sm:px-[15px] px-[25px] grow">
        <div class="me-auto pc-mob-drp">
          <ul class="inline-flex *:min-h-header-height *:inline-flex *:items-center">
            <li class="pc-h-item pc-sidebar-collapse max-lg:hidden lg:inline-flex">
              <a href="#" class="pc-head-link ltr:!ml-0 rtl:!mr-0 p-3 hover:bg-gray-100 rounded-lg text-gray-600" id="sidebar-hide">
                <i class="fa-solid fa-bars"></i>
              </a>
            </li>
            <li class="pc-h-item pc-sidebar-popup lg:hidden">
              <a href="#" class="pc-head-link ltr:!ml-0 rtl:!mr-0 p-3 hover:bg-gray-100 rounded-lg text-gray-600" id="mobile-collapse">
                <i class="fa-solid fa-bars"></i>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </header>

    <!-- [ Main Content ] start -->
    <div class="pc-container">
      <div class="pc-content">  

          <!-- Dashboard Header & Filter -->
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <div>
              <h2 class="text-2xl font-bold text-gray-800">Dashboard</h2>
              <p class="text-gray-500 text-sm mt-1">Ringkasan performa dan penjualan</p>
            </div>
            
            <!-- Filter Group -->
            <div class="inline-flex bg-gray-100 p-1 rounded-xl border border-gray-200">
              <button onclick="changeFilter('hari', this)" class="filter-btn px-4 py-2 text-sm font-semibold rounded-lg bg-white text-blue-600 shadow-sm border border-gray-200 transition-all focus:outline-none">
                Hari Ini
              </button>
              <button onclick="changeFilter('minggu', this)" class="filter-btn px-4 py-2 text-sm font-medium rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-200/50 transition-all focus:outline-none border border-transparent">
                Minggu Ini
              </button>
              <button onclick="changeFilter('bulan', this)" class="filter-btn px-4 py-2 text-sm font-medium rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-200/50 transition-all focus:outline-none border border-transparent">
                Bulan Ini
              </button>
            </div>
          </div>

          <!-- Dashboard Widgets -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
              <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-money-bill-wave"></i>
              </div>
              <div>
                <p class="text-gray-500 text-sm font-medium">Total Pendapatan</p>
                <h4 id="val-pendapatan" class="text-2xl font-bold text-gray-800">Rp 1.250.000</h4>
              </div>
            </div>
            
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
              <div class="w-12 h-12 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-cart-shopping"></i>
              </div>
              <div>
                <p class="text-gray-500 text-sm font-medium">Total Transaksi</p>
                <h4 id="val-transaksi" class="text-2xl font-bold text-gray-800">45 Pesanan</h4>
              </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
              <div class="w-12 h-12 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-mug-hot"></i>
              </div>
              <div>
                <p class="text-gray-500 text-sm font-medium">Menu Terjual</p>
                <h4 id="val-terjual" class="text-2xl font-bold text-gray-800">120 Item</h4>
              </div>
            </div>
          </div>

          <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
              <h3 class="font-bold text-lg mb-2">Selamat Datang di Halaman Admin</h3>
              <p class="text-gray-600">Gunakan menu di sebelah kiri untuk mengelola laporan penjualan, daftar menu, dan akun kasir.</p>
          </div>
      </div>
    </div>

    <!-- [ Footer ] start -->
    <footer class="pc-footer">
      <div class="footer-wrapper container-fluid mx-10">
        <div class="flex justify-center items-center gap-1.5">
            <p class="m-0">© Trafa Coffe ♥ by Team Phoenixcoded</p>
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
          btn.className = 'filter-btn px-4 py-2 text-sm font-medium rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-200/50 transition-all focus:outline-none border border-transparent';
        });

        // 3. Ubah tampilan tombol yang diklik menjadi aktif
        elementBtn.className = 'filter-btn px-4 py-2 text-sm font-semibold rounded-lg bg-white text-blue-600 shadow-sm border border-gray-200 transition-all focus:outline-none';
      }
    </script>
  </body>
</html>