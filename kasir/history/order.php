<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../path.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 1) {
  header("Location: " . BASE_URL . "auth/login");
  exit;
}

$limit         = 10;
$page          = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'all';
$sort          = isset($_GET['sort'])   ? $_GET['sort']   : 'terbaru';

// WHERE
$where_conditions = [];
if ($filter_status === 'success') {
  $where_conditions[] = "o.status = 1";
} elseif ($filter_status === 'pending') {
  $where_conditions[] = "o.status = 2";
} elseif ($filter_status === 'expired') {
  $where_conditions[] = "o.status = 3";
}
$where_sql = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";

// ORDER BY
$order_sql = "ORDER BY o.created_at DESC";
if ($sort === 'terlama')  $order_sql = "ORDER BY o.created_at ASC";
elseif ($sort === 'termahal') $order_sql = "ORDER BY o.total DESC";
elseif ($sort === 'termurah') $order_sql = "ORDER BY o.total ASC";

// Query langsung ke tabel order — user_name & table_name sudah tersimpan di kolom
$from_sql  = "FROM `order` o";

try {
  $count_stmt    = $pdo->query("SELECT COUNT(*) $from_sql $where_sql");
  $total_records = $count_stmt->fetchColumn();
  $total_pages   = $total_records ? ceil($total_records / $limit) : 0;
  if ($page > $total_pages && $total_pages > 0) $page = $total_pages;

  $offset = ($page - 1) * $limit;

  $query = "SELECT o.* $from_sql $where_sql $order_sql LIMIT :limit OFFSET :offset";

  $stmt = $pdo->prepare($query);
  $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
  $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
  $stmt->execute();
  $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $orders        = [];
  $total_records = 0;
  $total_pages   = 0;
  $offset        = 0;
}

