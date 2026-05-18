<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Не авторизован']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'] ?? 0;
$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("DELETE FROM ai_generations WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $userId]);

if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Запись не найдена']);
}