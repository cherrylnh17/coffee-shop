<?php
session_start();

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../path.php';

// Cek Autentikasi
if (!isset($_SESSION['username']) || $_SESSION['role'] != 1) {
    header("Location: " . BASE_URL . "auth/login");
    exit;
}

/*
|--------------------------------------------------------------------------
| LOAD DATA PRINTER UNTUK DITAMPILKAN DI INFO
|--------------------------------------------------------------------------
*/
$stmt = $pdo->query("
    SELECT *
    FROM printer
    WHERE type = 1
    AND is_active = 1
    LIMIT 1
");

$printer = $stmt->fetch(PDO::FETCH_ASSOC);

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
                        Web Bluetooth Printer
                    </h2>
                    <p class="text-gray-500 text-sm mt-2">
                        Koneksi thermal printer langsung melalui browser tanpa terminal/server.
                    </p>
                </div>

                <div id="js-alert" class="hidden mb-5 rounded-xl p-4 font-medium text-sm"></div>

                <?php if ($printer): ?>

                    <div class="overflow-hidden rounded-xl border border-gray-200">
                        <table class="w-full text-sm">
                            <tbody>
                                <tr class="border-b">
                                    <td class="p-4 font-semibold w-48">Nama Printer (DB)</td>
                                    <td class="p-4"><?= htmlspecialchars($printer['name']) ?></td>
                                </tr>
                                <tr class="border-b">
                                    <td class="p-4 font-semibold">Tipe Koneksi</td>
                                    <td class="p-4 font-mono text-blue-600">Web Bluetooth API</td>
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
                            <i class="fa-solid fa-link mr-2"></i>
                            Pilih & Sambungkan
                        </button>

                        <button id="btn-test" onclick="testPrint()" class="px-5 py-3 rounded-xl bg-green-600 text-white hover:bg-green-700 font-semibold transition-all hidden">
                            <i class="fa-solid fa-print mr-2"></i>
                            Test Print
                        </button>

                        <button id="btn-disconnect" onclick="disconnectBluetooth()" class="px-5 py-3 rounded-xl bg-red-600 text-white hover:bg-red-700 font-semibold transition-all hidden">
                            <i class="fa-solid fa-link-slash mr-2"></i>
                            Putuskan
                        </button>

                    </div>

                <?php else: ?>
                    <div class="rounded-xl bg-yellow-50 border border-yellow-200 p-5 text-yellow-700">
                        Tidak ada konfigurasi printer di database.
                    </div>
                <?php endif; ?>

            </div>

        </div>

    </div>
</main>

<script>
    let bluetoothDevice = null;
    let writeCharacteristic = null;

    // Perintah ESC/POS Standar
    const ESC = '\x1B';
    const GS = '\x1D';
    const INIT = ESC + '@';
    const CENTER = ESC + 'a' + '\x01';
    const LEFT = ESC + 'a' + '\x00';
    const BOLD_ON = ESC + 'E' + '\x01';
    const BOLD_OFF = ESC + 'E' + '\x00';
    const CUT = GS + 'V' + '\x41' + '\x03'; // Full cut

    // Elemen UI
    const statusBadge = document.getElementById('status-badge');
    const deviceName = document.getElementById('device-name');
    const btnConnect = document.getElementById('btn-connect');
    const btnTest = document.getElementById('btn-test');
    const btnDisconnect = document.getElementById('btn-disconnect');
    const jsAlert = document.getElementById('js-alert');

    function showAlert(message, isSuccess = true) {
        jsAlert.classList.remove('hidden', 'bg-green-50', 'text-green-700', 'border-green-200', 'bg-red-50', 'text-red-700', 'border-red-200');
        if (isSuccess) {
            jsAlert.classList.add('bg-green-50', 'text-green-700', 'border', 'border-green-200');
        } else {
            jsAlert.classList.add('bg-red-50', 'text-red-700', 'border', 'border-red-200');
        }
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

    async function connectBluetooth() {
        if (!navigator.bluetooth) {
            showAlert('Browser Anda tidak mendukung Web Bluetooth API. Gunakan Google Chrome atau Edge.', false);
            return;
        }

        try {
            // Meminta device dari browser
            bluetoothDevice = await navigator.bluetooth.requestDevice({
                // UUID ini adalah UUID standar untuk printer thermal (Serial Port Profile)
                filters: [
                    { services: ['000018f0-0000-1000-8000-00805f9b34fb'] }, // Custom UUID (Sering dipakai thermal)
                    { services: ['e7810a71-73ae-499d-8c15-faa9aef0c3f2'] }  // Custom UUID lainnya
                ],
                optionalServices: ['00001101-0000-1000-8000-00805f9b34fb'] // SPP Standar
            });

            // Handle jika device terputus tiba-tiba
            bluetoothDevice.addEventListener('gattserverdisconnected', onDisconnected);

            // Konek ke GATT Server
            const server = await bluetoothDevice.gatt.connect();

            // Mendapatkan Primary Service
            // Catatan: Anda mungkin perlu menyesuaikan UUID service sesuai merk printer Anda.
            // Gunakan aplikasi "nRF Connect" di HP untuk mengecek UUID service printer Anda jika gagal.
            const service = await server.getPrimaryService(bluetoothDevice.uuids[0]);
            
            const characteristics = await service.getCharacteristics();
            writeCharacteristic = characteristics.find(c => c.properties.write || c.properties.writeWithoutResponse);

            if (!writeCharacteristic) {
                throw new Error('Karakteristik penulisan (Write) tidak ditemukan pada device ini.');
            }

            updateUIConnected(bluetoothDevice.name);
            showAlert('Printer <b>' + bluetoothDevice.name + '</b> berhasil disambungkan!', true);

        } catch (error) {
            console.error(error);
            showAlert('Gagal menyambung: ' + error.message, false);
        }
    }

    async function testPrint() {
        if (!writeCharacteristic) {
            showAlert('Printer belum terhubung!', false);
            return;
        }

        try {
            const encoder = new TextEncoder();
            
            // Format Struk Test Print
            let receipt = INIT;
            receipt += CENTER + BOLD_ON + "TEST PRINTER WEB API\n" + BOLD_OFF;
            receipt += "COFFEE SHOP\n";
            receipt += "--------------------------------\n";
            receipt += LEFT;
            receipt += "Waktu   : " + new Date().toLocaleString('id-ID') + "\n";
            receipt += "Sistem  : Ubuntu Localhost\n";
            receipt += "Metode  : Web Bluetooth\n";
            receipt += "--------------------------------\n";
            receipt += CENTER + "Terima Kasih\n";
            receipt += "\n\n\n" + CUT;

            // Karena Web Bluetooth punya batas pengiriman data (biasanya 512 bytes per kiriman),
            // kita ubah ke Uint8Array dan kirim.
            const data = encoder.encode(receipt);
            await writeCharacteristic.writeValue(data);
            
            showAlert('Test print berhasil dikirim ke printer.', true);

        } catch (error) {
            console.error(error);
            showAlert('Gagal mencetak: ' + error.message, false);
        }
    }

    function disconnectBluetooth() {
        if (!bluetoothDevice) {
            return;
        }
        if (bluetoothDevice.gatt.connected) {
            bluetoothDevice.gatt.disconnect();
        }
    }

    function onDisconnected() {
        updateUIDisconnected();
        showAlert('Koneksi printer terputus.', false);
    }
</script>

<?php include '../layout/footer.php'; ?>