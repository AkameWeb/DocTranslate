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

$stmt = $pdo->prepare("
    SELECT id, source_text as source, translated_text as translated, 
           source_lang as `from`, target_lang as `to`, type, created_at as date 
    FROM translations 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 20
");
$stmt->execute([$userId]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($data as &$item) {
    $item['date'] = date('d.m H:i', strtotime($item['date']));
    $item['source'] = mb_substr($item['source'], 0, 100) . (mb_strlen($item['source']) > 100 ? '...' : '');
    $item['translated'] = mb_substr($item['translated'], 0, 100) . (mb_strlen($item['translated']) > 100 ? '...' : '');
    $item['from'] = $item['from'] === 'ru' ? 'Русский' : 'English';
    $item['to'] = $item['to'] === 'ru' ? 'Русский' : 'English';
}
echo json_encode($data);