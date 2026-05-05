<?php
session_start();
require_once '../../../config.php';
require_once '../../../path.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 2) {
    header("Location: " . BASE_URL . "auth/login");
    exit;
}

if (isset($_GET['export_json']) && $_GET['export_json'] == 1) {
    $filter_date = isset($_GET['filter_date']) ? $_GET['filter_date'] : 'all';
    $sort        = isset($_GET['sort'])        ? $_GET['sort']        : 'terbaru';

    $where_sql = "status = 1";
    if ($filter_date == 'harian') {
        $where_sql .= " AND DATE(created_at) = CURDATE()";
    } elseif ($filter_date == 'mingguan') {
        $where_sql .= " AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
    } elseif ($filter_date == 'bulanan') {
        $where_sql .= " AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";
    } elseif ($filter_date == 'tahunan') {
        $where_sql .= " AND YEAR(created_at) = YEAR(CURDATE())";
    }

    $order_sql = "ORDER BY created_at DESC";
    if ($sort == 'terlama')  $order_sql = "ORDER BY created_at ASC";
    elseif ($sort == 'termahal') $order_sql = "ORDER BY total DESC";
    elseif ($sort == 'termurah') $order_sql = "ORDER BY total ASC";

    try {
        $stmt = $pdo->query("SELECT * FROM `order` WHERE $where_sql $order_sql");
        $all  = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $all = [];
    }

    header('Content-Type: application/json');
    echo json_encode($all);
    exit;
}


$limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$filter_date = isset($_GET['filter_date']) ? $_GET['filter_date'] : 'all';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'terbaru';

$where_sql = "status = 1";

if ($filter_date == 'harian') {
    $where_sql .= " AND DATE(created_at) = CURDATE()";
} elseif ($filter_date == 'mingguan') {
    $where_sql .= " AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
} elseif ($filter_date == 'bulanan') {
    $where_sql .= " AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";
} elseif ($filter_date == 'tahunan') {
    $where_sql .= " AND YEAR(created_at) = YEAR(CURDATE())";
}

try {
    $total_stmt = $pdo->query("SELECT SUM(total) FROM `order` WHERE " . $where_sql);
    $grand_total = $total_stmt->fetchColumn() ?: 0;
} catch (PDOException $e) {
    $grand_total = 0; 
}
//t
$order_sql = "ORDER BY created_at DESC";
if ($sort == 'terlama') {
    $order_sql = "ORDER BY created_at ASC";
} elseif ($sort == 'termahal') {
    $order_sql = "ORDER BY total DESC";
} elseif ($sort == 'termurah') {
    $order_sql = "ORDER BY total ASC";
}

$count_stmt = $pdo->query("SELECT COUNT(*) FROM `order` WHERE " . $where_sql);
$total_records = $count_stmt->fetchColumn();

$total_pages = ceil($total_records / $limit);
if ($page > $total_pages && $total_pages > 0) {
    $page = $total_pages;
}

$offset = ($page - 1) * $limit;

try {
    $query = "SELECT * FROM `order` WHERE $where_sql $order_sql LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $orders = [];
}

$query_string = "&limit=$limit&filter_date=$filter_date&sort=$sort";

?>

<?php

$pageTitle = "Laporan Penjualan";
$currentPage = "laporan"; 

include '../layout/header.php';
include '../layout/sidebar.php';

