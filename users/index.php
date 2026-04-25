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
    header("Location: table");
    exit(); 
}

$table_code = htmlspecialchars($_GET['code']);

$query = "SELECT 1 FROM `table` WHERE name = ? LIMIT 1";
$stmt = $pdo->prepare($query);
$stmt->execute([$table_code]);

$exists = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$exists) {
    header("Location: table");
    exit();
} 

?>

<?php 
  $title = "Beranda"; 
  include 'layout/header.php'; 
?>

    <main class="w-full max-w-md mx-auto bg-gray-50 min-h-screen relative shadow-2xl flex flex-col overflow-x-hidden">
        <header class="sticky top-0 z-20 bg-white shadow-sm pt-5 pb-3 px-4 rounded-b-2xl">
            <div class="flex justify-between items-center mb-5">
                <a href="profile?code=<?=$table_code ?>" class="text-gray-800 hover:text-sky-500 transition-colors">
                    <i class="ph ph-list text-2xl"></i>
                </a>
                <a href="search?code=<?= $table_code ?>" class="text-gray-800 hover:text-sky-500 transition-colors">
                    <i class="ph ph-magnifying-glass text-2xl"></i>
                </a>
            </div>
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Träffa Coffee & Eatery</h1>
                    <p class="text-xs text-gray-500 mt-0.5">Pesan langsung dari mejamu 🍽️</p>
                </div>
                <div class="bg-sky-500 text-white px-3 py-1.5 rounded-xl flex flex-col items-center justify-center shadow-md border-[3px] border-sky-100 shrink-0 min-w-15">
                    <span class="text-[9px] font-bold uppercase tracking-widest opacity-90 -mb-0.75">Meja</span>
                    <span class="text-xl font-black tracking-wider"><?=  $table_code ?></span>
                </div>
            </div>
            <div class="flex overflow-x-auto gap-2.5 no-scrollbar pb-1" id="category-container"></div>
        </header>

        <div class="flex-1 px-4 py-5 space-y-4 pb-28" id="menu-container"></div>

        <div class="fixed bottom-6 w-full max-w-120 left-1/2 -translate-x-1/2 px-5 z-30 flex justify-end pointer-events-none">
            <button onclick="goToCheckout()" class="pointer-events-auto bg-sky-500 hover:bg-sky-600 text-white w-15 h-15 rounded-full shadow-xl flex items-center justify-center relative transition-transform active:scale-90 shrink-0 border-4 border-white">
                <i class="ph ph-shopping-cart text-2xl"></i>
                <span id="cart-badge" class="absolute -top-1 -right-1 bg-red-500 text-white text-[11px] font-bold w-6 h-6 rounded-full flex items-center justify-center border-2 border-white shadow-sm ">0</span>
            </button>
        </div>

        <div id="toast" class="fixed bottom-24 left-1/2 -translate-x-1/2 bg-gray-900 text-white px-4 py-2 rounded-full text-sm font-medium shadow-lg z-50 hidden toast-animate items-center gap-2">
            <i class="ph-fill ph-check-circle text-green-400 text-lg"></i>
            <span id="toast-msg">Ditambahkan ke keranjang</span>
        </div>

        <div id="modal-backdrop" class="fixed inset-0 bg-black/50 z-40 hidden opacity-0 transition-opacity duration-300" onclick="closeModal()"></div>

        <div id="product-modal" class="fixed max-w-md bottom-0 left-1/2 -translate-x-1/2 w-full  bg-white rounded-t-3xl z-50 transform translate-y-full transition-transform duration-300 flex flex-col max-h-[90vh]">
            <div class="flex justify-center pt-4 pb-2 cursor-pointer" onclick="closeModal()">
                <div class="w-12 h-1.5 bg-gray-300 rounded-full"></div>
            </div>
            <div class="overflow-y-auto no-scrollbar pb-24">
                <div class="px-4 pb-4">
                    <img id="modal-img" src="" alt="" class="w-full h-56 object-cover rounded-2xl bg-gray-100 shadow-sm">
                </div>
                <div class="px-5">
                    <div class="flex justify-between items-start gap-4 mb-2">
                        <h2 id="modal-title" class="text-xl font-bold text-gray-900 leading-tight">Nama Produk</h2>
                        <span id="modal-price" class="text-lg font-black text-sky-500 whitespace-nowrap">Rp 0</span>
                    </div>
                    <p id="modal-desc" class="text-sm text-gray-500 leading-relaxed">Deskripsi produk...</p>
                    <div class="mt-6">
                        <label class="block text-sm font-bold text-gray-800 mb-2 items-center gap-1.5">
                            <i class="ph ph-note-pencil text-lg text-gray-500"></i> Catatan Khusus
                        </label>
                        <textarea id="modal-note" rows="2" placeholder="Contoh: Esnya sedikit..." class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 bg-gray-50"></textarea>
                    </div>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 w-full bg-white border-t border-gray-100 p-4 flex gap-4 items-center rounded-t-2xl shadow-lg">
                <div class="flex items-center gap-3 bg-gray-50 border border-gray-200 p-1.5 rounded-xl">
                    <button onclick="changeModalQty(-1)" class="w-9 h-9 rounded-lg flex items-center justify-center text-gray-600 hover:bg-white"><i class="ph-bold ph-minus"></i></button>
                    <span id="modal-qty" class="font-bold text-gray-800 min-w-6 text-center">1</span>
                    <button onclick="changeModalQty(1)" class="w-9 h-9 rounded-lg flex items-center justify-center text-sky-600 hover:bg-white"><i class="ph-bold ph-plus"></i></button>
                </div>
                <button onclick="addFromModalToCart()" class="flex-1 bg-sky-500 hover:bg-sky-600 text-white rounded-xl py-3.5 font-bold text-sm flex items-center justify-center gap-2">
                    <span id="modal-total-btn">Rp 0</span>
                </button>
            </div>
        </div>
    </main>

