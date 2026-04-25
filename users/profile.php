<?php 
include '../config.php';

// Aktifkan error reporting untuk debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

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
    header("Location: table.php");
    exit();
} 
?>

<?php 
  $title = "Profile"; 
  include 'layout/header.php'; 
?>

<main class="w-full max-w-[480px] mx-auto bg-gray-50 min-h-screen relative shadow-2xl flex flex-col overflow-x-hidden">

    <header class="sticky top-0 z-20 bg-white border-b border-gray-100 px-4 py-4 flex items-center justify-between">
       
        <a href="index?code=<?= $table_code ?>" class="text-gray-600 hover:text-sky-500 transition-colors p-1 -ml-1">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <h1 class="text-base font-bold text-gray-900 absolute left-1/2 -translate-x-1/2">Pengaturan</h1>
        <div class="w-6"></div>
    </header>

    <div class="fade-in bg-gradient-to-br from-sky-500 to-sky-600 mx-4 mt-6 rounded-2xl p-5 text-white shadow-lg shadow-sky-200">
        <p class="text-xs font-bold uppercase tracking-widest opacity-75 mb-1">Sedang Makan di</p>
        <h2 class="text-xl font-black">Träffa Coffee & Eatery</h2>
        <div class="flex items-center gap-2 mt-3">
            <div class="bg-white/20 backdrop-blur-sm px-3 py-1.5 rounded-lg flex items-center gap-1.5 border border-white/30">
                <span class="text-xs font-bold opacity-80">Meja</span>
                <span class="font-black text-lg"><?= $table_code ?></span>
            </div>
            <span class="text-xs opacity-70">• Kudus, Jawa Tengah</span>
        </div>
    </div>

    <div class="px-4 pt-5 space-y-3 pb-8 fade-in">

    

        <!-- Clear Cart -->
        <button onclick="clearCart()" class="w-full bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex items-center justify-between hover:border-red-200 group transition-all">
            <div class="flex items-center gap-4 text-gray-700 group-hover:text-red-500 transition-colors">
                <div class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center group-hover:bg-red-50 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                    </svg>
                </div>
                <span class="font-semibold text-sm">Kosongkan Keranjang</span>
            </div>
        </button>

    </div>

    <footer class="mt-auto py-8 flex flex-col items-center justify-center gap-1">
        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-widest">Träffa Coffee & Eatery</p>
        <p class="text-[10px] text-gray-300">v1.0.0 • Powered by Anak Magang</p>
    </footer>


</main>
<script>
  
    function clearCart() {
        Swal.fire({
            title: "Apakah kamu yakin?",
            text: "Keranjang pesanan akan di kosongkan!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Ya, hapus!"
        }).then((result) => {
        if (result.isConfirmed)
            Swal.fire({
                title: "Terhapus!",
                text: "Pesanan telah dihapus.",
                icon: "success"
            });
            localStorage.removeItem('cart');
        });    
    }
    let toastT;
    function showToast(msg) {
        const t = document.getElementById('toast');
        document.getElementById('toast-msg').innerText = msg;
        t.classList.remove('hidden');
        clearTimeout(toastT);
        toastT = setTimeout(() => t.classList.add('hidden'), 2500);
    }
</script>


<?php include 'layout/footer.php'; ?>
