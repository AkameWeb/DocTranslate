<?php
session_start();
require_once '../../config/database.php';
require_once '../../app/services/OllamaService.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Не авторизован']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$text = $_POST['text'] ?? '';
$from = $_POST['from'] ?? 'ru';
$to = $_POST['to'] ?? 'en';
$topic = $_POST['topic'] ?? '';

$ollama = new OllamaService();

try {
    switch ($action) {
        case 'translate':
            $result = $ollama->translate($text, $from, $to);
            break;
        case 'summarize':
            $result = $ollama->summarize($text);
            break;
        case 'generate':
            $result = $ollama->generateText($topic);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Неизвестное действие']);
            exit;
    }
    
    // Сохраняем в БД
    $stmt = $pdo->prepare("INSERT INTO ai_generations (user_id, prompt, response, type) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $text ?: $topic, $result, $action]);
    
    echo json_encode(['success' => true, 'result' => $result]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}