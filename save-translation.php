<?php
session_start();
require_once 'config/db.php';

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

    $stmt = $pdo->prepare("INSERT INTO translations (user_id, source_text, translated_text, source_lang, target_lang, type) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $source, $translated, $from, $to, $type]);

    echo json_encode(['success' => true]);
}