$query_string = "&status=$filter_status&sort=$sort";
?>
<?php
$pageTitle   = "History Pesanan";
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

            <div class="relative w-full sm:w-auto">
              <select name="status" onchange="document.getElementById('page-input').value=1;this.form.submit()"
                class="appearance-none w-full sm:w-44 bg-white border border-gray-300 text-gray-700 py-2.5 pl-4 pr-10 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-medium transition-all cursor-pointer">
                <option value="all" <?php echo $filter_status == 'all'     ? 'selected' : ''; ?>>Semua Status</option>
                <option value="success" <?php echo $filter_status == 'success' ? 'selected' : ''; ?>>Sukses</option>
                <option value="pending" <?php echo $filter_status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="expired" <?php echo $filter_status == 'expired' ? 'selected' : ''; ?>>Expired</option>
              </select>
              <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                <i class="fa-solid fa-chevron-down text-xs"></i>
              </div>
            </div>

            <div class="relative w-full sm:w-auto">
              <select name="sort" onchange="document.getElementById('page-input').value=1;this.form.submit()"
                class="appearance-none w-full sm:w-44 bg-white border border-gray-300 text-gray-700 py-2.5 pl-4 pr-10 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-medium transition-all cursor-pointer">
                <option value="terbaru" <?php echo $sort == 'terbaru'  ? 'selected' : ''; ?>>Paling Baru</option>
                <option value="terlama" <?php echo $sort == 'terlama'  ? 'selected' : ''; ?>>Paling Lama</option>
                <option value="termahal" <?php echo $sort == 'termahal' ? 'selected' : ''; ?>>Total Termahal</option>
                <option value="termurah" <?php echo $sort == 'termurah' ? 'selected' : ''; ?>>Total Termurah</option>
              </select>
              <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                <i class="fa-solid fa-arrow-up-short-wide text-xs"></i>
              </div>
            </div>
          </form>
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
                    <td class="px-5 py-4"><?php echo $statusBadge; ?></td>
                    <td class="px-5 py-4">
                      <div class="flex items-center justify-center gap-2">
                        <button type="button"
                          onclick="openDetail(<?php echo $index; ?>, <?php echo (int)$o['id']; ?>)"
                          class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all duration-200 bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white border-transparent hover:border-blue-600">
                          <i class="fa-solid fa-eye"></i> Detail
                        </button>

                        <?php if ($raw_status === 1): ?>
                          <form action="struk" method="POST">
                            <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                            <button type="submit"
                              class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 border rounded-lg text-xs font-bold transition-all duration-200 shadow-sm bg-gray-50 text-gray-600 hover:bg-gray-800 hover:text-white hover:border-gray-800">
                              <i class="fa-solid fa-print"></i> Print
                            </button>
                          </form>
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

        <!-- Pagination -->
        <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
          <span class="text-sm font-medium text-gray-500">
            <?php
            $start = $total_records > 0 ? $offset + 1 : 0;
            $end   = $offset + count($orders);
            ?>
            Menampilkan <span class="font-medium text-gray-900"><?php echo $start; ?> - <?php echo $end; ?></span>
            dari <span class="font-medium text-gray-900"><?php echo $total_records; ?></span> pesanan
          </span>

          <div class="flex space-x-1">
            <?php if ($page > 1): ?>
              <a href="?page=<?php echo $page - 1; ?><?php echo $query_string; ?>"
                class="rounded border border-gray-200 px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                <i class="fa-solid fa-chevron-left text-xs"></i>
              </a>
            <?php else: ?>
              <button disabled class="rounded border border-gray-200/50 px-3 py-1.5 text-sm text-gray-600/50 cursor-not-allowed">
                <i class="fa-solid fa-chevron-left text-xs"></i>
              </button>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
              <a href="?page=<?php echo $i; ?><?php echo $query_string; ?>"
                class="rounded border px-3 py-1.5 text-sm transition-colors <?php echo $i == $page ? 'border-blue-600 bg-blue-600 text-white shadow-sm' : 'border-gray-200 text-gray-600 hover:bg-gray-50'; ?>">
                <?php echo $i; ?>
              </a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
              <a href="?page=<?php echo $page + 1; ?><?php echo $query_string; ?>"
                class="rounded border border-gray-200 px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                <i class="fa-solid fa-chevron-right text-xs"></i>
              </a>
            <?php else: ?>
              <button disabled class="rounded border border-gray-200/50 px-3 py-1.5 text-sm text-gray-600/50 cursor-not-allowed">
                <i class="fa-solid fa-chevron-right text-xs"></i>
              </button>
            <?php endif; ?>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>
<!-- [ Main Content ] end -->

<!-- Modal Detail Pesanan -->
<div id="detail-modal"
  class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/50 backdrop-blur-sm overflow-y-auto">
  <div class="relative w-full max-w-2xl p-4 my-4">
    <div class="relative rounded-xl border border-gray-200 bg-white shadow-xl">

      <!-- Header -->
      <div class="flex items-center justify-between border-b border-gray-100 p-4 md:p-5">
        <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
          <i class="fa-solid fa-receipt text-blue-600"></i> Detail Transaksi
        </h3>
        <button type="button" onclick="closeDetailModal()"
          class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:bg-gray-200 hover:text-gray-900">
          <i class="fa-solid fa-xmark text-lg"></i>
        </button>
      </div>

      <div class="p-4 md:p-5 space-y-4">

        <!-- Info grid -->
        <div class="grid grid-cols-2 gap-3">
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

        <!-- Ringkasan biaya -->
        

        <!-- Order Items -->
        <div>
          <span class="block text-xs text-gray-500 uppercase font-semibold mb-2 flex items-center gap-1.5">
            <i class="fa-solid fa-bowl-food text-gray-400"></i> Item Pesanan
          </span>
          <div id="det-items-wrap" class="border border-gray-200 rounded-lg overflow-hidden">
            <!-- diisi JS -->
            <div id="det-items-loading" class="px-4 py-6 text-center text-sm text-gray-400">
              <i class="fa-solid fa-spinner fa-spin mr-1"></i> Memuat item...
            </div>
            <table id="det-items-table" class="w-full text-sm text-left hidden">
              <thead class="bg-gray-50 border-b border-gray-200 text-gray-500 uppercase text-[10px] tracking-wider font-semibold">
                <tr>
                  <th class="px-4 py-2.5">Menu</th>
                  <th class="px-4 py-2.5 text-center">Qty</th>
                  <th class="px-4 py-2.5 text-right">Subtotal</th>
                </tr>
              </thead>
              <tbody id="det-items-body" class="divide-y divide-gray-100 bg-white"></tbody>
            </table>
            <div id="det-items-empty" class="px-4 py-6 text-center text-sm text-gray-400 hidden">
              Tidak ada item ditemukan.
            </div>
          </div>
        </div>

        <div class="border border-gray-200 rounded-lg overflow-hidden">
          <table class="w-full text-sm text-left text-gray-500">
            <tbody class="divide-y divide-gray-200">
              <tr class="bg-white">
                <th class="px-4 py-3 font-medium text-gray-900 bg-gray-50 w-1/3">Subtotal</th>
                <td class="px-4 py-3" id="det-subtotal">Rp 0</td>
              </tr>
              <tr class="bg-white">
                <th class="px-4 py-3 font-medium text-gray-900 bg-gray-50">Pajak</th>
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

      </div>
    </div>
  </div>
