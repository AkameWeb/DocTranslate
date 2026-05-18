<?php
class ErrorHandler {
    private static $logs = [];
    
    public static function log($message, $type = 'error') {
        $logFile = LOG_PATH . '/' . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] [$type] $message" . PHP_EOL;
        
        if (!is_dir(LOG_PATH)) {
            mkdir(LOG_PATH, 0777, true);
        }
        
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
    
    public static function jsonResponse($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    public static function handleException($e) {
        self::log($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        if (APP_DEBUG) {
            self::jsonResponse(['error' => $e->getMessage()], 500);
        } else {
            self::jsonResponse(['error' => 'Внутренняя ошибка сервера'], 500);
        }
    }
}