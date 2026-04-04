<?php
putenv('PATH=' . getenv('PATH') . ';C:\Program Files\Tesseract-OCR');
require_once 'vendor/autoload.php';

use thiagoalessio\TesseractOCR\TesseractOCR;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['image'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Изображение не загружено']);
    exit;
}

$file = $_FILES['image'];
$tmpPath = $file['tmp_name'];
$targetDir = __DIR__ . '/uploads/';

if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}

$imagePath = $targetDir . uniqid('img_') . '.png';
move_uploaded_file($tmpPath, $imagePath);

try {
    $text = (new TesseractOCR($imagePath))
        ->lang('rus+eng') // распознаём русский и английский одновременно
        ->run();
    
    unlink($imagePath);
    
    echo json_encode(['success' => true, 'text' => trim($text)]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Ошибка распознавания: ' . $e->getMessage()]);
}