<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../path.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 1) {
    header("Location: " . BASE_URL . "auth/login");
    exit;
}

$pageTitle   = "Dashboard Kasir";
$currentPage = "dashboard";
include __DIR__ . '/../layout/header.php';
?>

<main class="min-h-screen p-4 sm:p-6 lg:p-8">
    <div class="rounded-2xl border border-gray-100 bg-white p-6 sm:p-10 shadow-sm text-center max-w-lg mx-auto mt-10">

        <div class="mb-8">
            <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-4 text-blue-600">
                <i class="fa-solid fa-store text-2xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-800">Proses Pesanan</h2>
            <p class="text-sm text-gray-500 mt-2">
                Masukkan kode pesanan secara manual atau scan QR Code dari perangkat pelanggan.
            </p>
        </div>

        <form id="orderForm" action="proses_input.php" method="POST" class="flex flex-col gap-4">
            <div class="relative w-full">
                <input type="text" name="code" id="order_code"
                    placeholder="Contoh: ORD-12345678" required
                    class="w-full text-center tracking-widest bg-white border border-gray-300 text-gray-900 rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-4 pr-16 outline-none font-bold transition-all shadow-sm" />
                <button type="button" id="btn-scan"
                    class="absolute inset-y-0 right-0 flex items-center justify-center px-4 bg-gray-50 text-gray-500 border border-l-0 border-gray-300 rounded-r-xl hover:bg-gray-100 hover:text-blue-600 transition-colors group">
                    <i class="fa-solid fa-qrcode text-xl group-hover:scale-110 transition-transform"></i>
                </button>
            </div>
            <button type="submit"
                class="w-full text-white bg-blue-600 hover:bg-blue-700 font-bold rounded-xl text-sm px-5 py-3.5 flex items-center justify-center gap-2 transition-colors shadow-sm">
                <i class="fa-solid fa-keyboard"></i> Proses Pesanan
            </button>
        </form>

    </div>
</main>

<!-- Modal Scanner QR -->
<div id="scannerModal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/60 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl w-full max-w-md overflow-hidden shadow-2xl">
        <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="font-bold text-gray-800 flex items-center gap-2">
                <i class="fa-solid fa-camera text-blue-500"></i> Scan QR Code
            </h3>
            <button type="button" id="close-scan"
                class="text-gray-400 hover:text-red-500 bg-white rounded-full w-8 h-8 flex items-center justify-center shadow-sm transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <div class="p-5">
            <div id="reader" class="w-full overflow-hidden rounded-xl border-2 border-dashed border-blue-200"></div>
            <p class="text-xs text-center text-gray-500 mt-4">Posisikan QR Code di dalam area kotak</p>
        </div>
    </div>
</div>

<style>
    #reader video { width: 100% !important; border-radius: .5rem; object-fit: cover; }
</style>

<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    const html5QrCode    = new Html5Qrcode("reader");
    const scannerModal   = document.getElementById('scannerModal');
    const orderForm      = document.getElementById('orderForm');
    const orderCodeInput = document.getElementById('order_code');

    document.getElementById('btn-scan').addEventListener('click', () => {
        scannerModal.classList.remove('hidden');
        scannerModal.classList.add('flex');

        html5QrCode.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: { width: 250, height: 250 } },
            (decodedText) => {
                html5QrCode.stop().then(() => {
                    scannerModal.classList.add('hidden');
                    scannerModal.classList.remove('flex');
                    orderCodeInput.value = decodedText;
                    orderForm.dispatchEvent(new Event('submit'));
                }).catch(console.error);
            },
            () => {}
        ).catch(err => {
            Swal.fire({ icon: 'error', title: 'Akses Ditolak',
                text: 'Gagal mengakses kamera. Pastikan izin kamera diberikan.',
                confirmButtonColor: '#3b82f6' });
            scannerModal.classList.add('hidden');
            scannerModal.classList.remove('flex');
        });
    });

    document.getElementById('close-scan').addEventListener('click', () => {
        html5QrCode.stop().catch(() => {}).finally(() => {
            scannerModal.classList.add('hidden');
            scannerModal.classList.remove('flex');
        });
    });

    orderForm.addEventListener('submit', function(e) {
        e.preventDefault();
        fetch('proses_input.php', {
            method: 'POST',
            body: new URLSearchParams(new FormData(this))
        })
        .then(r => r.text())
        .then(data => {
            if (data.includes("SUCCESS:")) {
                window.location.href = data.split(":")[1];
            } else {
                Swal.fire({
                    icon: 'warning', title: 'Gagal', text: data,
                    confirmButtonColor: '#3b82f6',
                    timer: 3000, timerProgressBar: true,
                    didClose: () => { orderCodeInput.value = ''; orderCodeInput.focus(); }
                });
            }
        });
    });
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>