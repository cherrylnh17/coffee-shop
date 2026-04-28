<?php
session_start();


require_once '../../../config.php';
require_once '../../../path.php';

if (!isset($_SESSION['name']) || $_SESSION['role'] != 1) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit;
}


try {
    $order_stmt = $pdo->query("SELECT * FROM `order` ORDER BY created_at DESC");
    $db_orders = $order_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $db_orders = [];
}
 
?>
<?php 

$pageTitle = "History Pesanan";
$currentPage = "riwayat";

include '../layout/header.php';
include '../layout/sidebar.php';
?>

   
    <!-- [ Main Content ] start -->
    <div class="relative ml-0 min-h-[calc(100vh-135px)] top-[74px] transition-all duration-200 ease-in-out lg:ml-[280px]">
      <div class="p-4 sm:p-6 lg:p-8">
        
        <!-- [ breadcrumb ] start -->
        <div class="mb-6">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
              <h2 class="text-2xl font-bold text-gray-800 mb-1">Riwayat Pesanan</h2>
              <p class="text-sm text-gray-500">Pantau status dan kelola pesanan pelanggan hari ini.</p>
            </div>
            <ul class="flex items-center gap-2 text-sm text-gray-500">
                <li><a href="../index.php" class="hover:text-blue-600 transition-colors"><i class="fa-solid fa-house"></i></a></li>
                <li><i class="fa-solid fa-chevron-right text-[10px]"></i></li>
                <li class="text-gray-800 font-medium">Riwayat</li>
            </ul>
          </div>
        </div>
        <!-- [ breadcrumb ] end -->

        <div class="gap-x-6">
          
          <div class="bg-white p-4 md:p-6 rounded-2xl shadow-sm border border-gray-100">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-gray-100">
              <div class="flex items-center gap-2">
                 <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-list-check"></i>
                 </div>
                 <div>
                    <h3 class="font-bold text-lg text-gray-800">Daftar Transaksi</h3>
                 </div>
              </div>

              <!-- Filter dan Sort -->
              <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">

                <div class="flex items-center gap-2 w-full sm:w-auto">
                  <span class="text-sm text-gray-600 whitespace-nowrap">Tampilkan:</span>
                  <div class="relative w-full sm:w-20">
                    <select onchange="setLimit(this.value)" class="appearance-none w-full bg-white border border-gray-300 text-gray-700 py-2.5 pl-4 pr-8 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm font-medium transition-all cursor-pointer">
                      <option value="5">5</option>
                      <option value="10">10</option>
                      <option value="25">25</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                      <i class="fa-solid fa-chevron-down text-xs"></i>
                    </div>
                  </div>
                </div>
                
                <!-- Filter Status -->
                <div class="relative w-full sm:w-auto">
                  <select onchange="setFilter(this.value)" class="appearance-none w-full sm:w-48 bg-white border border-gray-300 text-gray-700 py-2.5 pl-4 pr-10 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm font-medium transition-all cursor-pointer">
                    <option value="all">Semua Status</option>
                    <option value="success">Sukses</option>
                    <option value="pending">Pending</option>
                    <option value="expired">Expired</option>
                  </select>
                  <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                    <i class="fa-solid fa-chevron-down text-xs"></i>
                  </div>
                </div>

                <!-- Sort By -->
                <div class="relative w-full sm:w-auto">
                  <select onchange="setSort(this.value)" class="appearance-none w-full sm:w-48 bg-white border border-gray-300 text-gray-700 py-2.5 pl-4 pr-10 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm font-medium transition-all cursor-pointer">
                    <option value="terbaru">Paling Baru</option>
                    <option value="terlama">Paling Lama</option>
                    <option value="termahal">Total Termahal</option>
                    <option value="termurah">Total Termurah</option>
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
                    <th class="px-5 py-4">No Meja</th>
                    <th class="px-5 py-4">Total Tagihan</th>
                    <th class="px-5 py-4">Dibuat</th>
                    <th class="px-5 py-4">Status</th>
                    <th class="px-5 py-4 text-center">Aksi</th>
                  </tr>
                </thead>
                <tbody id="order-table" class="divide-y divide-gray-100 bg-white">
                  <!-- Baris akan diisi oleh JavaScript Data dari PHP -->
                </tbody>
              </table>
            </div>

            <!-- Pagination Control -->
            <div class="flex flex-col sm:flex-row items-center justify-between mt-6 gap-4">
              <span class="text-sm font-medium text-gray-500" id="pagination-info">Menampilkan 0 pesanan</span>
              <div class="inline-flex items-center gap-1.5" id="pagination-controls">
                <!-- Tombol Pagination akan diisi oleh JavaScript -->
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
    <!-- [ Main Content ] end -->

    <!-- Modal Popup Detail Pesanan -->
    <button id="trigger-detail-modal" data-modal-target="detail-modal" data-modal-toggle="detail-modal" class="hidden"></button>
    <div id="detail-modal" tabindex="-1" aria-hidden="true" class="fixed left-0 right-0 top-0 z-50 hidden h-[calc(100%-1rem)] max-h-full w-full items-center justify-center overflow-y-auto overflow-x-hidden bg-gray-900/50 backdrop-blur-sm">
        <div class="relative max-h-full w-full max-w-2xl p-4">
            <div class="relative rounded-xl border border-gray-200 bg-white shadow-xl">
                <!-- Modal header -->
                <div class="flex items-center justify-between border-b border-gray-100 p-4 md:p-5">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <i class="fa-solid fa-receipt text-blue-600"></i> Detail Transaksi
                    </h3>
                    <button type="button" class="ms-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-transparent text-sm text-gray-400 hover:bg-gray-200 hover:text-gray-900" data-modal-hide="detail-modal">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>
                
                <!-- Modal body -->
                <div class="p-4 md:p-5">
                    <div class="grid grid-cols-2 gap-4 mb-4">
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
                                    <th class="px-4 py-3 font-medium text-gray-900 bg-gray-50">Status</th>
                                    <td class="px-4 py-3" id="det-status">-</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div>
                        <span class="block text-xs text-gray-500 uppercase font-semibold mb-2">Detail Pesanan Tambahan</span>
                        <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700 whitespace-pre-wrap min-h-[60px]" id="det-detail"></div>
                    </div>
                </div>

                <!-- Modal footer -->
                <div class="flex items-center p-4 md:p-5 border-t border-gray-100 rounded-b">
                    <button data-modal-hide="detail-modal" type="button" class="text-white bg-blue-600 hover:bg-blue-700 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-colors">Tutup Detail</button>
                </div>
            </div>
        </div>
    </div>


    <script>
      const rawDbOrders = <?php echo json_encode($db_orders); ?>;
      
      const orders = rawDbOrders.map((o, index) => {
          let statusStr = "pending";
          if (o.status == 1) statusStr = "success";
          if (o.status == 3) statusStr = "expired";
          
          let paymentMethod = "-";
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
              created: o.created_at || '',
              status: statusStr,
              raw_status: parseInt(o.status || 0)
          };
      });

      let currentFilter = "all";
      let currentSort = "terbaru";
      let currentPage = 1;

      let itemsPerPage = 5; 
      function parseDate(dateStr) {
          return new Date(dateStr).getTime();
      }

      function renderTable() {
          const table = document.getElementById("order-table");
          const paginationInfo = document.getElementById("pagination-info");
          const paginationControls = document.getElementById("pagination-controls");
          
          table.innerHTML = "";

          let filteredAndSorted = orders.filter(o => 
              currentFilter === "all" || o.status === currentFilter
          );

          filteredAndSorted.sort((a, b) => {
              if (currentSort === "termahal") return b.total - a.total;
              if (currentSort === "termurah") return a.total - b.total;
              
              const timeA = parseDate(a.created);
              const timeB = parseDate(b.created);
              
              if (currentSort === "terbaru") return timeB - timeA;
              if (currentSort === "terlama") return timeA - timeB;
              
              return 0;
          });

          const totalItems = filteredAndSorted.length;
          const totalPages = Math.ceil(totalItems / itemsPerPage);
          
          if (currentPage > totalPages && totalPages > 0) currentPage = totalPages;
          if (currentPage === 0 && totalPages > 0) currentPage = 1;

          const startIndex = (currentPage - 1) * itemsPerPage;
          const endIndex = startIndex + itemsPerPage;
          const paginatedItems = filteredAndSorted.slice(startIndex, endIndex);

          paginatedItems.forEach((o) => {
              let statusBadge = '';
              let isDisabled = false;
              if(o.raw_status === 1) {
                  statusBadge = `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold border bg-green-50 text-green-600 border-green-200"><i class="fa-solid fa-check-double"></i> Sukses</span>`;
              } else if (o.raw_status === 2) {
                  statusBadge = `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold border bg-yellow-50 text-yellow-600 border-yellow-200"><i class="fa-solid fa-hourglass-half"></i> Pending</span>`;
                  isDisabled = true;
              } else {
                  statusBadge = `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold border bg-red-50 text-red-600 border-red-200"><i class="fa-solid fa-xmark"></i> Expired</span>`;
                  isDisabled = true;
              }

              table.innerHTML += `
                  <tr class="hover:bg-blue-50/50 transition-colors group">
                      <td class="px-5 py-4 font-bold text-gray-900">
                         <div class="flex items-center gap-2">
                            <i class="fa-solid fa-utensils text-gray-300 group-hover:text-blue-400 transition-colors"></i>
                            ${o.meja}
                         </div>
                      </td>
                      <td class="px-5 py-4 font-medium text-gray-700">Rp ${o.total.toLocaleString('id-ID')}</td>
                      <td class="px-5 py-4 text-gray-500 text-xs font-medium"><i class="fa-regular fa-clock mr-1"></i> ${o.created}</td>
                      <td class="px-5 py-4">
                          ${statusBadge}
                      </td>
                      <td class="px-5 py-4">
                          <div class="flex items-center justify-center gap-2">
                              <button onclick="showDetail(${o._idx})" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all duration-200 bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white border border-transparent hover:border-blue-600">
                                  <i class="fa-solid fa-eye"></i> Detail
                              </button>
                              <button ${isDisabled ? 'disabled' : `onclick="printOrder('${o.code}')"`} 
                                      class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 border rounded-lg text-xs font-bold transition-all duration-200 shadow-sm ${isDisabled ? 'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed opacity-60' : 'bg-gray-50 text-gray-600 hover:bg-gray-800 hover:text-white hover:border-gray-800'}">
                                  <i class="fa-solid fa-print"></i> Print
                              </button>
                          </div>
                      </td>
                  </tr>
              `;
          });

          if (totalItems === 0) {
              paginationInfo.innerText = "Tidak ada pesanan";
              paginationControls.innerHTML = "";
          } else {
              paginationInfo.innerText = `Menampilkan ${startIndex + 1}-${Math.min(endIndex, totalItems)} dari ${totalItems} pesanan`;
              
              let controlsHtml = `
                  <button onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''} class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors shadow-sm bg-white"><i class="fa-solid fa-chevron-left text-xs"></i></button>
              `;
              for(let i = 1; i <= totalPages; i++) {
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

      function setLimit(limit) {
          itemsPerPage = parseInt(limit);
          currentPage = 1;
          renderTable();
      }

      function setFilter(filter) { currentFilter = filter; currentPage = 1; renderTable(); }
      function setSort(sortType) { currentSort = sortType; currentPage = 1; renderTable(); }
      function changePage(page) { currentPage = page; renderTable(); }
      function printOrder(code) { alert(`Mencetak struk pesanan dengan kode ${code}...`); }

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
          document.getElementById('det-detail').innerText = o.detail;

          let modalStatus = '';
          if(o.raw_status === 1) modalStatus = '<span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-bold border border-green-200">Sukses</span>';
          else if(o.raw_status === 2) modalStatus = '<span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded text-xs font-bold border border-yellow-200">Pending</span>';
          else modalStatus = '<span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs font-bold border border-red-200">Expired</span>';
          
          document.getElementById('det-status').innerHTML = modalStatus;

          document.getElementById('trigger-detail-modal').click();
      }
      
      renderTable();
    </script>



<?php 

include '../layout/footer.php'; 
?>