<?php
session_start();
require_once '../config.php';
require_once '../path.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 1) {
    header("Location: " . BASE_URL . "auth/login");
    exit;
}

// ============================================================
//  KONFIGURASI BLUETOOTH PRINTER
// ============================================================
define('PRINTER_BT_MAC',     'DC:0D:51:78:D7:83');
define('PRINTER_BT_CHANNEL', 1);
define('PRINTER_RFCOMM_DEV', '/dev/rfcomm0');

// ============================================================
//  DATA TAGIHAN DEFAULT INDOMARET
// ============================================================
$tagihan = [
    'corp_name'  => 'PT INDOMARCO PRISMATAMA',
    'corp_addr'  => 'JL ANCOL I/9-10 ANCOL BARAT-JAKARTA UTARA',
    'corp_npwp'  => 'NPWP 01.337.994.6-092.000',

    'toko_nama'  => 'NGEMBAL REJO - KAB KUDUS',
    'toko_alamat'=> 'Jl. Raya Jepara - Kudus No.Km. 3, RT.01/RW.01, Ngembal Rejo, Kec. Bae, Kab. Kudus 59322',

    'tanggal'    => date('d.m.y'), // Format: DD.MM.YY
    'jam'        => date('H:i'),
    'kasir_id'   => '2.1.75 45172/KASIR/01', 
    'kasir_nama' => 'KASIR',

    'items' => [
        ['qty'=>5, 'nama'=>'JAVANA TEH MLATI 350', 'harga'=>3000],
        ['qty'=>1, 'nama'=>'ROTI GANDUM SARI',     'harga'=>15000],
    ],

    'metode'     => 'TUNAI',
    'bayar'      => 50000,
];

// Kalkulasi subtotal, total, dpp, dan ppn
$tagihan['subtotal'] = array_sum(array_map(fn($i) => $i['qty'] * $i['harga'], $tagihan['items']));
$tagihan['total']    = $tagihan['subtotal']; // Tidak ada diskon/VC
$tagihan['kembali']  = $tagihan['bayar'] - $tagihan['total'];

// Asumsi PPN 11% sudah include dalam harga jual total
$tagihan['dpp'] = round($tagihan['total'] / 1.11);
$tagihan['ppn'] = $tagihan['total'] - $tagihan['dpp'];


// ============================================================
//  FUNGSI HELPER ESC/POS
// ============================================================
function fmt($n) { return number_format((float)$n, 0, ',', '.'); }

function justify($left, $right, $width = 32) {
    $space = $width - mb_strlen($left) - mb_strlen($right);
    if ($space < 1) $space = 1;
    return $left . str_repeat(' ', $space) . $right;
}

function center($text, $width = 32) {
    $len = mb_strlen($text);
    if ($len >= $width) return $text;
    $pad = intval(($width - $len) / 2);
    return str_repeat(' ', $pad) . $text;
}

function wrapLines($text, $width = 32) {
    return explode("\n", wordwrap($text, $width, "\n", true));
}

