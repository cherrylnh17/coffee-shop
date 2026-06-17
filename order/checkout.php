<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../path.php';
require_once __DIR__ . '/../helper/validateTable.php';

$table_code = htmlspecialchars($_GET['table']);

validateTable($pdo, $table_code);

try {
    $query = "SELECT * FROM menu ORDER BY created_at ASC";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ambil semua fee dari gratuity
    $feeStmt = $pdo->prepare("SELECT * FROM gratuity");
    $feeStmt->execute();
    $gratuitys = $feeStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error pada query: " . $e->getMessage());
}
?>

<?php
$title = "Identitas";
include __DIR__ . '/layout/header.php';
?>

<main class="w-full max-w-[480px] mx-auto bg-gray-50 min-h-screen relative shadow-2xl flex flex-col overflow-x-hidden">

    <header class="sticky top-0 z-20 bg-white shadow-sm pt-5 pb-4 px-4 flex items-center gap-3">
        <a href="<?= BASE_URL ?>order/<?= $table_code ?>/cart" class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 transition-colors">
            <i class="ph-bold ph-arrow-left text-lg"></i>
        </a>
        <h1 class="text-xl font-bold text-gray-900">Data Pemesan</h1>
    </header>

    <form id="formPesanan" method="POST" action="<?= BASE_URL ?>order/server/order_proses">
        <input type="hidden" name="table_name" value="<?= $table_code ?>">
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
                        <input type="text" name="customer_name" id="name" placeholder="Masukkan nama Anda" required
                            class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all bg-white">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Email <span class="text-gray-400 font-normal">(Opsional)</span></label>
                        <input type="email" name="customer_email" id="email" placeholder="contoh@email.com"
                            class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all bg-white">
                    </div>
                </div>

                <!-- Metode Pembayaran -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mt-4">
                    <h2 class="font-bold text-gray-900 mb-1 flex items-center gap-2">
                        <span class="w-7 h-7 bg-blue-100 rounded-lg flex items-center justify-center text-sm">💳</span>
                        Metode Pembayaran
                    </h2>
                    <p class="text-sm text-gray-400 mb-4">Pilih salah satu metode pembayaran</p>
                    <div class="grid grid-cols-2 gap-3">

                        <!-- Online (Tripay) -->
                        <label class="relative cursor-not-allowed">
                            <input type="radio" name="payment" value="2" class="peer hidden" disabled />
                            <div class="w-full p-4 text-center border-2 border-gray-200 rounded-2xl bg-gray-100 opacity-60 select-none">
                                <i class="ph ph-device-mobile text-2xl text-gray-400 mb-1 block"></i>
                                <span class="font-bold text-gray-400 text-sm">Online</span>
                                <p class="text-[10px] text-gray-400 mt-0.5">QRIS · OVO · DANA · ShopeePay</p>
                            </div>
                            <!-- Badge Coming Soon -->
                            <div class="absolute top-2 right-2">
                                <span class="bg-amber-400 text-white text-[9px] font-black px-1.5 py-0.5 rounded-full uppercase tracking-wide leading-none">
                                    Soon
                                </span>
                            </div>
                            <!-- Lock icon -->
                            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 pointer-events-none">
                                <div class="bg-white/80 backdrop-blur-sm rounded-full p-1.5 shadow">
                                    <i class="ph-bold ph-lock text-gray-400 text-sm block"></i>
                                </div>
                            </div>
                        </label>

                        <!-- Bayar di Kasir -->
                        <label class="relative cursor-pointer">
                            <input type="radio" name="payment" value="1" class="peer hidden" checked />
                            <div class="w-full p-4 text-center border-2 border-gray-100 rounded-2xl bg-gray-50 transition-all duration-200
                                peer-checked:border-blue-400 peer-checked:bg-blue-50 peer-checked:ring-4 peer-checked:ring-blue-100
                                hover:border-blue-200">
                                <i class="ph ph-cash-register text-2xl text-blue-500 mb-1 block"></i>
                                <span class="font-bold text-gray-700 text-sm">Bayar di Kasir</span>
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

                    <!-- Info kontekstual berdasarkan pilihan payment -->
                    <div id="info-kasir" class="mt-3 bg-blue-50 border border-blue-100 rounded-xl p-3 flex items-center gap-2">
                        <i class="ph ph-info text-blue-500 text-lg flex-shrink-0"></i>
                        <p class="text-xs text-blue-700 font-medium">Tunjukkan QR Code kepada kasir untuk menyelesaikan pembayaran.</p>
                    </div>
                    <div id="info-online" class="mt-3 bg-green-50 border border-green-100 rounded-xl p-3 items-center gap-2 hidden">
                        <div class="flex items-center gap-2">
                            <i class="ph ph-shield-check text-green-500 text-lg flex-shrink-0"></i>
                            <p class="text-xs text-green-700 font-medium">Bayar aman via Tripay. Pilih metode (QRIS, OVO, DANA, ShopeePay) di halaman berikutnya.</p>
                        </div>
                    </div>
                </div>

                <!-- Ringkasan pesanan -->
                <div class="mt-5 rounded-2xl border border-gray-100 overflow-hidden">
                    <div class="bg-gray-50 px-4 py-3 flex items-center gap-2 border-b border-gray-100">
                        <span class="w-7 h-7 bg-blue-100 rounded-lg flex items-center justify-center text-sm">🛍️</span>
                        <h2 class="font-bold text-gray-900 text-sm">Ringkasan Pesanan</h2>
                    </div>
                    <div id="order-summary" class="divide-y divide-gray-100 bg-white"></div>
                    <div class="bg-gray-50 px-4 py-3 space-y-2 border-t border-gray-100">
                        <div id="fee-lines" class="space-y-1"></div>
                        <div class="flex justify-between items-center pt-1 border-t border-dashed border-gray-200">
                            <span class="font-bold text-gray-900 text-sm">Total</span>
                            <span class="font-black text-blue-600 text-base" id="summary-total">Rp 0</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>

    <div class="fixed bottom-0 w-full max-w-[480px] left-1/2 -translate-x-1/2 p-4 bg-white border-t border-gray-200 z-30">
        <button id="btnCheckout" onclick="submitPesanan()" disabled
            class="w-full bg-gray-300 text-gray-400 cursor-not-allowed rounded-xl py-3.5 font-bold text-base shadow-lg transition-all duration-200 flex items-center justify-center gap-2">
            <span id="btnLabel">Lanjut ke Checkout</span>
            <i class="ph-bold ph-caret-right"></i>
        </button>
    </div>

