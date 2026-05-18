<?php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Не авторизован']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$id = (int)($input['id'] ?? 0);
$userId = (int)$_SESSION['user_id'];

if (!$id) {
    echo json_encode(['error' => 'Не указан ID']);
    exit;
}

try {
    // Удаляем запись, если она существует
    $stmt = $pdo->prepare("DELETE FROM translations WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $userId]);
    
    // Очищаем кэш (если используется)
    $cacheFile = __DIR__ . '/storage/cache/history_translations_' . $userId . '.cache';
    if (file_exists($cacheFile)) unlink($cacheFile);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}