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

$stmt = $pdo->prepare("SELECT converted_path, original_path FROM audio_conversions WHERE user_id = ?");
$stmt->execute([$userId]);
$convs = $stmt->fetchAll();
foreach ($convs as $c) {
    if (file_exists($c['converted_path'])) unlink($c['converted_path']);
    if (file_exists($c['original_path'])) unlink($c['original_path']);
}
$stmt = $pdo->prepare("DELETE FROM audio_conversions WHERE user_id = ?");
$stmt->execute([$userId]);
echo json_encode(['success' => true]);