<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../path.php';
require_once __DIR__ . '/../helper/validateTable.php';

$table_code = htmlspecialchars($_GET['table']);

validateTable($pdo, $table_code);

if (!isset($_GET['code']) || empty($_GET['code'])) {
    header("Location:  " . BASE_URL . "order/" . $table_code . "/index");
    exit();
} else {
    $order_code = $_GET['code'];
}

try {
    // Ambil data Order utama
    $stmt = $pdo->prepare("SELECT * FROM `order` WHERE code = ? AND `status` = 2");
    $stmt->execute([$order_code]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    if (empty($order)) {
        header("Location: " . BASE_URL . "order/" . $table_code . "/index");
        exit();
    }
    // Ambil rincian menu yang dibeli
    $stmtItem = $pdo->prepare("SELECT * FROM order_item WHERE order_id = ?");
    $stmtItem->execute([$order['id']]);
    $order_items = $stmtItem->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error pada query: " . $e->getMessage());
}

$payment = ($order['payment'] == 1) ? 'Bayar di Kasir' : 'Bayar Online';


$qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . $order['code'];

?>

<?php
$title = "Identitas";
include __DIR__ . '/layout/header.php';
?>

<main class="w-full max-w-[480px] mx-auto bg-gray-50 min-h-screen relative shadow-2xl flex flex-col overflow-x-hidden">

    <header class="sticky top-0 z-20 bg-white shadow-sm pt-5 pb-4 px-4 flex items-center gap-3">
        <a href="<?= BASE_URL ?>order/<?= $table_code ?>/identitas" class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 transition-colors">
            <i class="ph-bold ph-arrow-left text-lg"></i>
        </a>
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
                <div>
                    <p class="text-xs text-gray-500 mb-1">Batas Pembayaran</p>
                    <span class="text-sm font-bold text-blue-600"
                        id="display-countdown"
                        data-expire="<?= $order['expired_at'] ?>">
                        Loading...
                    </span>
                </div>
            </div>
        </div>

        <!-- QR Pembayaran -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 text-center">
            <h2 class="text-sm font-bold text-gray-900 mb-1">Order Code</h2>
            <div class="mb-4 flex justify-center font-semibold">
                <span class="bg-gray-100 border px-4 py-1.5 rounded-lg text-gray-500 text-sm"><?= $order['code'] ?></span>
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
                    <span class="font-medium text-gray-800">Rp <?= number_format($order['subtotal'], 0, ',', '.') ?></span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Pajak (12%)</span>
                    <span class="font-medium text-gray-800">Rp <?= number_format($order['tax'], 0, ',', '.') ?></span>
                </div>
            </div>
            <div class="dashed-line my-3"></div>
            <div class="flex justify-between items-center">
                <span class="font-bold text-gray-800">Total Tagihan</span>
                <span class="font-black text-blue-600 text-xl">Rp <?= number_format($order['total'], 0, ',', '.') ?></span>
            </div>
        </div>
    </div>

    <div class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-[480px] bg-white border-t border-gray-100 px-4 py-4 shadow-2xl z-20">
        <button onclick="backToMenu()"
            class="w-full bg-blue-500 text-white font-black py-4 rounded-2xl text-base shadow-lg shadow-blue-200 active:scale-95 transition-transform flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
            </svg>
            Pesan Lagi
        </button>
    </div>



</main>

<script>
    function backToMenu() {
        localStorage.removeItem('cart');
        localStorage.removeItem('buyer_data');
        window.location.href = '<?= BASE_URL ?>order/<?= $table_code ?>/index';
    }
    const itemNotes = JSON.parse(localStorage.getItem('item_notes')) || {};

    document.addEventListener('DOMContentLoaded', () => {
        // Ambil data dari PHP ke JS 
        const orderData = <?php echo json_encode($order); ?>;
        const itemsData = <?php echo json_encode($order_items); ?>;

        // Tampilkan Nama dan Metode Pembayaran dari Database
        document.getElementById('display-name').innerText = orderData.customer_name;

        // Konversi angka payment (1/2) ke teks
        const paymentText = orderData.payment == 1 ? 'Bayar di Kasir' : 'Online';
        document.getElementById('display-payment').innerText = paymentText;

        // Render Daftar Pesanan dari Database 
        const orderList = document.getElementById('order-list');
        orderList.innerHTML = '';

        itemsData.forEach(item => {
            const div = document.createElement('div');
            div.className = 'flex justify-between items-center py-2';
            div.innerHTML = `
                <div class="flex-1">
                    <p class="font-semibold text-gray-800 text-sm">${item.menu_name}</p>
                    <p class="text-xs text-gray-400">${item.qty}x @ Rp ${parseInt(item.subtotal/item.qty).toLocaleString('id-ID')}</p>
                    ${item.notes ? `<p class="text-xs text-amber-600 italic mt-0.5">📝 ${item.notes}</p>` : ''}
                </div>
                <div class="font-semibold text-gray-900 text-sm">Rp ${parseInt(item.subtotal).toLocaleString('id-ID')}</div>`;
            orderList.appendChild(div);
        });


        // localStorage.removeItem('cart');
        // localStorage.removeItem('buyer_data');

    });

    // Fungsi untuk cek status ke server setiap 3 detik
    const checkInterval = setInterval(async () => {
        try {
            const response = await fetch('<?= BASE_URL ?>order/server/cek_status?id=<?= $order['id'] ?>');
            const data = await response.json();

            if (data.status == 1) {
                localStorage.removeItem('cart');
                localStorage.removeItem('buyer_data');
                clearInterval(checkInterval);
                window.location.href = '<?= BASE_URL ?>order/<?= $table_code ?>/success/<?= $order_code ?>';
            }
        } catch (error) {
            console.error("Gagal cek status", error);
        }
    }, 3000); // 3000ms = 3 detik

    function startCountdown() {
        const display = document.getElementById('display-countdown');
        const expireTime = new Date(display.getAttribute('data-expire')).getTime();

        // Update setiap 1 detik
        const timer = setInterval(function() {
            const now = new Date().getTime();
            const distance = expireTime - now;

            // Jika waktu sudah habis atau lewat
            if (distance <= 0) {
                clearInterval(timer);
                display.innerHTML = "Kadaluarsa";
                display.classList.remove('text-blue-600');
                display.classList.add('text-red-600'); // Ubah warna jadi merahss
                return;
            }

            // Kalkulasi jam, menit, detik
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            // Tampilkan format 00:00:00
            display.innerHTML =
                (hours < 10 ? "0" + hours : hours) + ":" +
                (minutes < 10 ? "0" + minutes : minutes) + ":" +
                (seconds < 10 ? "0" + seconds : seconds);

        }, 1000);
    }

    // Jalankan fungsi saat halaman dimuat
    document.addEventListener('DOMContentLoaded', startCountdown);
</script>

<?php include __DIR__ . '/layout/footer.php'; ?>