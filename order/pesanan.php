<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../path.php';
require_once __DIR__ . '/../helper/validateTable.php';

$table_code = htmlspecialchars($_GET['table']);

validateTable($pdo, $table_code);

try {
    // Ambil data menu dari database
    $query = "SELECT * FROM menu ORDER BY created_at ASC";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error pada query: " . $e->getMessage());
}

?>

<?php
$title = "Pesanan";
include __DIR__ . '/layout/header.php';
?>

<main class="w-full max-w-[480px] mx-auto bg-gray-50 min-h-screen relative shadow-2xl flex flex-col overflow-x-hidden">

    <header class="sticky top-0 z-20 bg-white shadow-sm pt-5 pb-4 px-4 flex items-center gap-3">
        <a href="<?= BASE_URL ?>order/<?= $table_code ?>/index" class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 transition-colors">
            <i class="ph-bold ph-arrow-left text-lg"></i>
        </a>
        <h1 class="text-xl font-bold text-gray-900">Keranjang Pesanan</h1>
    </header>

    <div class="flex-1 px-4 py-5 space-y-4 pb-32 fade-in">

        <!-- Info Meja -->
        <div class="bg-sky-50 border border-sky-200 rounded-2xl p-4 flex items-center gap-3">
            <div class="w-10 h-10 bg-sky-500 rounded-xl flex items-center justify-center text-white">
                <i class="ph-fill ph-armchair text-xl"></i>
            </div>
            <div>
                <p class="text-xs text-sky-600 font-semibold uppercase tracking-wide">Nomor Meja</p>
                <p class="text-lg font-black text-sky-700"><?= $table_code ?></p>
            </div>
        </div>

        <!-- Daftar Item -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-4 pt-4 pb-2 flex items-center justify-between">
                <h2 class="font-bold text-gray-900 flex items-center gap-2">
                    <i class="ph-fill ph-shopping-bag text-blue-500 text-lg"></i> Item Pesanan
                </h2>
                <button onclick="clearCart()" class="text-xs text-red-400 hover:text-red-600 font-semibold transition-colors cursor-pointer">Kosongkan</button>
            </div>
            <div id="order-list" class="divide-y divide-gray-100"></div>
            <!-- Empty state -->
            <div id="empty-state" class="hidden py-12 flex-col items-center justify-center text-center px-4">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                    <i class="ph ph-shopping-cart text-2xl text-gray-400"></i>
                </div>
                <p class="font-bold text-gray-900">Keranjang kosong</p>
                <p class="text-xs text-gray-500 mt-1">Tambahkan menu terlebih dahulu.</p>
                <a href="<?= BASE_URL ?>order/<?= $table_code ?>/index" class="mt-4 px-5 py-2 bg-sky-500 text-white rounded-full text-sm font-semibold">
                    Lihat Menu
                </a>
            </div>
        </div>

        <!-- Ringkasan -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4" id="summary-section">
            <h2 class="font-bold text-gray-900 mb-3">Ringkasan</h2>
            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Subtotal (<span id="total-qty">0</span> item)</span>
                    <span class="font-semibold text-gray-800" id="subtotal-price">Rp 0</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Pajak (12%)</span>
                    <span class="font-semibold text-gray-800" id="tax-price">Rp 0</span>
                </div>
                <div class="border-t border-dashed border-gray-200 pt-2 flex justify-between items-center">
                    <span class="font-bold text-gray-900">Total</span>
                    <span class="font-black text-blue-600 text-lg" id="grand-total">Rp 0</span>
                </div>
            </div>
        </div>

        <!-- Tambah menu lagi -->
        <a href="<?= BASE_URL ?>order/<?= $table_code ?>/index" class="w-full border-2 border-dashed border-gray-200 rounded-2xl py-4 text-sm font-semibold text-gray-400 hover:border-sky-300 hover:text-sky-500 transition-all flex items-center justify-center gap-2">
            <i class="ph ph-plus-circle text-lg"></i> Tambah Menu Lagi
        </a>

    </div>

    <!-- Footer CTA -->
    <div class="fixed bottom-0 w-full max-w-[480px] left-1/2 -translate-x-1/2 p-4 bg-white border-t border-gray-200 z-30">
        <button
            id="checkout-btn"
            onclick="goToIdentitas()"
            class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white rounded-xl py-3.5 font-bold text-base shadow-lg transition-transform active:scale-[0.98] flex items-center justify-center gap-2 cursor-pointer">
            <span>Lanjut ke Pembayaran</span>
            <i class="ph-bold ph-caret-right"></i>
        </button>
    </div>

    <!-- Note Modal -->
    <div id="note-modal" class="fixed inset-0 bg-black/40 z-50 flex items-end justify-center opacity-0 transition-opacity duration-300">
        <div id="note-sheet" class="bg-white w-full max-w-[480px] rounded-t-3xl p-6 transform translate-y-full transition-transform duration-300 flex flex-col gap-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-gray-900 text-base" id="note-modal-title">Catatan</h3>
                    <p class="text-xs text-gray-400 mt-0.5" id="note-modal-subtitle">Opsional</p>
                </div>
                <button onclick="closeNoteModal()" class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors">
                    <i class="ph-bold ph-x text-sm"></i>
                </button>
            </div>
            <textarea id="note-input"
                placeholder="Contoh: tanpa bawang, level pedas sedang, tidak pakai es..."
                rows="3"
                class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-all bg-gray-50 resize-none leading-relaxed"></textarea>
            <div class="flex gap-3">
                <button onclick="clearNote()" class="flex-1 border border-gray-200 text-gray-500 font-semibold py-3 rounded-xl text-sm hover:bg-gray-50 transition-colors">
                    Hapus Catatan
                </button>
                <button onclick="saveNote()" class="flex-1 bg-blue-600 text-white font-bold py-3 rounded-xl text-sm hover:bg-blue-700 transition-colors active:scale-95">
                    Simpan
                </button>
            </div>
        </div>
    </div>

