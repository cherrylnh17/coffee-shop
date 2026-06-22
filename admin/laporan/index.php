<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../path.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 2) {
    header("Location: " . BASE_URL . "auth/login");
    exit;
}

// ── Ambil semua kasir unik dari tabel order untuk dropdown ──────────────────
try {
    $kasir_stmt = $pdo->query("SELECT DISTINCT user_id, user_name FROM `order` WHERE user_name IS NOT NULL ORDER BY user_name ASC");
    $kasir_list = $kasir_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $kasir_list = [];
}

// ── Parameter filter ────────────────────────────────────────────────────────
$limit        = isset($_GET['limit'])       ? max(1, (int)$_GET['limit']) : 10;
$page         = isset($_GET['page'])        ? max(1, (int)$_GET['page'])  : 1;
$filter_date  = isset($_GET['filter_date']) ? $_GET['filter_date']        : 'all';
$start_date   = isset($_GET['start_date'])  ? trim($_GET['start_date'])   : '';
$end_date     = isset($_GET['end_date'])    ? trim($_GET['end_date'])     : '';
$filter_kasir = isset($_GET['filter_kasir']) ? trim($_GET['filter_kasir']) : '';

// ── Build WHERE ─────────────────────────────────────────────────────────────
function buildWhere($filter_date, $start_date, $end_date, $filter_kasir) {
    $where = "status = 1";
    // preset date
    if ($filter_date == 'harian') {
        $where .= " AND DATE(created_at) = CURDATE()";
    } elseif ($filter_date == 'mingguan') {
        $where .= " AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
    } elseif ($filter_date == 'bulanan') {
        $where .= " AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";
    } elseif ($filter_date == 'tahunan') {
        $where .= " AND YEAR(created_at) = YEAR(CURDATE())";
    } elseif ($filter_date == 'custom') {
        if (!empty($start_date) && !empty($end_date)) {
            $sd = date('Y-m-d', strtotime($start_date));
            $ed = date('Y-m-d', strtotime($end_date));
            $where .= " AND DATE(created_at) BETWEEN '$sd' AND '$ed'";
        } elseif (!empty($start_date)) {
            $sd = date('Y-m-d', strtotime($start_date));
            $where .= " AND DATE(created_at) >= '$sd'";
        } elseif (!empty($end_date)) {
            $ed = date('Y-m-d', strtotime($end_date));
            $where .= " AND DATE(created_at) <= '$ed'";
        }
    }
    if (!empty($filter_kasir)) {
        $safe = addslashes($filter_kasir);
        $where .= " AND user_name = '$safe'";
    }
    return $where;
}

$where_sql = buildWhere($filter_date, $start_date, $end_date, $filter_kasir);

// ── Export JSON ─────────────────────────────────────────────────────────────
if (isset($_GET['export_json']) && $_GET['export_json'] == 1) {
    $order_sql = "ORDER BY created_at DESC";

    try {
        $stmt = $pdo->query("SELECT * FROM `order` WHERE $where_sql $order_sql");
        $all  = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) { $all = []; }

    header('Content-Type: application/json');
    echo json_encode($all);
    exit;
}

// ── Grand total & pagination ─────────────────────────────────────────────────
try {
    $total_stmt  = $pdo->query("SELECT SUM(total) FROM `order` WHERE $where_sql");
    $grand_total = $total_stmt->fetchColumn() ?: 0;
} catch (PDOException $e) { $grand_total = 0; }

$order_sql = "ORDER BY created_at DESC";

$count_stmt    = $pdo->query("SELECT COUNT(*) FROM `order` WHERE $where_sql");
$total_records = $count_stmt->fetchColumn();
$total_pages   = ceil($total_records / $limit);
if ($page > $total_pages && $total_pages > 0) $page = $total_pages;

$offset = ($page - 1) * $limit;

