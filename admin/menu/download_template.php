<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../path.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 2) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit;
}

// Path ke file template (simpan di folder yang tidak bisa diakses publik langsung,
// atau bisa juga di assets — sesuaikan ROOT_PATH Anda)
$templatePath = ROOT_PATH . '/assets/template/template_menu.xlsx';

// Fallback: generate on-the-fly jika file tidak ada (menggunakan ZipArchive)
if (!file_exists($templatePath)) {
    // Generate template sederhana tanpa library eksternal
    $tmpFile = tempnam(sys_get_temp_dir(), 'tpl_') . '.xlsx';
    generateTemplateXlsx($tmpFile);
    $templatePath = $tmpFile;
    $cleanup = true;
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="template_menu.xlsx"');
header('Content-Length: ' . filesize($templatePath));
header('Cache-Control: max-age=0');

readfile($templatePath);

if (!empty($cleanup) && file_exists($tmpFile)) {
    unlink($tmpFile);
}
exit;


// ─────────────────────────────────────────────────────────────
// Generate template .xlsx on-the-fly (tanpa library eksternal)
// Menggunakan ZipArchive + XML manual
// ─────────────────────────────────────────────────────────────
function generateTemplateXlsx(string $outputPath): void
{
    $zip = new ZipArchive();
    if ($zip->open($outputPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        die("Gagal membuat file template.");
    }

    // [Content_Types].xml
    $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml"  ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml"              ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml"     ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/sharedStrings.xml"         ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>
  <Override PartName="/xl/styles.xml"                ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>');

    // _rels/.rels
    $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>');

    // xl/_rels/workbook.xml.rels
    $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet"     Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>
  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles"        Target="styles.xml"/>
</Relationships>');

    // xl/workbook.xml
    $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
          xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="Template Menu" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>');

    // Shared strings: semua teks di sini
    $strings = [
        'nama_menu',
        'kategori (1=Makanan, 2=Minuman)',
        'harga',
        'deskripsi',
        'Nasi Goreng Special',
        'Nasi goreng dengan telur mata sapi dan ayam suwir.',
        'Es Kopi Susu',
        'Espresso dicampur susu segar dan gula aren.',
        'Ayam Geprek',
        '',
    ];

    $ssXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count($strings) . '" uniqueCount="' . count($strings) . '">';
    foreach ($strings as $s) {
        $ssXml .= '<si><t xml:space="preserve">' . htmlspecialchars($s, ENT_XML1) . '</t></si>';
    }
    $ssXml .= '</sst>';
    $zip->addFromString('xl/sharedStrings.xml', $ssXml);

    // Styles: header (bold, blue fill, white font) + normal
    $zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="2">
    <font><sz val="10"/><name val="Arial"/></font>
    <font><b/><sz val="10"/><color rgb="FFFFFFFF"/><name val="Arial"/></font>
  </fonts>
  <fills count="3">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF1D4ED8"/></patternFill></fill>
  </fills>
  <borders count="2">
    <border><left/><right/><top/><bottom/></border>
    <border>
      <left style="thin"><color rgb="FFCCCCCC"/></left>
      <right style="thin"><color rgb="FFCCCCCC"/></right>
      <top style="thin"><color rgb="FFCCCCCC"/></top>
      <bottom style="thin"><color rgb="FFCCCCCC"/></bottom>
    </border>
  </borders>
  <cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>
  <cellXfs count="3">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0"/>
    <xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>
    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0"/>
  </cellXfs>
</styleSheet>');

    // Sheet1 data
    // Row 1: header (style 1), Row 2-4: sample rows (style 0)
    // Columns: A=nama_menu(0), B=kategori(1), C=harga(2), D=deskripsi(3)
    $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <sheetFormatPr defaultRowHeight="20"/>
  <cols>
    <col min="1" max="1" width="28" customWidth="1"/>
    <col min="2" max="2" width="32" customWidth="1"/>
    <col min="3" max="3" width="18" customWidth="1"/>
    <col min="4" max="4" width="45" customWidth="1"/>
  </cols>
  <sheetData>
    <row r="1" ht="30" customHeight="1">
      <c r="A1" t="s" s="1"><v>0</v></c>
      <c r="B1" t="s" s="1"><v>1</v></c>
      <c r="C1" t="s" s="1"><v>2</v></c>
      <c r="D1" t="s" s="1"><v>3</v></c>
    </row>
    <row r="2">
      <c r="A2" t="s" s="0"><v>4</v></c>
      <c r="B2"       s="0"><v>1</v></c>
      <c r="C2"       s="0"><v>25000</v></c>
      <c r="D2" t="s" s="0"><v>5</v></c>
    </row>
    <row r="3">
      <c r="A3" t="s" s="0"><v>6</v></c>
      <c r="B3"       s="0"><v>2</v></c>
      <c r="C3"       s="0"><v>18000</v></c>
      <c r="D3" t="s" s="0"><v>7</v></c>
    </row>
    <row r="4">
      <c r="A4" t="s" s="0"><v>8</v></c>
      <c r="B4"       s="0"><v>1</v></c>
      <c r="C4"       s="0"><v>20000</v></c>
      <c r="D4" t="s" s="0"><v>9</v></c>
    </row>
  </sheetData>
</worksheet>';
    $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);

    $zip->close();
}