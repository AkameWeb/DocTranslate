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
$limit = 20;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

$stmt = $pdo->prepare("
    SELECT id, prompt, response, model, created_at 
    FROM ai_generations 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT ? OFFSET ?
");
$stmt->bindValue(1, $userId, PDO::PARAM_INT);
$stmt->bindValue(2, $limit, PDO::PARAM_INT);
$stmt->bindValue(3, $offset, PDO::PARAM_INT);
$stmt->execute();
$generations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Форматируем дату
foreach ($generations as &$g) {
    $g['created_at'] = date('d.m.Y H:i', strtotime($g['created_at']));
    $g['prompt'] = mb_substr($g['prompt'], 0, 80) . (mb_strlen($g['prompt']) > 80 ? '...' : '');
    $g['response'] = mb_substr($g['response'], 0, 100) . (mb_strlen($g['response']) > 100 ? '...' : '');
}

echo json_encode($generations);