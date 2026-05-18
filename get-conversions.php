<?php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("SELECT id, original_name, target_format, file_size, created_at FROM image_conversions WHERE user_id = ? ORDER BY created_at DESC LIMIT 30");
$stmt->execute([$userId]);
$conversions = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($conversions as &$c) {
    $c['created_at'] = date('d.m.Y H:i', strtotime($c['created_at']));
    $c['file_size'] = round($c['file_size'] / 1024, 2) . ' KB';
}
echo json_encode($conversions);