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
    // Сначала получаем пути к файлам
    $stmt = $pdo->prepare("SELECT converted_path, original_path FROM image_conversions WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $userId]);
    $conv = $stmt->fetch();

    if ($conv) {
        // Удаляем физические файлы
        if (file_exists($conv['converted_path'])) unlink($conv['converted_path']);
        if (file_exists($conv['original_path'])) unlink($conv['original_path']);
        
        // Удаляем запись из БД
        $delStmt = $pdo->prepare("DELETE FROM image_conversions WHERE id = ? AND user_id = ?");
        $delStmt->execute([$id, $userId]);
    }
    
    // Очищаем кэш (если используется)
    $cacheFile = __DIR__ . '/storage/cache/history_images_' . $userId . '.cache';
    if (file_exists($cacheFile)) unlink($cacheFile);
    
    // Всегда возвращаем успех, даже если запись не найдена
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}