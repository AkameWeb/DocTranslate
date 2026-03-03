<?php
require_once __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpWord\IOFactory;
use Stichoza\GoogleTranslate\GoogleTranslate;

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

$response = ['success' => false, 'translated' => '', 'original' => '', 'error' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $sourceLang = $_POST['from'] ?? 'ru';
    $targetLang = $_POST['to'] ?? 'en';

    // Проверка ошибок загрузки
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $response['error'] = 'Ошибка загрузки файла: код ' . $file['error'];
        echo json_encode($response);
        exit;
    }

    // Проверка расширения
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['doc', 'docx'])) {
        $response['error'] = 'Поддерживаются только файлы DOC и DOCX';
        echo json_encode($response);
        exit;
    }

    // Сохраняем файл
    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $tmpPath = $uploadDir . uniqid('doc_') . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $tmpPath)) {
        $response['error'] = 'Не удалось сохранить файл на сервере';
        echo json_encode($response);
        exit;
    }

    try {
        // Загружаем документ через PHPWord
        $phpWord = IOFactory::load($tmpPath);
        $text = '';

        // Обход всех секций документа
        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                // Простые текстовые элементы
                if (method_exists($element, 'getText')) {
                    $text .= $element->getText() . ' ';
                }
                // Элементы TextRun (содержат несколько кусков текста)
                elseif ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
                    foreach ($element->getElements() as $child) {
                        if (method_exists($child, 'getText')) {
                            $text .= $child->getText() . ' ';
                        }
                    }
                }
                // Для таблиц можно добавить обход, но пока ограничимся текстом
            }
        }

        // Если текст не найден, пробуем альтернативный метод (для .doc)
        if (empty(trim($text)) && $ext === 'doc') {
            // Метод getPlainText() может сработать для простых документов
            $text = $phpWord->getPlainText() ?: $text;
        }

        // Удаляем временный файл
        unlink($tmpPath);

        if (empty(trim($text))) {
            $response['error'] = 'Не удалось извлечь текст из документа (возможно, файл пуст или имеет сложное форматирование)';
            echo json_encode($response);
            exit;
        }

        // Перевод текста
        $tr = new GoogleTranslate();
        $tr->setSource($sourceLang)->setTarget($targetLang);
        $translated = $tr->translate($text);

        $response['success'] = true;
        $response['translated'] = $translated;
        $response['original'] = $text;

    } catch (Exception $e) {
        $response['error'] = 'Ошибка обработки: ' . $e->getMessage();
        if (isset($tmpPath) && file_exists($tmpPath)) {
            unlink($tmpPath);
        }
    }
} else {
    $response['error'] = 'Нет файла или неверный метод запроса';
}

echo json_encode($response);