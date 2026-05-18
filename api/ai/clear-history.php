<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Не авторизован']);
    exit;
}

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("DELETE FROM ai_generations WHERE user_id = ?");
$stmt->execute([$userId]);

echo json_encode(['success' => true]);