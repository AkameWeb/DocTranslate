<?php
session_start();
require_once 'vendor/autoload.php';
require_once 'config/db.php';               // если используете БД
require_once 'google-config.php';              // ваш файл с $clientID, $clientSecret, $redirectUri

use League\OAuth2\Client\Provider\Google;

$provider = new Google([
    'clientId'     => $clientID,
    'clientSecret' => $clientSecret,
    'redirectUri'  => $redirectUri,
]);

// Если нет кода - отправляем на Google
if (!isset($_GET['code'])) {
    $authUrl = $provider->getAuthorizationUrl();
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: ' . $authUrl);
    exit;
}

// Проверка state (защита от CSRF)
if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
    unset($_SESSION['oauth2state']);
    die('Invalid state');
}

// Получаем токен и данные пользователя
try {
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);

    $googleUser = $provider->getResourceOwner($token);

    $googleId = $googleUser->getId();
    $email = $googleUser->getEmail();
    $name = $googleUser->getName();
    $avatar = $googleUser->getAvatar();

    // Работа с базой данных
    // Проверяем, есть ли пользователь с таким google_id или email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE google_id = ? OR email = ?");
    $stmt->execute([$googleId, $email]);
    $user = $stmt->fetch();

    if ($user) {
        // Если пользователь найден по email, но google_id не был заполнен – обновляем
        if (!$user['google_id']) {
            $update = $pdo->prepare("UPDATE users SET google_id = ? WHERE id = ?");
            $update->execute([$googleId, $user['id']]);
        }
        $userId = $user['id'];
        $userName = $user['name'];
    } else {
        // Создаём нового пользователя
        $stmt = $pdo->prepare("INSERT INTO users (email, name, google_id, avatar) VALUES (?, ?, ?, ?)");
        $stmt->execute([$email, $name, $googleId, $avatar]);
        $userId = $pdo->lastInsertId();
        $userName = $name;
    }

    // Сохраняем в сессию
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_name'] = $userName;
    $_SESSION['user_email'] = $email;

    header('Location: index.php');
    exit;

} catch (Exception $e) {
    die('Ошибка получения данных от Google: ' . $e->getMessage());
}