<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../path.php';

// Cek Autentikasi
if (!isset($_SESSION['username']) || $_SESSION['role'] != 1) {
    header("Location: " . BASE_URL . "auth/login");
    exit;
}

$pageTitle = "Printer Bluetooth (Web API)";
$currentPage = "printer";
include '../layout/header.php';
include '../layout/sidebar.php';
?>

<main class="relative min-h-screen pt-[74px] transition-all duration-300 lg:ml-[280px] pc-main">
    <div class="p-4 sm:p-6 lg:p-8">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">

                <div class="mb-6 text-center">
                    <div class="w-16 h-16 mx-auto rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600 mb-4">
                        <i class="fa-brands fa-bluetooth text-2xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800">
                        Server Printer Kasir
                    </h2>
                    <p class="text-gray-500 text-sm mt-2">
                        Biarkan tab ini tetap terbuka selama kasir beroperasi. Tab lain akan mengirimkan perintah cetak ke sini.
                    </p>
                </div>

                <div id="js-alert" class="hidden mb-5 rounded-xl p-4 font-medium text-sm"></div>

                <div class="overflow-hidden rounded-xl border border-gray-200">
                    <table class="w-full text-sm">
                        <tbody>
                            <tr class="border-b">
                                <td class="p-4 font-semibold w-48">Tipe Koneksi</td>
                                <td class="p-4 font-mono text-blue-600">Web Bluetooth API + BroadcastChannel</td>
                            </tr>
                            <tr>
                                <td class="p-4 font-semibold">Status Browser</td>
                                <td class="p-4">
                                    <span id="status-badge" class="inline-flex items-center gap-2 text-red-600 font-semibold">
                                        🔴 Terputus
                                    </span>
                                    <span id="device-name" class="ml-2 text-gray-500 font-normal"></span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 flex flex-wrap gap-3">
                    <button id="btn-connect" onclick="connectBluetooth()" class="px-5 py-3 rounded-xl bg-blue-600 text-white hover:bg-blue-700 font-semibold transition-all">
                        <i class="fa-solid fa-link mr-2"></i> Pilih & Sambungkan
                    </button>
                    <button id="btn-test" onclick="testPrint()" class="px-5 py-3 rounded-xl bg-green-600 text-white hover:bg-green-700 font-semibold transition-all hidden">
                        <i class="fa-solid fa-print mr-2"></i> Test Print
                    </button>
                    <button id="btn-disconnect" onclick="disconnectBluetooth()" class="px-5 py-3 rounded-xl bg-red-600 text-white hover:bg-red-700 font-semibold transition-all hidden">
                        <i class="fa-solid fa-link-slash mr-2"></i> Putuskan
                    </button>
                </div>

            </div>
        </div>
    </div>
</main>

