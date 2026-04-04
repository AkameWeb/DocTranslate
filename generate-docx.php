<?php
require_once 'vendor/autoload.php';

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

$text = $_POST['text'] ?? '';

if (empty($text)) {
    http_response_code(400);
    die('Нет текста для сохранения');
}

// Удаляем недопустимые управляющие символы, оставляем только читаемые
$text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);
$text = trim($text);

if ($text === '') {
    http_response_code(400);
    die('Текст пуст после очистки');
}

try {
    $phpWord = new PhpWord();
    $section = $phpWord->addSection();

    // Добавляем текст с обработкой абзацев
    $paragraphs = explode("\n", $text);
    foreach ($paragraphs as $paragraph) {
        $paragraph = trim($paragraph);
        if ($paragraph !== '') {
            $section->addText(
                htmlspecialchars($paragraph, ENT_XML1, 'UTF-8'),
                ['name' => 'Arial', 'size' => 12],
                ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT]
            );
        }
        $section->addTextBreak(); // пустая строка между абзацами
    }

    // Сохраняем во временный файл
    $tempFile = tempnam(sys_get_temp_dir(), 'docx_');
    if ($tempFile === false) {
        throw new Exception('Не удалось создать временный файл');
    }

    $writer = IOFactory::createWriter($phpWord, 'Word2007');
    $writer->save($tempFile);

    // Проверяем, что файл не пустой
    if (filesize($tempFile) < 100) {
        throw new Exception('Созданный файл слишком маленький, возможно ошибка');
    }

    // Отправляем файл
    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Disposition: attachment; filename="translation.docx"');
    header('Content-Length: ' . filesize($tempFile));
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    readfile($tempFile);
    unlink($tempFile);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo 'Ошибка генерации DOCX: ' . $e->getMessage();
    if (isset($tempFile) && file_exists($tempFile)) {
        unlink($tempFile);
    }
}