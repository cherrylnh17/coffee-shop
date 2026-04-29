<?php
session_start();
require_once '../../config.php';
require_once '../../path.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 1) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit;
}

// Ambil kode order dari URL
$order_code = isset($_GET['code']) ? $_GET['code'] : '';

if (empty($order_code)) {
    echo "<script>alert('Kode Pesanan Tidak Ditemukan'); window.location.href='index.php';</script>";
    exit;
}

try {
    // 1. Ambil data Order utama
    $stmt = $pdo->prepare("SELECT * FROM `order` WHERE code = ?");
    $stmt->execute([$order_code]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo "<script>alert('Pesanan tidak ditemukan'); window.location.href='index.php';</script>";
        exit;
    }

    // 2. Ambil rincian menu
    $stmtItem = $pdo->prepare("SELECT * FROM order_item WHERE order_id = ?");
    $stmtItem->execute([$order['id']]);
    $order_items = $stmtItem->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

$payment_status = ($order['status'] == 1) ? 'Lunas' : 'Belum Bayar';
?>

<!doctype html>
<html lang="en" dir="ltr">
  <head>
    <title>Detail Pesanan | Träffa Coffee</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  </head>
  <body class="bg-gray-50 text-gray-800">

    <nav class="fixed inset-y-0 left-0 z-[1026] w-[280px] overflow-hidden border-r border-gray-200 bg-white transition-all duration-200 ease-in-out max-lg:-left-[280px] pc-sidebar">
      <div class="h-full w-full">
        <div class="flex h-[74px] items-center px-6 py-4">
          <a href="index.php" class="flex items-center gap-3">
            <img src="../../assets/image/logo.svg" class="h-8 w-8" alt="logo" onerror="this.src='https://placehold.co/32x32?text=Logo'" />
            <span class="inline-block rounded-md bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">Kasir Panel</span>
          </a>
        </div>

        <div class="h-[calc(100vh-74px)] overflow-y-auto py-3">
          <div class="mx-4 mb-4 rounded-xl border border-gray-100 bg-gray-50 p-4">
            <div class="flex items-center">
              <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 text-sm font-semibold text-blue-600 shadow-sm text-uppercase">
                <?= substr($_SESSION['username'], 0, 2); ?>
              </div>
              <div class="ml-3 mr-2 grow">
                <h6 class="mb-0 text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($_SESSION['name']); ?></h6>
                <small class="text-xs text-gray-500">Kasir</small>
              </div>
            </div>
          </div> 

          <div class="w-full">
            <ul class="flex flex-col gap-1.5 px-4 py-2">
              
              <li>
                <a href="index.php" class="group flex items-center gap-3 rounded-xl px-4 py-3 text-gray-600 transition-all duration-200 hover:bg-blue-50 hover:text-blue-600">
                  <span class="flex w-6 justify-center text-lg text-gray-400 transition-colors group-hover:text-blue-600"><i class="fa-solid fa-house"></i></span>
                  <span class="font-medium">Dashboard</span>
                </a>
              </li>

              <li>
                <a href="riwayat_pesanan/riwayat.php" class="group flex items-center gap-3 rounded-xl px-4 py-3 text-gray-600 transition-all duration-200 hover:bg-blue-50 hover:text-blue-600">
                  <span class="flex w-6 justify-center text-lg text-gray-400 transition-colors group-hover:text-blue-600"><i class="fa-solid fa-file-invoice-dollar"></i></span>
                  <span class="font-medium">Riwayat Pesanan</span>
                </a>
              </li>

              <li class="mt-5 px-4 py-2">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Authentication</span>
              </li>

              <li>
                <a href="tentang_akun/akun.php" class="group flex items-center gap-3 rounded-xl px-4 py-3 text-gray-600 transition-all duration-200 hover:bg-blue-50 hover:text-blue-600">
                  <span class="flex w-6 justify-center text-lg text-gray-400 transition-colors group-hover:text-blue-600"><i class="fa-solid fa-key"></i></span>
                  <span class="font-medium">Tentang Akun</span>
                </a>
              </li>

              <li>
                <a href="../../auth/login.php" class="group flex items-center gap-3 rounded-xl px-4 py-3 text-gray-600 transition-all duration-200 hover:bg-red-50 hover:text-red-600">
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
        <h1 class="text-lg font-bold">Detail Pesanan #<?= $order['code'] ?></h1>
      </div>
    </header>
    
    <main class="relative ml-0 min-h-[calc(100vh-74px)] top-[74px] transition-all duration-200 ease-in-out lg:ml-[280px] p-4 sm:p-6 lg:p-8">
      
      <div class="max-w-5xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          
          <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
              <div class="p-5 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                <h3 class="font-bold text-gray-800">Item Pesanan</h3>
                <span class="text-xs font-medium text-gray-500 uppercase tracking-widest">Total <?= count($order_items) ?> Items</span>
              </div>
              <div class="divide-y divide-gray-50">
                <?php foreach ($order_items as $item): ?>
                <div class="p-5 flex justify-between items-center hover:bg-gray-50/30 transition-colors">
                  <div>
                    <h4 class="font-bold text-gray-900"><?= htmlspecialchars($item['menu_name']) ?></h4>
                    <p class="text-sm text-gray-500"><?= $item['qty'] ?> x Rp <?= number_format($item['subtotal']/$item['qty'], 0, ',', '.') ?></p>
                    <?php if(!empty($item['notes'])): ?>
                      <div class="mt-2 text-xs bg-amber-50 text-amber-700 px-2 py-1 rounded inline-flex items-center gap-1">
                        <i class="fa-solid fa-note-sticky"></i> <?= htmlspecialchars($item['notes']) ?>
                      </div>
                    <?php endif; ?>
                  </div>
                  <div class="text-right">
                    <p class="font-bold text-gray-900">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></p>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4">
                <?php if($order['status'] == 1): ?>
                <div class="flex-1">
                    <button name="aksi" value="lunas" class="w-full bg-green-600 hover:bg-green-700 cursor-not-allowed text-white font-bold py-4 rounded-2xl shadow-lg shadow-blue-200 transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-cash-register"></i> Pesanan telah diselesaikan
                    </button>
                </div>
                <?php else: ?>
                <form action="update_order.php" method="POST" class="flex-1">
                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                    <button name="aksi" type="submit"  value="selesai" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-2xl shadow-lg shadow-green-200 transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-check-double"></i> Selesaikan Pesanan 
                    </button>
                </form>
                <?php endif; ?>
            </div>
          </div>

          <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
              <h3 class="text-sm font-bold text-gray-400 uppercase mb-4">Informasi Meja</h3>
              <div class="flex items-center gap-4 mb-6">
                <div class="h-12 w-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center text-xl font-bold">
                  <?= htmlspecialchars($order['table_name']) ?>
                </div>
                <div>
                  <p class="font-bold text-gray-900"><?= htmlspecialchars($order['customer_name']) ?></p>
                  <p class="text-xs text-gray-500 italic"><?= $payment_status ?> • <?= $order['code'] ?></p>
                </div>
              </div>

              <div class="space-y-3 pt-4 border-t border-gray-50">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-500">Subtotal</span>
                  <span class="font-medium text-gray-800">Rp <?= number_format($order['subtotal'], 0, ',', '.') ?></span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-500">Pajak (12%)</span>
                  <span class="font-medium text-gray-800">Rp <?= number_format($order['tax'], 0, ',', '.') ?></span>
                </div>
                <div class="pt-3 border-t border-dashed border-gray-200 flex justify-between items-center">
                  <span class="font-bold text-gray-900">Total Tagihan</span>
                  <span class="text-2xl font-black text-blue-600">Rp <?= number_format($order['total'], 0, ',', '.') ?></span>
                </div>
              </div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h3 class="text-sm font-bold text-gray-400 uppercase mb-4">Status Alur</h3>
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full <?= $order['status'] >= 0 ? 'bg-green-500' : 'bg-gray-300' ?>"></div>
                        <p class="text-sm <?= $order['status'] >= 0 ? 'font-bold' : '' ?>">Pesanan Masuk</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full <?= $order['status'] >= 1 ? 'bg-green-500' : 'bg-gray-300' ?>"></div>
                        <p class="text-sm <?= $order['status'] >= 1 ? 'font-bold' : '' ?>">Pembayaran Diterima</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full <?= $order['status'] >= 2 ? 'bg-green-500' : 'bg-gray-300' ?>"></div>
                        <p class="text-sm <?= $order['status'] >= 2 ? 'font-bold' : '' ?>">Pesanan Selesai</p>
                    </div>
                </div>
            </div>
          </div>

        </div>
      </div>
    </main>

  </body>
</html>