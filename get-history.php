<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("SELECT id, source_text as source, translated_text as translated, source_lang as `from`, target_lang as `to`, type, created_at as date FROM translations WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
$stmt->execute([$_SESSION['user_id']]);
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Форматируем дату под формат, ожидаемый фронтендом
foreach ($history as &$item) {
    $item['date'] = date('d.m H:i', strtotime($item['date']));
    $item['source'] = mb_substr($item['source'], 0, 100) . (mb_strlen($item['source']) > 100 ? '...' : '');
    $item['translated'] = mb_substr($item['translated'], 0, 100) . (mb_strlen($item['translated']) > 100 ? '...' : '');
    $item['from'] = $item['from'] === 'ru' ? 'Русский' : 'English';
    $item['to'] = $item['to'] === 'ru' ? 'Русский' : 'English';
}

echo json_encode($history);