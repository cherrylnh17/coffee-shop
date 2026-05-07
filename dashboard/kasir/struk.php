<?php
session_start();
if (!isset($_SESSION['username'])) {
    exit('Akses ditolak.');
}

require_once '../../config.php';

if (!isset($_GET['code'])) {
    exit('Kode pesanan tidak ditemukan.');
}

$code = $_GET['code'];

// Ambil data pesanan dari database
try {
    $stmt = $pdo->prepare("SELECT * FROM `order` WHERE code = ?");
    $stmt->execute([$code]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        exit('Pesanan tidak ditemukan.');
    }

    // MENGAMBIL RINCIAN MENU DARI TABEL order_item
    $stmtItem = $pdo->prepare("SELECT * FROM order_item WHERE order_id = ?");
    $stmtItem->execute([$order['id']]);
    $order_items = $stmtItem->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    exit('Terjadi kesalahan database.');
}

// FORMAT UANG RUPIAH
function formatRupiah($angka){
	return number_format((float)$angka, 0, ',', '.');
}

/** * MENGAMBIL UANG BAYAR DAN KEMBALIAN DARI DATABASE: 
 * Disesuaikan dengan nama kolom 'paid' dan 'change' 
 */
$uang_bayar = isset($order['paid']) ? $order['paid'] : $order['total']; 
$kembalian = isset($order['change']) ? $order['change'] : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk - <?php echo htmlspecialchars($order['code']); ?></title>
    <style>
        /* PENGATURAN KHUSUS PRINTER THERMAL (Kertas 58mm) */
        @page {
            margin: 0;
        }
        body {
            margin: 0;
            padding: 10px;
            font-family: 'Courier New', Courier, monospace; /* Font standar mesin kasir */
            font-size: 12px;
            color: #000;
            width: 58mm; /* Ukuran lebar kertas thermal kasir standar */
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        
        .header { margin-bottom: 10px; }
        .header h2 { margin: 0 0 2px 0; font-size: 16px; }
        .header p { margin: 0; font-size: 11px; }
        
        .divider { border-top: 1px dashed #000; margin: 8px 0; }
        
        .info-table { width: 100%; font-size: 11px; margin-bottom: 5px; }
        .info-table td { vertical-align: top; }
        
        .item-table { width: 100%; border-collapse: collapse; font-size: 11px; }
        .item-table th, .item-table td { padding: 2px 0; text-align: left; vertical-align: top; }
        .item-table th.right, .item-table td.right { text-align: right; }
        
        .total-section { width: 100%; font-size: 11px; margin-top: 5px; }
        .total-section td { padding: 2px 0; }
        .total-section .right { text-align: right; }
        
        .footer { text-align: center; margin-top: 15px; font-size: 10px; }
        
        /* Hilangkan elemen yang tidak perlu saat diprint */
        @media print {
            body { padding: 0; }
            #print-btn { display: none; }
        }
    </style>
</head>
<body>

    <div class="header text-center">
        <h2>TRÄFFA COFFEE</h2>
        <p>Jl. Nama Jalan Anda No. 123, Kota</p>
        <p>Telp: 0812-3456-7890</p>
    </div>
    
    <div class="divider"></div>
    
    <table class="info-table">
        <tr>
            <td>No</td>
            <td>: <?php echo htmlspecialchars($order['code']); ?></td>
        </tr>
        <tr>
            <td>Tgl</td>
            <td>: <?php echo date('d-m-Y H:i', strtotime($order['created_at'])); ?></td>
        </tr>
        <tr>
            <td>Kasir</td>
            <td>: <?php echo htmlspecialchars($order['user_name']); ?></td>
        </tr>
        <tr>
            <td>Meja</td>
            <td>: <?php echo htmlspecialchars($order['table_name']); ?></td>
        </tr>
        <tr>
            <td>Info</td>
            <td>: <?php echo htmlspecialchars($order['customer_name']); ?></td>
        </tr>
    </table>
    
    <div class="divider"></div>

    <!-- DETAIL PESANAN SESUAI FORMAT GAMBAR -->
    <table class="item-table">
        <?php foreach ($order_items as $item): 
            // Menghitung harga satuan = subtotal item dibagi kuantitas
            $harga_satuan = $item['subtotal'] / $item['qty'];
        ?>
        <tr>
            <td colspan="2"><?php echo htmlspecialchars($item['menu_name']); ?></td>
        </tr>
        <tr>
            <!-- Menambahkan spasi (padding) agar sedikit menjorok ke dalam -->
            <td style="padding-left: 10px;">
                <?php echo $item['qty']; ?> x <?php echo formatRupiah($harga_satuan); ?>
            </td>
            <td class="right"><?php echo formatRupiah($item['subtotal']); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <div class="divider"></div>
    
    <table class="total-section">
        <tr>
            <td>Subtotal</td>
            <td class="right"><?php echo formatRupiah($order['subtotal']); ?></td>
        </tr>
        <tr>
            <td>Pajak (Tax)</td>
            <td class="right"><?php echo formatRupiah($order['tax']); ?></td>
        </tr>
        <tr>
            <td class="font-bold">TOTAL TAGIHAN</td>
            <td class="right font-bold"><?php echo formatRupiah($order['total']); ?></td>
        </tr>
        <tr>
            <td>Tunai (<?php echo ($order['payment'] == 1) ? 'Kasir' : 'Online'; ?>)</td>
            <td class="right"><?php echo formatRupiah($uang_bayar); ?></td>
        </tr>
        <tr>
            <td class="font-bold">KEMBALIAN</td>
            <td class="right font-bold"><?php echo formatRupiah($kembalian); ?></td>
        </tr>
    </table>
    
    <div class="divider"></div>
    
    <div class="footer">
        <p>Terima kasih atas kunjungannya!</p>
        <p>Layanan Kritik & Saran:</p>
        <p>IG: @traffacoffee</p>
    </div>

    <!-- Tombol Manual Jika Gagal Print Otomatis -->
    <div id="print-btn" class="text-center" style="margin-top: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-weight:bold; cursor: pointer;">Cetak Sekarang</button>
    </div>

</body>
</html>