<?php

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../path.php';
require_once __DIR__ . '/../../helper/validateTable.php';

// ── Header JSON ──────────────────────────────────────────────────────────────
header('Content-Type: application/json');

// ── Helper respond ───────────────────────────────────────────────────────────
function respond(bool $success, array $payload = [], int $httpCode = 200): void
{
    http_response_code($httpCode);
    echo json_encode(array_merge(['success' => $success], $payload));
    exit;
}

// ── Validasi method ──────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    respond(false, ['message' => 'Method not allowed'], 405);
}

// ── Sanitasi & validasi param ────────────────────────────────────────────────
$table_code = isset($_GET['table']) ? htmlspecialchars(trim($_GET['table'])) : '';
if ($table_code === '') {
    respond(false, ['message' => 'Parameter table wajib diisi'], 400);
}

// Validasi meja (akan die() sendiri jika tidak valid)
validateTable($pdo, $table_code);

$offset   = max(0, (int)($_GET['offset']   ?? 0));
$limit    = min(50, max(1, (int)($_GET['limit'] ?? 5)));
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

// ── Query ─────────────────────────────────────────────────────────────────────
try {
    // Ambil limit+1 agar tahu apakah masih ada halaman berikutnya
    $fetchLimit = $limit + 1;

    if ($category !== '') {
        // Validasi: category hanya boleh angka
        if (!ctype_digit($category)) {
            respond(false, ['message' => 'Kategori tidak valid'], 400);
        }
        $stmt = $pdo->prepare(
            "SELECT * FROM menu
             WHERE category = :category
             ORDER BY created_at ASC
             LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':category', (int)$category, PDO::PARAM_INT);
    } else {
        $stmt = $pdo->prepare(
            "SELECT * FROM menu
             ORDER BY created_at ASC
             LIMIT :limit OFFSET :offset"
        );
    }

    $stmt->bindValue(':limit',  $fetchLimit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset,     PDO::PARAM_INT);
    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    respond(false, ['message' => 'Database error: ' . $e->getMessage()], 500);
}

// ── Tentukan has_more ─────────────────────────────────────────────────────────
$has_more = count($rows) > $limit;
$items    = $has_more ? array_slice($rows, 0, $limit) : $rows;

// ── Response ──────────────────────────────────────────────────────────────────
respond(true, [
    'items'    => $items,
    'has_more' => $has_more,
    'offset'   => $offset,
    'limit'    => $limit,
]);