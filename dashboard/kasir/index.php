<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../../auth/login.php");
    exit;
}
require_once '../../config.php';
?>

<!doctype html>
<html lang="en" dir="ltr">
  <head>
    <title>Manajemen Kasir | Träffa Coffee</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/flowbite@4.0.1/dist/flowbite.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/flowbite@4.0.1/dist/flowbite.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="icon" href="../../assets/image/favicon.svg" type="image/x-icon" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
      /* Memperbaiki tampilan kamera agar memenuhi container */
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

    <nav class="fixed inset-y-0 left-0 z-[1026] w-[280px] overflow-hidden border-r border-gray-200 bg-white transition-all duration-300 ease-in-out max-lg:-left-[280px] pc-sidebar">
      <div class="h-full w-full flex flex-col">
        <div class="flex h-[74px] items-center px-6 py-4">
          <a href="index.php" class="flex items-center gap-3">
            <img src="../../assets/image/logo.svg" class="h-8 w-8" alt="logo" />
            <span class="inline-block rounded-md bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">Kasir Panel</span>
          </a>
        </div>

        <div class="flex-1 overflow-y-auto py-3">
          <div class="mx-4 mb-4 rounded-xl border border-gray-100 bg-gray-50 p-4">
            <div class="flex items-center">
              <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-100 text-sm font-semibold text-blue-600 shadow-sm">
                NA
              </div>
              <div class="ml-3 overflow-hidden">
                <h6 class="truncate text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($_SESSION['username']); ?></h6>
                <small class="text-xs text-gray-500">Kasir</small>
              </div>
            </div>
          </div>

          <ul class="flex flex-col gap-1.5 px-4 py-2 text-sm">
            <li>
              <a href="index.php" class="group flex items-center gap-3 rounded-xl bg-blue-600 px-4 py-3 text-white shadow-md shadow-blue-500/20 transition-all duration-200">
                <span class="flex w-6 justify-center text-lg"><i class="fa-solid fa-house"></i></span>
                <span class="font-medium">Dashboard</span>
              </a>
            </li>
            <li>
              <a href="riwayat_pesanan/riwayat.php" class="group flex items-center gap-3 rounded-xl px-4 py-3 text-gray-600 transition-all duration-200 hover:bg-blue-50 hover:text-blue-600">
                <span class="flex w-6 justify-center text-lg text-gray-400 group-hover:text-blue-600"><i class="fa-solid fa-file-invoice-dollar"></i></span>
                <span class="font-medium">Riwayat Pesanan</span>
              </a>
            </li>
            <li class="mt-5 px-4 py-2">
              <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Authentication</span>
            </li>
            <li>
              <a href="tentang_akun/akun.php" class="group flex items-center gap-3 rounded-xl px-4 py-3 text-gray-600 transition-all duration-200 hover:bg-blue-50 hover:text-blue-600">
                <span class="flex w-6 justify-center text-lg text-gray-400 group-hover:text-blue-600"><i class="fa-solid fa-key"></i></span>
                <span class="font-medium">Tentang Akun</span>
              </a>
            </li>
            <li>
              <a href="../../auth/login.php" class="group flex items-center gap-3 rounded-xl px-4 py-3 text-gray-600 transition-all duration-200 hover:bg-red-50 hover:text-red-600">
                <span class="flex w-6 justify-center text-lg text-gray-400 group-hover:text-red-600"><i class="fa-solid fa-right-from-bracket"></i></span>
                <span class="font-medium">Log Out</span>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <header class="fixed inset-x-0 top-0 z-[1024] flex h-[74px] items-center bg-white/80 px-4 shadow-sm backdrop-blur-md transition-all duration-300 lg:left-[280px] pc-header">
      <div class="flex grow items-center sm:px-2">
        <div class="mr-auto">
          <button class="flex h-11 w-11 items-center justify-center rounded-lg text-gray-600 transition-all hover:bg-gray-100" id="sidebar-toggle-btn">
            <i class="fa-solid fa-bars text-lg"></i>
          </button>
        </div>
      </div>
    </header>
    
    <main class="relative min-h-screen pt-[74px] transition-all duration-300 lg:ml-[280px] pc-main">
      <div class="p-4 sm:p-6 lg:p-8">  
          <div class="rounded-2xl border border-gray-100 bg-white p-4 sm:p-8 shadow-sm text-center">
            <div class="mb-6">
                <h2 class="text-xl font-bold text-gray-800">Scan QR Code Pesanan</h2>
                <p class="text-sm text-gray-500">Arahkan kamera ke QR Code pelanggan untuk memproses pesanan.</p>
            </div>
            
            <div class="mx-auto w-full max-w-md overflow-hidden rounded-xl bg-gray-50 p-2 sm:p-4 border-2 border-dashed border-gray-200">
                <div id="reader" class="w-full"></div>
            </div>

            <div class="mt-6 flex flex-wrap justify-center gap-4">
                <div class="flex items-center gap-2 text-xs text-gray-500 bg-gray-100 px-3 py-2 rounded-full">
                    <i class="fa-solid fa-circle-info text-blue-500"></i> Pastikan cahaya terang
                </div>
                <div class="flex items-center gap-2 text-xs text-gray-500 bg-gray-100 px-3 py-2 rounded-full">
                    <i class="fa-solid fa-camera text-green-500"></i> Gunakan kamera belakang
                </div>
            </div>
          </div>
      </div>

      <footer class="py-6 border-t border-gray-200 bg-white text-center">
        <p class="text-sm text-gray-500">© Trafa Coffee ♥ by Anak Magang</p>
      </footer>
    </main>

    <script>
      document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.querySelector('.pc-sidebar');
        const header = document.querySelector('.pc-header');
        const main = document.querySelector('.pc-main');
        const overlay = document.getElementById('sidebar-overlay');
        const btnToggle = document.getElementById('sidebar-toggle-btn');

        function toggleSidebar() {
          const isMobile = window.innerWidth < 1024;
          if (isMobile) {
            sidebar.classList.toggle('max-lg:-left-[280px]');
            sidebar.classList.toggle('max-lg:left-0');
            overlay.classList.toggle('hidden');
          } else {
            sidebar.classList.toggle('lg:w-0');
            sidebar.classList.toggle('lg:border-r-0');
            header.classList.toggle('lg:left-0');
            main.classList.toggle('lg:ml-0');
          }
        }

        btnToggle.addEventListener('click', (e) => { e.preventDefault(); toggleSidebar(); });
        overlay.addEventListener('click', toggleSidebar);

        // Resize handler agar UI tidak glitch saat ganti orientasi layar
        window.addEventListener('resize', () => {
          if (window.innerWidth >= 1024) {
            overlay.classList.add('hidden');
          }
        });
      });
    </script>

    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
      function onScanSuccess(decodedText, decodedResult) {
          html5QrcodeScanner.clear();
          // Logika fetch Anda tetap sama...
          fetch('proses_scan.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
              body: 'qrcode_data=' + encodeURIComponent(decodedText)
          })
          .then(response => response.text())
          .then(data => {
              if (data.includes("SUCCESS:")) {
                  const targetUrl = data.split(":")[1];
                  window.location.href = targetUrl; 
              } else {
                  Swal.fire({
                      icon: 'warning',
                      title: 'Gagal',
                      text: data,
                      confirmButtonColor: '#3b82f6'
                  }).then(() => location.reload());
              }
          });
      }

      let html5QrcodeScanner = new Html5QrcodeScanner("reader", { 
          fps: 20,              
          qrbox: (viewfinderWidth, viewfinderHeight) => {
              const minEdge = Math.min(viewfinderWidth, viewfinderHeight);
              const qrboxSize = Math.floor(minEdge * 0.7);
              return { width: qrboxSize, height: qrboxSize };
          },
          aspectRatio: 1.0
      });
      html5QrcodeScanner.render(onScanSuccess);
    </script>

    <?php if (isset($_SESSION['swal_msg'])): ?>
      <script>
        Swal.fire({
            icon: '<?= $_SESSION['swal_msg']['icon'] ?>',
            title: '<?= $_SESSION['swal_msg']['title'] ?>',
            text: '<?= $_SESSION['swal_msg']['text'] ?>',
            timer: 3000,
            showConfirmButton: false
        });
      </script>
      <?php unset($_SESSION['swal_msg']); ?>
    <?php endif; ?>

  </body>
</html>