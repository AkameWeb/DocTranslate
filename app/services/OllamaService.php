<?php
class OllamaService {
    private $apiUrl = 'http://localhost:11434/api/generate';
    private $model = 'llama3.2:3b';
    
    public function generate($prompt, $system = null) {
        $data = [
            'model' => $this->model,
            'prompt' => $prompt,
            'stream' => false
        ];
        
        if ($system) {
            $data['system'] = $system;
        }
        
        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception('Ollama error: ' . $error);
        }
        
        $result = json_decode($response, true);
        return $result['response'] ?? '';
    }
    
    public function translate($text, $from, $to, $style = 'formal') {
        $prompt = "Переведи следующий текст с {$from} на {$to} в {$style} стиле без лишних комментариев:\n\n{$text}";
        return $this->generate($prompt);
    }
    
    public function summarize($text, $maxLength = 200) {
        $prompt = "Сделай краткое содержание (не более {$maxLength} символов) следующего текста:\n\n{$text}";
        return $this->generate($prompt);
    }
    
    public function generateText($topic, $type = 'article') {
        $prompt = "Напиши {$type} на тему: {$topic}. Текст должен быть уникальным и информативным.";
        return $this->generate($prompt);
    }
}