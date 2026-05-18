<?php
session_start();
require_once 'config/database.php';

$id = (int)($_GET['id'] ?? 0);
$userId = $_SESSION['user_id'] ?? null;

if (!$userId || !$id) {
    die('Доступ запрещён');
}

$stmt = $pdo->prepare("SELECT converted_path, original_name FROM image_conversions WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $userId]);
$conv = $stmt->fetch();

if (!$conv || !file_exists($conv['converted_path'])) {
    die('Файл не найден');
}

$ext = pathinfo($conv['converted_path'], PATHINFO_EXTENSION);
$mimeTypes = [
    'png' => 'image/png',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'webp' => 'image/webp',
    'gif' => 'image/gif',
    'bmp' => 'image/bmp'
];
$mime = $mimeTypes[$ext] ?? 'application/octet-stream';

header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="converted_' . $conv['original_name'] . '.' . $ext . '"');
header('Content-Length: ' . filesize($conv['converted_path']));
readfile($conv['converted_path']);