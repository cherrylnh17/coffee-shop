<?php 
include '../config.php';

// Aktifkan error reporting untuk debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    // Ambil data menu dari database
    $query = "SELECT * FROM menu ORDER BY created_at ASC";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC); 
} catch(PDOException $e) {
    die("Error pada query: " . $e->getMessage());
}


if (!isset($_GET['code']) || empty($_GET['code'])) {
    header("Location: ../");
    exit(); 
}

$table_code = htmlspecialchars($_GET['code']);

?>

<?php 
  $title = "Identitas"; 
  include 'layout/header.php'; 
?>

<main class="w-full max-w-[480px] mx-auto bg-gray-50 min-h-screen relative shadow-2xl flex flex-col overflow-x-hidden">

    <header class="sticky top-0 z-20 bg-white shadow-sm pt-5 pb-4 px-4 flex items-center gap-3">
        <a href="pesanan?code=<?= $table_code ?>" class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 transition-colors">
            <i class="ph-bold ph-arrow-left text-lg"></i>
        </a>
        <h1 class="text-xl font-bold text-gray-900">Data Pemesan</h1>
    </header>

    <form id="formPesanan" method="POST" action="order.php?code=<?= $table_code ?>">
    <div class="flex-1 px-4 py-6 space-y-5 pb-32 fade-in">

        <!-- Informasi Pembeli -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h2 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                <span class="w-7 h-7 bg-blue-100 rounded-lg flex items-center justify-center text-sm">👤</span>
                Informasi Pembeli
            </h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="customer_name" id="name" placeholder="Masukkan nama Anda"
                        class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all bg-white">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Email <span class="text-gray-400 font-normal">(Opsional)</span></label>
                    <input type="email" name="customer_email" id="email" placeholder="contoh@email.com"
                        class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all bg-white">
                </div>
        </div>

        <!-- Metode Pembayaran -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h2 class="font-bold text-gray-900 mb-1 flex items-center gap-2">
                <span class="w-7 h-7 bg-blue-100 rounded-lg flex items-center justify-center text-sm">💳</span>
                Metode Pembayaran
            </h2>
            <p class="text-sm text-gray-400 mb-4">Pilih salah satu metode pembayaran</p>
            <div class="grid grid-cols-2 gap-3">
                <!-- Online — Coming Soon -->
                <label class="relative cursor-not-allowed opacity-50">
                    <input type="radio" name="payment" value="online" class="peer hidden" disabled />
                    <div class="w-full p-4 text-center border-2 border-gray-200 rounded-2xl bg-gray-100 transition-all duration-200">
                        <i class="ph ph-device-mobile text-2xl text-gray-400 mb-1 block"></i>
                        <span class="font-bold text-gray-400 text-sm">Online</span>
                    </div>
                    <div class="absolute -top-2 left-1/2 -translate-x-1/2 bg-gray-600 text-white text-[10px] px-2 py-0.5 rounded-full uppercase tracking-wider whitespace-nowrap">
                        Coming Soon
                    </div>
                </label>
                <!-- Bayar di Kasir -->
                <label class="relative cursor-pointer">
                    <input type="radio" name="payment" value="1" class="peer hidden" checked />
                    <div class="w-full p-4 text-center border-2 border-gray-100 rounded-2xl bg-gray-50 transition-all duration-200
                                peer-checked:border-blue-400 peer-checked:bg-blue-50 peer-checked:ring-4 peer-checked:ring-blue-100
                                hover:border-blue-200">
                        <i class="ph ph-cash-register text-2xl text-blue-500 mb-1 block"></i>
                        <span class="font-bold text-gray-700 text-sm peer-checked:text-blue-700">Bayar di Kasir</span>
                    </div>
                    <div class="absolute top-2 right-2 opacity-0 peer-checked:opacity-100 transition-opacity">
                        <div class="bg-blue-400 rounded-full p-0.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-white" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </label>
            </div>
        </div>

        <!-- Ringkasan pesanan -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h2 class="font-bold text-gray-900 mb-3 flex items-center gap-2">
                <span class="w-7 h-7 bg-blue-100 rounded-lg flex items-center justify-center text-sm">🛍️</span>
                Ringkasan Pesanan
            </h2>
            <div id="order-summary" class="space-y-2 text-sm mb-3 divide-y divide-gray-50"></div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Pajak (12%)</span>
                <span class="font-semibold text-gray-800" id="tax-price">Rp 0</span>
            </div>
            <div class="border-t border-dashed border-gray-200 pt-3 flex justify-between items-center">
                <span class="font-bold text-gray-900">Total</span>
                <span class="font-black text-blue-600 text-base" id="summary-total">Rp 0</span>
            </div>
        </div>

        <!-- Info kasir -->
        <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 flex items-center gap-3">
            <i class="ph ph-info text-blue-500 text-xl flex-shrink-0"></i>
            <p class="text-sm text-blue-700 font-medium">Lanjutkan ke Checkout dan tunjukkan QR Code kepada kasir untuk menyelesaikan pembayaran.</p>
        </div>

    </div>
    </form>

    <div class="fixed bottom-0 w-full max-w-[480px] left-1/2 -translate-x-1/2 p-4 bg-white border-t border-gray-200 z-30">
        <button onclick="submitPesanan()" class="w-full bg-blue-600 hover:bg-blue-700 text-white rounded-xl py-3.5 font-bold text-base shadow-lg transition-transform active:scale-[0.98] flex items-center justify-center gap-2">
            <span>Lanjut ke Checkout</span>
            <i class="ph-bold ph-caret-right"></i>
        </button>
    </div>

