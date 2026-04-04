<?php
putenv('PATH=' . getenv('PATH') . ';C:\Program Files\Tesseract-OCR');
require_once 'vendor/autoload.php';

use thiagoalessio\TesseractOCR\TesseractOCR;
use Stichoza\GoogleTranslate\GoogleTranslate;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['image'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Изображение не загружено']);
    exit;
}

$file = $_FILES['image'];
$sourceLang = $_POST['from'] ?? 'ru';
$targetLang = $_POST['to'] ?? 'en';
$tmpPath = $file['tmp_name'];
$targetDir = __DIR__ . '/uploads/';

if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}

$imagePath = $targetDir . uniqid('img_') . '.png';
move_uploaded_file($tmpPath, $imagePath);

try {
    // 1. Распознаём текст
    $text = (new TesseractOCR($imagePath))
        ->lang('rus+eng')
        ->run();
    
    unlink($imagePath);
    
    if (empty(trim($text))) {
        echo json_encode(['error' => 'Не удалось распознать текст на изображении']);
        exit;
    }
    
    // 2. Переводим распознанный текст
    $tr = new GoogleTranslate();
    $tr->setSource($sourceLang)->setTarget($targetLang);
    $translated = $tr->translate($text);
    
    echo json_encode([
        'success' => true,
        'original' => $text,
        'translated' => $translated
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Ошибка: ' . $e->getMessage()]);
}