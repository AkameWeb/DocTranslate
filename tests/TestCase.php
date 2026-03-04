<?php
namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use PDO;

abstract class TestCase extends BaseTestCase
{
    protected static $pdo;

    public static function setUpBeforeClass(): void
    {
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $dbname = $_ENV['DB_NAME'] ?? 'doclang';
        $user = $_ENV['DB_USER'] ?? 'root';
        $pass = $_ENV['DB_PASS'] ?? '';

        self::$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
        self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    protected function setUp(): void
    {
        // Очищаем таблицы перед каждым тестом
        self::$pdo->exec("TRUNCATE TABLE users");
        self::$pdo->exec("TRUNCATE TABLE translations");
    }
}