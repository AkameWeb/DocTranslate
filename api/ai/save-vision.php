<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Не авторизован']);
    exit;
}

if (!isset($_FILES['image']) || !isset($_POST['detections'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Нет данных']);
    exit;
}

$userId = $_SESSION['user_id'];
$imageFile = $_FILES['image'];
$detectionsJson = $_POST['detections'];

// Сохраняем оригинал изображения в папку
$uploadDir = '../../public/uploads/vision/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
$ext = strtolower(pathinfo($imageFile['name'], PATHINFO_EXTENSION));
$filename = uniqid('vision_') . '.' . $ext;
$targetPath = $uploadDir . $filename;
move_uploaded_file($imageFile['tmp_name'], $targetPath);

// Сохраняем в БД
$stmt = $pdo->prepare("INSERT INTO ai_vision (user_id, image_path, detections) VALUES (?, ?, ?)");
$stmt->execute([$userId, 'uploads/vision/' . $filename, $detectionsJson]);

echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);