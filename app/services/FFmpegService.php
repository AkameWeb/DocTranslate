<?php
class FFmpegService {
    private $ffmpegPath;
    private $ffprobePath;
    
    public function __construct() {
        $this->ffmpegPath = $this->detectFFmpeg();
        $this->ffprobePath = $this->detectFFprobe();
    }
    
    private function detectFFmpeg() {
        // Проверяем окружение
        if (PHP_OS_FAMILY === 'Windows') {
            $paths = [
                'C:\ffmpeg\bin\ffmpeg.exe',
                'C:\Program Files\ffmpeg\bin\ffmpeg.exe',
                getenv('FFMPEG_PATH')
            ];
            foreach ($paths as $path) {
                if ($path && file_exists($path)) return $path;
            }
        }
        return 'ffmpeg';
    }
    
    private function detectFFprobe() {
        if (PHP_OS_FAMILY === 'Windows') {
            $paths = [
                'C:\ffmpeg\bin\ffprobe.exe',
                'C:\Program Files\ffmpeg\bin\ffprobe.exe',
                getenv('FFPROBE_PATH')
            ];
            foreach ($paths as $path) {
                if ($path && file_exists($path)) return $path;
            }
        }
        return 'ffprobe';
    }
    
    public function isAvailable() {
        exec($this->ffmpegPath . ' -version 2>&1', $output, $returnCode);
        return $returnCode === 0;
    }
    
    public function convert($inputPath, $outputPath, $format, $bitrate = 128, $sampleRate = 44100) {
        $cmd = escapeshellcmd($this->ffmpegPath) . " -i " . escapeshellarg($inputPath) . 
               " -b:a {$bitrate}k -ar {$sampleRate} " . escapeshellarg($outputPath) . " 2>&1";
        exec($cmd, $output, $returnCode);
        return $returnCode === 0;
    }
    
    public function cut($inputPath, $outputPath, $start, $end, $format = 'mp3', $bitrate = 128, $sampleRate = 44100) {
        $cmd = escapeshellcmd($this->ffmpegPath) . " -i " . escapeshellarg($inputPath) . 
               " -ss {$start} -to {$end} -b:a {$bitrate}k -ar {$sampleRate} " . escapeshellarg($outputPath) . " 2>&1";
        exec($cmd, $output, $returnCode);
        return $returnCode === 0;
    }
    
    public function getDuration($filePath) {
        $cmd = escapeshellcmd($this->ffprobePath) . " -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 " . escapeshellarg($filePath);
        exec($cmd, $output, $returnCode);
        return $returnCode === 0 ? floatval($output[0]) : 0;
    }
}