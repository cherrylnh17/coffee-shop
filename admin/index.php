<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../path.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 2) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit;
}

$name = $_SESSION['name'];
$words = explode(" ", $name);
$initials = "";
foreach ($words as $w) {
    $initials .= mb_substr($w, 0, 1);
}
$initials = strtoupper(substr($initials, 0, 2));

function rupiah($value) {
    return "Rp " . number_format((int)$value, 0, ',', '.');
}

function getDashboardData($pdo, $start, $end)
{
    $params       = [':start' => $start, ':end' => $end];
    $emptySummary = ['revenue' => 0, 'transactions' => 0, 'items_sold' => 0];

    try {
        $summaryStmt = $pdo->prepare('
            SELECT
                COALESCE(SUM(total), 0) AS revenue,
                COUNT(*)                AS transactions,
                COALESCE(SUM(qty), 0)  AS items_sold
            FROM `order`
            WHERE status = 1
              AND created_at BETWEEN :start AND :end
        ');
        $summaryStmt->execute($params);
        $summary = $summaryStmt->fetch(PDO::FETCH_ASSOC) ?: $emptySummary;

        $menuStmt = $pdo->prepare('
            SELECT
                menu_id,
                menu_name,
                SUM(qty)      AS terjual,
                SUM(subtotal) AS pendapatan
            FROM report
            WHERE sold_at BETWEEN :start AND :end
            GROUP BY menu_id, menu_name
            ORDER BY terjual DESC, pendapatan DESC
        ');
        $menuStmt->execute($params);
        $menus = $menuStmt->fetchAll(PDO::FETCH_ASSOC);

        // 5 transaksi terbaru
        $latestStmt = $pdo->prepare('
            SELECT
                id, code, customer_name, table_name,
                qty, total, payment, created_at
            FROM `order`
            WHERE status = 1
              AND created_at BETWEEN :start AND :end
            ORDER BY created_at DESC
            LIMIT 5
        ');
        $latestStmt->execute($params);
        $latest = $latestStmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'summary'             => $summary,
            'menus'               => $menus,
            'latest_transactions' => $latest,
        ];

    } catch (PDOException $e) {
        return [
            'summary'             => $emptySummary,
            'menus'               => [],
            'latest_transactions' => [],
        ];
    }
}

function getUsersByRole($pdo, $role) {
    $stmt = $pdo->prepare("SELECT * FROM user WHERE role = :role");
    $stmt->execute(['role' => $role]);
    return $stmt->fetchAll();
}

$countKasir = getUsersByRole($pdo, 1);
$todayStart = date('Y-m-d 00:00:00');
$todayEnd = date('Y-m-d 23:59:59');
$dashboard = getDashboardData($pdo, $todayStart, $todayEnd);
$today_data = $dashboard['summary'];

$initial_revenue = rupiah($today_data['revenue'] ?? 0);
$initial_transactions = (int)($today_data['transactions'] ?? 0) . " Pesanan";
$initial_items_sold = (int)($today_data['items_sold'] ?? 0) . " Produk";

$pageTitle = "Dashboard Admin";
$currentPage = "dashboard";

include 'layout/header.php';
include 'layout/sidebar.php';
?>

<main class="relative min-h-screen pt-[74px] transition-all duration-300 lg:ml-[280px] pc-main">
  <div class="p-4 sm:p-6 lg:p-8">

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

    <div class="mb-6 rounded-xl border border-gray-100 bg-white shadow-sm">
      <div class="flex flex-col gap-4 border-b border-gray-100 p-5 xl:flex-row xl:items-center xl:justify-between">
        <div>
          <h3 class="text-base font-bold text-gray-800">Menu Terlaris</h3>
          <p class="mt-0.5 text-xs text-gray-400" id="menu-table-label">Menampilkan data hari ini</p>
        </div>

        <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
          <div class="inline-flex rounded-xl border border-gray-200 bg-gray-100 p-1">
            <button onclick="applyPeriodFilter('hari', this)" class="filter-btn rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-blue-600 shadow-sm transition-all focus:outline-none">Hari Ini</button>
            <button onclick="applyPeriodFilter('minggu', this)" class="filter-btn rounded-lg border border-transparent px-4 py-2 text-sm font-medium text-gray-500 transition-all hover:bg-gray-200/50 hover:text-gray-700 focus:outline-none">Minggu Ini</button>
            <button onclick="applyPeriodFilter('bulan', this)" class="filter-btn rounded-lg border border-transparent px-4 py-2 text-sm font-medium text-gray-500 transition-all hover:bg-gray-200/50 hover:text-gray-700 focus:outline-none">Bulan Ini</button>
            <button onclick="toggleCustomDate()" id="btn-custom" class="filter-btn rounded-lg border border-transparent px-4 py-2 text-sm font-medium text-gray-500 transition-all hover:bg-gray-200/50 hover:text-gray-700 focus:outline-none">Custom</button>
          </div>

          <div id="custom-date-box" class="hidden flex flex-col gap-2 sm:flex-row sm:items-center">
            <div class="flex items-center gap-2">
              <label class="text-xs font-medium text-gray-500 whitespace-nowrap">Dari</label>
              <input type="date" id="filter-dari" class="rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 transition-all focus:border-blue-400 focus:outline-none focus:ring-1 focus:ring-blue-300">
            </div>
            <div class="flex items-center gap-2">
              <label class="text-xs font-medium text-gray-500 whitespace-nowrap">Sampai</label>
              <input type="date" id="filter-sampai" class="rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 transition-all focus:border-blue-400 focus:outline-none focus:ring-1 focus:ring-blue-300">
            </div>
            <button onclick="applyCustomFilter()" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-blue-700">
              <i class="fa-solid fa-filter text-xs"></i> Terapkan
            </button>
          </div>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">#</th>
              <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">Nama Menu</th>
              <th class="px-5 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-400">Total Terjual</th>
              <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-400">Total Pendapatan</th>
            </tr>
          </thead>
          <tbody id="menu-table-body" class="divide-y divide-gray-50"></tbody>
        </table>
      </div>

      <div id="menu-table-loading" class="hidden px-5 py-10 text-center text-sm text-gray-400">
        <i class="fa-solid fa-spinner fa-spin mr-1.5"></i> Memuat data...
      </div>

      <div id="menu-pagination" class="flex items-center justify-between border-t border-gray-100 px-5 py-3">
        <p class="text-xs text-gray-400" id="pagination-info"></p>
        <div class="flex items-center gap-1" id="pagination-buttons"></div>
      </div>
    </div>

    <div class="rounded-xl border border-gray-100 bg-white shadow-sm">
      <div class="border-b border-gray-100 p-5">
        <h3 class="text-base font-bold text-gray-800">5 Transaksi Terbaru</h3>
        <p class="mt-0.5 text-xs text-gray-400" id="transaction-table-label">Berdasarkan filter yang dipilih</p>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">Kode</th>
              <th class="px-5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">Pelanggan</th>
              <th class="px-5 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-400">Meja</th>
              <th class="px-5 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-400">Item</th>
              <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-400">Total</th>
              <th class="px-5 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-400">Waktu</th>
            </tr>
          </thead>
          <tbody id="transaction-table-body" class="divide-y divide-gray-50"></tbody>
        </table>
      </div>
    </div>

  </div>
</main>

<script>
  const PER_PAGE = 5;
  const initialMenus = <?= json_encode($dashboard['menus'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
  const initialTransactions = <?= json_encode($dashboard['latest_transactions'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
  let allMenuData = [];
  let currentPage = 1;

  function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>'"]/g, char => ({
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      "'": '&#039;',
      '"': '&quot;'
    }[char]));
  }

  function formatRupiah(value) {
    return 'Rp ' + parseInt(value || 0).toLocaleString('id-ID');
  }

  function formatDateTime(value) {
    if (!value) return '-';
    const date = new Date(String(value).replace(' ', 'T'));
    if (Number.isNaN(date.getTime())) return value;
    return date.toLocaleString('id-ID', {
      day: '2-digit', month: '2-digit', year: 'numeric',
      hour: '2-digit', minute: '2-digit'
    });
  }

  function setActiveFilterButton(activeBtn) {
    document.querySelectorAll('.filter-btn').forEach(btn => {
      btn.className = 'filter-btn rounded-lg border border-transparent px-4 py-2 text-sm font-medium text-gray-500 transition-all hover:bg-gray-200/50 hover:text-gray-700 focus:outline-none';
    });
    activeBtn.className = 'filter-btn rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-blue-600 shadow-sm transition-all focus:outline-none';
  }

  function updateSummary(summary) {
    document.getElementById('val-pendapatan').innerText = summary.pendapatan || 'Rp 0';
    document.getElementById('val-transaksi').innerText = summary.transaksi || '0 Pesanan';
    document.getElementById('val-terjual').innerText = summary.terjual || '0 Produk';
  }

  function renderPage(page) {
    currentPage = page;
    const tbody = document.getElementById('menu-table-body');
    const total = allMenuData.length;
    const start = (page - 1) * PER_PAGE;
    const end = Math.min(start + PER_PAGE, total);
    const pageData = allMenuData.slice(start, end);

    if (total === 0) {
      tbody.innerHTML = '<tr><td colspan="4" class="px-5 py-10 text-center text-sm text-gray-400">Tidak ada data menu pada periode ini.</td></tr>';
      document.getElementById('pagination-info').innerText = '';
      document.getElementById('pagination-buttons').innerHTML = '';
      return;
    }

    tbody.innerHTML = pageData.map((m, i) => {
      const no = start + i + 1;
      const terjual = parseInt(m.terjual || 0).toLocaleString('id-ID');
      const pendapatan = formatRupiah(m.pendapatan || 0);
      return `<tr class="transition-colors hover:bg-gray-50/50">
        <td class="px-5 py-3.5 font-medium text-gray-400">${no}</td>
        <td class="px-5 py-3.5 font-semibold text-gray-800">${escapeHtml(m.menu_name || m.name)}</td>
        <td class="px-5 py-3.5 text-center">
          <span class="inline-flex items-center gap-1 rounded-lg border border-blue-100 bg-blue-50 px-2.5 py-1 text-sm font-bold text-blue-600">
            ${terjual} <span class="text-xs font-medium text-blue-400">terjual</span>
          </span>
        </td>
        <td class="px-5 py-3.5 text-right font-semibold text-gray-700">${pendapatan}</td>
      </tr>`;
    }).join('');

    document.getElementById('pagination-info').innerText = `Menampilkan ${start + 1}–${end} dari ${total} menu`;

    const totalPages = Math.ceil(total / PER_PAGE);
    let btns = `<button onclick="renderPage(${page - 1})" ${page === 1 ? 'disabled' : ''}
      class="flex h-8 w-8 items-center justify-center rounded-lg border text-sm transition-colors ${page === 1 ? 'cursor-not-allowed border-gray-100 text-gray-300' : 'border-gray-200 text-gray-600 hover:bg-gray-100'}">
      <i class="fa-solid fa-chevron-left text-xs"></i>
    </button>`;

    for (let p = 1; p <= totalPages; p++) {
      btns += `<button onclick="renderPage(${p})"
        class="flex h-8 w-8 items-center justify-center rounded-lg border text-sm font-medium transition-colors ${p === page ? 'border-blue-500 bg-blue-600 text-white' : 'border-gray-200 text-gray-600 hover:bg-gray-100'}">
        ${p}
      </button>`;
    }

    btns += `<button onclick="renderPage(${page + 1})" ${page === totalPages ? 'disabled' : ''}
      class="flex h-8 w-8 items-center justify-center rounded-lg border text-sm transition-colors ${page === totalPages ? 'cursor-not-allowed border-gray-100 text-gray-300' : 'border-gray-200 text-gray-600 hover:bg-gray-100'}">
      <i class="fa-solid fa-chevron-right text-xs"></i>
    </button>`;

    document.getElementById('pagination-buttons').innerHTML = btns;
  }

  function renderTransactions(rows) {
    const tbody = document.getElementById('transaction-table-body');

    if (!rows || rows.length === 0) {
      tbody.innerHTML = '<tr><td colspan="6" class="px-5 py-10 text-center text-sm text-gray-400">Belum ada transaksi pada periode ini.</td></tr>';
      return;
    }

    tbody.innerHTML = rows.map(t => {
      return `<tr class="transition-colors hover:bg-gray-50/50">
        <td class="px-5 py-3.5 font-semibold text-gray-800">${escapeHtml(t.code)}</td>
        <td class="px-5 py-3.5 text-gray-600">${escapeHtml(t.customer_name || '-')}</td>
        <td class="px-5 py-3.5 text-center text-gray-600">${escapeHtml(t.table_name || '-')}</td>
        <td class="px-5 py-3.5 text-center">
          <span class="rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">${parseInt(t.qty || 0)} item</span>
        </td>
        <td class="px-5 py-3.5 text-right font-semibold text-gray-800">${formatRupiah(t.total || 0)}</td>
        <td class="px-5 py-3.5 text-right text-gray-500">${formatDateTime(t.created_at)}</td>
      </tr>`;
    }).join('');
  }

  function loadMenuData(rows) {
    allMenuData = rows || [];
    renderPage(1);
  }

  function setLoading(isLoading) {
    document.getElementById('menu-table-loading').classList.toggle('hidden', !isLoading);
    if (isLoading) {
      document.getElementById('menu-table-body').innerHTML = '';
      document.getElementById('pagination-buttons').innerHTML = '';
      document.getElementById('pagination-info').innerText = '';
    }
  }

  function applyResponse(data) {
    updateSummary(data.summary || {});
    loadMenuData(data.menus || []);
    renderTransactions(data.latest_transactions || []);
    document.getElementById('menu-table-label').innerText = `Menampilkan data ${data.label || '-'}`;
    document.getElementById('transaction-table-label').innerText = `5 transaksi terbaru • ${data.label || '-'}`;
  }

  function fetchDashboard(query) {
    setLoading(true);
    return fetch(`menu_filter.php?${query}`)
      .then(r => {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
      })
      .then(data => {
        if (!data.success) throw new Error(data.message || 'Gagal memuat data');
        applyResponse(data);
      })
      .catch(err => {
        document.getElementById('menu-table-body').innerHTML = `<tr><td colspan="4" class="px-5 py-10 text-center text-sm text-red-400">Gagal memuat data: ${escapeHtml(err.message)}</td></tr>`;
      })
      .finally(() => setLoading(false));
  }

  function applyPeriodFilter(period, btn) {
    setActiveFilterButton(btn);
    document.getElementById('custom-date-box').classList.add('hidden');
    fetchDashboard(`period=${period}`);
  }

  function toggleCustomDate() {
    const btn = document.getElementById('btn-custom');
    setActiveFilterButton(btn);
    document.getElementById('custom-date-box').classList.remove('hidden');
  }

  function applyCustomFilter() {
    const dari = document.getElementById('filter-dari').value;
    const sampai = document.getElementById('filter-sampai').value;

    if (!dari || !sampai) {
      alert('Pilih tanggal dari dan sampai terlebih dahulu.');
      return;
    }
    if (dari > sampai) {
      alert('Tanggal "Dari" tidak boleh lebih besar dari "Sampai".');
      return;
    }

    fetchDashboard(`period=custom&dari=${encodeURIComponent(dari)}&sampai=${encodeURIComponent(sampai)}`);
  }

  updateSummary({
    pendapatan: '<?= $initial_revenue ?>',
    transaksi: '<?= $initial_transactions ?>',
    terjual: '<?= $initial_items_sold ?>'
  });
  loadMenuData(initialMenus);
  renderTransactions(initialTransactions);
</script>

<?php include 'layout/footer.php'; ?>