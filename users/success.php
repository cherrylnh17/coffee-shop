<?php 
include '../config.php';

// Aktifkan error reporting untuk debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

$order_code = $_GET['code'];

if (!isset($_GET['code']) || empty($_GET['code'])) {
    header("Location: table");
    exit(); 
}

try {
    // Ambil data Order utama
    $stmt = $pdo->prepare("SELECT * FROM `order` WHERE code = ?"); 
    $stmt->execute([$order_code]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    if(empty($order)){
        header("Location: table");
        exit(); 
    }
    // Ambil rincian menu yang dibeli
    $stmtItem = $pdo->prepare("SELECT * FROM order_item WHERE order_id = ?");
    $stmtItem->execute([$order['id']]);
    $order_items = $stmtItem->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error pada query: " . $e->getMessage());
}

$payment = ($order['payment'] == 1) ? 'Bayar di Kasir' : 'Bayar Online';

?>


<?php 
  $title = "Identitas"; 
  include 'layout/header.php'; 
?>

<main class="w-full max-w-[480px] mx-auto bg-gray-50 min-h-screen relative shadow-2xl flex flex-col overflow-x-hidden">

  <div class="w-full max-w-[480px] flex flex-col min-h-screen relative z-10">

    <!-- Top gradient -->
    <div class="h-48 bg-gradient-to-b from-green-500 to-green-400 rounded-b-[2.5rem] flex flex-col items-center justify-center relative overflow-hidden">
      <div class="absolute inset-0 opacity-10">
        <div class="absolute top-2 left-4 text-6xl">🎉</div>
        <div class="absolute top-4 right-6 text-5xl">✨</div>
        <div class="absolute bottom-2 left-16 text-4xl">🎊</div>
      </div>
      <div class="success-icon pulse-ring w-20 h-20 bg-white rounded-full flex items-center justify-center shadow-xl shadow-green-700/20 relative z-10">
        <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
        </svg>
      </div>
    </div>

    <main class="flex-1 px-4 -mt-6 pb-32 space-y-4">

      <!-- Title -->
      <div class="fade-up-1 bg-white rounded-2xl p-5 shadow-sm border border-gray-100 text-center">
        <h1 class="text-2xl font-black text-gray-900 mt-1">Pesanan Berhasil! 🎉</h1>
        <p class="text-gray-500 text-sm mt-1.5 font-medium">Pesananmu sudah diterima oleh dapur</p>
        <div class="mt-4 bg-green-50 border border-green-200 rounded-xl px-4 py-3 inline-flex items-center gap-2">
          <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
          <span class="text-green-700 font-black text-sm tracking-wide" id="success-inv"><?= $order['code'] ?></span>
        </div>
      </div>

      <!-- Order Info -->
      <div class="fade-up-2 bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
        <h2 class="font-black text-gray-800 mb-3 flex items-center gap-2">
          <span class="w-6 h-6 bg-green-100 rounded-lg flex items-center justify-center text-sm">📋</span> Detail Pesanan
        </h2>
        <div class="space-y-2.5">
          <div class="flex justify-between items-center text-sm">
            <span class="text-gray-500 font-medium">Nama Pemesan</span>
            <span id="success-name" class="font-bold text-gray-800"><?= $order['customer_name'] ?></span>
          </div>
          <div class="flex justify-between items-center text-sm">
            <span class="text-gray-500 font-medium">Nomor Meja</span>
            <span class="font-bold text-gray-800"><?= $order['table_name'] ?></span>
          </div>
          <div class="flex justify-between items-center text-sm">
            <span class="text-gray-500 font-medium">Metode Bayar</span>
            <span id="success-payment" class="font-bold text-gray-800"><?= $payment ?></span>
          </div>
        </div>
      </div>

      <!-- Item Pesanan -->
      <div class="fade-up-3 bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
        <h2 class="font-black text-gray-800 mb-3 flex items-center gap-2">
          <span class="w-6 h-6 bg-green-100 rounded-lg flex items-center justify-center text-sm">🛍️</span> Item Pesanan
        </h2>
        
        <ul id="success-items" class="space-y-4 text-sm divide-y divide-gray-50">
          <?php foreach ($order_items as $item): ?>
            <li class="flex justify-between items-start first:pt-0">
              <div class="flex-1">
                <p class="font-semibold text-gray-800 text-sm"><?= htmlspecialchars($item['menu_name']) ?></p>
                
                <p class="text-xs text-gray-400">
                  <?= $item['qty'] ?>x @ Rp <?= number_format($item['subtotal'] / $item['qty'], 0, ',', '.') ?>
                </p>
                
                <?php if (!empty($item['notes'])): ?>
                  <p class="text-xs text-amber-600 italic mt-0.5">
                    📝 <?= htmlspecialchars($item['notes']) ?>
                  </p>
                <?php endif; ?>
              </div>
              
              <div class="font-semibold text-gray-900 text-sm">
                Rp <?= number_format($item['subtotal'], 0, ',', '.') ?>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>

        <div class="pt-2 mt-2 space-y-1.5 border-t border-dashed border-gray-200">
          <div class="flex justify-between text-sm text-gray-500">
            <span>Subtotal</span>
            <span class="font-medium text-gray-700">Rp <?= number_format($order['subtotal'], 0, ',', '.') ?></span>
          </div>
          <div class="flex justify-between text-sm text-gray-500">
            <span>Pajak (12%)</span>
            <span class="font-medium text-gray-700">Rp <?= number_format($order['tax'], 0, ',', '.') ?></span>
          </div>
          <div class="flex justify-between items-center pt-1.5 border-t border-gray-100">
            <span class="font-black text-gray-900">Total Pembayaran</span>
            <span class="font-black text-blue-500 text-base">Rp <?= number_format($order['total'], 0, ',', '.') ?></span>
          </div>
        </div>
      </div>

     

      <!-- Tips -->
      <div class="fade-up-5 bg-gradient-to-r from-blue-50 to-amber-50 border border-blue-200 rounded-xl px-4 py-3 flex gap-3">
        <span class="text-2xl flex-shrink-0">💡</span>
        <div>
          <p class="text-xs font-bold text-blue-700 mb-0.5">Tips</p>
          <p class="text-xs text-blue-600 leading-relaxed">Simpan screenshot halaman ini sebagai bukti pesanan. Tunjukkan nomor invoice kepada kasir jika diperlukan.</p>
        </div>
      </div>

    </main>

    <!-- Bottom CTA -->
    <div class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-[480px] bg-white border-t border-gray-100 px-4 py-4 shadow-2xl z-20">
      <a href="index?code=<?= $order['table_name'] ?>"
        class="w-full bg-blue-500 text-white font-black py-4 rounded-2xl text-base shadow-lg shadow-blue-200 active:scale-95 transition-transform flex items-center justify-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
        Kembali ke Menu
      </a>
    </div>

  </div>

</main>

<?php include 'layout/footer.php'; ?>

