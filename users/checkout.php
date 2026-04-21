<?php 
include '../config.php';

// Aktifkan error reporting untuk debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);


if (!isset($_GET['code']) || empty($_GET['code'])) {
    header("Location: ../");
    exit(); 
} else {
    $order_code = $_GET['code'];
}

try {
    // Ambil data menu dari database
    $query = "SELECT * FROM order WHERE code = ?";
    $stmt = $pdo->prepare("SELECT * FROM `order` WHERE code = ?");
    $stmt->execute([$order_code]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error pada query: " . $e->getMessage());
}

$payment = ($order['payment'] == 1) ? 'Bayar di Kasir' : 'Bayar Online';


$table_code = htmlspecialchars($_GET['code']);

$qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . $order['code'];

?>

<?php 
  $title = "Identitas"; 
  include 'layout/header.php'; 
?>

<main class="w-full max-w-[480px] mx-auto bg-gray-50 min-h-screen relative shadow-2xl flex flex-col overflow-x-hidden">

    <header class="sticky top-0 z-20 bg-white shadow-sm pt-5 pb-4 px-4 flex items-center gap-3">
        <button onclick="window.location.href='identitas.html'" class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 transition-colors">
            <i class="ph-bold ph-arrow-left text-lg"></i>
        </button>
        <h1 class="text-xl font-bold text-gray-900">Checkout</h1>
    </header>

    <div class="flex-1 px-4 py-5 space-y-4 pb-32 fade-in">

        <!-- Info Meja & Pemesan -->
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <p class="text-xs text-gray-500 mb-1">Nomor Meja</p>
                    <div class="bg-blue-100 text-blue-600 px-3 py-1.5 rounded-lg inline-flex items-center gap-1.5 border border-blue-200">
                        <i class="ph-fill ph-armchair"></i>
                        <span class="font-bold text-sm"><?= $order['table_name'] ?></span>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500 mb-0.5">Pemesan</p>
                    <p class="font-semibold text-gray-900 text-sm" id="display-name"><?= $order['customer_name'] ?></p>
                </div>
            </div>
            <div class="dashed-line my-3"></div>
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-xs text-gray-500 mb-1">Metode Bayar</p>
                    <span class="text-sm font-bold text-blue-600" id="display-payment"><?= $payment ?></span>
                </div>

            </div>
        </div>

        <!-- QR Pembayaran -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 text-center">
            <h2 class="text-sm font-bold text-gray-900 mb-1">Order Code</h2>
            <div class="mb-4 flex justify-center font-semibold">
                <span class="bg-gray-100 border px-4 py-1.5 rounded-lg text-gray-500 text-sm" ><?= $order['code'] ?></span>
            </div>
            <div class="mx-auto mb-3 p-2 bg-white border-2 border-gray-200 rounded-xl shadow-inner w-40 h-40 flex items-center justify-center">
                <img src="<?= $qrUrl; ?>" alt="QR Pembayaran" class="w-full h-full object-contain">
            </div>
            <div class="flex items-center justify-center gap-2 bg-yellow-50 border border-yellow-200 px-4 py-2.5 rounded-xl">
                <i class="ph-fill ph-warning text-yellow-500 text-xl flex-shrink-0"></i>
                <p class="text-xs text-yellow-700"><span class="font-bold">Tunjukkan QR Code</span> atau <span class="font-bold">Order Code</span> kepada kasir</p>
            </div>
        </div>

        <!-- Rincian Pesanan -->
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
            <h2 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                <i class="ph-fill ph-shopping-bag text-blue-500 text-lg"></i> Rincian Pesanan
            </h2>
            <div id="order-list" class="space-y-3 divide-y divide-gray-50"></div>
        </div>

        <!-- Ringkasan Pembayaran -->
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
            <h2 class="text-sm font-bold text-gray-900 mb-3">Ringkasan Pembayaran</h2>
            <div class="space-y-2 mb-3">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Subtotal</span>
                    <span class="font-medium text-gray-800" id="calc-subtotal">Rp 0</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Pajak (12%)</span>
                    <span class="font-medium text-gray-800" id="calc-tax">Rp 0</span>
                </div>
            </div>
            <div class="dashed-line my-3"></div>
            <div class="flex justify-between items-center">
                <span class="font-bold text-gray-800">Total Tagihan</span>
                <span class="font-black text-blue-600 text-xl" id="calc-total">Rp 0</span>
            </div>
        </div>

    </div>

    <!-- Confirm Button -->
    <!-- <div class="fixed bottom-0 w-full max-w-[480px] left-1/2 -translate-x-1/2 p-4 bg-white border-t border-gray-200 z-30">
        <button onclick="completeOrder()" class="w-full bg-blue-600 hover:bg-blue-700 text-white rounded-xl py-3.5 font-bold text-base shadow-lg transition-transform active:scale-[0.98] flex items-center justify-center gap-2">
            <i class="ph-bold ph-check-circle text-lg"></i>
            <span>Halaman Success</span>
        </button>
    </div> -->


    <div class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-[480px] bg-white border-t border-gray-100 px-4 py-4 shadow-2xl z-20">
      <button onclick="backToMenu()"
        class="w-full bg-blue-500 text-white font-black py-4 rounded-2xl text-base shadow-lg shadow-blue-200 active:scale-95 transition-transform flex items-center justify-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
        Pesan Lagi
      </button>
    </div>

    

</main>
<script src="menu-data.js"></script>
<script>
    function backToMenu() {
        localStorage.removeItem('cart');
        localStorage.removeItem('item_notes');
        window.location.href = 'index.html';
    }

    const itemNotes = JSON.parse(localStorage.getItem('item_notes')) || {};

    document.addEventListener('DOMContentLoaded', () => {
        const cart  = getCart();
        const buyer = JSON.parse(localStorage.getItem('buyer_data')) || {};
        if (!cart.length) { window.location.href = 'index.html'; return; }

        // Isi info pemesan
        document.getElementById('display-name').innerText    = buyer.name    || 'Pelanggan';
        document.getElementById('display-payment').innerText = buyer.payment || 'Bayar di Kasir';

        // Generate order code & QR
        const orderCode = 'APS7-' + Math.random().toString(36).toUpperCase().slice(2,8);
        const suffix = orderCode.split('-')[1];
        document.getElementById('code-suffix').innerText = suffix;
        document.getElementById('qr-image').src = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=TraffaCoffee-A01-${suffix}&color=111827`;

        // Rincian pesanan
        const orderList = document.getElementById('order-list');
        let subtotal = 0;
        cart.forEach(ci => {
            const menu = MENU_DATA.find(m => m.id === ci.id);
            if (!menu) return;
            const sub = menu.price * ci.qty;
            subtotal += sub;
            const div = document.createElement('div');
            div.className = 'flex justify-between items-center py-2';
            div.innerHTML = `
                <div class="flex-1">
                    <p class="font-semibold text-gray-800 text-sm">${menu.name}</p>
                    <p class="text-xs text-gray-400">${ci.qty}x @ ${formatRupiah(menu.price)}</p>
                    ${itemNotes[ci.id] ? `<p class="text-xs text-amber-600 italic mt-0.5">📝 ${itemNotes[ci.id]}</p>` : ''}
                </div>
                <div class="font-semibold text-gray-900 text-sm">${formatRupiah(sub)}</div>`;
            orderList.appendChild(div);
        });

        const tax   = subtotal * 0.12;
        const grand = subtotal + tax;
        document.getElementById('calc-subtotal').innerText = formatRupiah(subtotal);
        document.getElementById('calc-tax').innerText      = formatRupiah(tax);
        document.getElementById('calc-total').innerText    = formatRupiah(grand);
    });

    function completeOrder() {
        const buyer = JSON.parse(localStorage.getItem('buyer_data')) || {};
        const cart  = getCart();
        if (!cart.length) return;

        const subtotal = cart.reduce((s,ci) => {
            const m = MENU_DATA.find(x => x.id === ci.id);
            return s + (m ? m.price * ci.qty : 0);
        }, 0);
        const tax = subtotal * 0.12;

        // Simpan data untuk success.html
        const successData = {
            invoice:  generateInvoice(),
            name:     buyer.name    || 'Pelanggan',
            payment:  buyer.payment || 'Bayar di Kasir',
            notes:    buyer.notes   || '',
            items:    cart.map(ci => ({...ci, note: itemNotes[ci.id] || ''})),
            subtotal: subtotal,
            tax:      tax,
            total:    subtotal + tax
        };
        localStorage.setItem('success_data', JSON.stringify(successData));

        // Bersihkan cart & buyer data
        localStorage.removeItem('cart');
        localStorage.removeItem('buyer_data');
        localStorage.removeItem('item_notes');

        window.location.href = 'success.html';
    }
</script>

<?php include 'layout/footer.php'; ?>

