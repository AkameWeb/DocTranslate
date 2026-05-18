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
    // Получаем пути к файлам
    $stmt = $pdo->prepare("SELECT converted_path, original_path FROM audio_conversions WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $userId]);
    $conv = $stmt->fetch();
    
    if ($conv) {
        if (file_exists($conv['converted_path'])) unlink($conv['converted_path']);
        if (file_exists($conv['original_path'])) unlink($conv['original_path']);
        $delStmt = $pdo->prepare("DELETE FROM audio_conversions WHERE id = ? AND user_id = ?");
        $delStmt->execute([$id, $userId]);
    }
    
    $cacheFile = __DIR__ . '/storage/cache/history_audio_' . $userId . '.cache';
    if (file_exists($cacheFile)) unlink($cacheFile);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}