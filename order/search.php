<?php
include '../config.php';
include '../path.php';

try {
    // Ambil data menu dari database
    $query = "SELECT * FROM menu ORDER BY created_at ASC";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error pada query: " . $e->getMessage());
}

if (!isset($_GET['code']) || empty($_GET['code'])) {
    // Redirect jika tidak ada kode meja
    header("Location: table");
    exit();
}

$table_code = htmlspecialchars($_GET['code']);

$query = "SELECT 1 FROM `table` WHERE name = ? LIMIT 1";
$stmt = $pdo->prepare($query);
$stmt->execute([$table_code]);

$exists = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$exists) {
    header("Location: table.php");
    exit();
}

?>

<?php
$title = "Cari Menu";
include 'layout/header.php';
?>

<style>
    /* Sembunyikan scrollbar bawaan */
    ::-webkit-scrollbar {
        display: none;
    }

    /* Animasi cart floating */
    .cart-visible {
        transform: translateY(0);
    }

    .cart-hidden {
        transform: translateY(100%);
    }
</style>

<main class="w-full max-w-[480px] mx-auto bg-gray-50 min-h-screen relative shadow-2xl flex flex-col overflow-x-hidden">

    <header class="sticky top-0 z-30 bg-white shadow-sm px-4 py-3 flex items-center gap-3">
        <a href='index.php?code=<?= $table_code ?>'" class=" text-gray-700 hover:text-sky-500 transition-colors shrink-0">
            <i class="ph-bold ph-arrow-left text-xl"></i>
        </a>
        <div class="relative w-full">
            <i class="ph-bold ph-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-lg"></i>
            <input type="text" id="search-input" placeholder="Cari menu favoritmu..."
                class="w-full bg-gray-100 border-none text-sm text-gray-900 rounded-full pl-10 pr-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-sky-300 transition-all placeholder-gray-400 font-medium" autofocus>
        </div>
    </header>

    <div class="flex-1 px-4 py-6 pb-32">
        <div class="mb-5">
            <h2 class="text-[19px] font-extrabold text-gray-900 tracking-tight">Semua Menu</h2>
            <p class="text-xs text-gray-400 mt-0.5" id="result-count"></p>
        </div>
        <div id="menu-container" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden"></div>
    </div>

    <div id="floating-cart" class="fixed bottom-0 w-full max-w-[480px] left-1/2 -translate-x-1/2 bg-sky-500 text-white rounded-t-[28px] shadow-[0_-8px_20px_rgba(14,165,233,0.25)] z-40 transition-transform duration-300 ease-in-out cart-hidden">
        <div class="px-5 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <i class="ph-fill ph-shopping-bag text-3xl"></i>
                    <span id="cart-badge-icon" class="absolute -top-1 -right-1 bg-yellow-400 text-gray-900 text-[10px] font-bold w-5 h-5 rounded-full flex items-center justify-center border-2 border-sky-500">0</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-[11px] font-medium text-white/80 uppercase tracking-wide">Total</span>
                    <span id="cart-total-price" class="font-bold text-lg leading-none">Rp 0</span>
                </div>
            </div>
            <button onclick="goToCheckout()" class="bg-white text-sky-500 font-bold text-sm px-5 py-2.5 rounded-full shadow-sm hover:bg-gray-50 active:scale-95 transition-all flex items-center gap-1.5">
                Pesan (<span id="cart-btn-qty">0</span>)
            </button>
        </div>
    </div>

</main>

