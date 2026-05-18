<?php
session_start();
require_once 'config/database.php';
require_once 'app/helpers/Cache.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Не авторизован']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $source = $data['source'] ?? '';
    $translated = $data['translated'] ?? '';
    $from = $data['from'] ?? '';
    $to = $data['to'] ?? '';
    $type = $data['type'] ?? 'text';

    if (!$source || !$translated) {
        http_response_code(400);
        echo json_encode(['error' => 'Нет данных для сохранения']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO translations (user_id, source_text, translated_text, source_lang, target_lang, type) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $source, $translated, $from, $to, $type]);
        
        // Очищаем кэш истории для этого пользователя
        Cache::delete('history_translations_' . $_SESSION['user_id']);
        
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Ошибка базы данных: ' . $e->getMessage()]);
    }
}