</main>

<script src="<?= BASE_URL ?>assets/js/helperUrl.js"></script>
<script>
    const BASE_URL = "<?= BASE_URL ?>";
    let currentNoteItemId = null;

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

    function renderOrder() {
        const cart = getCart();
        const container = document.getElementById('order-list');
        const emptyState = document.getElementById('empty-state');
        const summarySection = document.getElementById('summary-section');
        const checkoutBtn = document.getElementById('checkout-btn');

        container.innerHTML = '';

        if (!cart.length) {
            emptyState.classList.remove('hidden');
            emptyState.classList.add('flex');
            summarySection.classList.add('hidden');
            checkoutBtn.disabled = true;
            checkoutBtn.classList.add('opacity-50', 'cursor-not-allowed');
            return;
        }

        emptyState.classList.add('hidden');
        emptyState.classList.remove('flex');
        summarySection.classList.remove('hidden');
        checkoutBtn.disabled = false;
        checkoutBtn.classList.remove('opacity-50', 'cursor-not-allowed');

        let subtotal = 0;
        let totalQty = 0;

        cart.forEach((item, index) => {
                    const menu = MENU_DATA.find(m => m.id == item.id);
                    if (!menu) return;

                    const sub = menu.price * item.qty;
                    subtotal += sub;
                    totalQty += item.qty;
                    const isLast = index === cart.length - 1;

                    const note = item.note || '';
                    const hasNote = note.trim() !== '';

                    const el = document.createElement('div');
                    el.className = `p-4 ${isLast ? '' : 'border-b border-gray-100'}`;
                    el.innerHTML = `
                <div class="flex gap-3 items-start">
                    <img src="${getImageUrl(item.image)}" alt="${menu.name}" class="w-14 h-14 rounded-xl object-cover flex-shrink-0 shadow-sm border border-gray-100">
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-sm text-gray-900 truncate">${menu.name}</p>
                        <p class="text-xs text-gray-400 mt-0.5">${formatRupiah(menu.price)} / item</p>
                        <p class="font-bold text-blue-600 text-sm mt-0.5">${formatRupiah(sub)}</p>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0 pt-1">
                        <button onclick="changeQty('${item.id}', -1)" class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center hover:bg-red-50 hover:text-red-500 transition-colors text-gray-600 cursor-pointer">
                            <i class="ph-bold ph-minus text-xs"></i>
                        </button>
                        <span class="text-sm font-bold text-gray-800 w-5 text-center">${item.qty}</span>
                        <button onclick="changeQty('${item.id}', 1)" class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center hover:bg-blue-700 transition-colors cursor-pointer">
                            <i class="ph-bold ph-plus text-xs"></i>
                        </button>
                    </div>
                </div>

                <div class="mt-2.5 ml-[68px]">
                    ${hasNote ? `
                    <div class="flex items-start gap-2">
                        <div class="flex-1 bg-amber-50 border border-amber-200 rounded-xl px-3 py-2">
                            <p class="text-[11px] text-amber-700 font-semibold leading-relaxed">"${note}"</p>
                        </div>
                        <button onclick="openNoteModal('${item.id}', \`${menu.name.replace(/`/g,"'")}\`)"
                            class="flex-shrink-0 w-8 h-8 rounded-xl bg-gray-100 flex items-center justify-center hover:bg-blue-50 hover:text-blue-500 transition-colors text-gray-500 cursor-pointer">
                            <i class="ph-bold ph-pencil text-xs"></i>
                        </button>
                    </div>` : `
                    <button onclick="openNoteModal('${item.id}', \`${menu.name.replace(/`/g,"'")}\`)"
                        class="flex items-center gap-1.5 text-[11px] text-gray-400 hover:text-blue-500 font-semibold transition-colors group cursor-pointer">
                        <i class="ph ph-plus-circle text-sm group-hover:text-blue-500"></i>
                        Tambah catatan <span class="text-gray-300 font-normal">(opsional)</span>
                    </button>`}
                </div>`;
            container.appendChild(el);
        });

        const tax = subtotal * 0.12;
        const grand = subtotal + tax;
        document.getElementById('total-qty').innerText      = totalQty;
        document.getElementById('subtotal-price').innerText = formatRupiah(subtotal);
        document.getElementById('tax-price').innerText      = formatRupiah(tax);
        document.getElementById('grand-total').innerText    = formatRupiah(grand);
    }

    function changeQty(id, delta) {
        let cart = getCart();
        const itemIndex = cart.findIndex(i => i.id == id);
        if (itemIndex === -1) return;
        
        cart[itemIndex].qty += delta;
        if (cart[itemIndex].qty <= 0) {
            cart.splice(itemIndex, 1);
        }
        
        saveCart(cart);
        renderOrder();
    }

    function clearCart() {
        if (!confirm('Yakin ingin mengosongkan keranjang?')) return;
        saveCart([]);
        renderOrder();
    }

    // Note modal 
    function openNoteModal(itemId, menuName) {
        currentNoteItemId = itemId;
        document.getElementById('note-modal-title').innerText    = `Catatan untuk: ${menuName}`;
                    document.getElementById('note-modal-subtitle').innerText = 'Opsional — bisa dikosongkan';

                    const cart = getCart();
                    const item = cart.find(i => i.id == itemId);
                    document.getElementById('note-input').value = (item && item.note) ? item.note : '';

                    const modal = document.getElementById('note-modal');
                    const sheet = document.getElementById('note-sheet');
                    modal.classList.remove('hidden');
                    setTimeout(() => {
                        modal.classList.remove('opacity-0');
                        sheet.classList.remove('translate-y-full');
                        document.getElementById('note-input').focus();
                    }, 10);
                }

                function closeNoteModal() {
                    const modal = document.getElementById('note-modal');
                    const sheet = document.getElementById('note-sheet');
                    modal.classList.add('opacity-0');
                    sheet.classList.add('translate-y-full');
                    setTimeout(() => modal.classList.add('hidden'), 300);
                    currentNoteItemId = null;
                }

                function saveNote() {
                    if (!currentNoteItemId) return;
                    const val = document.getElementById('note-input').value.trim();

                    let cart = getCart();
                    const itemIndex = cart.findIndex(i => i.id == currentNoteItemId);
                    if (itemIndex !== -1) {
                        cart[itemIndex].note = val;
                        saveCart(cart);
                    }

                    closeNoteModal();
                    setTimeout(renderOrder, 310);
                }

                function clearNote() {
                    document.getElementById('note-input').value = '';

                    if (currentNoteItemId) {
                        let cart = getCart();
                        const itemIndex = cart.findIndex(i => i.id == currentNoteItemId);
                        if (itemIndex !== -1) {
                            cart[itemIndex].note = '';
                            saveCart(cart);
                        }
                    }

                    closeNoteModal();
                    setTimeout(renderOrder, 310);
                }

                // Tutup modal kalau klik backdrop
                document.getElementById('note-modal').addEventListener('click', function(e) {
                    if (e.target === this) closeNoteModal();
                });

                function updateGotoButton() {
                    const cart = getCart();
                    const btn = document.getElementById('gotoidentitas');

                    btn.disabled = cart.length === 0;
                }

                function goToIdentitas() {
                    const cart = getCart();
                    if (!cart.length) return;
                    window.location.href = '<?= BASE_URL ?>order/<?= $table_code ?>/identitas';
                }

                document.addEventListener('DOMContentLoaded', () => {
                    renderOrder();
                });
</script>

<?php include __DIR__ . '/layout/footer.php'; ?>