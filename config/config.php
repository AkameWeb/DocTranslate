<?php
// Определяем путь к FFmpeg
if (PHP_OS_FAMILY === 'Windows') {
    $ffmpegPath = 'C:\ffmpeg\bin\ffmpeg.exe';
    // Если файл не существует, пробуем найти через where
    if (!file_exists($ffmpegPath)) {
        $output = [];
        exec('where ffmpeg 2>nul', $output);
        if (!empty($output)) {
            $ffmpegPath = $output[0];
        } else {
            $ffmpegPath = 'ffmpeg';
        }
    }
} else {
    // Linux / macOS
    $ffmpegPath = trim(shell_exec('which ffmpeg 2>/dev/null'));
    if (empty($ffmpegPath)) {
        $ffmpegPath = 'ffmpeg';
    }
}
define('FFMPEG_PATH', $ffmpegPath);


$test = shell_exec(FFMPEG_PATH . ' -version 2>&1');
if (strpos($test, 'ffmpeg version') === false) {
    error_log('FFmpeg not found at: ' . FFMPEG_PATH);
}