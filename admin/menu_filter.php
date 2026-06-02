<?php
/**
 * Endpoint AJAX dashboard admin.
 * Contoh:
 * - menu_filter.php?period=hari
 * - menu_filter.php?period=minggu
 * - menu_filter.php?period=bulan
 * - menu_filter.php?period=custom&dari=2026-06-01&sampai=2026-06-30
 */
session_start();
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['username']) || $_SESSION['role'] != 2) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

function rupiah($value) {
    return 'Rp ' . number_format((int)$value, 0, ',', '.');
}

$period = $_GET['period'] ?? 'hari';
$dari   = $_GET['dari']   ?? '';
$sampai = $_GET['sampai'] ?? '';

try {
    if ($period === 'hari') {
        $start = date('Y-m-d 00:00:00');
        $end   = date('Y-m-d 23:59:59');
        $label = 'Hari ini';
    } elseif ($period === 'minggu') {
        $start = date('Y-m-d 00:00:00', strtotime('monday this week'));
        $end   = date('Y-m-d 23:59:59', strtotime('sunday this week'));
        $label = 'Minggu ini';
    } elseif ($period === 'bulan') {
        $start = date('Y-m-01 00:00:00');
        $end   = date('Y-m-t 23:59:59');
        $label = 'Bulan ini';
    } elseif ($period === 'custom') {
        if (!$dari || !$sampai || !strtotime($dari) || !strtotime($sampai) || $dari > $sampai) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Range tanggal tidak valid']);
            exit;
        }
        $start = $dari . ' 00:00:00';
        $end   = $sampai . ' 23:59:59';
        $label = date('d/m/Y', strtotime($dari)) . ' - ' . date('d/m/Y', strtotime($sampai));
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Periode tidak valid']);
        exit;
    }

    $summaryStmt = $pdo->prepare("\n        SELECT\n            COALESCE(SUM(o.total), 0) AS revenue,\n            COUNT(DISTINCT o.id) AS transactions,\n            COALESCE(SUM(oi.qty), 0) AS items_sold\n        FROM `order` o\n        LEFT JOIN order_item oi ON o.id = oi.order_id\n        WHERE o.status = 1\n          AND o.created_at BETWEEN :start_date AND :end_date\n    ");
    $summaryStmt->execute([
        ':start_date' => $start,
        ':end_date'   => $end,
    ]);
    $summary = $summaryStmt->fetch(PDO::FETCH_ASSOC) ?: ['revenue' => 0, 'transactions' => 0, 'items_sold' => 0];

    $menuStmt = $pdo->prepare("\n        SELECT\n            oi.menu_id,\n            oi.menu_name,\n            SUM(oi.qty) AS terjual,\n            SUM(oi.subtotal) AS pendapatan\n        FROM order_item oi\n        INNER JOIN `order` o ON o.id = oi.order_id\n        WHERE o.status = 1\n          AND o.created_at BETWEEN :start_date AND :end_date\n        GROUP BY oi.menu_id, oi.menu_name\n        ORDER BY terjual DESC, pendapatan DESC\n    ");
    $menuStmt->execute([
        ':start_date' => $start,
        ':end_date'   => $end,
    ]);
    $menus = $menuStmt->fetchAll(PDO::FETCH_ASSOC);

    $latestStmt = $pdo->prepare("\n        SELECT\n            id, code, customer_name, table_name, qty, total, payment, created_at\n        FROM `order`\n        WHERE status = 1\n          AND created_at BETWEEN :start_date AND :end_date\n        ORDER BY created_at DESC\n        LIMIT 5\n    ");
    $latestStmt->execute([
        ':start_date' => $start,
        ':end_date'   => $end,
    ]);
    $latest = $latestStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'label' => $label,
        'summary' => [
            'pendapatan' => rupiah($summary['revenue'] ?? 0),
            'transaksi'  => (int)($summary['transactions'] ?? 0) . ' Pesanan',
            'terjual'    => (int)($summary['items_sold'] ?? 0) . ' Produk',
        ],
        'menus' => $menus,
        'latest_transactions' => $latest,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal memuat data dashboard']);
}
