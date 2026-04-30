<?php
session_start();
require_once '../../../config.php';
require_once '../../../path.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 1) {
    header("Location: " . BASE_URL . "auth/login");
    exit;
}

$limit = 10; // OTOMATIS: Limit dikunci secara permanen menjadi 10 baris per halaman
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'all';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'terbaru';

// 2. MEMBUAT KONDISI WHERE BERDASARKAN FILTER STATUS
$where_conditions = [];

if ($filter_status === 'success') {
    $where_conditions[] = "status = 1";
} elseif ($filter_status === 'pending') {
    $where_conditions[] = "status = 2";
} elseif ($filter_status === 'expired') {
    $where_conditions[] = "status = 3";
}

$where_sql = "";
if (count($where_conditions) > 0) {
    $where_sql = "WHERE " . implode(" AND ", $where_conditions);
}

// 3. MEMBUAT KONDISI ORDER BY (SORTING)
$order_sql = "ORDER BY created_at DESC"; // Default: Terbaru
if ($sort === 'terlama') {
    $order_sql = "ORDER BY created_at ASC";
} elseif ($sort === 'termahal') {
    $order_sql = "ORDER BY total DESC";
} elseif ($sort === 'termurah') {
    $order_sql = "ORDER BY total ASC";
}

// 4. MENGHITUNG PAGINATION DAN AMBIL DATA
try {
    $count_stmt = $pdo->query("SELECT COUNT(*) FROM `order` " . $where_sql);
    $total_records = $count_stmt->fetchColumn();

    $total_pages = ceil($total_records / $limit);
    if ($page > $total_pages && $total_pages > 0) {
        $page = $total_pages;
    }

    $offset = ($page - 1) * $limit;

    // AMBIL DATA FINAL DARI DATABASE SESUAI HALAMAN
    $query = "SELECT * FROM `order` $where_sql $order_sql LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $orders = [];
    $total_records = 0;
    $total_pages = 0;
    $offset = 0;
}

