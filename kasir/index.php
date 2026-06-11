<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../path.php';

// Autentikasi
if (!isset($_SESSION['username']) || $_SESSION['role'] != 1) {
    header("Location: " . BASE_URL . "auth/login");
    exit;
}

// Ambil inisial nama untuk avatar sidebar
$name    = $_SESSION['name'] ?? $_SESSION['username'] ?? 'K';
$words   = explode(" ", $name);
$initials = "";
foreach ($words as $w) {
    $initials .= mb_substr($w, 0, 1);
}
$initials = strtoupper(substr($initials, 0, 2));

// Halaman pertama yang dimuat di dalam iframe
$defaultPage = BASE_URL . "kasir/dashboard";
?>
<!doctype html>
<html lang="id" dir="ltr">
<head>
    <title>Kasir | Träffa Coffee</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/flowbite@4.0.1/dist/flowbite.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="icon" href="<?= BASE_URL ?>assets/image/icon.png" type="image/x-icon" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        html, body {
            height: 100%;
            margin: 0;
            overflow: hidden; /* Shell tidak scroll — iframe yang scroll */
        }

        /* ── Sidebar ── */
        .pc-sidebar {
            transition: transform 0.3s ease, width 0.3s ease, border 0.3s ease;
        }
        /* Desktop collapse */
        body.sidebar-collapsed .pc-sidebar       { width: 0 !important; border-right: none !important; overflow: hidden; }
        body.sidebar-collapsed .pc-header        { left: 0 !important; }
        body.sidebar-collapsed .iframe-area      { left: 0 !important; }

        /* Mobile: sembunyikan sidebar ke kiri */
        @media (max-width: 1023px) {
            .pc-sidebar  { transform: translateX(-100%); }
            .pc-sidebar.open { transform: translateX(0); }
            .iframe-area { left: 0 !important; }
        }

        /* ── Area iframe ── */
        .iframe-area {
            position: fixed;
            top: 74px;
            left: 280px;
            right: 0;
            bottom: 0;
            transition: left 0.3s ease;
        }
        #main-frame {
            width: 100%;
            height: 100%;
            border: none;
            display: block;
        }

        /* ── Widget Bluetooth ── */
        .bt-widget {
            background: linear-gradient(135deg, #eff6ff, #f0fdf4);
            border: 1px solid #dbeafe;
            transition: background 0.3s, border-color 0.3s;
        }
        .bt-widget.connected { background: linear-gradient(135deg, #f0fdf4, #dcfce7); border-color: #86efac; }
        .bt-widget.errored   { background: linear-gradient(135deg, #fff7ed, #fef2f2); border-color: #fca5a5; }

        @keyframes pulse-dot {
            0%,100% { opacity: 1; }
            50%      { opacity: .35; }
        }
        .pulse-dot { animation: pulse-dot 1.6s ease-in-out infinite; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">

<!-- Overlay gelap saat sidebar mobile terbuka -->
<div id="sidebar-overlay" class="fixed inset-0 z-[1025] bg-gray-900/50 backdrop-blur-sm hidden"></div>

<!-- ═══════════════════════════════════════════
     SIDEBAR — tidak pernah di-reload
═══════════════════════════════════════════ -->
<nav id="pc-sidebar" class="pc-sidebar fixed inset-y-0 left-0 z-[1026] w-[280px] border-r border-gray-200 bg-white overflow-hidden">
    <div class="h-full flex flex-col">

        <!-- Logo -->
        <div class="flex h-[74px] items-center px-6 border-b border-gray-100 shrink-0">
            <a href="#" onclick="navigateTo('<?= BASE_URL ?>kasir/dashboard'); return false;" class="flex items-center gap-3">
                <img src="<?= BASE_URL ?>assets/image/icon.png" class="h-8 w-8" alt="logo" />
                <span class="rounded-md bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">Kasir Panel</span>
            </a>
        </div>

        <!-- Konten sidebar bisa scroll -->
        <div class="flex-1 overflow-y-auto py-3 flex flex-col gap-3">

            <!-- Avatar user -->
            <div class="mx-4 rounded-xl border border-gray-100 bg-gray-50 p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 shrink-0 rounded-full bg-blue-100 text-blue-600 text-sm font-semibold flex items-center justify-center shadow-sm">
                        <?= htmlspecialchars($initials) ?>
                    </div>
                    <div class="overflow-hidden">
                        <p class="truncate text-sm font-semibold text-gray-800"><?= htmlspecialchars($_SESSION['username'] ?? 'Kasir') ?></p>
                        <p class="text-xs text-gray-500">Kasir</p>
                    </div>
                </div>
            </div>

            <!-- ══ WIDGET BLUETOOTH ══ -->
            <div class="mx-4">
                <div id="bt-widget" class="bt-widget rounded-xl p-4">

                    <!-- Baris atas: ikon + label + status dot -->
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                <i class="fa-brands fa-bluetooth text-blue-600"></i>
                            </div>
                            <span class="text-xs font-bold text-gray-700 uppercase tracking-wide">Printer</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span id="bt-dot" class="w-2 h-2 rounded-full bg-red-400 pulse-dot"></span>
                            <span id="bt-dot-label" class="text-xs font-semibold text-red-500">Terputus</span>
                        </div>
                    </div>

                    <!-- Nama printer (muncul saat connect) -->
                    <p id="bt-device-name" class="hidden text-xs text-gray-500 mb-3 truncate">
                        <i class="fa-solid fa-print mr-1 text-gray-400"></i>
                        <span id="bt-device-name-text"></span>
                    </p>

                    <!-- Tombol -->
                    <div class="flex gap-2">
                        <button id="bt-btn-connect" onclick="connectBluetooth()"
                            class="flex-1 flex items-center justify-center gap-1.5 rounded-lg bg-blue-600 py-2 px-3 text-xs font-semibold text-white hover:bg-blue-700 transition-colors">
                            <i class="fa-solid fa-link text-xs"></i> Sambungkan
                        </button>
                        <button id="bt-btn-disconnect" onclick="disconnectBluetooth()"
                            class="hidden flex items-center justify-center gap-1.5 rounded-lg bg-red-100 py-2 px-3 text-xs font-semibold text-red-600 hover:bg-red-200 transition-colors">
                            <i class="fa-solid fa-link-slash text-xs"></i> Putus
                        </button>
                        <button id="bt-btn-test" onclick="testPrint()" title="Test Print"
                            class="hidden flex items-center justify-center rounded-lg bg-green-100 py-2 px-3 text-xs text-green-700 hover:bg-green-200 transition-colors">
                            <i class="fa-solid fa-print text-xs"></i>
                        </button>
                    </div>

                    <!-- Pesan notif mini -->
                    <div id="bt-msg" class="hidden mt-2 rounded-lg px-3 py-2 text-xs"></div>
                </div>
            </div>
            <!-- ══ END WIDGET BLUETOOTH ══ -->

            <!-- Menu -->
            <ul class="flex flex-col gap-1 px-4 text-sm">
                <li>
                    <a href="#" onclick="navigateTo('<?= BASE_URL ?>kasir/dashboard'); return false;"
                        data-page="dashboard"
                        class="nav-link group flex items-center gap-3 rounded-xl px-4 py-3 text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-all">
                        <span class="nav-icon flex w-6 justify-center text-lg text-gray-400 group-hover:text-blue-600">
                            <i class="fa-solid fa-house"></i>
                        </span>
                        <span class="font-medium">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="#" onclick="navigateTo('<?= BASE_URL ?>kasir/history'); return false;"
                        data-page="riwayat"
                        class="nav-link group flex items-center gap-3 rounded-xl px-4 py-3 text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-all">
                        <span class="nav-icon flex w-6 justify-center text-lg text-gray-400 group-hover:text-blue-600">
                            <i class="fa-solid fa-file-invoice-dollar"></i>
                        </span>
                        <span class="font-medium">Riwayat Pesanan</span>
                    </a>
                </li>

                <li class="pt-4 pb-1 px-4">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-300">Akun</span>
                </li>

                <li>
                    <a href="#" onclick="navigateTo('<?= BASE_URL ?>kasir/profile'); return false;"
                        data-page="akun"
                        class="nav-link group flex items-center gap-3 rounded-xl px-4 py-3 text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-all">
                        <span class="nav-icon flex w-6 justify-center text-lg text-gray-400 group-hover:text-blue-600">
                            <i class="fa-solid fa-circle-user"></i>
                        </span>
                        <span class="font-medium">Profile</span>
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>auth/logout"
                        class="group flex items-center gap-3 rounded-xl px-4 py-3 text-gray-600 hover:bg-red-50 hover:text-red-600 transition-all">
                        <span class="flex w-6 justify-center text-lg text-gray-400 group-hover:text-red-600">
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </span>
                        <span class="font-medium">Log Out</span>
                    </a>
                </li>
            </ul>

        </div><!-- end scrollable -->
    </div>
</nav>

<!-- ═══════════════════════════════════════════
     HEADER
═══════════════════════════════════════════ -->
<header class="pc-header fixed inset-x-0 top-0 z-[1024] flex h-[74px] items-center bg-white/80 px-4 shadow-sm backdrop-blur-md transition-all duration-300 lg:left-[280px]">
    <button id="sidebar-toggle-btn"
        class="flex h-11 w-11 items-center justify-center rounded-lg text-gray-600 hover:bg-gray-100 transition-all">
        <i class="fa-solid fa-bars text-lg"></i>
    </button>
</header>

<!-- ═══════════════════════════════════════════
     IFRAME — konten berganti di sini
═══════════════════════════════════════════ -->
<div class="iframe-area">
    <iframe id="main-frame" src="<?= BASE_URL ?>kasir/dashboard" title="Konten Kasir"></iframe>
</div>


<!-- ═══════════════════════════════════════════
     SCRIPTS
═══════════════════════════════════════════ -->
<script src="https://cdn.jsdelivr.net/npm/flowbite@4.0.1/dist/flowbite.min.js"></script>
<script>
// ─────────────────────────────────────────────
// NAVIGASI IFRAME
// ─────────────────────────────────────────────
const mainFrame = document.getElementById('main-frame');

/**
 * Guard session: deteksi jika iframe diarahkan ke halaman login
 * (terjadi saat session PHP habis di tengah sesi).
 * Jika terdeteksi, redirect full page ke login agar tidak
 * tampil di dalam iframe.
 */
mainFrame.addEventListener('load', () => {
    try {
        const iframeUrl = mainFrame.contentWindow.location.href;
        if (iframeUrl.includes('/auth/login') || iframeUrl.includes('/login')) {
            window.location.href = iframeUrl;
        }
    } catch (e) {
        // Cross-origin error diabaikan (tidak relevan di sini)
    }
});

function navigateTo(url) {
    mainFrame.src = url;
    setActiveNav(url);
    // Tutup sidebar di mobile setelah klik menu
    if (window.innerWidth < 1024) closeMobileSidebar();
}

function setActiveNav(url) {
    // Tentukan page key dari URL
    let page = 'dashboard';
    if (url.includes('/history'))  page = 'riwayat';
    else if (url.includes('/profile')) page = 'akun';

    document.querySelectorAll('.nav-link').forEach(link => {
        const isActive = link.dataset.page === page;
        // Reset
        link.classList.toggle('bg-blue-600',  isActive);
        link.classList.toggle('text-white',    isActive);
        link.classList.toggle('shadow-md',     isActive);
        link.classList.toggle('text-gray-600', !isActive);

        const icon = link.querySelector('.nav-icon');
        if (icon) icon.classList.toggle('text-white', isActive);
    });
}

// Set active saat pertama load
setActiveNav('<?= $defaultPage ?>');


// ─────────────────────────────────────────────
// TOGGLE SIDEBAR
// ─────────────────────────────────────────────
const sidebarEl  = document.getElementById('pc-sidebar');
const overlay    = document.getElementById('sidebar-overlay');
const btnToggle  = document.getElementById('sidebar-toggle-btn');

function closeMobileSidebar() {
    sidebarEl.classList.remove('open');
    overlay.classList.add('hidden');
}

btnToggle.addEventListener('click', () => {
    if (window.innerWidth < 1024) {
        // Mobile: slide in/out
        const isOpen = sidebarEl.classList.toggle('open');
        overlay.classList.toggle('hidden', !isOpen);
    } else {
        // Desktop: collapse (width → 0)
        document.body.classList.toggle('sidebar-collapsed');
    }
});

overlay.addEventListener('click', closeMobileSidebar);

window.addEventListener('resize', () => {
    if (window.innerWidth >= 1024) {
        sidebarEl.classList.remove('open');
        overlay.classList.add('hidden');
    }
});


// ─────────────────────────────────────────────
// BLUETOOTH PRINTER
// Hidup selama tab ini tidak di-refresh.
// Halaman di dalam iframe memanggil window.parent.printStruk()
// ─────────────────────────────────────────────
let bluetoothDevice     = null;
let writeCharacteristic = null;

const btWidget   = document.getElementById('bt-widget');
const btDot      = document.getElementById('bt-dot');
const btDotLabel = document.getElementById('bt-dot-label');
const btDevWrap  = document.getElementById('bt-device-name');
const btDevText  = document.getElementById('bt-device-name-text');
const btBtnConn  = document.getElementById('bt-btn-connect');
const btBtnDisc  = document.getElementById('bt-btn-disconnect');
const btBtnTest  = document.getElementById('bt-btn-test');
const btMsg      = document.getElementById('bt-msg');

function btShowMsg(msg, ok = true) {
    btMsg.className = `mt-2 rounded-lg px-3 py-2 text-xs ${ok ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'}`;
    btMsg.innerHTML = msg;
    clearTimeout(btMsg._t);
    btMsg._t = setTimeout(() => { btMsg.className = 'hidden'; }, 5000);
}

function btSetConnected(name) {
    btWidget.classList.replace ? btWidget.classList.remove('errored') : null;
    btWidget.classList.add('connected');
    btDot.className = 'w-2 h-2 rounded-full bg-green-500';
    btDotLabel.className = 'text-xs font-semibold text-green-600';
    btDotLabel.textContent = 'Terhubung';
    btDevText.textContent = name;
    btDevWrap.classList.remove('hidden');
    btBtnConn.classList.add('hidden');
    btBtnDisc.classList.remove('hidden');
    btBtnTest.classList.remove('hidden');
}

function btSetDisconnected() {
    btWidget.classList.remove('connected', 'errored');
    btDot.className = 'w-2 h-2 rounded-full bg-red-400 pulse-dot';
    btDotLabel.className = 'text-xs font-semibold text-red-500';
    btDotLabel.textContent = 'Terputus';
    btDevWrap.classList.add('hidden');
    btBtnConn.classList.remove('hidden');
    btBtnDisc.classList.add('hidden');
    btBtnTest.classList.add('hidden');
    writeCharacteristic = null;
}

// Kirim data ke printer dengan chunking 400-byte
async function sendDataToPrinter(uint8) {
    const CHUNK = 400;
    for (let i = 0; i < uint8.length; i += CHUNK) {
        await writeCharacteristic.writeValue(uint8.slice(i, i + CHUNK));
        await new Promise(r => setTimeout(r, 50));
    }
}

// ← FUNGSI YANG DIPANGGIL DARI IFRAME
window.printStruk = async function(rawString) {
    if (!writeCharacteristic) {
        btShowMsg('⚠️ Printer belum terhubung. Klik <strong>Sambungkan</strong>.', false);
        btWidget.classList.add('errored');
        return false;
    }
    try {
        await sendDataToPrinter(new TextEncoder().encode(rawString));
        btShowMsg('✅ Struk berhasil dicetak!');
        return true;
    } catch (err) {
        btShowMsg('❌ ' + btFriendlyError(err, 'print'), false);
        return false;
    }
};

// Tetap tangani BroadcastChannel (fallback tab terpisah)
new BroadcastChannel('printer_channel').onmessage = e => window.printStruk(e.data);

async function connectBluetooth() {
    if (!navigator.bluetooth) {
        btShowMsg('Browser tidak mendukung Bluetooth. Gunakan Chrome terbaru.', false);
        return;
    }
    btBtnConn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xs"></i> Mencari...';
    btBtnConn.disabled  = true;

    try {
        bluetoothDevice = await navigator.bluetooth.requestDevice({
            acceptAllDevices: true,
            optionalServices: [
                '000018f0-0000-1000-8000-00805f9b34fb',
                '00001101-0000-1000-8000-00805f9b34fb',
                'e7810a71-73ae-499d-8c15-faa9aef0c3f2'
            ]
        });

        bluetoothDevice.addEventListener('gattserverdisconnected', () => {
            btSetDisconnected();
            btShowMsg('Printer terputus. Sambungkan kembali jika perlu.', false);
        });

        const server   = await bluetoothDevice.gatt.connect();
        const services = await server.getPrimaryServices();
        writeCharacteristic = null;

        for (const svc of services) {
            try {
                const chars = await svc.getCharacteristics();
                writeCharacteristic = chars.find(c => c.properties.write || c.properties.writeWithoutResponse);
                if (writeCharacteristic) break;
            } catch (_) {}
        }

        if (!writeCharacteristic) {
            btShowMsg('Printer tidak kompatibel untuk cetak Bluetooth.', false);
            btWidget.classList.add('errored');
            btBtnConn.innerHTML = '<i class="fa-solid fa-link text-xs"></i> Sambungkan';
            btBtnConn.disabled  = false;
            return;
        }

        btSetConnected(bluetoothDevice.name || 'Printer Thermal');
        btShowMsg('✅ Printer terhubung dan siap mencetak!');

    } catch (err) {
        btWidget.classList.add('errored');
        btShowMsg(btFriendlyError(err, 'connect'), false);
        btBtnConn.innerHTML = '<i class="fa-solid fa-link text-xs"></i> Sambungkan';
        btBtnConn.disabled  = false;
    }
}

async function testPrint() {
    if (!writeCharacteristic) return;
    let r  = "\x1B\x40\x1B\x61\x01\x1B\x45\x01TEST - TRÄFFA COFFEE\n\x1B\x45\x00";
        r += "Sidebar Bluetooth printer\n";
        r += "berhasil terhubung!\n";
        r += "Koneksi tetap aktif saat\n";
        r += "berpindah halaman.\n";
        r += "--------------------------------\n\n\n";
        r += "\x1D\x56\x41\x03";
    await window.printStruk(r);
}

function disconnectBluetooth() {
    if (bluetoothDevice?.gatt?.connected) bluetoothDevice.gatt.disconnect();
    btSetDisconnected();
}

function btFriendlyError(err, ctx) {
    const m = (err.message || '').toLowerCase();
    const n = (err.name    || '').toLowerCase();
    if (n === 'notfounderror' || m.includes('user cancelled') || m.includes('no device selected'))
        return 'Tidak ada printer yang dipilih.';
    if (m.includes('gatt') && m.includes('connect'))
        return 'Tidak bisa terhubung. Pastikan printer menyala dan dalam jangkauan.';
    if (m.includes('disconnected') || m.includes('connection'))
        return 'Koneksi terputus. Sambungkan kembali.';
    if (ctx === 'print') return 'Gagal mencetak. Pastikan printer masih terhubung.';
    return 'Gagal menghubungkan. Pastikan Bluetooth aktif.';
}

// SWAL dari server PHP
<?php if (isset($_SESSION['swal_msg'])): ?>
Swal.fire({
    icon:  '<?= $_SESSION['swal_msg']['icon'] ?>',
    title: '<?= $_SESSION['swal_msg']['title'] ?>',
    text:  '<?= $_SESSION['swal_msg']['text'] ?>',
});
<?php unset($_SESSION['swal_msg']); ?>
<?php endif; ?>
</script>

</body>
</html>