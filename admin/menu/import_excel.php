<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../path.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 2) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['excel_file'])) {
    header("Location: index");
    exit;
}

$file = $_FILES['excel_file'];

// Validasi upload
if ($file['error'] !== UPLOAD_ERR_OK) {
    header("Location: index?status=error&msg=" . urlencode("Gagal upload file."));
    exit;
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if ($ext !== 'xlsx') {
    header("Location: index?status=error&msg=" . urlencode("Format file harus .xlsx"));
    exit;
}

if ($file['size'] > 2 * 1024 * 1024) {
    header("Location: index?status=error&msg=" . urlencode("Ukuran file maksimal 2MB."));
    exit;
}

// Cek PhpSpreadsheet tersedia (via Composer) atau pakai ZipArchive manual reader
// Pakai ZipArchive + SimpleXML (built-in PHP, tanpa library tambahan)
$tmpPath = $file['tmp_name'];

try {
    $rows = readXlsxSimple($tmpPath);
} catch (Exception $e) {
    header("Location: index?status=error&msg=" . urlencode("Gagal membaca file: " . $e->getMessage()));
    exit;
}

if (empty($rows) || count($rows) < 2) {
    header("Location: index?status=error&msg=" . urlencode("File kosong atau hanya berisi header."));
    exit;
}

// Ambil header (baris pertama), normalkan
$headers = array_map(function($h) {
    return strtolower(trim(preg_replace('/\s*\(.*\)/', '', (string)$h)));
}, $rows[0]);

// Mapping kolom yang dibutuhkan
$colMap = [];
$required = ['nama_menu', 'kategori', 'harga'];
foreach ($headers as $i => $h) {
    $colMap[$h] = $i;
}

foreach ($required as $req) {
    if (!array_key_exists($req, $colMap)) {
        header("Location: index?status=error&msg=" . urlencode("Kolom '$req' tidak ditemukan di file Excel. Pastikan menggunakan template yang benar."));
        exit;
    }
}

$imported   = 0;
$skipped    = 0;
$errors     = [];

$stmt = $pdo->prepare("INSERT INTO menu (name, price, category, image, description, sort_order) VALUES (?, ?, ?, ?, ?, ?)");

// Get current max sort_order
$maxSortStmt = $pdo->query("SELECT COALESCE(MAX(sort_order), 0) FROM menu");
$currentSort = (int)$maxSortStmt->fetchColumn();

for ($r = 1; $r < count($rows); $r++) {
    $row = $rows[$r];

    // Lewati baris kosong
    $rowStr = implode('', array_map('trim', $row));
    if (empty($rowStr)) continue;

    $name        = isset($row[$colMap['nama_menu']]) ? trim((string)$row[$colMap['nama_menu']]) : '';
    $kategori    = isset($row[$colMap['kategori']])  ? trim((string)$row[$colMap['kategori']])  : '';
    $harga_raw   = isset($row[$colMap['harga']])     ? trim((string)$row[$colMap['harga']])     : '';
    $deskripsi   = isset($colMap['deskripsi'], $row[$colMap['deskripsi']]) ? trim((string)$row[$colMap['deskripsi']]) : '';

    // Validasi nama
    if (empty($name)) {
        $skipped++;
        $errors[] = "Baris " . ($r + 1) . ": nama_menu kosong, dilewati.";
        continue;
    }

    // Validasi kategori
    $category = (int)$kategori;
    if (!in_array($category, [1, 2])) {
        $skipped++;
        $errors[] = "Baris " . ($r + 1) . ": kategori tidak valid ($kategori), harus 1 atau 2.";
        continue;
    }

    // Validasi harga
    $harga_clean = preg_replace('/[^0-9]/', '', $harga_raw);
    $price = max(0, (int)$harga_clean);
    if ($price <= 0 && $harga_raw !== '0') {
        $skipped++;
        $errors[] = "Baris " . ($r + 1) . ": harga tidak valid ($harga_raw).";
        continue;
    }

    $description = !empty($deskripsi) ? $deskripsi : 'Tidak ada deskripsi.';
    $image       = 'https://placehold.co/400x300?text=' . urlencode($name);

    try {
        $currentSort++;
        $stmt->execute([$name, $price, $category, $image, $description, $currentSort]);
        $imported++;
    } catch (PDOException $e) {
        $skipped++;
        $errors[] = "Baris " . ($r + 1) . ": gagal simpan ($name) — " . $e->getMessage();
    }
}

if ($imported > 0 && $skipped === 0) {
    header("Location: index?status=import_success&imported=" . $imported);
} elseif ($imported > 0 && $skipped > 0) {
    $msg = "$imported menu berhasil, $skipped dilewati.";
    if (!empty($errors)) $msg .= ' Detail: ' . implode(' | ', array_slice($errors, 0, 3));
    header("Location: index?status=import_partial&msg=" . urlencode($msg));
} else {
    $msg = "Tidak ada data yang berhasil diimpor.";
    if (!empty($errors)) $msg .= ' ' . implode(' | ', array_slice($errors, 0, 3));
    header("Location: index?status=error&msg=" . urlencode($msg));
}
exit;


// ─────────────────────────────────────────────────────────────
// Helper: Baca .xlsx dengan ZipArchive + SimpleXML (tanpa library)
// ─────────────────────────────────────────────────────────────
function readXlsxSimple(string $filePath): array
{
    $zip = new ZipArchive();
    if ($zip->open($filePath) !== true) {
        throw new Exception("Tidak bisa membuka file zip/xlsx.");
    }

    // Baca shared strings
    $sharedStrings = [];
    $ssXml = $zip->getFromName('xl/sharedStrings.xml');
    if ($ssXml !== false) {
        $ss = simplexml_load_string($ssXml);
        if ($ss) {
            foreach ($ss->si as $si) {
                if (isset($si->t)) {
                    $sharedStrings[] = (string)$si->t;
                } else {
                    // Concatenate <r><t> fragments
                    $text = '';
                    foreach ($si->r as $r) {
                        if (isset($r->t)) $text .= (string)$r->t;
                    }
                    $sharedStrings[] = $text;
                }
            }
        }
    }

    // Baca sheet pertama
    $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
    $zip->close();

    if ($sheetXml === false) {
        throw new Exception("Sheet1 tidak ditemukan.");
    }

    $sheet = simplexml_load_string($sheetXml);
    if (!$sheet) {
        throw new Exception("Gagal parse sheet XML.");
    }

    $data = [];
    foreach ($sheet->sheetData->row as $row) {
        $rowData   = [];
        $maxColIdx = 0;

        foreach ($row->c as $cell) {
            $colLetter = preg_replace('/[0-9]/', '', (string)$cell['r']);
            $colIdx    = colLetterToIndex($colLetter);
            $maxColIdx = max($maxColIdx, $colIdx);

            $type  = (string)($cell['t'] ?? '');
            $value = (string)($cell->v ?? '');

            if ($type === 's') {
                // shared string
                $value = $sharedStrings[(int)$value] ?? '';
            } elseif ($type === 'inlineStr') {
                $value = (string)($cell->is->t ?? '');
            }
            // angka/tanggal: pakai value langsung

            $rowData[$colIdx] = $value;
        }

        // Isi kolom yang skip supaya array rapi
        $filled = [];
        for ($i = 0; $i <= $maxColIdx; $i++) {
            $filled[] = $rowData[$i] ?? '';
        }
        $data[] = $filled;
    }

    return $data;
}

function colLetterToIndex(string $col): int
{
    $col   = strtoupper($col);
    $index = 0;
    $len   = strlen($col);
    for ($i = 0; $i < $len; $i++) {
        $index = $index * 26 + (ord($col[$i]) - ord('A') + 1);
    }
    return $index - 1; // 0-based
}