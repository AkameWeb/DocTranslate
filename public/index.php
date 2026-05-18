<?php
// Загружаем конфигурацию
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/ffmpeg.php';
require_once __DIR__ . '/../app/helpers/functions.php';

session_start();

// Маршрутизация
$requestUri = $_SERVER['REQUEST_URI'];
$scriptName = $_SERVER['SCRIPT_NAME'];

// Простая маршрутизация (можно заменить на Router позже)
if ($requestUri === '/' || $requestUri === '/index.php') {
    require_once __DIR__ . '/../templates/index.php';
} elseif (strpos($requestUri, '/api/') === 0) {
    // API маршруты
    $apiPath = str_replace('/api/', '', $requestUri);
    $apiFile = __DIR__ . '/../app/controllers/' . ucfirst(explode('?', $apiPath)[0]) . '.php';
    if (file_exists($apiFile)) {
        require_once $apiFile;
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'API endpoint not found']);
    }
} else {
    // Обработка API-запросов (оставляем старые файлы временно)
    $legacyFile = __DIR__ . '/../' . ltrim($requestUri, '/');
    if (file_exists($legacyFile) && pathinfo($legacyFile, PATHINFO_EXTENSION) === 'php') {
        require_once $legacyFile;
    } else {
        http_response_code(404);
        echo '404 Not Found';
    }
}