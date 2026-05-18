<?php
// Настройки приложения
define('APP_NAME', 'OmniLang');
define('APP_DEBUG', true);  // На продакшене false
define('APP_URL', 'http://localhost/DocLang');

// Пути
define('BASE_PATH', dirname(__DIR__));
define('UPLOAD_PATH', BASE_PATH . '/uploads');
define('LOG_PATH', BASE_PATH . '/storage/logs');

// Лимиты
define('MAX_FILE_SIZE', 50 * 1024 * 1024); // 50MB
define('MAX_UPLOAD_FILES', 5);

// Время выполнения
ini_set('max_execution_time', 300);
ini_set('memory_limit', '256M');

// Обработка ошибок
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}