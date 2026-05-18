<?php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Не авторизован']);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    // Получаем все пути к файлам перед удалением
    $stmt = $pdo->prepare("SELECT converted_path, original_path FROM image_conversions WHERE user_id = ?");
    $stmt->execute([$userId]);
    $conversions = $stmt->fetchAll();
    
    foreach ($conversions as $conv) {
        if (file_exists($conv['converted_path'])) unlink($conv['converted_path']);
        if (file_exists($conv['original_path'])) unlink($conv['original_path']);
    }
    
    $stmt = $pdo->prepare("DELETE FROM image_conversions WHERE user_id = ?");
    $stmt->execute([$userId]);
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка базы данных']);
}