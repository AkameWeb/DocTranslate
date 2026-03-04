<?php
namespace Tests\Unit;

use Tests\TestCase;

class TranslationTest extends TestCase
{
    public function testSaveTranslation()
    {
        // Создаём пользователя
        $stmt = self::$pdo->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
        $stmt->execute(['Translator', 'trans@test.com']);
        $userId = self::$pdo->lastInsertId();

        // Сохраняем перевод
        $source = 'Hello world';
        $translated = 'Привет мир';
        $from = 'en';
        $to = 'ru';
        $type = 'text';

        $stmt = self::$pdo->prepare("INSERT INTO translations (user_id, source_text, translated_text, source_lang, target_lang, type) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $source, $translated, $from, $to, $type]);

        // Получаем историю пользователя
        $stmt = self::$pdo->prepare("SELECT * FROM translations WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        $history = $stmt->fetchAll();

        $this->assertCount(1, $history);
        $this->assertEquals($source, $history[0]['source_text']);
        $this->assertEquals($translated, $history[0]['translated_text']);
        $this->assertEquals($from, $history[0]['source_lang']);
        $this->assertEquals($to, $history[0]['target_lang']);
    }

    public function testDeleteTranslation()
    {
        // Создаём пользователя
        $stmt = self::$pdo->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
        $stmt->execute(['Deleter', 'del@test.com']);
        $userId = self::$pdo->lastInsertId();

        // Сохраняем перевод
        $stmt = self::$pdo->prepare("INSERT INTO translations (user_id, source_text, translated_text, source_lang, target_lang) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$userId, 'Source', 'Target', 'en', 'ru']);
        $transId = self::$pdo->lastInsertId();

        // Удаляем
        $stmt = self::$pdo->prepare("DELETE FROM translations WHERE id = ? AND user_id = ?");
        $stmt->execute([$transId, $userId]);

        // Проверяем, что запись удалена
        $stmt = self::$pdo->prepare("SELECT * FROM translations WHERE id = ?");
        $stmt->execute([$transId]);
        $this->assertEmpty($stmt->fetch());
    }
}