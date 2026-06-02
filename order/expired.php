<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../path.php';
require_once __DIR__ . '/../helper/validateTable.php';

$table_code = htmlspecialchars($_GET['table']);

validateTable($pdo, $table_code);

if (!isset($_GET['code']) || empty($_GET['code'])) {
    header("Location: " . BASE_URL . "order/" . $table_code . "/index");
    exit();
} else {
    $order_code = $_GET['code'];
}

try {
    // Hanya izinkan order yang sudah expired (status = 3)
    $stmt = $pdo->prepare("SELECT * FROM `order` WHERE code = ? AND `status` = 3");
    $stmt->execute([$order_code]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (empty($order)) {
        header("Location: " . BASE_URL . "order/" . $table_code . "/index");
        exit();
    }

    // Ambil rincian item pesanan
    $stmtItem = $pdo->prepare("SELECT * FROM order_item WHERE order_id = ?");
    $stmtItem->execute([$order['id']]);
    $order_items = $stmtItem->fetchAll(PDO::FETCH_ASSOC);

    // Ambil rincian fee per baris (snapshot saat order dibuat)
    $stmtFee = $pdo->prepare("SELECT * FROM order_fee WHERE order_id = ?");
    $stmtFee->execute([$order['id']]);
    $order_fees = $stmtFee->fetchAll(PDO::FETCH_ASSOC);

    // Fallback untuk order lama sebelum migrasi
    if (empty($order_fees)) {
        $feeStmt = $pdo->prepare("SELECT * FROM fee_setting");
        $feeStmt->execute();
        foreach ($feeStmt->fetchAll(PDO::FETCH_ASSOC) as $f) {
            $amt = (int)$f['type'] === 1
                ? (int) round($order['subtotal'] * ((float)$f['value'] / 100))
                : (int) round((float)$f['value']);
            $order_fees[] = ['name' => $f['name'], 'type' => $f['type'], 'rate' => $f['value'], 'amount' => $amt];
        }
    }
} catch (PDOException $e) {
    die("Error pada query: " . $e->getMessage());
}

$payment = ($order['payment'] == 1) ? 'Bayar di Kasir' : 'Bayar Online';
?>

<?php
$title = "Pesanan Kadaluarsa";
include __DIR__ . '/layout/header.php';
?>

<main class="w-full max-w-[480px] mx-auto bg-gray-50 min-h-screen relative shadow-2xl flex flex-col overflow-x-hidden">

    <header class="sticky top-0 z-20 bg-white shadow-sm pt-5 pb-4 px-4 flex items-center gap-3">
        <a href="<?= BASE_URL ?>order/<?= $table_code ?>/index"
            class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 transition-colors">
            <i class="ph-bold ph-arrow-left text-lg"></i>
        </a>
        <h1 class="text-xl font-bold text-gray-900">Pesanan Kadaluarsa</h1>
    </header>

    <div class="flex-1 px-4 py-5 space-y-4 pb-32 fade-in">

        <!-- Banner Kadaluarsa -->
        <div class="bg-red-50 border border-red-200 rounded-2xl p-4 flex items-center gap-3">
            <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="ph-fill ph-clock-countdown text-red-500 text-xl"></i>
            </div>
            <div>
                <p class="font-bold text-red-700 text-sm">Batas Waktu Pembayaran Habis</p>
                <p class="text-xs text-red-500 mt-0.5">Pesanan ini sudah tidak bisa diproses. Silakan buat pesanan baru.</p>
            </div>
        </div>

        <!-- Info Meja & Pemesan -->
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <p class="text-xs text-gray-500 mb-1">Nomor Meja</p>
                    <div class="bg-gray-100 text-gray-500 px-3 py-1.5 rounded-lg inline-flex items-center gap-1.5 border border-gray-200">
                        <i class="ph-fill ph-armchair"></i>
                        <span class="font-bold text-sm"><?= htmlspecialchars($order['table_name']) ?></span>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500 mb-0.5">Pemesan</p>
                    <p class="font-semibold text-gray-900 text-sm"><?= htmlspecialchars($order['customer_name']) ?></p>
                </div>
            </div>
            <div class="dashed-line my-3"></div>
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-xs text-gray-500 mb-1">Metode Bayar</p>
                    <span class="text-sm font-bold text-gray-400"><?= $payment ?></span>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500 mb-1">Kadaluarsa Sejak</p>
                    <span class="text-sm font-bold text-red-500">
                        <?= date('d M Y, H:i', strtotime($order['expired_at'])) ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Order Code (QR dinonaktifkan, tampilkan kode saja) -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 text-center">
            <h2 class="text-sm font-bold text-gray-900 mb-1">Order Code</h2>
            <div class="mb-4 flex justify-center font-semibold">
                <span class="bg-gray-100 border px-4 py-1.5 rounded-lg text-gray-400 text-sm line-through">
                    <?= htmlspecialchars($order['code']) ?>
                </span>
            </div>
            <!-- QR diblur sebagai tanda tidak valid -->
            <div class="mx-auto mb-3 p-2 bg-white border-2 border-gray-200 rounded-xl shadow-inner w-40 h-40 flex items-center justify-center relative overflow-hidden">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=<?= urlencode($order['code']) ?>"
                    alt="QR Kadaluarsa"
                    class="w-full h-full object-contain opacity-20 blur-sm select-none pointer-events-none">
                <!-- Overlay tanda kadaluarsa -->
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <i class="ph-fill ph-x-circle text-red-400 text-4xl mb-1"></i>
                    <span class="text-[10px] font-black text-red-500 uppercase tracking-widest">Kadaluarsa</span>
                </div>
            </div>
            <div class="flex items-center justify-center gap-2 bg-red-50 border border-red-200 px-4 py-2.5 rounded-xl">
                <i class="ph-fill ph-warning text-red-400 text-xl flex-shrink-0"></i>
                <p class="text-xs text-red-600">QR Code ini sudah <span class="font-bold">tidak berlaku</span>. Pesanan tidak dapat diproses.</p>
            </div>
        </div>

        <!-- Rincian Pesanan -->
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
            <h2 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                <i class="ph-fill ph-shopping-bag text-gray-400 text-lg"></i>
                <span class="text-gray-500">Rincian Pesanan</span>
            </h2>
            <div class="space-y-3 divide-y divide-gray-50">
                <?php foreach ($order_items as $item): ?>
                <div class="flex justify-between items-center py-2 opacity-60">
                    <div class="flex-1">
                        <p class="font-semibold text-gray-500 text-sm"><?= htmlspecialchars($item['menu_name']) ?></p>
                        <p class="text-xs text-gray-400">
                            <?= $item['qty'] ?>x @ Rp <?= number_format($item['subtotal'] / $item['qty'], 0, ',', '.') ?>
                        </p>
                        <?php if (!empty($item['notes'])): ?>
                        <p class="text-xs text-amber-500 italic mt-0.5">📝 <?= htmlspecialchars($item['notes']) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="font-semibold text-gray-400 text-sm">
                        Rp <?= number_format($item['subtotal'], 0, ',', '.') ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Ringkasan Pembayaran -->
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 opacity-60">
            <h2 class="text-sm font-bold text-gray-500 mb-3">Ringkasan Pembayaran</h2>
            <div class="space-y-2 mb-3">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-400">Subtotal</span>
                    <span class="font-medium text-gray-400">Rp <?= number_format($order['subtotal'], 0, ',', '.') ?></span>
                </div>
                <?php foreach ($order_fees as $fee): ?>
                <?php $suffix = (int)$fee['type'] === 1 ? ' (' . rtrim(rtrim(number_format((float)$fee['rate'], 2), '0'), '.') . '%)' : ''; ?>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-400"><?= htmlspecialchars($fee['name']) . $suffix ?></span>
                    <span class="font-medium text-gray-400">Rp <?= number_format($fee['amount'], 0, ',', '.') ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="dashed-line my-3"></div>
            <div class="flex justify-between items-center">
                <span class="font-bold text-gray-400">Total Tagihan</span>
                <span class="font-black text-gray-400 text-xl line-through">Rp <?= number_format($order['total'], 0, ',', '.') ?></span>
            </div>
        </div>

    </div>

    <!-- Bottom CTA -->
    <div class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-[480px] bg-white border-t border-gray-100 px-4 py-4 shadow-2xl z-20">
        <button onclick="pesanLagi()"
            class="w-full bg-blue-500 text-white font-black py-4 rounded-2xl text-base shadow-lg shadow-blue-200 active:scale-95 transition-transform flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
            </svg>
            Pesan Lagi
        </button>
    </div>

</main>

<script>
    function pesanLagi() {
        localStorage.removeItem('cart');
        localStorage.removeItem('buyer_data');
        window.location.href = '<?= BASE_URL ?>order/<?= $table_code ?>/index';
    }
</script>

<?php include __DIR__ . '/layout/footer.php'; ?>