</main>

<script>
    const MENU_DATA  = <?php echo json_encode($result); ?>;
    const gratuitys  = <?php echo json_encode($gratuitys); ?>;

    function calculateFees(subtotal) {
        let totalFee = 0;
        const feeLines = [];
        gratuitys.forEach(fee => {
            let amount = parseInt(fee.type) === 1
                ? subtotal * (parseFloat(fee.value) / 100)
                : parseFloat(fee.value);
            totalFee += amount;
            feeLines.push({ name: fee.name, type: parseInt(fee.type), value: parseFloat(fee.value), amount });
        });
        return { totalFee, feeLines };
    }

    function getCart()      { return JSON.parse(localStorage.getItem('cart')) || []; }
    function saveCart(cart) { localStorage.setItem('cart', JSON.stringify(cart)); }

    function formatRupiah(angka) {
        return 'Rp ' + parseInt(angka).toLocaleString('id-ID');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const cart = getCart();

        if (!cart.length) {
            window.location.href = 'index?code=<?= $table_code ?>';
            return;
        }

        // Render ringkasan pesanan
        const listEl = document.getElementById('order-summary');
        let total = 0;
        cart.forEach(ci => {
            const menu = MENU_DATA.find(m => m.id === ci.id);
            if (!menu) return;
            const sub = menu.price * ci.qty;
            total += sub;
            const hasNote = ci.note && ci.note.trim() !== '';
            const row = document.createElement('div');
            row.className = 'px-4 py-3';
            row.innerHTML = `
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-md bg-blue-600 text-white text-[10px] font-black flex-shrink-0">${ci.qty}</span>
                            <span class="font-semibold text-sm text-gray-800 truncate">${menu.name}</span>
                        </div>
                        <p class="text-[11px] text-gray-400 mt-0.5 ml-7">${formatRupiah(menu.price)} / item</p>
                        ${hasNote ? `<div class="ml-7 mt-1.5 flex items-start gap-1.5">
                            <i class="ph ph-note-pencil text-amber-400 text-xs mt-0.5 flex-shrink-0"></i>
                            <span class="text-[11px] text-amber-600 leading-relaxed italic">${ci.note}</span>
                        </div>` : ''}
                    </div>
                    <span class="font-bold text-sm text-gray-900 flex-shrink-0 pt-0.5">${formatRupiah(sub)}</span>
                </div>`;
            listEl.appendChild(row);
        });

        const { totalFee, feeLines } = calculateFees(total);

        const feeLinesEl = document.getElementById('fee-lines');
        feeLinesEl.innerHTML = '';
        feeLines.forEach(f => {
            const suffix = f.type === 1 ? ` (${f.value}%)` : '';
            const row = document.createElement('div');
            row.className = 'flex justify-between text-xs text-gray-500';
            row.innerHTML = `
                <span>${f.name}${suffix}</span>
                <span class="font-semibold">${formatRupiah(f.amount)}</span>`;
            feeLinesEl.appendChild(row);
        });

        document.getElementById('summary-total').innerText = formatRupiah(total + totalFee);

        // Pre-fill dari localStorage jika ada
        const saved = JSON.parse(localStorage.getItem('buyer_data'));
        if (saved) {
            document.getElementById('name').value  = saved.name  || '';
            document.getElementById('email').value = saved.email || '';
            if (saved.payment) {
                const radio = document.querySelector(`input[name="payment"][value="${saved.payment}"]`);
                if (radio) radio.checked = true;
                updatePaymentUI(saved.payment);
            }
        }

        toggleCheckoutButton();
    });

    // Toggle info kasir/online saat radio berubah
    document.querySelectorAll('input[name="payment"]').forEach(radio => {
        radio.addEventListener('change', () => updatePaymentUI(radio.value));
    });

    function updatePaymentUI(val) {
        const infoKasir  = document.getElementById('info-kasir');
        const infoOnline = document.getElementById('info-online');
        const btnLabel   = document.getElementById('btnLabel');

        if (val == '2') {
            infoKasir.classList.add('hidden');
            infoOnline.classList.remove('hidden');
            infoOnline.classList.add('flex');
            btnLabel.textContent = 'Pilih Metode Pembayaran';
        } else {
            infoOnline.classList.add('hidden');
            infoOnline.classList.remove('flex');
            infoKasir.classList.remove('hidden');
            btnLabel.textContent = 'Lanjut ke Checkout';
        }
    }

    function submitPesanan() {
        const form = document.getElementById('formPesanan');
        if (!form.reportValidity()) return;

        const cart        = getCart();
        const paymentVal  = document.querySelector('input[name="payment"]:checked').value;

        const buyerData = {
            name:    document.getElementById('name').value,
            email:   document.getElementById('email').value,
            payment: paymentVal,
        };
        localStorage.setItem('buyer_data', JSON.stringify(buyerData));

        if (paymentVal == '2') {
            // ── Pembayaran Online → kirim order dulu, lalu redirect ke pilih channel
            const hiddenCart = document.createElement('input');
            hiddenCart.type  = 'hidden';
            hiddenCart.name  = 'cart_data';
            hiddenCart.value = JSON.stringify(cart);
            form.appendChild(hiddenCart);

            // Override action supaya order_proses tahu ini online, nanti redirect ke payment_online
            const hiddenRedirect = document.createElement('input');
            hiddenRedirect.type  = 'hidden';
            hiddenRedirect.name  = 'redirect_to';
            hiddenRedirect.value = 'payment_online';
            form.appendChild(hiddenRedirect);

            form.submit();
        } else {
            // ── Bayar di Kasir → flow lama
            const hiddenCart = document.createElement('input');
            hiddenCart.type  = 'hidden';
            hiddenCart.name  = 'cart_data';
            hiddenCart.value = JSON.stringify(cart);
            form.appendChild(hiddenCart);

            form.submit();
        }
    }

    // Toggle tombol checkout berdasarkan isian nama
    const nameInput   = document.getElementById('name');
    const btnCheckout = document.getElementById('btnCheckout');

    function toggleCheckoutButton() {
        const filled = nameInput.value.trim() !== '';
        btnCheckout.disabled = !filled;
        if (filled) {
            btnCheckout.classList.remove('bg-gray-300', 'text-gray-400', 'cursor-not-allowed');
            btnCheckout.classList.add('bg-blue-600', 'hover:bg-blue-700', 'text-white', 'active:scale-[0.98]');
        } else {
            btnCheckout.classList.add('bg-gray-300', 'text-gray-400', 'cursor-not-allowed');
            btnCheckout.classList.remove('bg-blue-600', 'hover:bg-blue-700', 'text-white', 'active:scale-[0.98]');
        }
    }

    nameInput.addEventListener('input', toggleCheckoutButton);
    toggleCheckoutButton();

    // Error message dari URL
    const urlParams    = new URLSearchParams(window.location.search);
    const errorMessage = urlParams.get('m');
    if (errorMessage) {
        Swal.fire({
            title: "Waduh!",
            text:  errorMessage,
            icon:  "error",
            confirmButtonText:  "Oke",
            confirmButtonColor: "#3b82f6",
        });
        window.history.replaceState({}, document.title, window.location.pathname + "?table=" + urlParams.get('table'));
    }
</script>

<?php include __DIR__ . '/layout/footer.php'; ?>