<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../path.php';
require_once __DIR__ . '/../helper/validateTable.php';

$table_code = htmlspecialchars($_GET['table']);
validateTable($pdo, $table_code);

// Wajib ada order code
if (!isset($_GET['code']) || empty($_GET['code'])) {
    header("Location: " . BASE_URL . "order/" . $table_code . "/checkout");
    exit();
}

$order_code = $_GET['code'];

try {
    // Ambil order, pastikan belum dibayar & belum expired
    $stmt = $pdo->prepare("SELECT * FROM `order` WHERE code = ?");
    $stmt->execute([$order_code]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (empty($order)) {
        header("Location: " . BASE_URL . "order/" . $table_code . "/checkout");
        exit();
    }

    if ($order['status'] == 1) {
        header("Location: " . BASE_URL . "order/" . $table_code . "/success/" . $order_code);
        exit();
    }

    $now        = time();
    $expired_at = strtotime($order['expired_at']);
    if ($expired_at < $now) {
        header("Location: " . BASE_URL . "order/" . $table_code . "/expired/" . $order_code);
        exit();
    }

    // Ambil rincian fee
    $stmtFee = $pdo->prepare("SELECT * FROM order_fee WHERE order_id = ?");
    $stmtFee->execute([$order['id']]);
    $order_fees = $stmtFee->fetchAll(PDO::FETCH_ASSOC);

    if (empty($order_fees)) {
        $feeStmt = $pdo->prepare("SELECT * FROM gratuity");
        $feeStmt->execute();
        foreach ($feeStmt->fetchAll(PDO::FETCH_ASSOC) as $f) {
            $amt = (int)$f['type'] === 1
                ? (int) round($order['subtotal'] * ((float)$f['value'] / 100))
                : (int) round((float)$f['value']);
            $order_fees[] = ['name' => $f['name'], 'type' => $f['type'], 'rate' => $f['value'], 'amount' => $amt];
        }
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// ── Channel yang tersedia (sesuaikan dengan akun Tripay Anda) ────────────────
// Sandbox Tripay mendukung semua channel untuk testing.
// Format: [ code, label, warna, deskripsi singkat ]
$channels = [
    [
        'code'     => 'QRIS',
        'label'    => 'QRIS',
        'desc'     => 'Scan QR dari aplikasi apapun',
        'color'    => 'from-red-500 to-orange-400',
        'icon_url' => 'https://tripay.co.id/assets/images/payment/QRIS.png',
    ],
    [
        'code'     => 'SHOPEPAY',
        'label'    => 'ShopeePay',
        'desc'     => 'Bayar langsung via ShopeePay',
        'color'    => 'from-orange-500 to-red-400',
        'icon_url' => 'https://tripay.co.id/assets/images/payment/SHOPEPAY.png',
    ],
    [
        'code'     => 'OVO',
        'label'    => 'OVO',
        'desc'     => 'Bayar langsung via OVO',
        'color'    => 'from-purple-600 to-purple-400',
        'icon_url' => 'https://tripay.co.id/assets/images/payment/OVO.png',
    ],
    [
        'code'     => 'DANA',
        'label'    => 'DANA',
        'desc'     => 'Bayar langsung via DANA',
        'color'    => 'from-blue-500 to-cyan-400',
        'icon_url' => 'https://tripay.co.id/assets/images/payment/DANA.png',
    ],
];
?>

<?php
$title = "Pilih Metode Pembayaran";
include __DIR__ . '/layout/header.php';
?>

<main class="w-full max-w-[480px] mx-auto bg-gray-50 min-h-screen relative shadow-2xl flex flex-col overflow-x-hidden">

    <header class="sticky top-0 z-20 bg-white shadow-sm pt-5 pb-4 px-4 flex items-center gap-3">
        <a href="<?= BASE_URL ?>order/<?= $table_code ?>/checkout"
            class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 transition-colors">
            <i class="ph-bold ph-arrow-left text-lg"></i>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-900">Pilih Pembayaran</h1>
            <p class="text-xs text-gray-400">Order: <?= htmlspecialchars($order_code) ?></p>
        </div>
    </header>

    <div class="flex-1 px-4 py-5 space-y-4 pb-36 fade-in">

        <!-- Total tagihan -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-500 rounded-2xl p-4 text-white shadow-lg shadow-blue-200">
            <p class="text-sm text-blue-100 font-medium mb-0.5">Total Tagihan</p>
            <p class="text-3xl font-black">Rp <?= number_format($order['total'], 0, ',', '.') ?></p>
            <div class="flex items-center gap-3 mt-3 text-xs text-blue-100">
                <span><i class="ph ph-user mr-1"></i><?= htmlspecialchars($order['customer_name']) ?></span>
                <span>·</span>
                <span><i class="ph-fill ph-armchair mr-1"></i>Meja <?= htmlspecialchars($order['table_name']) ?></span>
            </div>
        </div>

        <!-- Pilih channel -->
        <div>
            <h2 class="text-sm font-bold text-gray-600 mb-3 px-1">Pilih metode pembayaran</h2>
            <div class="space-y-3" id="channelList">
                <?php foreach ($channels as $ch): ?>
                <label class="relative cursor-pointer block">
                    <input type="radio" name="channel" value="<?= $ch['code'] ?>" class="peer hidden channel-radio" />
                    <div class="flex items-center gap-4 bg-white border-2 border-gray-100 rounded-2xl p-4 shadow-sm
                        peer-checked:border-blue-400 peer-checked:bg-blue-50 peer-checked:ring-4 peer-checked:ring-blue-100
                        hover:border-blue-200 transition-all duration-200">
                        <!-- Logo -->
                        <div class="w-14 h-14 rounded-xl overflow-hidden bg-gray-50 border border-gray-100 flex items-center justify-center flex-shrink-0">
                            <img src="<?= $ch['icon_url'] ?>"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'"
                                 alt="<?= $ch['label'] ?>"
                                 class="w-full h-full object-contain p-1">
                            <!-- Fallback gradient icon -->
                            <div class="hidden w-full h-full bg-gradient-to-br <?= $ch['color'] ?> items-center justify-center rounded-xl">
                                <i class="ph ph-device-mobile text-white text-2xl"></i>
                            </div>
                        </div>
                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-gray-900 text-sm"><?= $ch['label'] ?></p>
                            <p class="text-xs text-gray-400 mt-0.5"><?= $ch['desc'] ?></p>
                        </div>
                        <!-- Checkmark -->
                        <div class="opacity-0 peer-checked:opacity-100 transition-opacity flex-shrink-0">
                            <div class="w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center">
                                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <!-- Checkmark overlay (workaround untuk sibling) -->
                    <div class="absolute right-4 top-1/2 -translate-y-1/2 hidden peer-checked:flex w-6 h-6 bg-blue-500 rounded-full items-center justify-center pointer-events-none">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Ringkasan biaya -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
            <h2 class="text-sm font-bold text-gray-800 mb-3">Rincian Biaya</h2>
            <div class="space-y-1.5 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Subtotal</span>
                    <span class="font-medium text-gray-700">Rp <?= number_format($order['subtotal'], 0, ',', '.') ?></span>
                </div>
                <?php foreach ($order_fees as $fee): ?>
                <?php $suffix = (int)$fee['type'] === 1 ? ' (' . rtrim(rtrim(number_format((float)$fee['rate'], 2), '0'), '.') . '%)' : ''; ?>
                <div class="flex justify-between">
                    <span class="text-gray-500"><?= htmlspecialchars($fee['name']) . $suffix ?></span>
                    <span class="font-medium text-gray-700">Rp <?= number_format($fee['amount'], 0, ',', '.') ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="border-t border-dashed border-gray-200 mt-3 pt-3 flex justify-between">
                <span class="font-bold text-gray-900">Total</span>
                <span class="font-black text-blue-600">Rp <?= number_format($order['total'], 0, ',', '.') ?></span>
            </div>
        </div>

        <!-- Badge aman -->
        <div class="flex items-center justify-center gap-2 text-xs text-gray-400">
            <i class="ph ph-lock text-green-500"></i>
            <span>Pembayaran diproses aman via <strong class="text-gray-600">Tripay</strong></span>
        </div>

    </div>

    <!-- Bottom CTA -->
    <div class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-[480px] bg-white border-t border-gray-100 px-4 py-4 shadow-2xl z-30">
        <!-- Loading state -->
        <div id="btnLoading" class="hidden w-full bg-blue-400 text-white rounded-2xl py-4 font-bold text-base flex items-center justify-center gap-2">
            <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span>Memproses...</span>
        </div>
        <!-- Normal button -->
        <button id="btnBayar" onclick="prosesPayment()" disabled
            class="w-full bg-gray-300 text-gray-400 cursor-not-allowed rounded-2xl py-4 font-bold text-base shadow-lg transition-all duration-200 flex items-center justify-center gap-2">
            <i class="ph ph-credit-card text-xl"></i>
            <span>Bayar Sekarang</span>
        </button>
    </div>

</main>

<script>
    const ORDER_CODE  = '<?= $order_code ?>';
    const TABLE_CODE  = '<?= $table_code ?>';
    const BASE_URL    = '<?= BASE_URL ?>';

    // Aktifkan tombol ketika channel dipilih
    document.querySelectorAll('.channel-radio').forEach(radio => {
        radio.addEventListener('change', () => {
            const btn = document.getElementById('btnBayar');
            btn.disabled = false;
            btn.classList.remove('bg-gray-300', 'text-gray-400', 'cursor-not-allowed');
            btn.classList.add('bg-blue-600', 'hover:bg-blue-700', 'text-white', 'active:scale-[0.98]');
        });
    });

    async function prosesPayment() {
        const selected = document.querySelector('.channel-radio:checked');
        if (!selected) return;

        const channel    = selected.value;
        const btnBayar   = document.getElementById('btnBayar');
        const btnLoading = document.getElementById('btnLoading');

        // Tampilkan loading
        btnBayar.classList.add('hidden');
        btnLoading.classList.remove('hidden');
        btnLoading.classList.add('flex');

        try {
            const resp = await fetch(`${BASE_URL}order/server/tripay_create`, {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({ order_code: ORDER_CODE, channel: channel }),
            });

            const data = await resp.json();

            if (data.success && data.payment_url) {
                // Redirect ke halaman pembayaran Tripay
                window.location.href = data.payment_url;
            } else {
                throw new Error(data.message || 'Gagal membuat transaksi');
            }
        } catch (err) {
            // Kembalikan tombol
            btnLoading.classList.add('hidden');
            btnLoading.classList.remove('flex');
            btnBayar.classList.remove('hidden');

            Swal.fire({
                title:              'Gagal!',
                text:               err.message,
                icon:               'error',
                confirmButtonText:  'Coba Lagi',
                confirmButtonColor: '#3b82f6',
            });
        }
    }
</script>

<?php include __DIR__ . '/layout/footer.php'; ?>