// String URL Parameter untuk navigasi halaman agar filter tidak hilang
$query_string = "&status=$filter_status&sort=$sort";
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
              <form method="GET" id="filter-form" class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto items-center">
                <input type="hidden" name="page" id="page-input" value="<?php echo $page; ?>">

                <!-- Filter Status -->
                <div class="relative w-full sm:w-auto">
                  <select name="status" onchange="document.getElementById('page-input').value = 1; this.form.submit()" class="appearance-none w-full sm:w-44 bg-white border border-gray-300 text-gray-700 py-2.5 pl-4 pr-10 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm font-medium transition-all cursor-pointer">
                    <option value="all" <?php echo ($filter_status == 'all') ? 'selected' : ''; ?>>Semua Status</option>
                    <option value="success" <?php echo ($filter_status == 'success') ? 'selected' : ''; ?>>Sukses</option>
                    <option value="pending" <?php echo ($filter_status == 'pending') ? 'selected' : ''; ?>>Pending</option>
                    <option value="expired" <?php echo ($filter_status == 'expired') ? 'selected' : ''; ?>>Expired</option>
                  </select>
                  <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                    <i class="fa-solid fa-chevron-down text-xs"></i>
                  </div>
                </div>

                <!-- Sort By -->
                <div class="relative w-full sm:w-auto">
                  <select name="sort" onchange="document.getElementById('page-input').value = 1; this.form.submit()" class="appearance-none w-full sm:w-44 bg-white border border-gray-300 text-gray-700 py-2.5 pl-4 pr-10 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm font-medium transition-all cursor-pointer">
                    <option value="terbaru" <?php echo ($sort == 'terbaru') ? 'selected' : ''; ?>>Paling Baru</option>
                    <option value="terlama" <?php echo ($sort == 'terlama') ? 'selected' : ''; ?>>Paling Lama</option>
                    <option value="termahal" <?php echo ($sort == 'termahal') ? 'selected' : ''; ?>>Total Termahal</option>
                    <option value="termurah" <?php echo ($sort == 'termurah') ? 'selected' : ''; ?>>Total Termurah</option>
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
                <tbody class="divide-y divide-gray-100 bg-white">
                  <?php if (empty($orders)): ?>
                    <tr>
                      <td colspan="5" class="px-5 py-8 text-center text-gray-500">Tidak ada pesanan yang ditemukan.</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($orders as $index => $o): 
                        // Status Evaluasi
                        $raw_status = (int)$o['status'];
                        
                        if ($raw_status === 1) {
                            $statusBadge = '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold border bg-green-50 text-green-600 border-green-200"><i class="fa-solid fa-check-double"></i> Sukses</span>';
                        } elseif ($raw_status === 2) {
                            $statusBadge = '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold border bg-yellow-50 text-yellow-600 border-yellow-200"><i class="fa-solid fa-hourglass-half"></i> Pending</span>';
                        } else {
                            $statusBadge = '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold border bg-red-50 text-red-600 border-red-200"><i class="fa-solid fa-xmark"></i> Expired</span>';
                        }
                    ?>
                    <tr class="hover:bg-blue-50/50 transition-colors group">
                      <td class="px-5 py-4 font-bold text-gray-900">
                         <div class="flex items-center gap-2">
                            <i class="fa-solid fa-utensils text-gray-300 group-hover:text-blue-400 transition-colors"></i>
                            <?php echo htmlspecialchars($o['table_name'] ?? '-'); ?>
                         </div>
                      </td>
                      <td class="px-5 py-4 font-medium text-gray-700">Rp <?php echo number_format((float)$o['total'], 0, ',', '.'); ?></td>
                      <td class="px-5 py-4 text-gray-500 text-xs font-medium"><i class="fa-regular fa-clock mr-1"></i> <?php echo htmlspecialchars($o['created_at']); ?></td>
                      <td class="px-5 py-4">
                          <?php echo $statusBadge; ?>
                      </td>
                      <td class="px-5 py-4">
                          <div class="flex items-center justify-center gap-2">
                              <button type="button" 
                                      data-modal-target="detail-modal" 
                                      data-modal-show="detail-modal" 
                                      onclick="populateDetail(<?php echo $index; ?>)" 
                                      class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all duration-200 bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white border-transparent hover:border-blue-600">
                                  <i class="fa-solid fa-eye"></i> Detail
                              </button>
                              
                              <!-- Tombol Print: HANYA aktif jika Status Sukses (1), jika tidak di-disable -->
                              <?php if ($raw_status === 1): ?>
                                  <button type="button" onclick="printOrder('<?php echo htmlspecialchars($o['code'] ?? ''); ?>')" 
                                          class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 border rounded-lg text-xs font-bold transition-all duration-200 shadow-sm bg-gray-50 text-gray-600 hover:bg-gray-800 hover:text-white hover:border-gray-800">
                                      <i class="fa-solid fa-print"></i> Print
                                  </button>
                              <?php else: ?>
                                  <button type="button" disabled 
                                          class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 border rounded-lg text-xs font-bold transition-all duration-200 shadow-sm bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed opacity-60">
                                      <i class="fa-solid fa-print"></i> Print
                                  </button>
                              <?php endif; ?>
                          </div>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>

            <!-- Pagination Control -->
            <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                <span class="text-sm font-medium text-gray-500">
                  <?php
                  $start = ($total_records > 0) ? $offset + 1 : 0;
                  $end = $offset + count($orders);
                  ?>
                  Menampilkan <span class="font-medium text-gray-900"><?php echo $start; ?> - <?php echo $end; ?></span> dari <span class="font-medium text-gray-900"><?php echo $total_records; ?></span> pesanan
                </span>
                
                <div class="flex space-x-1">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?><?php echo $query_string; ?>" 
                          class="rounded border border-gray-200 px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-50 transition-colors"><i class="fa-solid fa-chevron-left text-xs"></i></a>
                    <?php else: ?>
                        <button disabled class="rounded border border-gray-200/50 px-3 py-1.5 text-sm text-gray-600/50 hover:bg-gray-50/50 transition-colors"><i class="fa-solid fa-chevron-left text-xs cursor-not-allowed"></i></button>
                    <?php endif; ?>

                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?><?php echo $query_string; ?>" 
                          class="rounded border px-3 py-1.5 text-sm transition-colors <?php echo ($i == $page) ? 'border-blue-600 bg-blue-600 text-white shadow-sm' : 'border-gray-200 text-gray-600 hover:bg-gray-50'; ?>">
                          <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?><?php echo $query_string; ?>" 
                          class="rounded border border-gray-200 px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-50 transition-colors"><i class="fa-solid fa-chevron-right text-xs"></i></a>
                    <?php else: ?>
                        <button disabled class="rounded border border-gray-200/50 px-3 py-1.5 text-sm text-gray-600/50 hover:bg-gray-50/50 transition-colors"><i class="fa-solid fa-chevron-right text-xs cursor-not-allowed"></i></button>
                    <?php endif; ?>
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
      const pageOrders = <?php echo json_encode($orders); ?>;

      function populateDetail(index) {
          const o = pageOrders[index];
          
          let paymentMethod = "Lainnya";
          if (o.payment == 1) paymentMethod = "Kasir";
          if (o.payment == 2) paymentMethod = "Online";

          // Memasukkan data ke dalam elemen Modal
          document.getElementById('det-code').innerText = o.code || '-';
          document.getElementById('det-user').innerText = o.user_name || '-';
          document.getElementById('det-table').innerText = o.table_name || '-';
          document.getElementById('det-customer').innerText = o.customer_name || '-';
          document.getElementById('det-subtotal').innerText = 'Rp ' + parseFloat(o.subtotal || 0).toLocaleString('id-ID');
          document.getElementById('det-tax').innerText = 'Rp ' + parseFloat(o.tax || 0).toLocaleString('id-ID');
          document.getElementById('det-total').innerText = 'Rp ' + parseFloat(o.total || 0).toLocaleString('id-ID');
          document.getElementById('det-payment').innerText = paymentMethod;
          document.getElementById('det-status').innerText = o.status || '-';
          document.getElementById('det-detail').innerText = o.detail || 'Tidak ada catatan.';

          // Mengatur badge status untuk Modal
          const rawStatus = parseInt(o.status);
          let modalStatus = '';
          if(rawStatus === 1) modalStatus = '<span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-bold border border-green-200">Sukses</span>';
          else if(rawStatus === 2) modalStatus = '<span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded text-xs font-bold border border-yellow-200">Pending</span>';
          else modalStatus = '<span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs font-bold border border-red-200">Expired</span>';
          
          document.getElementById('det-status').innerHTML = modalStatus;
      }

      function printOrder(code) {
          alert(`Mencetak struk pesanan dengan kode ${code}...`);
      }
    </script>



<?php 

include '../layout/footer.php'; 
?>