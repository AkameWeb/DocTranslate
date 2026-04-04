<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method not allowed');
}

$text = isset($_POST['text']) ? trim($_POST['text']) : '';
if (empty($text)) {
    http_response_code(400);
    die('No text provided');
}

try {
    $options = new Options();
    $options->set('defaultFont', 'DejaVu Sans');
    $dompdf = new Dompdf($options);
    $html = '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: DejaVu Sans, sans-serif; line-height: 1.6; margin: 2cm; }
            .content { white-space: pre-wrap; }
        </style>
    </head>
    <body>
        <div class="content">' . nl2br(htmlspecialchars($text)) . '</div>
    </body>
    </html>';
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="translation.pdf"');
    echo $dompdf->output();
} catch (Exception $e) {
    http_response_code(500);
    die('Error generating PDF: ' . $e->getMessage());
}