<script src="<?= BASE_URL ?>assets/js/helperUrl.js"></script>
<script>
    // Tarik data dari PHP ke JS
    const BASE_URL = "<?= BASE_URL ?>";
    const MENU_DATA = <?php echo json_encode($result); ?>;

    // Helper Keranjang 
    function getCart() {
        return JSON.parse(localStorage.getItem('cart')) || [];
    }

    function saveCart(cartData) {
        localStorage.setItem('cart', JSON.stringify(cartData));
    }

    function formatRupiah(angka) {
        return 'Rp ' + parseInt(angka).toLocaleString('id-ID');
    }

    function cartCount() {
        return cart.reduce((s, i) => s + (i.qty || 1), 0);
    }

    let cart = getCart();

    function renderMenus(data) {
        const container = document.getElementById('menu-container');
        const countEl = document.getElementById('result-count');
        container.innerHTML = '';
        countEl.innerText = `${data.length} menu ditemukan`;

        if (!data.length) {
            container.innerHTML = `
                <div class="py-12 flex flex-col items-center justify-center text-center px-4">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                        <i class="ph ph-magnifying-glass text-2xl text-gray-400"></i>
                    </div>
                    <p class="font-bold text-gray-900">Menu tidak ditemukan</p>
                    <p class="text-xs text-gray-500 mt-1">Coba cari dengan kata kunci lain.</p>
                </div>`;
            return;
        }

        data.forEach((item, index) => {
            const isLast = index === data.length - 1;
            const inCart = cart.find(c => c.id == item.id);

            // Konversi ID Kategori ke Label (sesuai index.php)
            const catLabel = item.category == '1' ? 'Makanan' : (item.category == '2' ? 'Minuman' : item.category);

            const card = document.createElement('div');
            card.className = `flex gap-4 p-4 items-center ${isLast ? '' : 'border-b border-gray-100'} hover:bg-gray-50 transition-colors`;
            card.innerHTML = `
                <div class="w-20 h-20 rounded-xl overflow-hidden shrink-0 shadow-sm border border-gray-100">
                    <img src="${getImageUrl(item.image)}" alt="${item.name}" class="w-full h-full object-cover">
                </div>
                <div class="flex-1 min-w-0 py-0.5">
                    <span class="text-[9px] font-bold uppercase tracking-widest px-2 py-0.5 rounded-full bg-blue-50 text-blue-500">${catLabel}</span>
                    <h3 class="font-bold text-gray-900 text-sm mt-1 truncate">${item.name}</h3>
                    <p class="text-[11px] text-gray-500 mt-0.5 line-clamp-1 leading-relaxed">${item.description}</p>
                    <p class="font-bold text-sky-600 text-[13px] mt-1">${formatRupiah(item.price)}</p>
                </div>
                <div class="shrink-0">
                    ${inCart ? `
                    <div class="flex flex-col items-center gap-1.5">
                        <button onclick="changeQty('${item.id}',1)" class="w-7 h-7 rounded-full bg-sky-500 text-white flex items-center justify-center active:scale-90 transition-all"><i class="ph-bold ph-plus text-xs"></i></button>
                        <span class="text-sm font-bold text-gray-800">${inCart.qty}</span>
                        <button onclick="changeQty('${item.id}',-1)" class="w-7 h-7 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center active:scale-90 transition-all"><i class="ph-bold ph-minus text-xs"></i></button>
                    </div>` : `
                    <button onclick="addToCart('${item.id}')" class="px-4 py-1.5 rounded-full border-2 border-sky-500 text-sky-500 text-[11px] font-bold hover:bg-sky-500 hover:text-white transition-colors active:scale-95">
                        ADD
                    </button>`}
                </div>`;
            container.appendChild(card);
        });
    }

    function addToCart(itemId) {
        const e = cart.find(i => i.id == itemId);
        if (e) {
            e.qty += 1;
        } else {
            // Mempertahankan struktur yang sama dengan index.php
            const item = MENU_DATA.find(i => i.id == itemId);
            cart.push({
                id: item.id,
                name: item.name,
                price: item.price,
                qty: 1,
                note: "",
                image: item.image
            });
        }
        saveCart(cart);
        updateCartUI();
        renderMenus(currentFiltered());
    }

    function changeQty(itemId, delta) {
        const idx = cart.findIndex(i => i.id == itemId);
        if (idx === -1) return;
        cart[idx].qty += delta;
        if (cart[idx].qty <= 0) cart.splice(idx, 1);
        saveCart(cart);
        updateCartUI();
        renderMenus(currentFiltered());
    }

    function currentFiltered() {
        const q = document.getElementById('search-input').value.toLowerCase();
        return q ? MENU_DATA.filter(i => i.name.toLowerCase().includes(q) || i.description.toLowerCase().includes(q)) : MENU_DATA;
    }

    function updateCartUI() {
        const fc = document.getElementById('floating-cart');
        const total = cartCount();

        if (total > 0) {
            let price = 0;
            cart.forEach(ci => {
                const m = MENU_DATA.find(x => x.id == ci.id);
                if (m) price += m.price * ci.qty;
            });
            document.getElementById('cart-badge-icon').innerText = total;
            document.getElementById('cart-btn-qty').innerText = total;
            document.getElementById('cart-total-price').innerText = formatRupiah(price);

            fc.classList.remove('cart-hidden');
            fc.classList.add('cart-visible');
        } else {
            fc.classList.remove('cart-visible');
            fc.classList.add('cart-hidden');
        }
    }

    function goToCheckout() {
        if (!cart.length) return;
        // Bawa kode meja ke halaman checkout
        window.location.href = 'pesanan?code=<?= $table_code ?>';
    }

    // Trigger pencarian saat mengetik
    document.getElementById('search-input').addEventListener('input', () => renderMenus(currentFiltered()));

    // Load inisial page
    document.addEventListener('DOMContentLoaded', () => {
        cart = getCart();
        renderMenus(MENU_DATA);
        updateCartUI();
    });
</script>

<?php include 'layout/footer.php'; ?>