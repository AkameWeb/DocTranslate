<?php
// Настройки FFmpeg
if (PHP_OS_FAMILY === 'Windows') {
    $ffmpegPath = getenv('FFMPEG_PATH') ?: 'C:\ffmpeg\bin\ffmpeg.exe';
    $ffprobePath = getenv('FFPROBE_PATH') ?: 'C:\ffmpeg\bin\ffprobe.exe';
} else {
    $ffmpegPath = trim(shell_exec('which ffmpeg 2>/dev/null')) ?: '/usr/bin/ffmpeg';
    $ffprobePath = trim(shell_exec('which ffprobe 2>/dev/null')) ?: '/usr/bin/ffprobe';
}

define('FFMPEG_PATH', $ffmpegPath);
define('FFPROBE_PATH', $ffprobePath);
define('FFMPEG_TIMEOUT', 300); // секунд