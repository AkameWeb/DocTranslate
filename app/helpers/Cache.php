<?php
class Cache {
    private static $cacheDir = __DIR__ . '/../../storage/cache/';
    private static $defaultTtl = 300; // 5 минут по умолчанию
    
    public static function init() {
        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0777, true);
        }
    }
    
    public static function get($key, $ttl = null) {
        self::init();
        $ttl = $ttl ?? self::$defaultTtl;
        $file = self::$cacheDir . md5($key) . '.cache';
        
        if (file_exists($file) && (time() - filemtime($file)) < $ttl) {
            return unserialize(file_get_contents($file));
        }
        return null;
    }
    
    public static function set($key, $data, $ttl = null) {
        self::init();
        $ttl = $ttl ?? self::$defaultTtl;
        $file = self::$cacheDir . md5($key) . '.cache';
        file_put_contents($file, serialize($data));
        return true;
    }
    
    public static function delete($key) {
        $file = self::$cacheDir . md5($key) . '.cache';
        if (file_exists($file)) {
            unlink($file);
        }
    }
    
    public static function clear($prefix = null) {
        self::init();
        $files = glob(self::$cacheDir . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                if ($prefix === null || strpos(basename($file), $prefix) === 0) {
                    unlink($file);
                }
            }
        }
    }
    
    public static function getStats() {
        self::init();
        $files = glob(self::$cacheDir . '*.cache');
        $stats = [
            'total' => count($files),
            'size' => 0,
            'oldest' => time(),
            'newest' => 0
        ];
        
        foreach ($files as $file) {
            $size = filesize($file);
            $stats['size'] += $size;
            $mtime = filemtime($file);
            if ($mtime < $stats['oldest']) $stats['oldest'] = $mtime;
            if ($mtime > $stats['newest']) $stats['newest'] = $mtime;
        }
        
        $stats['size'] = round($stats['size'] / 1024, 2); // KB
        $stats['oldest'] = date('Y-m-d H:i:s', $stats['oldest']);
        $stats['newest'] = date('Y-m-d H:i:s', $stats['newest']);
        
        return $stats;
    }
}