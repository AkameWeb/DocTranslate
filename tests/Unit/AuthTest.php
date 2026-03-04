<?php
namespace Tests\Unit;

use Tests\TestCase;

class AuthTest extends TestCase
{
    public function testUserRegistration()
    {
        // Подготовка данных
        $name = 'Test User';
        $email = 'test@example.com';
        $password = 'secret123';

        // Вставка пользователя
        $stmt = self::$pdo->prepare("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)");
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt->execute([$name, $email, $hash]);

        // Проверка, что пользователь добавлен
        $stmt = self::$pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        $this->assertNotEmpty($user);
        $this->assertEquals($name, $user['name']);
        $this->assertTrue(password_verify($password, $user['password_hash']));
    }

    public function testUserLoginSuccess()
    {
        // Создаём пользователя
        $email = 'login@test.com';
        $password = 'pass123';
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = self::$pdo->prepare("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)");
        $stmt->execute(['Login User', $email, $hash]);

        // Проверка пароля
        $stmt = self::$pdo->prepare("SELECT password_hash FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        $this->assertTrue(password_verify($password, $user['password_hash']));
    }

    public function testUserLoginWrongPassword()
    {
        $email = 'wrong@test.com';
        $hash = password_hash('correct', PASSWORD_DEFAULT);
        $stmt = self::$pdo->prepare("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)");
        $stmt->execute(['Wrong', $email, $hash]);

        // Проверка с неверным паролем
        $stmt = self::$pdo->prepare("SELECT password_hash FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        $this->assertFalse(password_verify('wrongpass', $user['password_hash']));
    }
}