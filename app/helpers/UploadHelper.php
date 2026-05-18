<?php
class UploadHelper {
    public static function saveFile($file, $subDir = '') {
        $uploadDir = UPLOAD_PATH . '/' . $subDir;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $originalName = basename($file['name']);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $newName = uniqid() . '.' . $extension;
        $targetPath = $uploadDir . '/' . $newName;
        
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return [
                'success' => true,
                'path' => $targetPath,
                'name' => $originalName,
                'size' => $file['size']
            ];
        }
        
        return ['success' => false, 'error' => 'Не удалось сохранить файл'];
    }
    
    public static function deleteFile($path) {
        if (file_exists($path)) {
            return unlink($path);
        }
        return false;
    }
    
    public static function getFileSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}