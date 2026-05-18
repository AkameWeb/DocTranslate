<?php
class CacheHelper {
    private static $cacheDir = STORAGE_PATH . '/cache';
    
    public static function get($key, $ttl = 3600) {
        $file = self::$cacheDir . '/' . md5($key) . '.cache';
        if (file_exists($file) && (time() - filemtime($file)) < $ttl) {
            return unserialize(file_get_contents($file));
        }
        return null;
    }
    
    public static function set($key, $data) {
        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0777, true);
        }
        $file = self::$cacheDir . '/' . md5($key) . '.cache';
        file_put_contents($file, serialize($data));
    }
    
    public static function clear($key) {
        $file = self::$cacheDir . '/' . md5($key) . '.cache';
        if (file_exists($file)) {
            unlink($file);
        }
    }
}