try {
    $stmt = $pdo->prepare("SELECT * FROM `order` WHERE $where_sql $order_sql LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { $orders = []; }

// ── Meja Paling Sering Dikunjungi ──────────────────────────────────────────
try {
    $top_meja_stmt = $pdo->query("SELECT table_name, COUNT(*) AS visit_count FROM `order` WHERE $where_sql AND table_name IS NOT NULL AND table_name != '' GROUP BY table_name ORDER BY visit_count DESC LIMIT 10");
    $top_meja = $top_meja_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { $top_meja = []; }

// ── Kasir Paling Sering Melayani ──────────────────────────────────────────
try {
    $top_kasir_stmt = $pdo->query("SELECT user_name, COUNT(*) AS serve_count FROM `order` WHERE $where_sql AND user_name IS NOT NULL GROUP BY user_name ORDER BY serve_count DESC LIMIT 10");
    $top_kasir = $top_kasir_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { $top_kasir = []; }

$params = [];
$params['filter_date'] = $filter_date;

if ($limit != 10) {
    $params['limit'] = $limit;
}
if ($filter_date === 'custom') {
    if (!empty($start_date)) $params['start_date'] = $start_date;
    if (!empty($end_date)) $params['end_date'] = $end_date;
}
if (!empty($filter_kasir)) {
    $params['filter_kasir'] = $filter_kasir;
}

$query_string = "";
if (!empty($params)) {
    $query_string = "&" . http_build_query($params);
}

// Label filter aktif untuk info box
$filter_labels = ['all'=>'Semua Waktu','harian'=>'Hari Ini','mingguan'=>'Minggu Ini','bulanan'=>'Bulan Ini','tahunan'=>'Tahun Ini','custom'=>'Custom'];
$active_filter_label = $filter_labels[$filter_date] ?? 'Semua Waktu';
if ($filter_date == 'custom') {
    $active_filter_label = 'Custom: ' . ($start_date ?: '...') . ' s/d ' . ($end_date ?: '...');
}
?>

<?php
$pageTitle   = "Laporan Penjualan";
$currentPage = "laporan";

include __DIR__ . '/../layout/header.php'; 
include __DIR__ . '/../layout/sidebar.php';
?>

    <main class="relative min-h-screen pt-[74px] transition-all duration-300 lg:ml-[280px] pc-main">
      <div class="p-4 sm:p-6 lg:p-8">
        <!-- Statistik Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
          <!-- Meja Paling Sering Dikunjungi -->
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center gap-2 mb-4">
              <div class="w-8 h-8 rounded-lg bg-orange-50 flex items-center justify-center">
                <i class="fa-solid fa-chair text-orange-500 text-sm"></i>
              </div>
              <h4 class="font-bold text-gray-800">Meja Paling Sering Dikunjungi</h4>
            </div>
            <?php if (empty($top_meja)): ?>
              <p class="text-sm text-gray-400 text-center py-4">Belum ada data.</p>
            <?php else: ?>
              <div class="space-y-2">
                <?php foreach ($top_meja as $idx => $tm): 
                  $max_count = $top_meja[0]['visit_count'];
                  $pct = ($max_count > 0) ? round(($tm['visit_count'] / $max_count) * 100) : 0;
                ?>
                <div class="flex items-center gap-3">
                  <span class="w-6 h-6 flex items-center justify-center rounded-full bg-orange-100 text-orange-600 text-xs font-bold flex-shrink-0"><?= $idx + 1 ?></span>
                  <div class="flex-1">
                    <div class="flex items-center justify-between mb-1">
                      <span class="text-sm font-semibold text-gray-700"><?= htmlspecialchars($tm['table_name']) ?></span>
                      <span class="text-xs font-bold text-orange-600"><?= $tm['visit_count'] ?>x</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                      <div class="bg-orange-500 h-1.5 rounded-full" style="width: <?= $pct ?>%"></div>
                    </div>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>

          <!-- Kasir Paling Sering Melayani -->
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center gap-2 mb-4">
              <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                <i class="fa-solid fa-user-tie text-blue-500 text-sm"></i>
              </div>
              <h4 class="font-bold text-gray-800">Kasir Paling Sering Melayani</h4>
            </div>
            <?php if (empty($top_kasir)): ?>
              <p class="text-sm text-gray-400 text-center py-4">Belum ada data.</p>
            <?php else: ?>
              <div class="space-y-2">
                <?php foreach ($top_kasir as $idx => $tk): 
                  $max_count = $top_kasir[0]['serve_count'];
                  $pct = ($max_count > 0) ? round(($tk['serve_count'] / $max_count) * 100) : 0;
                ?>
                <div class="flex items-center gap-3">
                  <span class="w-6 h-6 flex items-center justify-center rounded-full bg-blue-100 text-blue-600 text-xs font-bold flex-shrink-0"><?= $idx + 1 ?></span>
                  <div class="flex-1">
                    <div class="flex items-center justify-between mb-1">
                      <span class="text-sm font-semibold text-gray-700"><?= htmlspecialchars($tk['user_name']) ?></span>
                      <span class="text-xs font-bold text-blue-600"><?= $tk['serve_count'] ?>x</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                      <div class="bg-blue-500 h-1.5 rounded-full" style="width: <?= $pct ?>%"></div>
                    </div>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <div class="gap-x-6">
          <div class="bg-white p-4 md:p-6 rounded-2xl shadow-sm border border-gray-100">

            <!-- Header -->
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-4 pb-4 border-b border-gray-100">
              <div class="flex items-center gap-2">
                <div>
                  <h3 class="font-bold text-xl text-gray-800">Laporan Penjualan</h3>
                </div>
              </div>
              <div class="flex items-center gap-2 w-full sm:w-auto">
                <button onclick="openExportModal()" class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700" type="button">
                  <i class="fa-solid fa-file-export mr-2"></i>Export
                </button>
              </div>
            </div>

            <!-- Filter Bar -->
            <form method="GET" id="filter-form" class="flex flex-col sm:flex-row flex-wrap gap-3 mb-6 bg-gray-50/50 p-4 rounded-xl border border-gray-100 items-end">
              <input type="hidden" name="page" id="page-input" value="1">
              <input type="hidden" name="limit" value="<?php echo $limit; ?>">

              <!-- Filter Periode (termasuk Custom) -->
              <div class="w-full sm:w-auto flex-1 sm:flex-none">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Periode</label>
                <div class="relative">
                  <select name="filter_date" id="filter_date_select" onchange="toggleCustomDate(this.value)"
                          class="appearance-none w-full sm:w-44 bg-white border border-gray-300 text-gray-700 py-2.5 pl-4 pr-10 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm transition-all cursor-pointer">
                    <option value="all"     <?php echo ($filter_date=='all')     ?'selected':''; ?>>Semua Waktu</option>
                    <option value="harian"  <?php echo ($filter_date=='harian')  ?'selected':''; ?>>Hari Ini</option>
                    <option value="mingguan"<?php echo ($filter_date=='mingguan')?'selected':''; ?>>Minggu Ini</option>
                    <option value="bulanan" <?php echo ($filter_date=='bulanan') ?'selected':''; ?>>Bulan Ini</option>
                    <option value="tahunan" <?php echo ($filter_date=='tahunan') ?'selected':''; ?>>Tahun Ini</option>
                    <option value="custom"  <?php echo ($filter_date=='custom')  ?'selected':''; ?>>Custom...</option>
                  </select>
                  <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                    <i class="fa-regular fa-calendar text-xs"></i>
                  </div>
                </div>
              </div>

              <!-- Range Custom (muncul hanya jika pilih Custom) -->
              <div id="custom-date-wrap" class="<?php echo ($filter_date=='custom') ? 'flex' : 'hidden'; ?> flex-col sm:flex-row gap-3 items-end">
                <div class="w-full sm:w-auto">
                  <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Dari Tanggal</label>
                  <input type="date" name="start_date" id="start_date" value="<?php echo htmlspecialchars($start_date); ?>"
                         class="w-full sm:w-40 bg-white border border-gray-300 text-gray-700 py-2.5 px-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </div>
                <div class="w-full sm:w-auto">
                  <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Sampai Tanggal</label>
                  <input type="date" name="end_date" id="end_date" value="<?php echo htmlspecialchars($end_date); ?>"
                         class="w-full sm:w-40 bg-white border border-gray-300 text-gray-700 py-2.5 px-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </div>
              </div>

              <!-- Filter Kasir -->
              <div class="w-full sm:w-auto flex-1 sm:flex-none">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Kasir</label>
                <div class="relative">
                  <select name="filter_kasir" class="appearance-none w-full sm:w-44 bg-white border border-gray-300 text-gray-700 py-2.5 pl-4 pr-10 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm transition-all cursor-pointer">
                    <option value="">Semua Kasir</option>
                    <?php foreach ($kasir_list as $k): ?>
                      <option value="<?php echo htmlspecialchars($k['user_name']); ?>" <?php echo ($filter_kasir == $k['user_name']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($k['user_name']); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                    <i class="fa-solid fa-user-tie text-xs"></i>
                  </div>
                </div>
              </div>

              <!-- Tombol Terapkan -->
              <div class="w-full sm:w-auto flex-1 sm:flex-none">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-lg text-sm transition-colors flex items-center justify-center gap-2">
                  <i class="fa-solid fa-filter"></i> Terapkan
                </button>
              </div>

              <!-- Reset -->
              <?php if (!empty($filter_kasir) || $filter_date !== 'all'): ?>
              <div class="w-full sm:w-auto flex-1 sm:flex-none">
                <a href="?" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold py-2.5 px-4 rounded-lg text-sm transition-colors flex items-center justify-center gap-2">
                  <i class="fa-solid fa-rotate-right"></i> Reset
                </a>
              </div>
              <?php endif; ?>
            </form>

            <!-- Tabel -->
            <div class="overflow-x-auto rounded-xl border border-gray-200">
              <table class="w-full text-sm text-left whitespace-nowrap">
                <thead class="bg-gray-50 text-gray-600 font-semibold border-b border-gray-200 uppercase text-[11px] tracking-wider">
                  <tr>
                    <th class="px-5 py-4">Nama Kasir</th>
                    <th class="px-5 py-4 max-w-[200px]">Menu Pesanan</th>
                    <th class="px-5 py-4">Pelanggan</th>
                    <th class="px-5 py-4">Jumlah (Total)</th>
                    <th class="px-5 py-4">Pembayaran</th>
                    <th class="px-5 py-4">Date & Time</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                  <?php if (empty($orders)): ?>
                    <tr>
                      <td colspan="7" class="px-5 py-8 text-center text-gray-500">Tidak ada laporan transaksi yang ditemukan.</td>
                    </tr>
                  <?php else: ?>
                    <?php
                    $no = $offset + 1;
                    foreach ($orders as $index => $o):
                        $detail_text  = $o['detail'] ?? '';
                        $detail_short = (strlen($detail_text) > 35) ? substr($detail_text, 0, 35) . '...' : $detail_text;
                        $payment = "Lainnya";
                        if ($o['payment'] == 1) $payment = "Kasir";
                        if ($o['payment'] == 2) $payment = "Online";
                    ?>
                    <tr class="hover:bg-blue-50/50 transition-colors group">
                      <td class="px-5 py-4">
                        <button type="button"
                                onclick="openDetail(<?php echo $index; ?>, <?php echo (int)$o['id']; ?>)"
                                class="inline-flex items-center gap-2 bg-blue-50 hover:bg-blue-600 text-blue-700 hover:text-white border border-blue-200 hover:border-blue-600 px-3 py-1.5 rounded-lg shadow-sm transition-all duration-200 cursor-pointer"
                                title="Klik untuk melihat detail transaksi">
                          <i class="fa-solid fa-user-tie"></i>
                          <span class="font-bold"><?php echo htmlspecialchars($o['user_name'] ?? '-'); ?></span>
                          <i class="fa-solid fa-chevron-right text-[10px] ml-1 opacity-70"></i>
                        </button>
                      </td>
                      <td class="px-5 py-4 text-gray-600 max-w-[200px] truncate" title="<?php echo htmlspecialchars($detail_text); ?>">
                        <?php echo htmlspecialchars($detail_short); ?>
                      </td>
                      <td class="px-5 py-4 text-gray-700"><?php echo htmlspecialchars($o['customer_name'] ?? '-'); ?></td>
                      <td class="px-5 py-4 font-bold text-green-600">Rp <?php echo number_format((float)$o['total'], 0, ',', '.'); ?></td>
                      <td class="px-5 py-4">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-md text-xs font-bold <?php echo ($payment === 'Online') ? 'bg-purple-100 text-purple-700/70' : 'bg-teal-100 text-teal-700/70'; ?>">
                          <?php echo ($payment === 'Online') ? '<i class="fa-solid fa-globe"></i>' : '<i class="fa-solid fa-cash-register"></i>'; ?>
                          <?php echo $payment; ?>
                        </span>
                      </td>
                      <td class="px-5 py-4 text-gray-500 text-xs font-medium"><i class="fa-regular fa-clock mr-1"></i> <?php echo htmlspecialchars($o['created_at']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
                <tfoot class="bg-gray-50 border-t border-gray-200 font-bold text-gray-800">
                  <tr>
                    <td colspan="4" class="px-5 py-4 text-right">Total Pendapatan Terfilter:</td>
                    <td colspan="3" class="px-5 py-4 text-blue-700 text-lg">Rp <?php echo number_format((float)$grand_total, 0, ',', '.'); ?></td>
                  </tr>
                </tfoot>
              </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
              <span class="text-sm text-gray-500">
                <?php
                $start = ($total_records > 0) ? $offset + 1 : 0;
                $end   = $offset + count($orders);
                ?>
                Menampilkan <span class="font-medium text-gray-900"><?php echo $start; ?> - <?php echo $end; ?></span>
                dari <span class="font-medium text-gray-900"><?php echo $total_records; ?></span> laporan
              </span>
              <div class="flex space-x-1">
                <?php if ($page > 1): ?>
                  <a href="?page=<?php echo $page - 1; ?><?php echo $query_string; ?>" class="rounded border border-gray-200 px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-50 transition-colors"><i class="fa-solid fa-chevron-left text-xs"></i></a>
                <?php else: ?>
                  <button disabled class="rounded border border-gray-200/50 px-3 py-1.5 text-sm text-gray-600/50 cursor-not-allowed"><i class="fa-solid fa-chevron-left text-xs"></i></button>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                  <a href="?page=<?php echo $i; ?><?php echo $query_string; ?>"
                     class="rounded border px-3 py-1.5 text-sm transition-colors <?php echo ($i == $page) ? 'border-blue-600 bg-blue-600 text-white shadow-sm' : 'border-gray-200 text-gray-600 hover:bg-gray-50'; ?>">
                    <?php echo $i; ?>
                  </a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                  <a href="?page=<?php echo $page + 1; ?><?php echo $query_string; ?>" class="rounded border border-gray-200 px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-50 transition-colors"><i class="fa-solid fa-chevron-right text-xs"></i></a>
                <?php else: ?>
                  <button disabled class="rounded border border-gray-200/50 px-3 py-1.5 text-sm text-gray-600/50 cursor-not-allowed"><i class="fa-solid fa-chevron-right text-xs"></i></button>
                <?php endif; ?>
              </div>
            </div>

          </div>
        </div>
      </div>
    </main>

    <!-- ══════════════════════════════════════════════════════
         MODAL DETAIL (mengikuti order.php)
    ══════════════════════════════════════════════════════ -->
    <div id="detail-modal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-gray-900/50 backdrop-blur-md">
      <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl flex flex-col max-h-[90vh]">

        <!-- Header Modal -->
        <div class="flex-shrink-0 flex items-center justify-between border-b border-gray-100 px-5 py-4">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
              <i class="fa-solid fa-receipt text-blue-600 text-sm"></i>
            </div>
            <h3 class="text-base font-bold text-gray-900">Detail Transaksi</h3>
          </div>
          <button type="button" onclick="closeDetailModal()" class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-700 transition-colors">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <!-- Body Modal -->
        <div class="overflow-y-auto p-5 space-y-4">

          <!-- Info Cards -->
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
            <div class="bg-gray-50 rounded-xl border border-gray-100 px-3 py-2.5">
              <span class="block text-[10px] text-gray-400 uppercase tracking-wider font-semibold mb-1">Kode Pesanan</span>
              <span id="det-code" class="text-sm font-bold text-gray-900 truncate block">-</span>
            </div>
            <div class="bg-gray-50 rounded-xl border border-gray-100 px-3 py-2.5">
              <span class="block text-[10px] text-gray-400 uppercase tracking-wider font-semibold mb-1">Kasir / User</span>
              <span id="det-user" class="text-sm font-bold text-gray-900 block">-</span>
            </div>
            <div class="bg-gray-50 rounded-xl border border-gray-100 px-3 py-2.5">
              <span class="block text-[10px] text-gray-400 uppercase tracking-wider font-semibold mb-1">Meja</span>
              <span id="det-table" class="text-sm font-bold text-gray-900 block">-</span>
            </div>
            <div class="bg-gray-50 rounded-xl border border-gray-100 px-3 py-2.5">
              <span class="block text-[10px] text-gray-400 uppercase tracking-wider font-semibold mb-1">Nama Pelanggan</span>
              <span id="det-customer" class="text-sm font-bold text-gray-900 block">-</span>
            </div>
          </div>

          <!-- Status & Metode Bayar -->
          <div class="flex items-center gap-3 px-4 py-3 bg-gray-50 rounded-xl border border-gray-100">
            <div class="flex items-center gap-2.5 flex-1">
              <div id="det-status-icon" class="w-7 h-7 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-check text-green-600 text-xs"></i>
              </div>
              <div>
                <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold">Status</p>
                <p id="det-status" class="text-sm font-bold text-green-600">-</p>
              </div>
            </div>
            <div class="w-px h-8 bg-gray-200"></div>
            <div class="flex items-center gap-2.5 flex-1">
              <div class="w-7 h-7 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-cash-register text-blue-500 text-xs"></i>
              </div>
              <div>
                <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold">Metode Bayar</p>
                <p id="det-payment" class="text-sm font-bold text-blue-600">-</p>
              </div>
            </div>
            <div class="w-px h-8 bg-gray-200"></div>
            <div class="flex items-center gap-2.5 flex-1">
              <div class="w-7 h-7 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                <i class="fa-regular fa-clock text-gray-500 text-xs"></i>
              </div>
              <div>
                <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold">Waktu</p>
                <p id="det-date" class="text-xs font-semibold text-gray-600">-</p>
              </div>
            </div>
          </div>

          <!-- Item Pesanan -->
          <div>
            <p class="text-[11px] text-gray-400 uppercase tracking-wider font-semibold mb-2 flex items-center gap-1.5">
              <i class="fa-solid fa-bowl-food"></i> Item Pesanan
            </p>
            <div class="border border-gray-200 rounded-xl overflow-hidden">
              <div id="det-items-loading" class="px-4 py-6 text-center text-sm text-gray-400">
                <i class="fa-solid fa-spinner fa-spin mr-1"></i> Memuat item...
              </div>
              <table id="det-items-table" class="w-full text-sm text-left hidden">
                <thead class="bg-gray-50 border-b border-gray-200">
                  <tr>
                    <th class="px-4 py-2.5 text-[10px] text-gray-400 uppercase tracking-wider font-semibold">Menu</th>
                    <th class="px-4 py-2.5 text-[10px] text-gray-400 uppercase tracking-wider font-semibold text-center">Qty</th>
                    <th class="px-4 py-2.5 text-[10px] text-gray-400 uppercase tracking-wider font-semibold text-right">Subtotal</th>
                  </tr>
                </thead>
                <tbody id="det-items-body" class="divide-y divide-gray-100 bg-white"></tbody>
              </table>
              <div id="det-items-empty" class="px-4 py-6 text-center text-sm text-gray-400 hidden">
                Tidak ada item ditemukan.
              </div>
            </div>
          </div>

          <!-- Ringkasan Biaya -->
          <div class="border border-gray-200 rounded-xl overflow-hidden">
            <div class="flex justify-between items-center px-4 py-3 bg-white">
              <span class="text-sm text-gray-500">Subtotal</span>
              <span id="det-subtotal" class="text-sm font-semibold text-gray-800">Rp 0</span>
            </div>
            <!-- Fee dari DB -->
            <div id="det-fees-wrap"></div>
            <div class="border-t border-dashed border-gray-200 mx-4"></div>
            <div class="flex justify-between items-center px-4 py-3.5 bg-blue-50">
              <div class="flex items-center gap-2">
                <i class="fa-solid fa-coins text-blue-500 text-sm"></i>
                <span class="text-sm font-bold text-blue-900">Total Tagihan</span>
              </div>
              <span id="det-total" class="text-base font-bold text-blue-600">Rp 0</span>
            </div>
          </div>

        </div>

        <!-- Footer Modal -->
        <div class="flex-shrink-0 px-5 py-4 border-t border-gray-100 bg-gray-50 rounded-b-2xl flex justify-end">
          <button type="button" onclick="closeDetailModal()" class="px-5 py-2.5 bg-white border border-gray-300 text-gray-600 rounded-xl hover:bg-gray-100 text-sm font-semibold transition-colors">
            Tutup
          </button>
        </div>

      </div>
    </div>

    <!-- SheetJS CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <!-- Modal Export Excel -->
    <div id="export-modal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-gray-900/50 backdrop-blur-md">
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
              Filter aktif: <strong><?php echo htmlspecialchars($active_filter_label); ?></strong>
              <?php if (!empty($filter_kasir)): ?>
                &mdash; Kasir: <strong><?php echo htmlspecialchars($filter_kasir); ?></strong>
              <?php endif; ?>
            </div>
          </div>
          <div class="flex items-center gap-3 p-4 md:p-5 border-t border-gray-100">
            <button onclick="closeExportModal()" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">Batal</button>
          </div>
        </div>
      </div>
    </div>

    <script>
      const pageOrders   = <?php echo json_encode($orders); ?>;
      const BASE_URL_JS  = "<?php echo BASE_URL; ?>";
      // URL export menyertakan SEMUA filter aktif
      const allOrdersUrl = '?export_json=1&filter_date=<?php echo urlencode($filter_date); ?>&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>&filter_kasir=<?php echo urlencode($filter_kasir); ?>';

      function toggleCustomDate(val) {
        const wrap = document.getElementById('custom-date-wrap');
        if (val === 'custom') {
          wrap.classList.remove('hidden');
          wrap.classList.add('flex');
        } else {
          wrap.classList.add('hidden');
          wrap.classList.remove('flex');
          // kosongkan input tanggal agar tidak ikut terkirim
          document.getElementById('start_date').value = '';
          document.getElementById('end_date').value   = '';
        }
      }

      document.getElementById('filter-form').addEventListener('submit', function(e) {
        // Ambil elemen input
        const pageInput = document.getElementById('page-input');
        const limitInput = document.querySelector('input[name="limit"]');
        const filterDate = document.getElementById('filter_date_select');
        const startDate = document.getElementById('start_date');
        const endDate = document.getElementById('end_date');
        const filterKasir = document.querySelector('select[name="filter_kasir"]');

        // 1. Matikan 'page' jika nilainya 1 (karena PHP sudah set default 1)
        if (pageInput && pageInput.value === '1') {
          pageInput.disabled = true;
        }

        // 2. Matikan 'limit' jika nilainya 10 (karena PHP sudah set default 10)
        if (limitInput && limitInput.value === '10') {
          limitInput.disabled = true;
        }

        // 3. Matikan tanggal jika filter bukan 'custom' atau jika tanggalnya kosong
        if (filterDate.value !== 'custom') {
          if (startDate) startDate.disabled = true;
          if (endDate) endDate.disabled = true;
        } else {
          if (startDate && startDate.value.trim() === '') startDate.disabled = true;
          if (endDate && endDate.value.trim() === '') endDate.disabled = true;
        }

        // 4. Matikan kasir jika tidak ada yang dipilih (kosong)
        if (filterKasir && filterKasir.value.trim() === '') {
          filterKasir.disabled = true;
        }
      });

      function rp(val) {
        return "Rp " + parseFloat(val || 0).toLocaleString("id-ID");
      }

      // ── Export Modal ──────────────────────────────────────────────────────
      function openExportModal() {
        const m = document.getElementById('export-modal');
        m.classList.remove('hidden'); m.classList.add('flex');
      }
      function closeExportModal() {
        const m = document.getElementById('export-modal');
        m.classList.add('hidden'); m.classList.remove('flex');
      }
      document.getElementById('export-modal').addEventListener('click', function(e) {
        if (e.target === this) closeExportModal();
      });

      function buildExcelRows(orders) {
        const rows = [['No','Kode Pesanan','Nama Kasir','Nama Pelanggan','Menu Pesanan','Subtotal (Rp)','Pajak (Rp)','Total (Rp)','Metode Pembayaran','Tanggal & Waktu']];
        orders.forEach((o, i) => {
          rows.push([
            i + 1, o.code||'-', o.user_name||'-', o.customer_name||'-',
            o.detail||'-', parseFloat(o.subtotal||0), parseFloat(o.tax||0),
            parseFloat(o.total||0), o.payment==1?'Kasir':o.payment==2?'Online':'Lainnya',
            o.created_at||'-'
          ]);
        });
        return rows;
      }

      function downloadExcel(rows, filename) {
        const ws = XLSX.utils.aoa_to_sheet(rows);
        ws['!cols'] = [{wch:5},{wch:18},{wch:20},{wch:20},{wch:40},{wch:16},{wch:14},{wch:16},{wch:18},{wch:20}];
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Laporan Penjualan');
        XLSX.writeFile(wb, filename);
        closeExportModal();
      }

      function exportExcel(scope) {
        const filterDate  = '<?php echo $filter_date; ?>';
        const kasir       = '<?php echo addslashes($filter_kasir); ?>';
        const today       = new Date().toISOString().slice(0, 10);
        const label = {'all':'Semua','harian':'Harian','mingguan':'Mingguan','bulanan':'Bulanan','tahunan':'Tahunan','custom':'Custom'}[filterDate] || 'Semua';
        const kasirPart = kasir ? '_' + kasir : '';
        const filename  = 'Laporan_Penjualan_' + label + kasirPart + '_' + today + '.xlsx';

        if (scope === 'page') {
          downloadExcel(buildExcelRows(pageOrders), filename);
        } else {
          const btn = event.currentTarget;
          const ori = btn.innerHTML;
          btn.disabled = true;
          btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-2xl text-green-400"></i><span>Memuat...</span><span class="text-xs font-normal text-gray-400">Mohon tunggu</span>';
          fetch(allOrdersUrl)
            .then(r => r.json())
            .then(data => { downloadExcel(buildExcelRows(data), filename); })
            .catch(() => alert('Gagal mengambil data. Silakan coba lagi.'))
            .finally(() => { btn.disabled = false; btn.innerHTML = ori; });
        }
      }

      // ── Detail Modal ──────────────────────────────────────────────────────
      function openDetail(index, orderId) {
        const o = pageOrders[index];
        const s = parseInt(o.status);

        document.getElementById("det-code").innerText     = o.code          || "-";
        document.getElementById("det-user").innerText     = o.user_name     || "-";
        document.getElementById("det-table").innerText    = o.table_name    || "-";
        document.getElementById("det-customer").innerText = o.customer_name || "-";
        document.getElementById("det-subtotal").innerText = rp(o.subtotal);
        document.getElementById("det-total").innerText    = rp(o.total);
        document.getElementById("det-date").innerText     = o.created_at    || "-";

        // Status badge
        const statusEl   = document.getElementById("det-status");
        const statusIcon = document.getElementById("det-status-icon");
        if (s === 1) {
          statusEl.innerText = "Sukses";
          statusEl.className = "text-sm font-bold text-green-600";
          statusIcon.className = "w-7 h-7 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0";
          statusIcon.innerHTML = '<i class="fa-solid fa-check text-green-600 text-xs"></i>';
        } else if (s === 2) {
          statusEl.innerText = "Pending";
          statusEl.className = "text-sm font-bold text-yellow-600";
          statusIcon.className = "w-7 h-7 rounded-lg bg-yellow-100 flex items-center justify-center flex-shrink-0";
          statusIcon.innerHTML = '<i class="fa-solid fa-hourglass-half text-yellow-500 text-xs"></i>';
        } else {
          statusEl.innerText = "Expired";
          statusEl.className = "text-sm font-bold text-red-500";
          statusIcon.className = "w-7 h-7 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0";
          statusIcon.innerHTML = '<i class="fa-solid fa-xmark text-red-500 text-xs"></i>';
        }

        // Metode bayar
        document.getElementById("det-payment").innerText = o.payment == 1 ? "Kasir" : o.payment == 2 ? "Online" : "Lainnya";

        // Reset loading state
        document.getElementById("det-fees-wrap").innerHTML =
          '<div class="px-4 py-2.5 text-xs text-gray-400 italic flex items-center gap-1"><i class="fa-solid fa-spinner fa-spin"></i> Memuat biaya...</div>';
        document.getElementById("det-items-loading").classList.remove("hidden");
        document.getElementById("det-items-table").classList.add("hidden");
        document.getElementById("det-items-empty").classList.add("hidden");
        document.getElementById("det-items-body").innerHTML = "";

        // Tampilkan modal
        const modal = document.getElementById("detail-modal");
        modal.classList.remove("hidden");
        modal.classList.add("flex");
        document.body.classList.add("overflow-hidden");

        // Fetch items
        fetch(BASE_URL_JS + "admin/laporan/items?order_id=" + orderId)
          .then(r => r.json())
          .then(items => {
            document.getElementById("det-items-loading").classList.add("hidden");
            if (!items || items.length === 0) {
              document.getElementById("det-items-empty").classList.remove("hidden");
              return;
            }
            const tbody = document.getElementById("det-items-body");
            items.forEach(item => {
              const hasNote = item.notes && item.notes.trim() !== "";
              const row = document.createElement("tr");
              row.className = "align-top";
              row.innerHTML = `
                <td class="px-4 py-3">
                  <div class="font-semibold text-gray-800">${item.menu_name}</div>
                  ${hasNote ? `<div class="mt-1 flex items-start gap-1.5">
                    <i class="fa-solid fa-note-sticky text-amber-400 text-xs mt-0.5 flex-shrink-0"></i>
                    <span class="text-xs text-amber-600 italic">${item.notes}</span>
                  </div>` : ""}
                </td>
                <td class="px-4 py-3 text-center">
                  <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-blue-50 text-blue-600 text-xs font-bold border border-blue-100">${item.qty}</span>
                </td>
                <td class="px-4 py-3 text-right font-semibold text-gray-800">${rp(item.subtotal)}</td>
              `;
              tbody.appendChild(row);
            });
            document.getElementById("det-items-table").classList.remove("hidden");
          })
          .catch(() => {
            document.getElementById("det-items-loading").classList.add("hidden");
            document.getElementById("det-items-empty").classList.remove("hidden");
          });

        // Fetch fees dari DB
        fetch(BASE_URL_JS + "kasir/history/items?order_id=" + orderId + "&fees=1")
          .then(r => r.json())
          .then(data => {
            const wrap = document.getElementById("det-fees-wrap");
            const fees = data.fees || [];
            if (fees.length === 0) {
              wrap.innerHTML = `
                <div class="flex justify-between items-center px-4 py-3 border-t border-gray-100">
                  <span class="text-sm text-gray-400 italic">Tidak ada biaya tambahan</span>
                  <span class="text-sm text-gray-400">-</span>
                </div>`;
              return;
            }
            let html = `<div class="border-t border-gray-100 px-4 pt-2.5 pb-1">
              <p class="text-[10px] text-gray-400 uppercase tracking-wider font-semibold mb-1.5">Biaya tambahan</p>`;
            fees.forEach(fee => {
              const badge = parseInt(fee.type) === 1
                ? `<span class="ml-1.5 text-[10px] bg-gray-100 text-gray-500 border border-gray-200 rounded px-1.5 py-0.5">${parseFloat(fee.rate)%1===0?parseInt(fee.rate):parseFloat(fee.rate).toFixed(1)}%</span>`
                : '';
              html += `
                <div class="flex justify-between items-center py-1.5">
                  <div class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-gray-300 flex-shrink-0"></span>
                    <span class="text-sm text-gray-500">${fee.name}${badge}</span>
                  </div>
                  <span class="text-sm text-gray-600">${rp(fee.amount)}</span>
                </div>`;
            });
            html += `</div>`;
            wrap.innerHTML = html;
          })
          .catch(() => {
            document.getElementById("det-fees-wrap").innerHTML = "";
          });
      }

      function closeDetailModal() {
        const modal = document.getElementById("detail-modal");
        modal.classList.add("hidden");
        modal.classList.remove("flex");
        document.body.classList.remove("overflow-hidden");
      }

      document.getElementById("detail-modal").addEventListener("click", function(e) {
        if (e.target === this) closeDetailModal();
      });
    </script>

<?php 
include __DIR__ . '/../layout/footer.php'; 
?>