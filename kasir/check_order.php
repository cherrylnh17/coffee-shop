<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../path.php'; 


if (!isset($_SESSION['username']) || $_SESSION['role'] != 1) {
    header("Location: " . BASE_URL . "auth/login");
    exit;
}

// Ambil kode order dari URL
$order_code = isset($_GET['code']) ? $_GET['code'] : '';

if (empty($order_code)) {
    echo "<script>alert('Kode Pesanan Tidak Ditemukan'); window.location.href='index';</script>";
    exit;
}

try {
    // 1. Ambil data Order utama
    $stmt = $pdo->prepare("SELECT * FROM `order` WHERE code = ?");
    $stmt->execute([$order_code]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo "<script>alert('Pesanan tidak ditemukan'); window.location.href='index';</script>";
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

<?php 

$pageTitle = "Check Pesanan";
$currentPage = "check";

include 'layout/header.php';
include 'layout/sidebar.php';
?>


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
                    <button name="aksi" value="lunas" disabled class="w-full bg-green-600 hover:bg-green-700 cursor-not-allowed text-white font-bold py-4 rounded-2xl shadow-lg shadow-blue-200 transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-cash-register"></i> Pesanan telah diselesaikan
                    </button>
                </div>
                <?php else: ?>
                <form action="print_order" method="POST" class="flex-1" id="checkout-form">
                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                    <input type="hidden" name="total" value="<?= $order['total'] ?>">
                    
                    <button name="aksi" type="submit" id="btn-selesai" disabled value="selesai" class="w-full bg-gray-400 cursor-not-allowed text-white font-bold py-4 rounded-2xl shadow-lg transition-all flex items-center justify-center gap-2">
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
                
                <?php if($order['status'] != 1): ?>
                
                <div class="pt-4 flex justify-between items-center">
                  <label for="paid-input" class="font-bold text-gray-900">Uang Bayar</label>
                  <div class="relative w-1/2">
                     <span class="absolute left-3 top-2 text-gray-500 font-medium text-sm">Rp</span>
                     <input type="text" name="paid" id="paid-input" inputmode="numeric" form="checkout-form" required placeholder="0"
                            class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-right font-bold text-gray-900 outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                  </div>
                </div>
                
                <div class="pt-2 flex justify-between items-center">
                  <span class="font-bold text-green-600">Kembalian</span>
                  <span class="text-xl font-black text-green-600" id="change-display">Rp 0</span>
                  <input type="hidden" name="change" id="change-input" form="checkout-form" value="0">
                </div>

                <?php else: ?>
                
                <div class="pt-4 flex justify-between items-center text-sm border-t border-dashed border-gray-200">
                  <span class="text-gray-500">Uang Bayar</span>
                  <span class="font-medium text-gray-800">Rp <?= number_format($order['paid'] ?? 0, 0, ',', '.') ?></span>
                </div>
                <div class="pt-2 flex justify-between items-center">
                  <span class="font-bold text-green-600">Kembalian</span>
                  <span class="font-bold text-green-600 text-lg">Rp <?= number_format($order['change'] ?? 0, 0, ',', '.') ?></span>
                </div>
                
                <?php endif; ?>
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

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const paidInput = document.getElementById('paid-input');
        const changeDisplay = document.getElementById('change-display');
        const changeInput = document.getElementById('change-input');
        const btnSelesai = document.getElementById('btn-selesai');
        const totalAmount = <?= (int)$order['total'] ?>;

        if (paidInput) {
            paidInput.addEventListener('input', function() {
                let raw = this.value.replace(/\./g, '');
                let paid = parseInt(raw) || 0;

                this.value = paid === 0 ? '' : paid.toLocaleString('id-ID');

                let change = paid - totalAmount;

                if (change < 0) {
                    changeDisplay.innerText = "Uang Kurang!";
                    changeDisplay.classList.remove('text-green-600');
                    changeDisplay.classList.add('text-red-500');
                    changeInput.value = 0;
                    
                    if (btnSelesai) {
                        btnSelesai.disabled = true;
                        btnSelesai.classList.remove('bg-blue-600', 'hover:bg-blue-700', 'shadow-green-200');
                        btnSelesai.classList.add('bg-gray-400', 'cursor-not-allowed');
                    }
                } else {
                    changeDisplay.innerText = "Rp " + change.toLocaleString('id-ID');
                    changeDisplay.classList.remove('text-red-500');
                    changeDisplay.classList.add('text-green-600');
                    changeInput.value = change;
                    
                    if (btnSelesai) {
                        btnSelesai.disabled = false;
                        btnSelesai.classList.remove('bg-gray-400', 'cursor-not-allowed');
                        btnSelesai.classList.add('bg-blue-600', 'hover:bg-blue-700', 'shadow-green-200');
                    }
                }
            });

            document.getElementById('checkout-form').addEventListener('submit', function(e) {
                let raw = paidInput.value.replace(/\./g, '');
                let paid = parseInt(raw) || 0;
                
                if (paid < totalAmount) {
                    e.preventDefault();
                    alert("Uang yang dimasukkan masih kurang!");
                    return false;
                }
                
                paidInput.value = raw;
            });
        }
    });
    </script>


<?php 
include 'layout/footer.php'; 
?>