?>

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
                  <button onclick="openExportModal()" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700" type="button">
                    <i class="fa-solid fa-file-export mr-2"></i>Export
                  </button>
                </div>
                
                <form method="GET" id="filter-form" class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto items-center">
                <input type="hidden" name="page" id="page-input" value="<?php echo $page; ?>">

                <div class="relative w-full sm:w-auto">
                  <select name="filter_date" onchange="document.getElementById('page-input').value = 1; this.form.submit()" class="appearance-none w-full sm:w-44 bg-white border border-gray-300 text-gray-700 py-2.5 pl-4 pr-10 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm font-medium transition-all cursor-pointer">
                    <option value="all" <?php echo ($filter_date == 'all') ? 'selected' : ''; ?>>Semua Waktu</option>
                    <option value="harian" <?php echo ($filter_date == 'harian') ? 'selected' : ''; ?>>Hari Ini</option>
                    <option value="mingguan" <?php echo ($filter_date == 'mingguan') ? 'selected' : ''; ?>>Minggu Ini</option>
                    <option value="bulanan" <?php echo ($filter_date == 'bulanan') ? 'selected' : ''; ?>>Bulan Ini</option>
                    <option value="tahunan" <?php echo ($filter_date == 'tahunan') ? 'selected' : ''; ?>>Tahun Ini</option>
                  </select>
                  <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                    <i class="fa-regular fa-calendar text-xs"></i>
                  </div>
                </div>

                <div class="relative w-full sm:w-auto">
                  <select name="sort" onchange="document.getElementById('page-input').value = 1; this.form.submit()" class="appearance-none w-full sm:w-44 bg-white border border-gray-300 text-gray-700 py-2.5 pl-4 pr-10 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm font-medium transition-all cursor-pointer">
                    <option value="terbaru" <?php echo ($sort == 'terbaru') ? 'selected' : ''; ?>>Paling Baru</option>
                    <option value="terlama" <?php echo ($sort == 'terlama') ? 'selected' : ''; ?>>Paling Lama</option>
                    <option value="termahal" <?php echo ($sort == 'termahal') ? 'selected' : ''; ?>>Total Tertinggi</option>
                    <option value="termurah" <?php echo ($sort == 'termurah') ? 'selected' : ''; ?>>Total Terendah</option>
                  </select>
                  <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                    <i class="fa-solid fa-arrow-up-short-wide text-xs"></i>
                  </div>
                </div>
              </form>

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
                <tbody class="divide-y divide-gray-100 bg-white">
                  <?php if (empty($orders)): ?>
                    <tr>
                      <td colspan="8" class="px-5 py-8 text-center text-gray-500">Tidak ada laporan transaksi yang ditemukan.</td>
                    </tr>
                  <?php else: ?>
                    <?php 
                    $no = $offset + 1;
                    foreach ($orders as $index => $o): 
                        $detail_text = $o['detail'] ?? '';
                        $detail_short = (strlen($detail_text) > 35) ? substr($detail_text, 0, 35) . '...' : $detail_text;
                        
                        $payment = "Lainnya";
                        if ($o['payment'] == 1) $payment = "Kasir";
                        if ($o['payment'] == 2) $payment = "Online";
                    ?>
                    <tr class="hover:bg-blue-50/50 transition-colors group">
                      <td class="px-5 py-4 text-center font-medium text-gray-500"><?php echo $no++; ?></td>
                      <td class="px-5 py-4 font-bold text-gray-900">
                          <div class="flex items-center gap-2">
                            <i class="fa-solid fa-user-tie text-gray-300"></i> <?php echo htmlspecialchars($o['user_name'] ?? '-'); ?>
                          </div>
                      </td>
                      <td class="px-5 py-4 text-gray-600 max-w-[200px] truncate" title="<?php echo htmlspecialchars($detail_text); ?>">
                          <?php echo htmlspecialchars($detail_short); ?>
                      </td>
                      <td class="px-5 py-4 text-gray-700"><?php echo htmlspecialchars($o['customer_name'] ?? '-'); ?></td>
                      <td class="px-5 py-4 font-bold text-green-600">Rp <?php echo number_format((float)$o['total'], 0, ',', '.'); ?></td>
                      <td class="px-5 py-4">
                          <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded text-xs font-semibold <?php echo ($payment === 'Online') ? 'bg-purple-50 text-purple-600 border border-purple-200' : 'bg-blue-50 text-blue-600 border border-blue-200'; ?>">
                              <?php echo ($payment === 'Online') ? '<i class="fa-solid fa-globe"></i>' : '<i class="fa-solid fa-cash-register"></i>'; ?> 
                              <?php echo $payment; ?>
                          </span>
                      </td>
                      <td class="px-5 py-4 text-gray-500 text-xs font-medium"><i class="fa-regular fa-clock mr-1"></i> <?php echo htmlspecialchars($o['created_at']); ?></td>
                      <td class="px-5 py-4 text-center">
                          <button onclick="showDetail(<?php echo $index; ?>)" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all duration-200 bg-gray-50 text-gray-600 hover:bg-blue-600 hover:text-white border border-gray-200 hover:border-blue-600 shadow-sm">
                              Detail
                          </button>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
                <tfoot class="bg-gray-50 border-t border-gray-200 font-bold text-gray-800">
                  <tr>
                    <td colspan="4" class="px-5 py-4 text-right">Total Pendapatan Terfilter:</td>
                    <td colspan="4" class="px-5 py-4 text-blue-700 text-lg">Rp <?php echo number_format((float)$grand_total, 0, ',', '.'); ?></td>
                  </tr>
                </tfoot>
              </table>
            </div>

            <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                <span class="text-sm text-gray-500">
                  <?php
                  $start = ($total_records > 0) ? $offset + 1 : 0;
                  $end = $offset + count($orders);
                  ?>
                  Menampilkan <span class="font-medium text-gray-900"><?php echo $start; ?> - <?php echo $end; ?></span> dari <span class="font-medium text-gray-900"><?php echo $total_records; ?></span> laporan
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

    
    <!-- SheetJS CDN  -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <!-- Modal Export Excel -->
    <div id="export-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/50 backdrop-blur-sm">
        <div class="relative w-full max-w-md p-4">
            <div class="relative rounded-xl border border-gray-200 bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-gray-100 p-4 md:p-5">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <i class="fa-solid fa-file-excel text-green-600"></i> Export Laporan Penjualan
                    </h3>
                    <button onclick="closeExportModal()" class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-transparent text-sm text-gray-400 hover:bg-gray-200 hover:text-gray-900">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <div class="p-4 md:p-5 space-y-4">
                    <p class="text-sm text-gray-600">Pilih cakupan data yang ingin di-export ke file Excel (<code>.xlsx</code>).</p>

                    <div class="grid grid-cols-2 gap-3">
                        <button onclick="exportExcel('page')" class="flex flex-col items-center gap-2 rounded-xl border-2 border-gray-200 p-4 text-center text-sm font-medium text-gray-700 transition-all hover:border-blue-500 hover:bg-blue-50 hover:text-blue-700">
                            <i class="fa-solid fa-file-lines text-2xl text-blue-400"></i>
                            <span>Halaman Ini</span>
                            <span class="text-xs font-normal text-gray-400"><?php echo count($orders); ?> data</span>
                        </button>
                        <button onclick="exportExcel('all')" class="flex flex-col items-center gap-2 rounded-xl border-2 border-gray-200 p-4 text-center text-sm font-medium text-gray-700 transition-all hover:border-green-500 hover:bg-green-50 hover:text-green-700">
                            <i class="fa-solid fa-database text-2xl text-green-400"></i>
                            <span>Semua Data</span>
                            <span class="text-xs font-normal text-gray-400"><?php echo $total_records; ?> data</span>
                        </button>
                    </div>

                    <div class="rounded-lg bg-blue-50 border border-blue-100 p-3 text-xs text-blue-700">
                        <i class="fa-solid fa-circle-info mr-1"></i>
                        Filter aktif: <strong><?php
                            $filter_labels = ['all'=>'Semua Waktu','harian'=>'Hari Ini','mingguan'=>'Minggu Ini','bulanan'=>'Bulan Ini','tahunan'=>'Tahun Ini'];
                            echo $filter_labels[$filter_date] ?? 'Semua Waktu';
                        ?></strong> &mdash; Urutan: <strong><?php
                            $sort_labels = ['terbaru'=>'Paling Baru','terlama'=>'Paling Lama','termahal'=>'Total Tertinggi','termurah'=>'Total Terendah'];
                            echo $sort_labels[$sort] ?? 'Paling Baru';
                        ?></strong>
                    </div>
                </div>

                <div class="flex items-center gap-3 p-4 md:p-5 border-t border-gray-100">
                    <button onclick="closeExportModal()" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">Batal</button>
                </div>
            </div>
        </div>
    </div>

    <script>
      const pageOrders = <?php echo json_encode($orders); ?>;
      const allOrdersUrl = '?export_json=1&filter_date=<?php echo urlencode($filter_date); ?>&sort=<?php echo urlencode($sort); ?>';

      function openExportModal() {
          const modal = document.getElementById('export-modal');
          modal.classList.remove('hidden');
          modal.classList.add('flex');
      }
      function closeExportModal() {
          const modal = document.getElementById('export-modal');
          modal.classList.add('hidden');
          modal.classList.remove('flex');
      }
      document.getElementById('export-modal').addEventListener('click', function(e) {
          if (e.target === this) closeExportModal();
      });

    //   export excel
      function buildExcelRows(orders) {
          const rows = [
              ['No','Kode Pesanan','Nama Kasir','Nama Pelanggan','Menu Pesanan','Subtotal (Rp)','Pajak (Rp)','Total (Rp)','Metode Pembayaran','Tanggal & Waktu']
          ];
          orders.forEach((o, i) => {
              const payment = o.payment == 1 ? 'Kasir' : o.payment == 2 ? 'Online' : 'Lainnya';
              rows.push([
                  i + 1,
                  o.code || '-',
                  o.user_name || '-',
                  o.customer_name || '-',
                  o.detail || '-',
                  parseFloat(o.subtotal || 0),
                  parseFloat(o.tax || 0),
                  parseFloat(o.total || 0),
                  payment,
                  o.created_at || '-'
              ]);
          });
          return rows;
      }

      function downloadExcel(rows, filename) {
          const ws = XLSX.utils.aoa_to_sheet(rows);
          ws['!cols'] = [
              {wch:5},{wch:18},{wch:20},{wch:20},{wch:40},
              {wch:16},{wch:14},{wch:16},{wch:18},{wch:20}
          ];
          const wb = XLSX.utils.book_new();
          XLSX.utils.book_append_sheet(wb, ws, 'Laporan Penjualan');
          XLSX.writeFile(wb, filename);
          closeExportModal();
      }

      function exportExcel(scope) {
          const filterDate  = '<?php echo $filter_date; ?>';
          const filterLabel = {'all':'Semua','harian':'Harian','mingguan':'Mingguan','bulanan':'Bulanan','tahunan':'Tahunan'}[filterDate] || 'Semua';
          const today       = new Date().toISOString().slice(0, 10);
          const filename    = 'Laporan_Penjualan_' + filterLabel + '_' + today + '.xlsx';

          if (scope === 'page') {
              downloadExcel(buildExcelRows(pageOrders), filename);
          } else {
              const btn = event.currentTarget;
              const originalHTML = btn.innerHTML;
              btn.disabled = true;
              btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-2xl text-green-400"></i><span>Memuat...</span><span class="text-xs font-normal text-gray-400">Mohon tunggu</span>';

              fetch(allOrdersUrl)
                  .then(r => r.json())
                  .then(data => { downloadExcel(buildExcelRows(data), filename); })
                  .catch(() => alert('Gagal mengambil data. Silakan coba lagi.'))
                  .finally(() => { btn.disabled = false; btn.innerHTML = originalHTML; });
          }
      }

      function showDetail(index) {
          const o = pageOrders[index];
          
          let paymentMethod = "Lainnya";
          if (o.payment == 1) paymentMethod = "Kasir";
          if (o.payment == 2) paymentMethod = "Online";

          document.getElementById('det-code').innerText = o.code || '-';
          document.getElementById('det-user').innerText = o.user_name || '-';
          document.getElementById('det-table').innerText = o.table_name || '-';
          document.getElementById('det-customer').innerText = o.customer_name || '-';
          document.getElementById('det-subtotal').innerText = 'Rp ' + parseFloat(o.subtotal || 0).toLocaleString('id-ID');
          document.getElementById('det-tax').innerText = 'Rp ' + parseFloat(o.tax || 0).toLocaleString('id-ID');
          document.getElementById('det-total').innerText = 'Rp ' + parseFloat(o.total || 0).toLocaleString('id-ID');
          document.getElementById('det-payment').innerText = paymentMethod;
          document.getElementById('det-date').innerText = o.created_at || '-';
          document.getElementById('det-detail').innerText = o.detail || 'Tidak ada catatan.';

          document.getElementById('trigger-detail-modal').click();
      }
    </script>


<?php 
include '../layout/footer.php'; 
?>