// ============================================================
//  BUILD ESC/POS — FORMAT INDOMARET
// ============================================================
function buildIndomaretReceipt($t) {
    $ESC       = "\x1B";
    $GS        = "\x1D";
    $INIT      = $ESC . "@";
    $BOLD_ON   = $ESC . "E\x01";
    $BOLD_OFF  = $ESC . "E\x00";
    $ALIGN_L   = $ESC . "a\x00";
    $ALIGN_C   = $ESC . "a\x01";
    $ALIGN_R   = $ESC . "a\x02";
    $LF        = "\n";
    $SEP_DASH  = str_repeat('-', 32);
    $CUT       = $GS  . "V\x41\x03";

    $d = $INIT;

    // ── HEADER KORPORAT ──
    $d .= $ALIGN_L;
    $d .= $t['corp_name'] . $LF;
    
    foreach (wrapLines($t['corp_addr'], 32) as $line) {
        $d .= $line . $LF;
    }
    $d .= $t['corp_npwp'] . $LF;
    $d .= $LF;

    // ── NAMA & ALAMAT TOKO ──
    $d .= $ALIGN_C;
    $d .= $t['toko_nama'] . $LF;
    foreach (wrapLines($t['toko_alamat'], 32) as $line) {
        $d .= $line . $LF;
    }
    $d .= $ALIGN_L;
    $d .= $SEP_DASH . $LF;

    // ── INFO TRANSAKSI ──
    // Format: 07.11.20-15:45    2.1.75 45172/DESI WID/01
    $waktu = $t['tanggal'] . "-" . $t['jam'];
    $kasir = "2.1.75 45172/" . strtoupper($t['kasir_nama']) . "/01";
    $d .= justify($waktu, $kasir) . $LF;
    $d .= $LF;

    // ── ITEM ──
    foreach ($t['items'] as $item) {
        $sub = $item['qty'] * $item['harga'];
        $itemLine1 = substr($item['nama'], 0, 32);
        
        // Format: NAMA BARANG [spasi] QTY [spasi] HARGA [spasi] SUBTOTAL
        // Jika nama barang panjang, taruh di baris pertama, harga di baris kedua
        if (strlen($itemLine1) > 16) {
            $d .= $itemLine1 . $LF;
            $detailHarga = sprintf("%3d  %7s %8s", $item['qty'], fmt($item['harga']), fmt($sub));
            $d .= str_pad("", 32 - strlen($detailHarga), " ") . $detailHarga . $LF;
        } else {
            $detail = sprintf("%-15s %2d %6s %7s", $itemLine1, $item['qty'], fmt($item['harga']), fmt($sub));
            $d .= $detail . $LF;
        }
    }
    $d .= $ALIGN_R;
    $d .= "------------------" . $LF; // Garis putus-putus pendek khas indomaret

    // ── TOTAL ──
    // Indomaret struk menggunakan format rata kanan dengan spasi statis
    $d .= "HARGA JUAL : " . str_pad(fmt($t['subtotal']), 10, " ", STR_PAD_LEFT) . $LF;
    $d .= "------------------" . $LF;
    $d .= "TOTAL : "      . str_pad(fmt($t['total']), 10, " ", STR_PAD_LEFT) . $LF;
    $d .= strtoupper($t['metode']) . " : " . str_pad(fmt($t['bayar']), 10, " ", STR_PAD_LEFT) . $LF;
    $d .= "KEMBALI : "    . str_pad(fmt($t['kembali']), 10, " ", STR_PAD_LEFT) . $LF;
    $d .= $LF;

    // ── FOOTER PPN & LAYANAN KONSUMEN ──
    $d .= $ALIGN_L;
    $d .= "PPN     : DPP= " . fmt($t['dpp']) . " PPN= " . fmt($t['ppn']) . $LF;
    $d .= $ALIGN_C;
    $d .= "LAYANAN KONSUMEN SMS 0811 1500 280" . $LF;
    $d .= "CALL CENTER 1500280" . $LF;
    $d .= "KONTAK@INDOMARET.CO.ID" . $LF;

    // Cut Paper
    $d .= $LF . $LF . $LF . $CUT;

    return $d;
}

// ============================================================
//  PRINT BLUETOOTH
// ============================================================
function printViaRfcomm($data) {
    $dev = PRINTER_RFCOMM_DEV;
    if (!file_exists($dev))  return ['success'=>false,'message'=>"Device {$dev} tidak ditemukan. Jalankan: sudo rfcomm bind 0 ".PRINTER_BT_MAC." ".PRINTER_BT_CHANNEL];
    if (!is_writable($dev))  return ['success'=>false,'message'=>"Tidak ada izin tulis ke {$dev}. Jalankan: sudo usermod -aG dialout www-data && sudo chmod 666 {$dev}"];
    $fp = @fopen($dev,'wb');
    if (!$fp)                return ['success'=>false,'message'=>"Gagal membuka {$dev}."];
    fwrite($fp,$data); fclose($fp);
    return ['success'=>true,'message'=>'Struk berhasil dicetak.'];
}

function printViaShell($data) {
    $mac=PRINTER_BT_MAC; $ch=PRINTER_BT_CHANNEL;
    $tmp=tempnam(sys_get_temp_dir(),'escpos_').'.bin';
    if (file_put_contents($tmp,$data)===false) return ['success'=>false,'message'=>'Gagal buat file temp.'];
    $cmd="rfcomm connect /dev/rfcomm1 {$mac} {$ch} </dev/null & sleep 1 && cat ".escapeshellarg($tmp)." > /dev/rfcomm1; rfcomm release /dev/rfcomm1 2>&1";
    $out=shell_exec($cmd); unlink($tmp);
    if (strpos((string)$out,"Can't")!==false||strpos((string)$out,'error')!==false)
        return ['success'=>false,'message'=>"Gagal BT: ".trim($out)];
    return ['success'=>true,'message'=>'Struk dicetak via shell.'];
}

function printToThermal($data) {
    $r=printViaRfcomm($data);
    if ($r['success']) return $r;
    if (strpos($r['message'],'tidak ditemukan')!==false) return printViaShell($data);
    return $r;
}