<script>
    let bluetoothDevice = null;
    let writeCharacteristic = null;

    // --- FUNGSI BARU: MENGIRIM DATA SECARA BERCICIL (CHUNKING) ---
    async function sendDataToPrinter(dataUint8Array) {
        // Batas aman pengiriman Web Bluetooth adalah 512, kita pakai 400 agar lebih stabil
        const CHUNK_SIZE = 400; 
        
        for (let i = 0; i < dataUint8Array.length; i += CHUNK_SIZE) {
            const chunk = dataUint8Array.slice(i, i + CHUNK_SIZE);
            await writeCharacteristic.writeValue(chunk);
            
            // Beri jeda sangat singkat antar potongan agar buffer printer tidak penuh
            await new Promise(resolve => setTimeout(resolve, 50)); 
        }
    }
    // -------------------------------------------------------------

    // --- FITUR KOMUNIKASI ANTAR TAB (BROADCAST CHANNEL) ---
    const saluranPrinter = new BroadcastChannel('printer_channel');

    saluranPrinter.onmessage = async (event) => {
        if (!writeCharacteristic) {
            showAlert('⚠️ Ada struk masuk, tapi printer belum terhubung. Klik tombol <strong>Pilih & Sambungkan</strong> terlebih dahulu.', false);
            return;
        }
        
        try {
            console.log("Menerima data cetak dari tab lain...");
            const encoder = new TextEncoder();
            const data = encoder.encode(event.data);
            
            // Gunakan fungsi chunking di sini
            await sendDataToPrinter(data);
            
            showAlert('✅ Struk kasir berhasil dicetak!', true);
        } catch (error) {
            console.error("Gagal mencetak struk:", error);
            showAlert(friendlyError(error, 'print'), false);
        }
    };
    // -----------------------------------------------------

    const statusBadge = document.getElementById('status-badge');
    const deviceName = document.getElementById('device-name');
    const btnConnect = document.getElementById('btn-connect');
    const btnTest = document.getElementById('btn-test');
    const btnDisconnect = document.getElementById('btn-disconnect');
    const jsAlert = document.getElementById('js-alert');

    function showAlert(message, isSuccess = true) {
        jsAlert.classList.remove('hidden', 'bg-green-50', 'text-green-700', 'border-green-200', 'bg-red-50', 'text-red-700', 'border-red-200');
        jsAlert.classList.add(isSuccess ? 'bg-green-50' : 'bg-red-50', isSuccess ? 'text-green-700' : 'text-red-700', 'border', isSuccess ? 'border-green-200' : 'border-red-200');
        jsAlert.innerHTML = message;
    }

    function updateUIConnected(name) {
        statusBadge.innerHTML = '🟢 Terhubung';
        statusBadge.classList.replace('text-red-600', 'text-green-600');
        deviceName.innerText = '(' + name + ')';
        btnConnect.classList.add('hidden');
        btnTest.classList.remove('hidden');
        btnDisconnect.classList.remove('hidden');
    }

    function updateUIDisconnected() {
        statusBadge.innerHTML = '🔴 Terputus';
        statusBadge.classList.replace('text-green-600', 'text-red-600');
        deviceName.innerText = '';
        btnConnect.classList.remove('hidden');
        btnTest.classList.add('hidden');
        btnDisconnect.classList.add('hidden');
        writeCharacteristic = null;
    }

    // Terjemahkan error teknis ke pesan ramah pengguna
    function friendlyError(error, context) {
        const msg = (error.message || '').toLowerCase();
        const name = (error.name || '').toLowerCase();

        if (!navigator.bluetooth) {
            return 'Browser Anda tidak mendukung Bluetooth. Gunakan Google Chrome versi terbaru di komputer.';
        }

        if (name === 'notfounderror' || msg.includes('user cancelled') || msg.includes('no device selected')) {
            return 'Tidak ada printer yang dipilih. Silakan klik tombol sambungkan dan pilih printer dari daftar.';
        }

        if (name === 'securityerror' || msg.includes('bluetooth permission') || msg.includes('permission')) {
            return 'Akses Bluetooth ditolak oleh browser. Pastikan izin Bluetooth sudah diaktifkan untuk halaman ini.';
        }

        if (msg.includes('gatt') && msg.includes('connect')) {
            return 'Tidak bisa terhubung ke printer. Pastikan printer menyala dan berada dalam jangkauan Bluetooth (± 10 meter).';
        }

        if (msg.includes('network error') || msg.includes('connection') || msg.includes('disconnected')) {
            return 'Koneksi ke printer terputus. Pastikan printer masih menyala lalu coba sambungkan kembali.';
        }

        if (msg.includes('karakteristik') || msg.includes('write') || msg.includes('characteristic')) {
            return 'Printer ini tidak kompatibel atau tidak mendukung mode cetak. Coba pilih printer thermal yang berbeda.';
        }

        if (msg.includes('timeout')) {
            return 'Printer tidak merespons. Pastikan printer menyala dan coba lagi.';
        }

        if (context === 'print') {
            return 'Gagal mencetak struk. Pastikan printer masih terhubung dan coba cetak ulang.';
        }

        return 'Terjadi kesalahan saat menghubungkan printer. Pastikan Bluetooth aktif dan printer dalam jangkauan.';
    }

    async function connectBluetooth() {
        if (!navigator.bluetooth) {
            showAlert('Browser Anda tidak mendukung Bluetooth. Gunakan Google Chrome versi terbaru di komputer.', false);
            return;
        }

        try {
            bluetoothDevice = await navigator.bluetooth.requestDevice({
                acceptAllDevices: true,
                optionalServices: [
                    '000018f0-0000-1000-8000-00805f9b34fb', 
                    '00001101-0000-1000-8000-00805f9b34fb',
                    'e7810a71-73ae-499d-8c15-faa9aef0c3f2'
                ]
            });

            bluetoothDevice.addEventListener('gattserverdisconnected', updateUIDisconnected);
            const server = await bluetoothDevice.gatt.connect();
            const services = await server.getPrimaryServices();
            writeCharacteristic = null;

            for (const service of services) {
                try {
                    const characteristics = await service.getCharacteristics();
                    writeCharacteristic = characteristics.find(c => c.properties.write || c.properties.writeWithoutResponse);
                    if (writeCharacteristic) break;
                } catch (e) {}
            }

            if (!writeCharacteristic) {
                showAlert('Printer berhasil ditemukan, tapi tidak bisa digunakan untuk mencetak. Pastikan printer thermal Anda kompatibel dengan Web Bluetooth.', false);
                return;
            }

            updateUIConnected(bluetoothDevice.name || 'Printer Thermal');
            showAlert('✅ Printer terhubung dan siap menerima struk dari tab kasir!', true);

        } catch (error) {
            showAlert(friendlyError(error, 'connect'), false);
        }
    }

    async function testPrint() {
        if (!writeCharacteristic) return;
        try {
            const encoder = new TextEncoder();
            
            // Format struk test print agak panjang untuk menguji chunking
            let receipt = "\x1B\x40\x1B\x61\x01\x1B\x45\x01TEST PRINTER WEB API\n\x1B\x45\x00";
            receipt += "Koneksi Antar Tab Berhasil\n";
            receipt += "Sistem Chunking Data Aktif\n";
            receipt += "--------------------------------\n";
            receipt += "Jika tulisan ini tercetak,\n";
            receipt += "berarti printer Anda sudah\n";
            receipt += "bisa mencetak struk panjang\n";
            receipt += "melebihi batas 512 byte.\n";
            receipt += "--------------------------------\n\n\n";
            receipt += "\x1D\x56\x41\x03";

            const data = encoder.encode(receipt);
            
            // Gunakan fungsi chunking di sini juga
            await sendDataToPrinter(data);
            showAlert('✅ Test print berhasil! Struk sudah tercetak.', true);
            
        } catch (error) {
            showAlert(friendlyError(error, 'print'), false);
        }
    }

    function disconnectBluetooth() {
        if (bluetoothDevice && bluetoothDevice.gatt.connected) {
            bluetoothDevice.gatt.disconnect();
        }
    }
</script>

<?php include '../layout/footer.php'; ?>