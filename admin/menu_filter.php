<?php


session_start();
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');


if (empty($_SESSION['username']) || (int) $_SESSION['role'] !== 2) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

function rupiah($value)
{
    return 'Rp ' . number_format((int) $value, 0, ',', '.');
}

function resolvePeriod($period, $dari, $sampai)
{
    switch ($period) {
        case 'hari':
            return [
                'start' => date('Y-m-d 00:00:00'),
                'end'   => date('Y-m-d 23:59:59'),
                'label' => 'Hari ini',
            ];

        case 'minggu':
            return [
                'start' => date('Y-m-d 00:00:00', strtotime('monday this week')),
                'end'   => date('Y-m-d 23:59:59',  strtotime('sunday this week')),
                'label' => 'Minggu ini',
            ];

        case 'bulan':
            return [
                'start' => date('Y-m-01 00:00:00'),
                'end'   => date('Y-m-t 23:59:59'),
                'label' => 'Bulan ini',
            ];

        case 'custom':
            if (!$dari || !$sampai || !strtotime($dari) || !strtotime($sampai) || $dari > $sampai) {
                return ['error' => 'Range tanggal tidak valid'];
            }
            return [
                'start' => $dari   . ' 00:00:00',
                'end'   => $sampai . ' 23:59:59',
                'label' => date('d/m/Y', strtotime($dari)) . ' - ' . date('d/m/Y', strtotime($sampai)),
            ];

        default:
            return ['error' => 'Periode tidak valid'];
    }
}


$period = $_GET['period'] ?? 'hari';
$dari   = $_GET['dari']   ?? '';
$sampai = $_GET['sampai'] ?? '';

$range = resolvePeriod($period, $dari, $sampai);

if (isset($range['error'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $range['error']]);
    exit;
}

$start = $range['start'];
$end   = $range['end'];
$label = $range['label'];

try {
    $params = [':start' => $start, ':end' => $end];

    // Summary — dari tabel `order` langsung, qty sudah teragregasi per order
    $summaryStmt = $pdo->prepare('
        SELECT
            COALESCE(SUM(total), 0) AS revenue,
            COUNT(*)                AS transactions,
            COALESCE(SUM(qty), 0)  AS items_sold
        FROM `order`
        WHERE status = 1
          AND created_at BETWEEN :start AND :end
    ');
    $summaryStmt->execute($params);
    $summary = $summaryStmt->fetch(PDO::FETCH_ASSOC)
        ?: ['revenue' => 0, 'transactions' => 0, 'items_sold' => 0];

    $menuStmt = $pdo->prepare('
        SELECT
            menu_id,
            menu_name,
            SUM(qty)      AS terjual,
            SUM(subtotal) AS pendapatan
        FROM report
        WHERE sold_at BETWEEN :start AND :end
        GROUP BY menu_id, menu_name
        ORDER BY terjual DESC, pendapatan DESC
    ');
    $menuStmt->execute($params);
    $menus = $menuStmt->fetchAll(PDO::FETCH_ASSOC);

    // 5 transaksi terbaru dalam periode
    $latestStmt = $pdo->prepare('
        SELECT
            id, code, customer_name, table_name,
            qty, total, payment, created_at
        FROM `order`
        WHERE status = 1
          AND created_at BETWEEN :start AND :end
        ORDER BY created_at DESC
        LIMIT 5
    ');
    $latestStmt->execute($params);
    $latest = $latestStmt->fetchAll(PDO::FETCH_ASSOC);


    echo json_encode([
        'success' => true,
        'label'   => $label,
        'summary' => [
            'pendapatan' => rupiah($summary['revenue']),
            'transaksi'  => (int) $summary['transactions'] . ' Pesanan',
            'terjual'    => (int) $summary['items_sold']   . ' Produk',
        ],
        'menus'               => $menus,
        'latest_transactions' => $latest,
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal memuat data dashboard']);
}