</div>

<script>
  const pageOrders  = <?php echo json_encode($orders); ?>;
  const BASE_URL_JS = "<?php echo BASE_URL; ?>";

  function rp(val) {
    return "Rp " + parseFloat(val || 0).toLocaleString("id-ID");
  }

  function openDetail(index, orderId) {
    const o = pageOrders[index];

    // Info dasar
    document.getElementById("det-code").innerText     = o.code          || "-";
    document.getElementById("det-user").innerText     = o.user_name     || "-";
    document.getElementById("det-table").innerText    = o.table_name    || "-";
    document.getElementById("det-customer").innerText = o.customer_name || "-";
    document.getElementById("det-subtotal").innerText = rp(o.subtotal);
    document.getElementById("det-tax").innerText      = rp(o.tax);
    document.getElementById("det-total").innerText    = rp(o.total);

    const pm = o.payment == 1 ? "Kasir" : o.payment == 2 ? "Online" : "Lainnya";
    document.getElementById("det-payment").innerText = pm;

    const s  = parseInt(o.status);
    const sc = s === 1
      ? '<span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-bold border border-green-200">Sukses</span>'
      : s === 2
        ? '<span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded text-xs font-bold border border-yellow-200">Pending</span>'
        : '<span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs font-bold border border-red-200">Expired</span>';
    document.getElementById("det-status").innerHTML = sc;

    // Reset state items
    document.getElementById("det-items-loading").classList.remove("hidden");
    document.getElementById("det-items-table").classList.add("hidden");
    document.getElementById("det-items-empty").classList.add("hidden");
    document.getElementById("det-items-body").innerHTML = "";

    // Tampilkan modal
    const modal = document.getElementById("detail-modal");
    modal.classList.remove("hidden");
    modal.classList.add("flex");

    // Fetch order items via AJAX
    fetch(BASE_URL_JS + "kasir/history/items?order_id=" + orderId)
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
                <span class="text-xs text-amber-600 italic leading-relaxed">${item.notes}</span>
              </div>` : ""}
            </td>
            <td class="px-4 py-3 text-center">
              <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-blue-50 text-blue-700 text-xs font-black border border-blue-100">${item.qty}</span>
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
  }

  function closeDetailModal() {
    const modal = document.getElementById("detail-modal");
    modal.classList.add("hidden");
    modal.classList.remove("flex");
  }

  // Tutup klik di luar modal
  document.getElementById("detail-modal").addEventListener("click", function(e) {
    if (e.target === this) closeDetailModal();
  });
</script>

<?php include '../layout/footer.php'; ?>