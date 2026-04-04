<?php
session_start();
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $name = trim($data['name'] ?? '');
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';

    if (!$name || !$email || !$password) {
        http_response_code(400);
        echo json_encode(['error' => 'Заполните все поля']);
        exit;
    }

    // Проверка, существует ли пользователь
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => 'Email уже зарегистрирован']);
        exit;
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)");
    $stmt->execute([$name, $email, $passwordHash]);

    $userId = $pdo->lastInsertId();
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;

    echo json_encode(['success' => true, 'user' => ['name' => $name, 'email' => $email]]);
}