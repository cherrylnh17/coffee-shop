<?php
session_start();
require_once '../../config.php'; 
require_once '../../path.php';   

if (!isset($_SESSION['name']) || $_SESSION['role'] != 1) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit;
}

$pageTitle = "Dashboard Kasir";
$currentPage = "dashboard";

include 'layout/header.php';
include 'layout/sidebar.php';
?>

<main class="relative min-h-screen pt-[74px] transition-all duration-300 lg:ml-[280px] pc-main pb-10">
    <div class="p-4 sm:p-6 lg:p-8">  
        <div class="rounded-2xl border border-gray-100 bg-white p-4 sm:p-8 shadow-sm text-center">
            <div class="mb-6">
                <h2 class="text-xl font-bold text-gray-800">Scan QR Code Pesanan</h2>
                <p class="text-sm text-gray-500">Arahkan kamera ke QR Code pelanggan untuk memproses pesanan.</p>
            </div>
            
            <div class="mx-auto w-full max-w-md overflow-hidden rounded-xl bg-gray-50 p-2 sm:p-4 border-2 border-dashed border-gray-200">
                <div id="reader" class="w-full"></div>
            </div>

            <div class="mt-6 flex flex-wrap justify-center gap-4">
                <div class="flex items-center gap-2 text-xs text-gray-500 bg-gray-100 px-3 py-2 rounded-full">
                    <i class="fa-solid fa-circle-info text-blue-500"></i> Pastikan cahaya terang
                </div>
                <div class="flex items-center gap-2 text-xs text-gray-500 bg-gray-100 px-3 py-2 rounded-full">
                    <i class="fa-solid fa-camera text-green-500"></i> Gunakan kamera belakang
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    function onScanSuccess(decodedText, decodedResult) {
        html5QrcodeScanner.clear();
        fetch('proses_scan.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'qrcode_data=' + encodeURIComponent(decodedText)
        })
        .then(response => response.text())
        .then(data => {
            if (data.includes("SUCCESS:")) {
                const targetUrl = data.split(":")[1];
                window.location.href = targetUrl; 
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Gagal',
                    text: data,
                    confirmButtonColor: '#3b82f6'
                }).then(() => location.reload());
            }
        });
    }

    let html5QrcodeScanner = new Html5QrcodeScanner("reader", { 
        fps: 20,              
        qrbox: (viewfinderWidth, viewfinderHeight) => {
            const minEdge = Math.min(viewfinderWidth, viewfinderHeight);
            const qrboxSize = Math.floor(minEdge * 0.7);
            return { width: qrboxSize, height: qrboxSize };
        },
        aspectRatio: 1.0
    });
    html5QrcodeScanner.render(onScanSuccess);
</script>

<?php 

include 'layout/footer.php'; 
?>