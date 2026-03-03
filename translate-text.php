<?php
require_once __DIR__ . '/vendor/autoload.php';

use Stichoza\GoogleTranslate\GoogleTranslate;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Метод не разрешён']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$text = trim($input['text'] ?? '');
$from = $input['from'] ?? 'ru';
$to = $input['to'] ?? 'en';

if (!$text) {
    http_response_code(400);
    echo json_encode(['error' => 'Текст не может быть пустым']);
    exit;
}

try {
    $tr = new GoogleTranslate();
    $tr->setSource($from)->setTarget($to);
    $translated = $tr->translate($text);

    echo json_encode([
        'success' => true,
        'original' => $text,
        'translated' => $translated
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка перевода: ' . $e->getMessage()]);
}