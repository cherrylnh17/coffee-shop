<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../path.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 1) {
    header("Location: " . BASE_URL . "auth/login");
    exit;
}

$order_code = isset($_GET['code']) ? $_GET['code'] : '';

if (empty($order_code)) {
    header("Location: index");
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM `order` WHERE code = ?");
    $stmt->execute([$order_code]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo "<script>alert('Pesanan tidak ditemukan'); window.location.href='index';</script>";
        exit;
    }

    $stmtItem = $pdo->prepare("SELECT * FROM order_item WHERE order_id = ?");
    $stmtItem->execute([$order['id']]);
    $order_items = $stmtItem->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$pageTitle   = "Print Dapur";
$currentPage = "check";

include 'layout/header.php';
include 'layout/sidebar.php';
?>

<!-- TOP HEADER BAR -->
<header class="fixed inset-x-0 top-0 z-[1025] flex h-[74px] items-center bg-white border-b border-gray-200 px-4 shadow-sm transition-all duration-200 ease-in-out lg:left-[280px]">
  <div class="flex grow items-center gap-3 sm:px-2">
    <a href="check?code=<?= htmlspecialchars($order_code) ?>"
       class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 hover:bg-blue-50 hover:text-blue-600 text-gray-500 transition-colors">
      <i class="fa-solid fa-arrow-left text-sm"></i>
    </a>
    <div>
      <h1 class="text-base font-bold text-gray-800 leading-tight">Print Tiket Dapur</h1>
      <span class="text-xs text-gray-400">#<?= htmlspecialchars($order['code']) ?></span>
    </div>
    <div class="ml-auto flex items-center gap-2 bg-amber-50 border border-amber-200 text-amber-600 text-xs font-semibold px-3 py-1.5 rounded-full">
      <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
      Menunggu konfirmasi
    </div>
  </div>
</header>

<!-- MAIN -->
<main class="relative ml-0 min-h-[calc(100vh-74px)] top-[74px] transition-all duration-200 ease-in-out lg:ml-[280px] bg-gray-50 p-4 sm:p-6 lg:p-8">
  <div class="max-w-5xl mx-auto">

    <!-- PAGE TITLE -->
    <div class="mb-6">
      <span class="text-xs font-bold text-blue-500 uppercase tracking-widest">Konfirmasi Cetak</span>
      <h2 class="text-2xl font-extrabold text-gray-900 mt-1">Siap cetak ke dapur?</h2>
      <p class="text-sm text-gray-500 mt-1">Pastikan printer thermal menyala &amp; terhubung sebelum menekan tombol print.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

      <!-- ====================== LEFT: Ticket Preview ====================== -->
      <div class="lg:col-span-2 flex flex-col gap-3">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Preview Tiket</p>

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
          <!-- Blue top bar -->
          <div class="bg-blue-600 h-2 w-full"></div>

          <div class="p-5 font-mono text-sm">

            <!-- Meja heading -->
            <div class="text-center mb-4">
              <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-blue-50 text-blue-600 mb-2">
                <i class="fa-solid fa-utensils text-lg"></i>
              </div>
              <div class="text-2xl font-black text-gray-900 tracking-tight">
                MEJA <?= strtoupper(htmlspecialchars($order['table_name'])) ?>
              </div>
              <div class="text-xs text-gray-400 mt-1"><?= htmlspecialchars($order['code']) ?></div>
              <div class="text-xs text-gray-400"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></div>
              <?php if (!empty($order['customer_name'])): ?>
              <div class="text-xs text-gray-500 mt-0.5">Pembeli: <?= htmlspecialchars($order['customer_name']) ?></div>
              <?php endif; ?>
            </div>

            <div class="border-t border-dashed border-gray-300 my-3"></div>

            <!-- Items -->
            <div class="space-y-2">
              <?php $no = 1; foreach ($order_items as $item): ?>
              <div>
                <div class="flex gap-1.5 text-xs font-semibold text-gray-800">
                  <span class="text-blue-500 w-4 shrink-0"><?= $no ?>.</span>
                  <span class="text-gray-500">[<?= $item['qty'] ?>x]</span>
                  <span><?= htmlspecialchars(mb_substr($item['menu_name'], 0, 20)) ?></span>
                </div>
                <?php if (!empty($item['notes'])): ?>
                <div class="text-xs text-gray-400 pl-6">&rarr; <?= htmlspecialchars($item['notes']) ?></div>
                <?php endif; ?>
              </div>
              <?php $no++; endforeach; ?>
            </div>

            <div class="border-t border-dashed border-gray-300 my-3"></div>
            <div class="text-center text-xs text-gray-400 uppercase tracking-widest">** Segera Diproses **</div>
          </div>

          <!-- Blue bottom bar -->
          <div class="bg-blue-600 h-2 w-full"></div>
        </div>

        <p class="text-xs text-gray-400">Lebar cetak: 58mm / 32 char</p>
      </div>

      <!-- ====================== RIGHT: Summary + Actions ====================== -->
      <div class="lg:col-span-3 flex flex-col gap-5">

        <!-- Order Summary -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 bg-gray-50">
            <h3 class="text-sm font-bold text-gray-700">Ringkasan Pesanan</h3>
            <span class="text-xs font-semibold text-blue-500 bg-blue-50 px-2.5 py-1 rounded-full">
              <?= count($order_items) ?> item
            </span>
          </div>

          <div class="divide-y divide-gray-50">
            <?php $no = 1; foreach ($order_items as $item): ?>
            <div class="flex items-start gap-3 px-5 py-3.5 hover:bg-blue-50/40 transition-colors">
              <span class="shrink-0 w-6 h-6 rounded-md bg-blue-100 text-blue-600 text-xs font-bold flex items-center justify-center">
                <?= $no ?>
              </span>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-900 truncate"><?= htmlspecialchars($item['menu_name']) ?></p>
                <?php if (!empty($item['notes'])): ?>
                <p class="text-xs text-amber-600 mt-0.5 flex items-center gap-1">
                  <i class="fa-solid fa-note-sticky text-[10px]"></i>
                  <?= htmlspecialchars($item['notes']) ?>
                </p>
                <?php endif; ?>
              </div>
              <span class="shrink-0 text-sm font-bold text-gray-500 bg-gray-100 px-2.5 py-0.5 rounded-full">
                <?= $item['qty'] ?>x
              </span>
            </div>
            <?php $no++; endforeach; ?>
          </div>

          <div class="flex items-center justify-between px-5 py-3 bg-gray-50 border-t border-gray-100">
            <span class="text-xs text-gray-400">Meja <?= htmlspecialchars($order['table_name']) ?></span>
            <span class="text-xs font-semibold text-blue-600"><?= count($order_items) ?> menu dipesan</span>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 space-y-3">
          <h3 class="text-sm font-bold text-gray-700 mb-1">Aksi</h3>

          <form action="print_kitchen" method="POST" id="print-form">
            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
            <input type="hidden" name="order_code" value="<?= htmlspecialchars($order_code) ?>">
            <button type="submit" id="btn-print"
              class="w-full flex items-center justify-center gap-3 bg-blue-600 hover:bg-blue-700 active:scale-[.98] text-white font-bold text-base py-4 rounded-xl shadow-md shadow-blue-200 transition-all duration-150">
              <i class="fa-solid fa-receipt" id="btn-icon"></i>
              <span id="btn-text">Print ke Dapur</span>
              <svg id="btn-spinner" class="hidden animate-spin w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
              </svg>
            </button>
          </form>

          <a href="<?= BASE_URL; ?>kasir/history/order"
            class="w-full flex items-center justify-center gap-2 border border-gray-200 hover:border-gray-300 hover:bg-gray-50 text-gray-500 hover:text-gray-700 font-semibold text-sm py-3.5 rounded-xl transition-all duration-150">
            <i class="fa-solid fa-file-invoice-dollar"></i>
            Cek Riwayat Pesanan
          </a>

          <p class="text-xs text-gray-400 text-center pt-1">
            Transaksi sudah tersimpan. Lewati jika printer tidak tersedia - data tidak akan hilang.
          </p>
        </div>

      </div>
    </div>
  </div>
</main>

<!-- TOAST -->
<div id="toast"
  class="fixed bottom-6 right-6 z-[9999] flex items-center gap-3 bg-white border border-gray-200 text-gray-800 px-5 py-3.5 rounded-2xl shadow-xl text-sm font-medium max-w-xs translate-y-20 opacity-0 transition-all duration-300 pointer-events-none">
  <i id="toast-icon" class="fa-solid fa-circle-check text-green-500 text-base"></i>
  <span id="toast-msg">Tiket berhasil dicetak!</span>
</div>

<script>
document.getElementById('print-form').addEventListener('submit', function () {
  const btn     = document.getElementById('btn-print');
  const icon    = document.getElementById('btn-icon');
  const text    = document.getElementById('btn-text');
  const spinner = document.getElementById('btn-spinner');

  btn.disabled = true;
  btn.classList.add('opacity-70', 'cursor-not-allowed');
  icon.classList.add('hidden');
  spinner.classList.remove('hidden');
  text.textContent = 'Mengirim ke printer...';
});

<?php if (isset($_SESSION['print_msg'])): ?>
(function () {
  const msg      = <?= json_encode($_SESSION['print_msg']) ?>;
  const toast    = document.getElementById('toast');
  const toastMsg = document.getElementById('toast-msg');
  const toastIco = document.getElementById('toast-icon');

  toastMsg.textContent = msg.text;
  toastIco.className   = msg.icon === 'success'
    ? 'fa-solid fa-circle-check text-green-500 text-base'
    : 'fa-solid fa-triangle-exclamation text-amber-500 text-base';

  toast.classList.remove('translate-y-20', 'opacity-0', 'pointer-events-none');
  toast.classList.add('translate-y-0', 'opacity-100');

  setTimeout(() => {
    toast.classList.add('translate-y-20', 'opacity-0', 'pointer-events-none');
    toast.classList.remove('translate-y-0', 'opacity-100');
  }, 4000);
})();
<?php unset($_SESSION['print_msg']); endif; ?>
</script>

<?php if (isset($_SESSION['print_payload'])): ?>
    <script>
        // Sambungkan ke channel printer yang ada di tab sebelahnya
        const saluranKirim = new BroadcastChannel('printer_channel');
        
        // Dekode Base64 menjadi string biasa
        const dataStruk = atob("<?= $_SESSION['print_payload'] ?>");
        
        // Kirimkan perintah cetak
        saluranKirim.postMessage(dataStruk);
    </script>
    <?php 
    // Hapus sesi agar tidak tercetak ulang jika halaman direfresh
    unset($_SESSION['print_payload']); 
    ?>
<?php endif; ?>

<?php include 'layout/footer.php'; ?>