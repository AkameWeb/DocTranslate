<?php
session_start();
require_once 'config/database.php';
require_once 'app/helpers/Cache.php';

header('Content-Type: application/json');

try {
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        echo json_encode(['error' => 'Только авторизованные пользователи']);
        exit;
    }
    
    if (!isset($_FILES['audio']) || $_FILES['audio']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['error' => 'Файл не загружен']);
        exit;
    }
    
    $file = $_FILES['audio'];
    $start = (float)($_POST['start'] ?? 0);
    $end = (float)($_POST['end'] ?? 0);
    $format = $_POST['format'] ?? 'mp3';
    $bitrate = (int)($_POST['bitrate'] ?? 128);
    $sampleRate = (int)($_POST['sample_rate'] ?? 44100);
    
    if ($file['size'] > 50 * 1024 * 1024) {
        echo json_encode(['error' => 'Файл слишком большой. Максимум 50MB']);
        exit;
    }
    
    $allowedFormats = ['mp3', 'wav', 'ogg', 'flac'];
    if (!in_array($format, $allowedFormats)) {
        echo json_encode(['error' => 'Неподдерживаемый формат']);
        exit;
    }
    
    if ($start >= $end) {
        echo json_encode(['error' => 'Некорректный диапазон обрезки']);
        exit;
    }
    
    $uploadDir = __DIR__ . '/public/uploads/audio/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $originalName = basename($file['name']);
    $originalPath = $uploadDir . uniqid('original_audio_') . '.' . pathinfo($originalName, PATHINFO_EXTENSION);
    
    if (!move_uploaded_file($file['tmp_name'], $originalPath)) {
        echo json_encode(['error' => 'Не удалось сохранить файл']);
        exit;
    }
    
    $convertedName = uniqid('cut_audio_') . '.' . $format;
    $convertedPath = $uploadDir . $convertedName;
    
    $ffmpegPath = 'C:\ffmpeg\bin\ffmpeg.exe'; // Укажите свой путь
    
    if (!file_exists($ffmpegPath)) {
        unlink($originalPath);
        echo json_encode(['error' => 'FFmpeg не найден']);
        exit;
    }
    
    $cmd = escapeshellcmd($ffmpegPath) . " -i " . escapeshellarg($originalPath) . " -ss $start -to $end -b:a {$bitrate}k -ar {$sampleRate} " . escapeshellarg($convertedPath) . " 2>&1";
    exec($cmd, $output, $returnCode);
    
    if ($returnCode !== 0) {
        unlink($originalPath);
        if (file_exists($convertedPath)) unlink($convertedPath);
        echo json_encode(['error' => 'Ошибка FFmpeg: ' . implode("\n", $output)]);
        exit;
    }
    
    $fileSize = filesize($convertedPath);
    
    $stmt = $pdo->prepare("INSERT INTO audio_conversions (user_id, original_name, original_path, converted_path, target_format, bitrate, sample_rate, file_size) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $originalName, $originalPath, $convertedPath, $format, $bitrate, $sampleRate, $fileSize]);
    $convertedId = $pdo->lastInsertId();
    
    Cache::delete('history_audio_' . $userId);
    
    echo json_encode([
        'success' => true,
        'download_url' => "download-audio.php?id=$convertedId",
        'size' => round($fileSize / 1024, 2) . ' KB'
    ]);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Ошибка: ' . $e->getMessage()]);
}