</main>
<script> 
    
    const MENU_DATA = <?php echo json_encode($result); ?>;

     function getCart() {
        return JSON.parse(localStorage.getItem('cart')) || [];
    }
    

    function saveCart(cart) {
        localStorage.setItem('cart', JSON.stringify(cart));
    }

    function formatRupiah(angka) {
        return 'Rp ' + parseInt(angka).toLocaleString('id-ID');
    }

    

    document.addEventListener('DOMContentLoaded', () => {
        const cart = getCart();

        console.log(cart);

        if (!cart.length) { window.location.href = 'index?code<?= $table_code ?>'; return; }

        // Render ringkasan pesanan
        const listEl = document.getElementById('order-summary');
        let total = 0;
        cart.forEach(ci => {
            const menu = MENU_DATA.find(m => m.id === ci.id);
            if (!menu) return;
            const sub = menu.price * ci.qty;
            total += sub;
            const row = document.createElement('div');
            row.className = 'flex justify-between items-center py-1.5 text-sm';
            row.innerHTML = `
                <span class="text-gray-600 flex items-center gap-1.5">
                    <span class="w-5 h-5 bg-blue-100 text-blue-600 rounded-full text-[10px] font-bold flex items-center justify-center">${ci.qty}</span>
                    ${menu.name}
                </span>
                <span class="font-semibold text-gray-800">${formatRupiah(sub)}</span>
                `;
            listEl.appendChild(row);
        });
        const tax = total * 0.12;
        document.getElementById('tax-price').innerText = formatRupiah(tax);
        document.getElementById('summary-total').innerText = formatRupiah(total + tax);

        // Pre-fill dari localStorage jika ada
        const saved = JSON.parse(localStorage.getItem('buyer_data'));
        if (saved) {
            document.getElementById('name').value  = saved.name  || '';
            document.getElementById('email').value = saved.email || '';
            if (saved.payment) {
                const radio = document.querySelector(`input[name="payment"][value="${saved.payment}"]`);
                if (radio) radio.checked = true;
            }
        }
    });

    // Contoh cara memasukkan data cart ke form sebelum submit
    function submitPesanan() {
        const cart = getCart();
        // Buat input hidden secara dinamis untuk cart
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'cart_data';
        hiddenInput.value = JSON.stringify(cart);
        
        const form = document.getElementById('formPesanan');
        form.appendChild(hiddenInput);
        form.submit(); 
    }
</script>


<?php include 'layout/footer.php'; ?>

