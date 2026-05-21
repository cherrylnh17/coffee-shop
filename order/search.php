<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../path.php';
require_once __DIR__ . '/../helper/validateTable.php';

$table_code = htmlspecialchars($_GET['table']);

validateTable($pdo, $table_code);

try {
    $stmt = $pdo->prepare("SELECT * FROM menu ORDER BY created_at ASC");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

$query = htmlspecialchars(trim($_GET['code'] ?? ''));
?>
<?php
$title = "Cari Menu";
include __DIR__ . '/layout/header.php';
?>

<main class="w-full max-w-[480px] mx-auto bg-gray-50 min-h-screen relative shadow-2xl flex flex-col overflow-x-hidden">

    <!-- Header Search -->
    <header class="sticky top-0 z-30 bg-white shadow-sm px-4 pt-4 pb-3">
        <div class="flex items-center gap-3">
            <a href="<?= BASE_URL ?>order/<?= $table_code ?>/index"
                class="w-9 h-9 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 transition-colors shrink-0 text-gray-700">
                <i class="ph-bold ph-arrow-left text-lg"></i>
            </a>
            <div class="relative flex-1">
                <i class="ph-bold ph-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-base pointer-events-none"></i>
                <input type="text" id="search-input" placeholder="Cari menu favoritmu..." autocomplete="off" autofocus
                    value="<?= $query ?>"
                    class="w-full bg-gray-100 text-sm text-gray-900 rounded-full pl-10 pr-10 py-2.5 focus:outline-none focus:ring-2 focus:ring-sky-400 transition-all placeholder-gray-400 font-medium border-none">
                <button id="clear-btn" onclick="clearSearch()"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors hidden">
                    <i class="ph-bold ph-x text-sm"></i>
                </button>
            </div>
        </div>

        <!-- Filter kategori -->
        <div class="flex gap-2 mt-3 overflow-x-auto no-scrollbar pb-0.5">
            <button data-cat="Semua" onclick="setCategory('Semua')"
                class="cat-btn px-4 py-1.5 rounded-full text-xs font-bold whitespace-nowrap transition-all bg-sky-500 text-white">
                Semua
            </button>
            <button data-cat="1" onclick="setCategory('1')"
                class="cat-btn px-4 py-1.5 rounded-full text-xs font-bold whitespace-nowrap transition-all bg-white border border-gray-200 text-gray-600">
                🍽️ Makanan
            </button>
            <button data-cat="2" onclick="setCategory('2')"
                class="cat-btn px-4 py-1.5 rounded-full text-xs font-bold whitespace-nowrap transition-all bg-white border border-gray-200 text-gray-600">
                🧋 Minuman
            </button>
        </div>
    </header>

    <div class="flex-1 px-4 pt-4 pb-32">

        <!-- Jumlah hasil -->
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs text-gray-400 font-medium" id="result-count">Memuat...</p>
        </div>

        <!-- Daftar menu -->
        <div id="menu-container" class="space-y-2"></div>

        <!-- Empty state -->
        <div id="empty-state" class="hidden flex-col items-center justify-center text-center py-16">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                <i class="ph ph-magnifying-glass text-2xl text-gray-400"></i>
            </div>
            <p class="font-bold text-gray-900">Tidak ditemukan</p>
            <p class="text-xs text-gray-500 mt-1">Coba kata kunci lain.</p>
        </div>

    </div>

    <!-- Floating cart -->
    <div id="floating-cart"
        class="fixed bottom-0 w-full max-w-[480px] left-1/2 -translate-x-1/2 z-40 transition-transform duration-300 ease-in-out translate-y-full">
        <div class="mx-4 mb-4 bg-sky-500 rounded-2xl shadow-xl px-4 py-3.5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <div class="w-10 h-10 bg-sky-400 rounded-xl flex items-center justify-center">
                        <i class="ph-fill ph-shopping-bag text-white text-xl"></i>
                    </div>
                    <span id="cart-badge"
                        class="absolute -top-1.5 -right-1.5 bg-yellow-400 text-gray-900 text-[10px] font-black w-5 h-5 rounded-full flex items-center justify-center border-2 border-sky-500">0</span>
                </div>
                <div>
                    <p class="text-[10px] text-sky-200 font-semibold uppercase tracking-wide">Total Pesanan</p>
                    <p id="cart-total" class="text-white font-black text-base leading-none">Rp 0</p>
                </div>
            </div>
            <button onclick="goToCheckout()"
                class="bg-white text-sky-600 font-bold text-sm px-5 py-2.5 rounded-xl shadow-sm hover:bg-sky-50 active:scale-95 transition-all">
                Pesan <span id="cart-qty-btn" class="font-black">(0)</span>
            </button>
        </div>
    </div>

    <!-- Backdrop -->
    <div id="modal-backdrop" class="fixed inset-0 bg-black/50 z-40 hidden opacity-0 transition-opacity duration-300" onclick="closeModal()"></div>

    <!-- Product Modal -->
    <div id="product-modal" class="fixed max-w-[480px] bottom-0 left-1/2 -translate-x-1/2 w-full bg-white rounded-t-3xl z-50 transform translate-y-full transition-transform duration-300 flex flex-col max-h-[90vh]">
        <!-- Drag handle -->
        <div class="flex justify-center pt-4 pb-2 cursor-pointer shrink-0" onclick="closeModal()">
            <div class="w-12 h-1.5 bg-gray-200 rounded-full"></div>
        </div>
        <!-- Scrollable content -->
        <div class="overflow-y-auto no-scrollbar pb-28">
            <div class="px-4 pb-4">
                <img id="modal-img" src="" alt=""
                    class="w-full h-52 object-cover rounded-2xl bg-gray-100 shadow-sm">
            </div>
            <div class="px-5">
                <!-- Badge kategori -->
                <span id="modal-cat-badge" class="inline-block text-[9px] font-black uppercase tracking-widest px-2.5 py-1 rounded-full mb-2"></span>
                <div class="flex justify-between items-start gap-3 mb-1.5">
                    <h2 id="modal-title" class="text-xl font-bold text-gray-900 leading-tight flex-1">Nama Produk</h2>
                    <span id="modal-price" class="text-lg font-black text-sky-500 whitespace-nowrap">Rp 0</span>
                </div>
                <p id="modal-desc" class="text-sm text-gray-500 leading-relaxed"></p>

                <!-- Catatan -->
                <div class="mt-5">
                    <label class="flex items-center gap-1.5 text-sm font-bold text-gray-800 mb-2">
                        <i class="ph ph-note-pencil text-base text-gray-400"></i>
                        Catatan Khusus
                        <span class="text-gray-400 font-normal text-xs">(opsional)</span>
                    </label>
                    <textarea id="modal-note" rows="2"
                        placeholder="Contoh: tanpa bawang, level pedas sedang, tidak pakai es..."
                        class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-sky-400 focus:border-transparent bg-gray-50 resize-none leading-relaxed transition-all"></textarea>
                </div>
            </div>
        </div>
        <!-- Footer aksi -->
        <div class="absolute bottom-0 left-0 w-full bg-white border-t border-gray-100 p-4 flex gap-3 items-center">
            <div class="flex items-center gap-1 bg-gray-100 border border-gray-200 p-1 rounded-xl shrink-0">
                <button onclick="changeModalQty(-1)"
                    class="w-9 h-9 rounded-lg flex items-center justify-center text-gray-500 hover:bg-white hover:text-red-500 hover:shadow-sm transition-all">
                    <i class="ph-bold ph-minus text-sm"></i>
                </button>
                <span id="modal-qty" class="font-black text-gray-800 min-w-[28px] text-center tabular-nums">1</span>
                <button onclick="changeModalQty(1)"
                    class="w-9 h-9 rounded-lg bg-sky-500 text-white flex items-center justify-center hover:bg-sky-600 transition-all">
                    <i class="ph-bold ph-plus text-sm"></i>
                </button>
            </div>
            <button onclick="addFromModal()"
                class="flex-1 bg-sky-500 hover:bg-sky-600 active:scale-[0.98] text-white rounded-xl py-3.5 font-bold text-sm flex items-center justify-center gap-2 transition-all shadow-sm">
                <i class="ph-bold ph-shopping-bag-open text-base"></i>
                <span id="modal-total-btn">Tambah — Rp 0</span>
            </button>
        </div>
    </div>

</main>

<script src="<?= BASE_URL ?>assets/js/helperUrl.js"></script>
<script>
    const BASE_URL = "<?= BASE_URL ?>";
    const TABLE_CODE = "<?= $table_code ?>";
    const MENU_DATA = <?php echo json_encode($result); ?>;

    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    let currentCategory = 'Semua';

    //  Format 
    function formatRupiah(n) {
        return 'Rp ' + parseInt(n).toLocaleString('id-ID');
    }

    //  Filter data 
    function filteredData() {
        const q = document.getElementById('search-input').value.toLowerCase().trim();
        return MENU_DATA.filter(item => {
            const matchCat = currentCategory === 'Semua' || String(item.category) === currentCategory;
            const matchQ = !q || item.name.toLowerCase().includes(q) || item.description.toLowerCase().includes(q);
            return matchCat && matchQ;
        });
    }

    //  Render menu list ─
    function renderMenus() {
        const data = filteredData();
        const container = document.getElementById('menu-container');
        const empty = document.getElementById('empty-state');
        const countEl = document.getElementById('result-count');

        container.innerHTML = '';

        if (!data.length) {
            empty.classList.remove('hidden');
            empty.classList.add('flex');
            countEl.innerText = '0 menu ditemukan';
            return;
        }

        empty.classList.add('hidden');
        empty.classList.remove('flex');
        countEl.innerText = `${data.length} menu ditemukan`;

        data.forEach(item => {
            const inCart = cart.find(c => c.id == item.id);
            const catLabel = item.category == '1' ? 'Makanan' : (item.category == '2' ? 'Minuman' : '—');
            const catColor = item.category == '1' ?
                'bg-orange-50 text-orange-500' :
                'bg-sky-50 text-sky-500';

            const card = document.createElement('div');
            card.className = 'flex gap-3 p-3.5 bg-white rounded-2xl border border-gray-100 shadow-sm items-center cursor-pointer active:bg-gray-50 transition-colors';
            card.onclick = (e) => {
                if (!e.target.closest('[id^="ctrl-"]') && !e.target.closest('button')) openModal(item.id);
            };
            card.innerHTML = `
                <img src="${getImageUrl(item.image)}" alt="${item.name}"
                    class="w-[72px] h-[72px] rounded-xl object-cover flex-shrink-0 border border-gray-100">
                <div class="flex-1 min-w-0">
                    <span class="inline-block text-[9px] font-black uppercase tracking-widest px-2 py-0.5 rounded-full mb-1 ${catColor}">${catLabel}</span>
                    <h3 class="font-bold text-gray-900 text-sm truncate leading-tight">${item.name}</h3>
                    <p class="text-[11px] text-gray-400 line-clamp-1 mt-0.5">${item.description}</p>
                    <p class="font-black text-sky-500 text-sm mt-1">${formatRupiah(item.price)}</p>
                </div>
                <div class="shrink-0" id="ctrl-${item.id}">
                    ${inCart ? qtyCtrlHTML(item.id, inCart.qty) : addBtnHTML(item.id)}
                </div>`;
            container.appendChild(card);
        });
    }

    function addBtnHTML(id) {
        return `
        <button onclick="addToCart('${id}')"
            class="group flex items-center gap-1.5 bg-sky-500 hover:bg-sky-600 active:scale-95 text-white text-xs font-bold px-3.5 py-2 rounded-xl shadow-sm transition-all duration-150">
            <i class="ph-bold ph-plus text-xs"></i>
            <span>Tambah</span>
        </button>`;
    }

    function qtyCtrlHTML(id, qty) {
        return `
        <div class="flex items-center gap-2 bg-gray-100 rounded-xl p-1">
            <button onclick="changeQty('${id}', -1)"
                class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-500 hover:bg-white hover:text-red-500 hover:shadow-sm active:scale-90 transition-all duration-150">
                <i class="ph-bold ph-minus text-xs"></i>
            </button>
            <span class="text-sm font-black text-gray-800 min-w-[18px] text-center tabular-nums">${qty}</span>
            <button onclick="changeQty('${id}', 1)"
                class="w-8 h-8 rounded-lg bg-sky-500 text-white flex items-center justify-center hover:bg-sky-600 hover:shadow-sm active:scale-90 transition-all duration-150">
                <i class="ph-bold ph-plus text-xs"></i>
            </button>
        </div>`;
    }

    //  Cart actions 
    function addToCart(itemId) {
        const item = MENU_DATA.find(i => i.id == itemId);
        if (!item) return;
        const idx = cart.findIndex(i => i.id == itemId);
        if (idx !== -1) {
            cart[idx].qty++;
        } else {
            cart.push({
                id: item.id,
                name: item.name,
                price: item.price,
                qty: 1,
                note: '',
                image: item.image
            });
        }
        saveAndRefresh(itemId);
    }

    function changeQty(itemId, delta) {
        const idx = cart.findIndex(i => i.id == itemId);
        if (idx === -1) return;
        cart[idx].qty += delta;
        if (cart[idx].qty <= 0) cart.splice(idx, 1);
        saveAndRefresh(itemId);
    }

    function saveAndRefresh(itemId) {
        localStorage.setItem('cart', JSON.stringify(cart));
        // Update hanya kontrol item yang berubah (tidak re-render semua)
        const inCart = cart.find(c => c.id == itemId);
        const el = document.getElementById(`ctrl-${itemId}`);
        if (el) el.innerHTML = inCart ? qtyCtrlHTML(itemId, inCart.qty) : addBtnHTML(itemId);
        updateCartUI();
    }

    function updateCartUI() {
        const fc = document.getElementById('floating-cart');
        const total = cart.reduce((s, i) => s + i.qty, 0);
        if (total > 0) {
            let price = 0;
            cart.forEach(ci => {
                const m = MENU_DATA.find(x => x.id == ci.id);
                if (m) price += m.price * ci.qty;
            });
            document.getElementById('cart-badge').innerText = total;
            document.getElementById('cart-qty-btn').innerText = `(${total})`;
            document.getElementById('cart-total').innerText = formatRupiah(price);
            fc.classList.remove('translate-y-full');
        } else {
            fc.classList.add('translate-y-full');
        }
    }

    function goToCheckout() {
        if (!cart.length) return;
        window.location.href = `${BASE_URL}order/${TABLE_CODE}/pesanan`;
    }

    //  Search & filter 
    const searchInput = document.getElementById('search-input');
    const clearBtn = document.getElementById('clear-btn');

    searchInput.addEventListener('input', () => {
        clearBtn.classList.toggle('hidden', searchInput.value === '');
        renderMenus();
    });

    function clearSearch() {
        searchInput.value = '';
        clearBtn.classList.add('hidden');
        searchInput.focus();
        renderMenus();
    }

    function setCategory(cat) {
        currentCategory = cat;
        document.querySelectorAll('.cat-btn').forEach(btn => {
            const active = btn.dataset.cat === cat;
            btn.className = `cat-btn px-4 py-1.5 rounded-full text-xs font-bold whitespace-nowrap transition-all ${
                active ? 'bg-sky-500 text-white' : 'bg-white border border-gray-200 text-gray-600'
            }`;
        });
        renderMenus();
    }

    //  Modal 
    let currentModalItem = null;
    let modalQty = 1;

    function openModal(itemId) {
        const item = MENU_DATA.find(i => i.id == itemId);
        if (!item) return;
        currentModalItem = item;

        const existing = cart.find(c => c.id == itemId);
        modalQty = existing ? existing.qty : 1;
        document.getElementById('modal-note').value = existing?.note || '';

        // Isi konten modal
        document.getElementById('modal-img').src = getImageUrl(item.image);
        document.getElementById('modal-title').innerText = item.name;
        document.getElementById('modal-desc').innerText = item.description;
        document.getElementById('modal-price').innerText = formatRupiah(item.price);

        // Badge kategori
        const badge = document.getElementById('modal-cat-badge');
        const isMakanan = String(item.category) === '1';
        badge.innerText = isMakanan ? 'Makanan' : 'Minuman';
        badge.className = `inline-block text-[9px] font-black uppercase tracking-widest px-2.5 py-1 rounded-full mb-2 ${
            isMakanan ? 'bg-orange-50 text-orange-500' : 'bg-sky-50 text-sky-500'
        }`;

        updateModalUI();

        // Tampilkan modal
        const backdrop = document.getElementById('modal-backdrop');
        const modal = document.getElementById('product-modal');
        backdrop.classList.remove('hidden');
        requestAnimationFrame(() => {
            backdrop.classList.add('opacity-100');
            modal.classList.remove('translate-y-full');
        });
    }

    function closeModal() {
        const backdrop = document.getElementById('modal-backdrop');
        const modal = document.getElementById('product-modal');
        modal.classList.add('translate-y-full');
        backdrop.classList.remove('opacity-100');
        setTimeout(() => backdrop.classList.add('hidden'), 300);
        currentModalItem = null;
    }

    function changeModalQty(d) {
        modalQty = Math.max(1, modalQty + d);
        updateModalUI();
    }

    function updateModalUI() {
        document.getElementById('modal-qty').innerText = modalQty;
        document.getElementById('modal-total-btn').innerText = `Tambah — ${formatRupiah(currentModalItem.price * modalQty)}`;
    }

    function addFromModal() {
        if (!currentModalItem) return;
        const note = document.getElementById('modal-note').value.trim();
        const idx = cart.findIndex(i => i.id == currentModalItem.id);
        if (idx !== -1) {
            cart[idx].qty = modalQty;
            cart[idx].note = note;
        } else {
            cart.push({
                id: currentModalItem.id,
                name: currentModalItem.name,
                price: currentModalItem.price,
                qty: modalQty,
                note,
                image: currentModalItem.image
            });
        }
        localStorage.setItem('cart', JSON.stringify(cart));
        const id = currentModalItem.id;
        closeModal();
        // Refresh kontrol qty di kartu yang bersangkutan
        setTimeout(() => {
            const inCart = cart.find(c => c.id == id);
            const el = document.getElementById(`ctrl-${id}`);
            if (el) el.innerHTML = inCart ? qtyCtrlHTML(id, inCart.qty) : addBtnHTML(id);
            updateCartUI();
        }, 310);
    }

    // Tutup modal dengan swipe/escape
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeModal();
    });

    //  Init 
    document.addEventListener('DOMContentLoaded', () => {
        // Tampilkan tombol clear jika ada keyword dari URL
        if (searchInput.value) clearBtn.classList.remove('hidden');
        renderMenus();
        updateCartUI();
    });
</script>

<?php include __DIR__ . '/layout/footer.php'; ?>