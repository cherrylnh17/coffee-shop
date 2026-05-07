<?php
session_start();
require_once '../config.php';
require_once '../path.php';

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

$initial_revenue = "Rp " . number_format((int)$today_data['revenue'], 0, ',', '.');
$initial_transactions = (int)$today_data['transactions'] . " Pesanan";
$initial_items_sold = (int)$today_data['items_sold'] . " Produk";

function getUsersByRole($pdo, $role) {
    $stmt = $pdo->prepare("SELECT * FROM user WHERE role = :role");
    $stmt->execute(['role' => $role]);
    return $stmt->fetchAll();
}

$countKasir = getUsersByRole($pdo, 1);
 
?>

<?php

$pageTitle = "Dashboard Admin";
$currentPage = "dashboard";

include 'layout/header.php';
include 'layout/sidebar.php';

?>

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
 
<?php 
include 'layout/footer.php'; 
?>