<script>
    // Konversi data PHP ke JSON
    const MENU_DATA = <?php echo json_encode($result); ?>;

    // Fungsi Keranjang Local Storage
    function getCart() {
        return JSON.parse(localStorage.getItem('cart')) || [];
    }
    function saveCart(cartData) {
        localStorage.setItem('cart', JSON.stringify(cartData));
    }

    function formatRupiah(angka) {
        return 'Rp ' + parseInt(angka).toLocaleString('id-ID');
    }

    const categories = [
        { id: "Semua", label: "Semua" },
        { id: "1", label: "Makanan" }, // Menggunakan string untuk sinkronisasi DB
        { id: "2", label: "Minuman" }
    ];

    let currentCategory = "Semua";
    let cart = getCart();
    let currentModalItem = null;
    let modalQty = 1;

    function renderCategories() {
        const container = document.getElementById('category-container');
        container.innerHTML = '';
        categories.forEach(cat => {
            const isActive = cat.id === currentCategory;
            const btn = document.createElement('button');
            btn.className = `px-5 py-2 rounded-full whitespace-nowrap text-sm transition-all ${isActive ? 'bg-sky-500 text-white' : 'bg-white border text-gray-600'}`;
            btn.innerText = cat.label; 
            btn.onclick = () => { currentCategory = cat.id; renderCategories(); renderMenus(); };
            container.appendChild(btn);
        });
    }

    function renderMenus() {
        const container = document.getElementById('menu-container');
        container.innerHTML = '';
        
        const filtered = currentCategory === "Semua" 
            ? MENU_DATA 
            : MENU_DATA.filter(i => String(i.category) === currentCategory);

        filtered.forEach(item => {
            const totalInCart = cart.filter(c => c.id == item.id).reduce((s, c) => s + c.qty, 0);
            
            const card = document.createElement('div');
            card.className = "menu-card flex bg-white p-3.5 rounded-2xl shadow-sm gap-4 items-center border border-gray-100 cursor-pointer";
            card.onclick = () => openModal(item.id);

            card.innerHTML = `
                <img src="${item.image}" class="w-24 h-24 object-cover rounded-xl">
                <div class="flex-1 min-w-0">
                    <h3 class="font-bold text-gray-900 truncate">${item.name}</h3>
                    <p class="text-[11px] text-gray-500 line-clamp-2">${item.description}</p>
                    <div class="flex justify-between items-center mt-2">
                        <span class="font-bold text-sky-600">${formatRupiah(item.price)}</span>
                        ${totalInCart > 0 ? `
                            <div class="flex items-center gap-2">
                                <button onclick="event.stopPropagation(); changeQty('${item.id}',-1)" class="w-7 h-7 rounded-full bg-gray-100 text-gray-600">-</button>
                                <span class="text-sm font-bold">${totalInCart}</span>
                                <button onclick="event.stopPropagation(); changeQty('${item.id}',1)" class="w-7 h-7 rounded-full bg-sky-500 text-white">+</button>
                            </div>
                        ` : `
                            <button class="w-8 h-8 rounded-full bg-sky-50 text-sky-600 border border-sky-200 flex items-center justify-center">
                                <i class="ph-bold ph-plus"></i>
                            </button>
                        `}
                    </div>
                </div>`;
            container.appendChild(card);
        });
    }

    // Modal Logic
    function openModal(itemId) {
        const item = MENU_DATA.find(i => i.id == itemId);
        if (!item) return;
        currentModalItem = item;
        
        // cek keranjang Apakah item ini sudah pernah ditambahkan?
        const existingItem = cart.find(c => c.id == itemId);
        
        if (existingItem) {
            // Jika sudah ada, gunakan quantity dan catatan dari keranjang
            modalQty = existingItem.qty;
            document.getElementById('modal-note').value = existingItem.note || "";
        } else {
            // Jika belum ada di keranjang, kembalikan ke default
            modalQty = 1;
            document.getElementById('modal-note').value = "";
        }

        document.getElementById('modal-img').src = `${item.image}`;
        document.getElementById('modal-title').innerText = item.name;
        document.getElementById('modal-desc').innerText = item.description;
        document.getElementById('modal-price').innerText = formatRupiah(item.price);
        
        updateModalUI();
        
        document.getElementById('modal-backdrop').classList.remove('hidden');
        setTimeout(() => {
            document.getElementById('modal-backdrop').classList.add('opacity-100');
            document.getElementById('product-modal').classList.remove('translate-y-full');
        }, 10);
    }

    function closeModal() {
        document.getElementById('product-modal').classList.add('translate-y-full');
        document.getElementById('modal-backdrop').classList.remove('opacity-100');
        setTimeout(() => document.getElementById('modal-backdrop').classList.add('hidden'), 300);
    }

    function changeModalQty(d) {
        modalQty = Math.max(1, modalQty + d);
        updateModalUI();
    }

    function updateModalUI() {
        document.getElementById('modal-qty').innerText = modalQty;
        document.getElementById('modal-total-btn').innerText = formatRupiah(currentModalItem.price * modalQty);
    }

    function addFromModalToCart() {
        const note = document.getElementById('modal-note').value.trim();
        
        // hanya cek berdasarkan id
        const existingIndex = cart.findIndex(i => i.id == currentModalItem.id);
        
        if (existingIndex !== -1) {
            // Jika sudah ada, timpa quantity dan catatannya dengan yang baru dari modal
            cart[existingIndex].qty = modalQty; 
            cart[existingIndex].note = note;
        } else {
            // Jika belum ada, buat sebagai item baru di keranjang
            cart.push({ 
                id: currentModalItem.id, 
                name: currentModalItem.name, 
                price: currentModalItem.price, 
                qty: modalQty, 
                note: note, 
                image: currentModalItem.image 
            });
        }
        
        syncCart();
        closeModal();
        showToast("Keranjang diperbarui");
    }

    function changeQty(id, d) {
        const idx = cart.findIndex(i => i.id == id);
        if (idx !== -1) {
            cart[idx].qty += d;
            if (cart[idx].qty <= 0) cart.splice(idx, 1);
            syncCart();
        }
    }

    function syncCart() {
        saveCart(cart);
        updateCartBadge();
        renderMenus();
    }

    function updateCartBadge() {
        const total = cart.reduce((s, i) => s + i.qty, 0);
        const badge = document.getElementById('cart-badge');
        badge.innerText = total;
        total > 0 ? badge.classList.remove('hidden') : badge.classList.add('hidden');
    }

    function showToast(msg) {
        const t = document.getElementById('toast');
        document.getElementById('toast-msg').innerText = msg;
        t.classList.remove('hidden');
        setTimeout(() => t.classList.add('hidden'), 2000);
    }

    function goToCheckout() {
        if (cart.length === 0) return showToast("Keranjang kosong!");
        window.location.href = 'pesanan?code=<?= $table_code ?>';
    }

    window.onload = () => { renderCategories(); renderMenus(); updateCartBadge(); };
</script>


<?php include 'layout/footer.php'; ?>