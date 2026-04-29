<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/db.php';
require_once __DIR__ . '/functions/jwt.php';
session_start();

// 1. CSRF-защита
if (($_GET['state'] ?? '') !== ($_SESSION['oauth_state'] ?? '')) {
    die('Invalid state — possible CSRF attack');
}
unset($_SESSION['oauth_state']);

// 2. Обмен code → access_token
$ch = curl_init('https://github.com/login/oauth/access_token');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'client_id' => 'Ov23liw16aNwoMpW3r14',
        'client_secret' => '4d25b1db270aafe1b07d9680daa9277c6ead4a2f',
        'code' => $_GET['code'],
    ]),
    CURLOPT_HTTPHEADER => ['Accept: application/json'],
    CURLOPT_RETURNTRANSFER => true,
]);
$response = json_decode(curl_exec($ch), true);
curl_close($ch);

if (empty($response['access_token'])) {
    die('Failed to get access token from GitHub');
}
$access_token = $response['access_token'];

// 3. Запрос профиля
$ch = curl_init('https://api.github.com/user');
curl_setopt_array($ch, [
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer $access_token",
        'User-Agent: Boardy'
    ],
    CURLOPT_RETURNTRANSFER => true,
]);
$profile = json_decode(curl_exec($ch), true);
curl_close($ch);

if (empty($profile['id'])) {
    die('Failed to fetch GitHub profile');
}

// 4. Найти или создать пользователя
$stmt = $pdo->prepare('SELECT id, name FROM users WHERE github_id = ?');
$stmt->execute([$profile['id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $name = $profile['login'] ?? $profile['name'] ?? 'github_user';
    $email = $profile['email'] ?? '';
    $stmt = $pdo->prepare('INSERT INTO users (name, email, github_id, password_hash) VALUES (?, ?, ?, NULL)');
    $stmt->execute([$name, $email, $profile['id']]);
    $user = ['id' => $pdo->lastInsertId(), 'name' => $name];
}

// 5. Создаём сессию
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['name'];
header('Location: /messages.php');
exit;
 
