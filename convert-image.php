<?php
session_start();
require_once 'vendor/autoload.php';
require_once 'config/database.php';
require_once 'app/helpers/Cache.php';

use Intervention\Image\ImageManagerStatic as Image;

header('Content-Type: application/json');

if (!isset($_FILES['image'])) {
    echo json_encode(['error' => 'Файл не загружен']);
    exit;
}

$file = $_FILES['image'];
$format = $_POST['format'] ?? 'png';
$quality = (int)($_POST['quality'] ?? 90);
$width = (int)($_POST['width'] ?? 0);
$height = (int)($_POST['height'] ?? 0);
$keepAspect = isset($_POST['keepAspect']) && $_POST['keepAspect'] === 'true';

$allowedFormats = ['png', 'jpg', 'jpeg', 'webp', 'gif', 'bmp'];
if (!in_array($format, $allowedFormats)) {
    echo json_encode(['error' => 'Неподдерживаемый формат']);
    exit;
}

// Лимит на размер файла (50MB)
if ($file['size'] > 50 * 1024 * 1024) {
    echo json_encode(['error' => 'Файл слишком большой. Максимум 50MB']);
    exit;
}

$uploadDir = __DIR__ . '/public/uploads/images/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$originalName = basename($file['name']);
$originalExt = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
$originalPath = $uploadDir . uniqid('original_') . '.' . $originalExt;

if (!move_uploaded_file($file['tmp_name'], $originalPath)) {
    echo json_encode(['error' => 'Не удалось сохранить файл']);
    exit;
}

try {
    Image::configure(['driver' => 'gd']);
    $image = Image::make($originalPath);
    
    // Оптимизация максимального размера
    $maxWidth = 1920;
    $maxHeight = 1080;
    if ($image->width() > $maxWidth || $image->height() > $maxHeight) {
        $image->resize($maxWidth, $maxHeight, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
    }
    
    // Изменение размера (если указано пользователем)
    if ($width > 0 || $height > 0) {
        if ($keepAspect) {
            $image->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        } else {
            $image->resize($width, $height);
        }
    }
    
    // Конвертация
    $convertedName = uniqid('converted_') . '.' . $format;
    $convertedPath = $uploadDir . $convertedName;
    
    if ($format === 'jpg' || $format === 'jpeg') {
        $image->save($convertedPath, $quality, 'jpg');
    } elseif ($format === 'webp') {
        $image->save($convertedPath, $quality, 'webp');
    } else {
        $image->save($convertedPath, $quality);
    }
    
    $fileSize = filesize($convertedPath);
    
    // Сохраняем в БД
    $userId = $_SESSION['user_id'] ?? null;
    $stmt = $pdo->prepare("INSERT INTO image_conversions (user_id, original_name, original_path, converted_path, target_format, quality, width, height, file_size) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $originalName, $originalPath, $convertedPath, $format, $quality, $width, $height, $fileSize]);
    $convertedId = $pdo->lastInsertId();
    
    // Очищаем кэш истории изображений
    if ($userId) {
        Cache::delete('history_images_' . $userId);
    }
    
    echo json_encode([
        'success' => true,
        'converted_id' => $convertedId,
        'download_url' => "download-converted.php?id=$convertedId",
        'format' => $format,
        'size' => round($fileSize / 1024, 2) . ' KB'
    ]);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Ошибка конвертации: ' . $e->getMessage()]);
}