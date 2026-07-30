<?php
require dirname(__DIR__) . '/app/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');

$event = active_event();
if (!$event) {
    echo json_encode(['data' => []], JSON_UNESCAPED_UNICODE);
    exit;
}

$category = strtoupper((string) ($_GET['category'] ?? 'BUJANG'));
if (!in_array($category, ['BUJANG', 'DAYANG'], true)) {
    $category = 'BUJANG';
}

$data = LeaderboardService::publicRankings((int) $event['id'], $category);

echo json_encode([
    'event' => [
        'name' => $event['name'],
        'year' => (int) $event['year'],
        'status' => $event['status'],
    ],
    'category' => $category,
    'display_unit' => 'percentage',
    'updated_at' => date(DATE_ATOM),
    'data' => $data,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
