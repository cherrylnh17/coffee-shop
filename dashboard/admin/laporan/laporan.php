<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../../../auth/login.php");
    exit;
}

require_once '../../../config.php';

$user_id = $_SESSION['user_id'];
try {
    $stmt = $pdo->prepare("SELECT * FROM user WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    $nama_lengkap = !empty($user['name']) ? $user['name'] : $user['username'];
    $inisial = strtoupper(substr($nama_lengkap, 0, 2));
} catch (PDOException $e) {
    $nama_lengkap = "Admin";
    $inisial = "AD";
}

try {
    $order_stmt = $pdo->query("SELECT * FROM `order` WHERE status = 1 ORDER BY created_at DESC");
    $db_orders = $order_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $db_orders = [];
}

?>

<!doctype html>
<html lang="en" dir="ltr">
  <head>
    <title>Laporan Penjualan | Träffa Coffee</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link href="https://cdn.jsdelivr.net/npm/flowbite@4.0.1/dist/flowbite.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/flowbite@4.0.1/dist/flowbite.min.js"></script>
    
    <link rel="icon" href="../../../assets/image/favicon.svg" type="image/x-icon" />
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  </head>
  <body class="bg-gray-50 text-gray-800">

    <nav class="fixed inset-y-0 left-0 z-[1026] w-[280px] overflow-hidden border-r border-gray-200 bg-white transition-all duration-200 ease-in-out max-lg:-left-[280px] pc-sidebar">
      <div class="h-full w-full">
        <div class="flex h-[74px] items-center px-6 py-4">
          <a href="../index.php" class="flex items-center gap-3">
            <img src="../../../assets/image/logo.svg" class="h-8 w-8" alt="logo" onerror="this.src='https://placehold.co/32x32?text=Logo'" />
            <span class="inline-block rounded-md bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">Admin Panel</span>
          </a>
        </div>

        <div class="h-[calc(100vh-74px)] overflow-y-auto py-3">
          <div class="mx-4 mb-4 rounded-xl border border-gray-100 bg-gray-50 p-4">
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
                <a href="../index.php" class="group flex items-center gap-3 rounded-xl px-4 py-3 text-gray-600 transition-all duration-200 hover:bg-blue-50 hover:text-blue-600">
                  <span class="flex w-6 justify-center text-lg text-gray-400 transition-colors group-hover:text-blue-600"><i class="fa-solid fa-house"></i></span>
                  <span class="font-medium">Dashboard</span>
                </a>
              </li>

              <li>
                <a href="laporan.php" class="group flex items-center gap-3 rounded-xl bg-blue-600 px-4 py-3 text-white shadow-md shadow-blue-500/20 transition-all duration-200">
                  <span class="flex w-6 justify-center text-lg"><i class="fa-solid fa-file-invoice-dollar"></i></span>
                  <span class="font-medium">laporan Penjualan</span>
                </a>
              </li>

              <li>
                <a href="../manajemen_menu/manajemenmenu.php" class="group flex items-center gap-3 rounded-xl px-4 py-3 text-gray-600 transition-all duration-200 hover:bg-blue-50 hover:text-blue-600">
                  <span class="flex w-6 justify-center text-lg text-gray-400 transition-colors group-hover:text-blue-600"><i class="fa-solid fa-mug-hot"></i></span>
                  <span class="font-medium">Manajemen Menu</span>
                </a>
              </li>

              <li>
                <a href="../manajemen_meja/manajemenmeja.php" class="group flex items-center gap-3 rounded-xl px-4 py-3 text-gray-600 transition-all duration-200 hover:bg-blue-50 hover:text-blue-600">
                  <span class="flex w-6 justify-center text-lg text-gray-400 transition-colors group-hover:text-blue-600"><i class="fa-solid fa-chair"></i></span>
                  <span class="font-medium">Manajemen Meja</span>
                </a>
              </li>

              <li>
                <a href="../manajemen_kasir/manajemenkasir.php" class="group flex items-center gap-3 rounded-xl px-4 py-3 text-gray-600 transition-all duration-200 hover:bg-blue-50 hover:text-blue-600">
                  <span class="flex w-6 justify-center text-lg text-gray-400 transition-colors group-hover:text-blue-600"><i class="fa-solid fa-users-gear"></i></span>
                  <span class="font-medium">Manajemen kasir</span>
                </a>
              </li>

              <li class="mt-5 px-4 py-2">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Authentication</span>
              </li>

              <li>
                <a href="../../../auth/login.php" class="group flex items-center gap-3 rounded-xl px-4 py-3 text-gray-600 transition-all duration-200 hover:bg-red-50 hover:text-red-600">
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
        
        <div class="gap-x-6">
          
          <div class="bg-white p-4 md:p-6 rounded-2xl shadow-sm border border-gray-100">
            
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6 pb-4 border-b border-gray-100">
              <div class="flex items-center gap-2">
                 <div>
                    <h3 class="font-bold text-xl text-gray-800">Laporan Penjualan</h3>
                 </div>
              </div>

              <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto items-center">

                <div class="flex items-center gap-2 w-full sm:w-auto">
                  <button data-modal-target="authentication-modal" data-modal-toggle="authentication-modal" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700" type="button">
                    <i class="fa-solid fa-file-export mr-2"></i>Export
                </button>
                </div>
                
                <div class="relative w-full sm:w-auto">
                  <select onchange="setDateFilter(this.value)" class="appearance-none w-full sm:w-44 bg-white border border-gray-300 text-gray-700 py-2.5 pl-4 pr-10 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm font-medium transition-all cursor-pointer">
                    <option value="all">Semua Waktu</option>
                    <option value="harian">Hari Ini</option>
                    <option value="mingguan">Minggu Ini</option>
                    <option value="bulanan">Bulan Ini</option>
                    <option value="tahunan">Tahun Ini</option>
                  </select>
                  <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                    <i class="fa-regular fa-calendar text-xs"></i>
                  </div>
                </div>

                <div class="relative w-full sm:w-auto">
                  <select onchange="setSort(this.value)" class="appearance-none w-full sm:w-44 bg-white border border-gray-300 text-gray-700 py-2.5 pl-4 pr-10 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm font-medium transition-all cursor-pointer">
                    <option value="terbaru">Paling Baru</option>
                    <option value="terlama">Paling Lama</option>
                    <option value="termahal">Total Tertinggi</option>
                    <option value="termurah">Total Terendah</option>
                  </select>
                  <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                    <i class="fa-solid fa-arrow-up-short-wide text-xs"></i>
                  </div>
                </div>

              </div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-gray-200">
              <table class="w-full text-sm text-left whitespace-nowrap">
                <thead class="bg-gray-50 text-gray-600 font-semibold border-b border-gray-200 uppercase text-[11px] tracking-wider">
                  <tr>
                    <th class="px-5 py-4 w-12 text-center">No</th>
                    <th class="px-5 py-4">Nama Kasir</th>
                    <th class="px-5 py-4 max-w-[200px]">Menu Pesanan</th>
                    <th class="px-5 py-4">Pelanggan</th>
                    <th class="px-5 py-4">Jumlah (Total)</th>
                    <th class="px-5 py-4">Pembayaran</th>
                    <th class="px-5 py-4">Date & Time</th>
                    <th class="px-5 py-4 text-center">Action</th>
                  </tr>
                </thead>
                <tbody id="report-table" class="divide-y divide-gray-100 bg-white">
                  </tbody>
                <tfoot class="bg-gray-50 border-t border-gray-200 font-bold text-gray-800">
                  <tr>
                    <td colspan="4" class="px-5 py-4 text-right">Total Pendapatan Terfilter:</td>
                    <td colspan="4" class="px-5 py-4 text-blue-700 text-lg" id="total-revenue-info">Rp 0</td>
                  </tr>
                </tfoot>
              </table>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-between mt-6 gap-4">
              <span class="text-sm font-medium text-gray-500" id="pagination-info">Menampilkan 0 laporan</span>
              <div class="inline-flex items-center gap-1.5" id="pagination-controls">
                </div>
            </div>

          </div>
        </div>
      </div>
    </div>
    <button id="trigger-detail-modal" data-modal-target="detail-modal" data-modal-toggle="detail-modal" class="hidden"></button>
    <div id="detail-modal" tabindex="-1" aria-hidden="true" class="fixed left-0 right-0 top-0 z-50 hidden h-[calc(100%-1rem)] max-h-full w-full items-center justify-center overflow-y-auto overflow-x-hidden bg-gray-900/50 backdrop-blur-sm">
        <div class="relative max-h-full w-full max-w-3xl p-4">
            <div class="relative rounded-xl border border-gray-200 bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-gray-100 p-4 md:p-5">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <i class="fa-solid fa-receipt text-blue-600"></i> Detail Lengkap Transaksi
                    </h3>
                    <button type="button" class="ms-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-transparent text-sm text-gray-400 hover:bg-gray-200 hover:text-gray-900" data-modal-hide="detail-modal">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                
                <div class="p-4 md:p-5">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                            <span class="block text-xs text-gray-500 uppercase font-semibold mb-1">Kode Pesanan</span>
                            <span id="det-code" class="text-sm font-bold text-gray-900">-</span>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                            <span class="block text-xs text-gray-500 uppercase font-semibold mb-1">Kasir / User</span>
                            <span id="det-user" class="text-sm font-bold text-gray-900">-</span>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                            <span class="block text-xs text-gray-500 uppercase font-semibold mb-1">Meja</span>
                            <span id="det-table" class="text-sm font-bold text-gray-900">-</span>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                            <span class="block text-xs text-gray-500 uppercase font-semibold mb-1">Nama Pelanggan</span>
                            <span id="det-customer" class="text-sm font-bold text-gray-900">-</span>
                        </div>
                    </div>

                    <div class="border border-gray-200 rounded-lg overflow-hidden mb-4">
                        <table class="w-full text-sm text-left text-gray-500">
                            <tbody class="divide-y divide-gray-200">
                                <tr class="bg-white">
                                    <th class="px-4 py-3 font-medium text-gray-900 bg-gray-50 w-1/3">Subtotal</th>
                                    <td class="px-4 py-3" id="det-subtotal">Rp 0</td>
                                </tr>
                                <tr class="bg-white">
                                    <th class="px-4 py-3 font-medium text-gray-900 bg-gray-50">Pajak (Tax)</th>
                                    <td class="px-4 py-3" id="det-tax">Rp 0</td>
                                </tr>
                                <tr class="bg-blue-50">
                                    <th class="px-4 py-3 font-bold text-blue-900">Total Tagihan</th>
                                    <td class="px-4 py-3 font-bold text-blue-700" id="det-total">Rp 0</td>
                                </tr>
                                <tr class="bg-white">
                                    <th class="px-4 py-3 font-medium text-gray-900 bg-gray-50">Metode Bayar</th>
                                    <td class="px-4 py-3" id="det-payment">-</td>
                                </tr>
                                <tr class="bg-white">
                                    <th class="px-4 py-3 font-medium text-gray-900 bg-gray-50">Waktu Transaksi</th>
                                    <td class="px-4 py-3" id="det-date">-</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div>
                        <span class="block text-xs text-gray-500 uppercase font-semibold mb-2">Detail Menu Pesanan</span>
                        <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-800 whitespace-pre-wrap font-medium font-mono min-h-[80px]" id="det-detail"></div>
                    </div>
                </div>

                <div class="flex items-center p-4 md:p-5 border-t border-gray-100 rounded-b">
                    <button data-modal-hide="detail-modal" type="button" class="text-white bg-blue-600 hover:bg-blue-700 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-colors">Tutup Detail</button>
                </div>
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
      document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.querySelector('.pc-sidebar');
        const header = document.querySelector('header');
        const mainContent = document.querySelector('header').nextElementSibling;
        const footer = document.querySelector('footer');

        const btnDesktop = document.getElementById('sidebar-hide');
        if (btnDesktop && sidebar && header && mainContent && footer) {
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

        const btnMobile = document.getElementById('mobile-collapse');
        if (btnMobile && sidebar) {
          btnMobile.addEventListener('click', function(e) {
            e.preventDefault();
            sidebar.classList.toggle('max-lg:-left-[280px]');
            sidebar.classList.toggle('max-lg:left-0');
          });
        }
      });
    </script>

    <script>
      const rawDbOrders = <?php echo json_encode($db_orders); ?>;
      
      const orders = rawDbOrders.map((o, index) => {
          let paymentMethod = "Lainnya";
          if (o.payment == 1) paymentMethod = "Kasir";
          if (o.payment == 2) paymentMethod = "Online";

          return {
              _idx: index,
              code: o.code || '-',
              user_name: o.user_name || '-',
              meja: o.table_name || '-',
              customer_name: o.customer_name || '-',
              subtotal: parseFloat(o.subtotal || 0),
              tax: parseFloat(o.tax || 0),
              total: parseFloat(o.total || 0),
              payment: paymentMethod,
              detail: o.detail || 'Tidak ada catatan.',
              created: o.created_at || ''
          };
      });

      let currentSort = "terbaru";
      let dateFilter = "all";
      let currentPage = 1;
      let itemsPerPage = 10; 

      function isWithinDateFilter(dateStr, filter) {
          if (filter === 'all') return true;
          if (!dateStr) return false;

          const orderDate = new Date(dateStr);
          const now = new Date();
          
          const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
          const oDay = new Date(orderDate.getFullYear(), orderDate.getMonth(), orderDate.getDate());

          if (filter === 'harian') {
              return today.getTime() === oDay.getTime();
          }
          if (filter === 'mingguan') {
              const diffTime = Math.abs(today - oDay);
              const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
              return diffDays <= 7 && oDay <= today; 
          }
          if (filter === 'bulanan') {
              return today.getMonth() === oDay.getMonth() && today.getFullYear() === oDay.getFullYear();
          }
          if (filter === 'tahunan') {
              return today.getFullYear() === oDay.getFullYear();
          }
          return true;
      }

      function parseDate(dateStr) {
          return new Date(dateStr).getTime();
      }

      function truncateText(str, length) {
          if (str.length <= length) return str;
          return str.substring(0, length) + '...';
      }

      function renderTable() {
          const table = document.getElementById("report-table");
          const paginationInfo = document.getElementById("pagination-info");
          const paginationControls = document.getElementById("pagination-controls");
          const totalRevenueEl = document.getElementById("total-revenue-info");
          
          table.innerHTML = "";

          let filteredAndSorted = orders.filter(o => isWithinDateFilter(o.created, dateFilter));

          filteredAndSorted.sort((a, b) => {
              if (currentSort === "termahal") return b.total - a.total;
              if (currentSort === "termurah") return a.total - b.total;
              
              const timeA = parseDate(a.created);
              const timeB = parseDate(b.created);
              
              if (currentSort === "terbaru") return timeB - timeA;
              if (currentSort === "terlama") return timeA - timeB;
              
              return 0;
          });

          const grandTotal = filteredAndSorted.reduce((sum, o) => sum + o.total, 0);
          totalRevenueEl.innerText = 'Rp ' + grandTotal.toLocaleString('id-ID');

          const totalItems = filteredAndSorted.length;
          const totalPages = Math.ceil(totalItems / itemsPerPage);
          
          if (currentPage > totalPages && totalPages > 0) currentPage = totalPages;
          if (currentPage === 0 && totalPages > 0) currentPage = 1;

          const startIndex = (currentPage - 1) * itemsPerPage;
          const endIndex = startIndex + itemsPerPage;
          const paginatedItems = filteredAndSorted.slice(startIndex, endIndex);

          paginatedItems.forEach((o, loopIndex) => {
              const rowNumber = startIndex + loopIndex + 1;
              table.innerHTML += `
                  <tr class="hover:bg-blue-50/50 transition-colors group">
                      <td class="px-5 py-4 text-center font-medium text-gray-500">${rowNumber}</td>
                      <td class="px-5 py-4 font-bold text-gray-900">
                          <div class="flex items-center gap-2">
                            <i class="fa-solid fa-user-tie text-gray-300"></i> ${o.user_name}
                          </div>
                      </td>
                      <td class="px-5 py-4 text-gray-600 max-w-[200px] truncate" title="${o.detail}">
                          ${truncateText(o.detail, 35)}
                      </td>
                      <td class="px-5 py-4 text-gray-700">${o.customer_name}</td>
                      <td class="px-5 py-4 font-bold text-green-600">Rp ${o.total.toLocaleString('id-ID')}</td>
                      <td class="px-5 py-4">
                          <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded text-xs font-semibold ${o.payment === 'Online' ? 'bg-purple-50 text-purple-600 border border-purple-200' : 'bg-blue-50 text-blue-600 border border-blue-200'}">
                              ${o.payment === 'Online' ? '<i class="fa-solid fa-globe"></i>' : '<i class="fa-solid fa-cash-register"></i>'} ${o.payment}
                          </span>
                      </td>
                      <td class="px-5 py-4 text-gray-500 text-xs font-medium"><i class="fa-regular fa-clock mr-1"></i> ${o.created}</td>
                      <td class="px-5 py-4 text-center">
                          <button onclick="showDetail(${o._idx})" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all duration-200 bg-gray-50 text-gray-600 hover:bg-blue-600 hover:text-white border border-gray-200 hover:border-blue-600 shadow-sm">
                              Detail
                          </button>
                      </td>
                  </tr>
              `;
          });

          if (totalItems === 0) {
              paginationInfo.innerText = "Tidak ada laporan transaksi.";
              paginationControls.innerHTML = "";
          } else {
              paginationInfo.innerText = `Menampilkan ${startIndex + 1}-${Math.min(endIndex, totalItems)} dari ${totalItems} laporan`;
              
              let controlsHtml = `
                  <button onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''} class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors shadow-sm bg-white"><i class="fa-solid fa-chevron-left text-xs"></i></button>
              `;
              
              let startPage = Math.max(1, currentPage - 2);
              let endPage = Math.min(totalPages, currentPage + 2);
              
              for(let i = startPage; i <= endPage; i++) {
                  controlsHtml += `
                      <button onclick="changePage(${i})" class="w-8 h-8 flex items-center justify-center rounded-lg text-sm font-bold transition-colors shadow-sm ${currentPage === i ? 'bg-blue-600 text-white border border-blue-600' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50'}">${i}</button>
                  `;
              }
              
              controlsHtml += `
                  <button onclick="changePage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''} class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors shadow-sm bg-white"><i class="fa-solid fa-chevron-right text-xs"></i></button>
              `;
              paginationControls.innerHTML = controlsHtml;
          }
      }

      function setLimit(limit) { itemsPerPage = parseInt(limit); currentPage = 1; renderTable(); }
      function setDateFilter(filter) { dateFilter = filter; currentPage = 1; renderTable(); }
      function setSort(sortType) { currentSort = sortType; currentPage = 1; renderTable(); }
      function changePage(page) { currentPage = page; renderTable(); }

      function showDetail(index) {
          const o = orders[index];
          
          document.getElementById('det-code').innerText = o.code;
          document.getElementById('det-user').innerText = o.user_name;
          document.getElementById('det-table').innerText = o.meja;
          document.getElementById('det-customer').innerText = o.customer_name;
          document.getElementById('det-subtotal').innerText = 'Rp ' + o.subtotal.toLocaleString('id-ID');
          document.getElementById('det-tax').innerText = 'Rp ' + o.tax.toLocaleString('id-ID');
          document.getElementById('det-total').innerText = 'Rp ' + o.total.toLocaleString('id-ID');
          document.getElementById('det-payment').innerText = o.payment;
          document.getElementById('det-date').innerText = o.created;
          document.getElementById('det-detail').innerText = o.detail;

          document.getElementById('trigger-detail-modal').click();
      }

      renderTable();
    </script>
    </body>
</html>

