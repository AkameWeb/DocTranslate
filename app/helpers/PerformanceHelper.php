<?php
class PerformanceHelper {
    private static $startTime;
    private static $startMemory;
    
    public static function start() {
        self::$startTime = microtime(true);
        self::$startMemory = memory_get_usage();
    }
    
    public static function end() {
        $time = round((microtime(true) - self::$startTime) * 1000, 2);
        $memory = round((memory_get_usage() - self::$startMemory) / 1024, 2);
        error_log("Performance: {$time}ms, {$memory}KB");
        return ['time' => $time, 'memory' => $memory];
    }
}

// Использование в начале скрипта:
// PerformanceHelper::start();
// ... код ...
// $perf = PerformanceHelper::end();