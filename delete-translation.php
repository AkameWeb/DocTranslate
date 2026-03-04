<?php
session_start();
require_once 'config/db.php';

header('Content-Type: application/json');

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Не авторизован']);
    exit;
}

$userId = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);
$translationId = $input['id'] ?? 0;

if (!$translationId) {
    http_response_code(400);
    echo json_encode(['error' => 'Не указан ID перевода']);
    exit;
}

try {
    // Удаляем только запись, принадлежащую текущему пользователю
    $stmt = $pdo->prepare("DELETE FROM translations WHERE id = ? AND user_id = ?");
    $stmt->execute([$translationId, $userId]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Запись не найдена или не принадлежит вам']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка базы данных']);
}