// ============================================================
//  HANDLE POST / AJAX
// ============================================================
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['aksi']??'')==='print_indomaret') {

    if (!empty($_POST['kasir_nama'])) $tagihan['kasir_nama'] = $_POST['kasir_nama'];
    if (!empty($_POST['metode']))     $tagihan['metode']     = $_POST['metode'];
    
    if (!empty($_POST['bayar'])) {
        $tagihan['bayar'] = (int)$_POST['bayar'];
    }

    if (!empty($_POST['items']) && is_array($_POST['items'])) {
        $tagihan['items'] = [];
        foreach ($_POST['items'] as $it) {
            $tagihan['items'][] = [
                'qty'   => (int)($it['qty']   ?? 1),
                'nama'  => strtoupper($it['nama']  ?? ''),
                'harga' => (int)($it['harga'] ?? 0),
            ];
        }
    }

    // Rekalkulasi
    $tagihan['subtotal'] = array_sum(array_map(fn($i)=>$i['qty']*$i['harga'], $tagihan['items']));
    $tagihan['total']    = $tagihan['subtotal'];
    $tagihan['kembali']  = $tagihan['bayar'] - $tagihan['total'];
    $tagihan['dpp']      = round($tagihan['total'] / 1.11);
    $tagihan['ppn']      = $tagihan['total'] - $tagihan['dpp'];

    $result = printToThermal(buildIndomaretReceipt($tagihan));

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        header('Content-Type: application/json');
        echo json_encode($result); exit;
    }
    $_SESSION['swal_msg'] = ['icon'=>$result['success']?'success':'warning','title'=>$result['success']?'Berhasil!':'Gagal Print','text'=>$result['message']];
    header("Location: indomaret.php"); exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Print Struk Indomaret</title>
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/bootstrap.min.css">
<style>
/* ── Layout ── */
body { background: #e8e8e8; font-family: 'Segoe UI', sans-serif; }

/* ── Struk kertas ── */
.receipt-wrap { perspective: 600px; }
.receipt {
  font-family: 'Courier New', Courier, monospace;
  font-size: 12.5px;
  line-height: 1.4;
  background: #fffef9;
  width: 300px;
  margin: 0 auto;
  padding: 20px 14px 32px;
  position: relative;
  filter: drop-shadow(0 4px 16px rgba(0,0,0,.22));
  /* Tepi zigzag atas dan bawah */
  -webkit-mask:
    radial-gradient(circle at 50% 0,       transparent 7px, #000 7px) top    / 18px 8px  repeat-x,
    radial-gradient(circle at 50% 100%,    transparent 7px, #000 7px) bottom / 18px 8px  repeat-x,
    linear-gradient(#000, #000) center / 100% calc(100% - 16px) no-repeat;
  mask:
    radial-gradient(circle at 50% 0,       transparent 7px, #000 7px) top    / 18px 8px  repeat-x,
    radial-gradient(circle at 50% 100%,    transparent 7px, #000 7px) bottom / 18px 8px  repeat-x,
    linear-gradient(#000, #000) center / 100% calc(100% - 16px) no-repeat;
}

.receipt-inner { transform: rotate(-.15deg); }

.r-corp        { font-size: 11.5px; color: #333; margin-bottom: 10px; width: 65%; display: inline-block; }
.r-logo-box    { float: right; width: 30%; text-align: center; border: 2px dashed #005eaa; border-radius: 6px; padding: 4px; font-weight: 800; color: #005eaa; font-size: 12px; margin-top: 4px; }
/* Styling Logo Indomaret yang Baru */
.r-logo-indomaret {
  float: right;
  background: #005eaa; /* Biru Indomaret */
  color: #fff;
  padding: 3px 12px;
  border-radius: 5px;
  font-family: "Arial Black", Gadget, sans-serif;
  font-size: 14px;
  font-style: italic;
  position: relative;
  margin-top: 5px;
  letter-spacing: -0.5px;
  border-bottom: 3px solid #e31e24; /* Aksen Merah */
}

.r-logo-indomaret::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  bottom: 0;
  width: 5px;
  background: #fdb913; /* Aksen Kuning */
  border-top-left-radius: 5px;
  border-bottom-left-radius: 5px;
}
.r-toko        { text-align: center; margin-top: 15px; font-size: 12.5px; }
.r-addr        { font-size: 12.5px; line-height: 1.3; margin-top: 2px; }

.sep-dash      { border: none; border-top: 1px dashed #444; margin: 6px 0; }
.sep-short     { border: none; border-top: 1px dashed #444; margin: 4px 0 4px auto; width: 50%; }

.r-row         { display: flex; justify-content: space-between; font-size: 12.5px; }
.r-row .r      { text-align: right; }

.r-item-row    { display: flex; flex-wrap: wrap; justify-content: space-between; font-size: 12.5px; margin-bottom: 2px; }
.r-item-name   { width: 100%; margin-bottom: 1px;}
.r-item-details { width: 100%; display: flex; justify-content: flex-end; gap: 12px; }

.r-totals      { display: flex; justify-content: flex-end; margin-bottom: 2px; }
.r-totals-lbl  { width: 100px; text-align: left; }
.r-totals-val  { width: 80px; text-align: right; }

.r-footer      { text-align: center; margin-top: 15px; font-size: 11.5px; }

/* ── Panel form ── */
.panel         { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 2px 10px rgba(0,0,0,.08); }
.item-block    { background: #f4f8fb; border: 1px solid #d9e6f2; border-radius: 8px; padding: 12px; margin-bottom: 8px; }
.btn-indomaret { background: #005eaa; color: #fff; border: none; font-weight: 700; letter-spacing: .5px; transition: background .2s; }
.btn-indomaret:hover { background: #004580; color: #fff; }
.section-label { font-size: 10.5px; text-transform: uppercase; letter-spacing: 1.2px; color: #666; font-weight: 700; margin-bottom: 8px; }
</style>
</head>
<body>
<div class="container py-4">
<div class="row g-4 align-items-start">

  <div class="col-md-5">
    <p class="section-label mb-3">Preview Struk</p>
    <div class="receipt-wrap">
    <div class="receipt">
    <div class="receipt-inner">

      <div class="clearfix">
        <div class="r-logo-indomaret">Indomaret</div>
        
        <div class="r-corp">
            <?= $tagihan['corp_name'] ?><br>
            <?= $tagihan['corp_addr'] ?><br>
            <?= $tagihan['corp_npwp'] ?>
        </div>
        </div>

      <div class="r-toko">
        <div><?= htmlspecialchars($tagihan['toko_nama']) ?></div>
        <div class="r-addr"><?= htmlspecialchars($tagihan['toko_alamat']) ?></div>
      </div>

      <hr class="sep-dash">
      <div class="r-row">
        <span><?= $tagihan['tanggal'].'-'.$tagihan['jam'] ?></span>
        <span>2.1.75 45172/<?= strtoupper(htmlspecialchars($tagihan['kasir_nama'])) ?>/01</span>
      </div>
      <div style="margin-bottom: 12px;"></div>

      <?php foreach($tagihan['items'] as $item):
        $sub = $item['qty'] * $item['harga']; ?>
        <div class="r-item-row">
            <div class="r-item-name"><?= htmlspecialchars($item['nama']) ?></div>
            <div class="r-item-details">
                <span><?= $item['qty'] ?></span>
                <span style="width:50px; text-align:right;"><?= fmt($item['harga']) ?></span>
                <span style="width:65px; text-align:right;"><?= fmt($sub) ?></span>
            </div>
        </div>
      <?php endforeach; ?>

      <hr class="sep-short">
      
      <div class="r-totals"><span class="r-totals-lbl">HARGA JUAL :</span><span class="r-totals-val"><?= fmt($tagihan['subtotal']) ?></span></div>
      <hr class="sep-short">
      <div class="r-totals"><span class="r-totals-lbl">TOTAL :</span><span class="r-totals-val"><?= fmt($tagihan['total']) ?></span></div>
      <div class="r-totals"><span class="r-totals-lbl"><?= strtoupper(htmlspecialchars($tagihan['metode'])) ?> :</span><span class="r-totals-val"><?= fmt($tagihan['bayar']) ?></span></div>
      <div class="r-totals"><span class="r-totals-lbl">KEMBALI :</span><span class="r-totals-val"><?= fmt($tagihan['kembali']) ?></span></div>

      <div style="margin-top: 15px;">
        PPN     : DPP= <?= fmt($tagihan['dpp']) ?> PPN= <?= fmt($tagihan['ppn']) ?>
      </div>
      <div class="r-footer">
        LAYANAN KONSUMEN SMS 0811 1500 280<br>
        CALL CENTER 1500280<br>
        KONTAK@INDOMARET.CO.ID
      </div>

    </div></div></div></div>

  <div class="col-md-7">
    <div class="panel">
      <h5 class="mb-4" style="font-weight:800; color:#005eaa;">🖨️ Print Struk Indomaret</h5>

      <form method="POST" action="indomaret.php" id="formPrint">
        <input type="hidden" name="aksi" value="print_indomaret">

        <div class="section-label">Info Transaksi</div>
        <div class="row g-2 mb-3">
          <div class="col-6">
            <label class="form-label form-label-sm">Nama Kasir</label>
            <input type="text" name="kasir_nama" class="form-control form-control-sm" value="<?= htmlspecialchars($tagihan['kasir_nama']) ?>">
          </div>
          <div class="col-6">
            <label class="form-label form-label-sm">Waktu Transaksi (Otomatis)</label>
            <input type="text" class="form-control form-control-sm" value="<?= $tagihan['tanggal'].'-'.$tagihan['jam'] ?>" disabled>
          </div>
        </div>

        <div class="section-label">Item Produk</div>
        <?php foreach($tagihan['items'] as $i=>$item): ?>
        <div class="item-block">
          <div class="row g-1">
            <div class="col-12">
              <label class="form-label form-label-sm mb-0">Nama Produk</label>
              <input type="text" name="items[<?=$i?>][nama]" class="form-control form-control-sm" value="<?= htmlspecialchars($item['nama']) ?>">
            </div>
            <div class="col-4">
              <label class="form-label form-label-sm mb-0">Qty</label>
              <input type="number" name="items[<?=$i?>][qty]" class="form-control form-control-sm" value="<?= $item['qty'] ?>">
            </div>
            <div class="col-8">
              <label class="form-label form-label-sm mb-0">Harga Satuan (Rp)</label>
              <input type="number" name="items[<?=$i?>][harga]" class="form-control form-control-sm" value="<?= $item['harga'] ?>">
            </div>
          </div>
        </div>
        <?php endforeach; ?>

        <div class="section-label mt-3">Pembayaran</div>
        <div class="row g-2 mb-3">
          <div class="col-6">
            <label class="form-label form-label-sm">Metode</label>
            <select name="metode" class="form-select form-select-sm">
              <option value="TUNAI"       <?= $tagihan['metode']==='TUNAI'?'selected':''       ?>>TUNAI</option>
              <option value="I-SAKU"      <?= $tagihan['metode']==='I-SAKU'?'selected':''      ?>>I-SAKU</option>
              <option value="DEBIT BCA"   <?= $tagihan['metode']==='DEBIT BCA'?'selected':''   ?>>DEBIT BCA</option>
              <option value="SHOPEEPAY"   <?= $tagihan['metode']==='SHOPEEPAY'?'selected':''   ?>>SHOPEEPAY</option>
            </select>
          </div>
          <div class="col-6">
            <label class="form-label form-label-sm">Jumlah Bayar (Rp)</label>
            <input type="number" name="bayar" class="form-control form-control-sm" value="<?= $tagihan['bayar'] ?>">
          </div>
        </div>

        <button type="submit" class="btn btn-indomaret w-100 py-2 mt-1">
          🖨️ &nbsp;Print ke Printer Thermal Bluetooth
        </button>
      </form>

      <div id="printStatus" class="mt-3"></div>
    </div>
  </div>

</div>
</div>

<?php if(isset($_SESSION['swal_msg'])): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
Swal.fire({
  icon:  '<?= $_SESSION['swal_msg']['icon'] ?>',
  title: '<?= addslashes($_SESSION['swal_msg']['title']) ?>',
  text:  '<?= addslashes($_SESSION['swal_msg']['text']) ?>',
});
</script>
<?php unset($_SESSION['swal_msg']); endif; ?>

<script>
document.getElementById('formPrint').addEventListener('submit', function(e) {
  e.preventDefault();
  const btn    = this.querySelector('button[type=submit]');
  const status = document.getElementById('printStatus');
  btn.disabled = true;
  btn.innerHTML = '⏳ &nbsp;Mengirim ke printer...';

  fetch('indomaret.php', {
    method: 'POST',
    body: new FormData(this),
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  })
  .then(r => r.json())
  .then(d => {
    status.innerHTML = d.success
      ? '<div class="alert alert-success py-2">✅ ' + d.message + '</div>'
      : '<div class="alert alert-warning py-2">⚠️ '  + d.message + '</div>';
  })
  .catch(() => {
    status.innerHTML = '<div class="alert alert-danger py-2">❌ Kesalahan koneksi ke server.</div>';
  })
  .finally(() => {
    btn.disabled = false;
    btn.innerHTML = '🖨️ &nbsp;Print ke Printer Thermal Bluetooth';
  });
});
</script>
</body>
</html>