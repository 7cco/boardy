<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

require __DIR__ . '/../functions/jwt.php';
require __DIR__ . '/../db.php';

header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$jwt = generate_jwt(
    $_SESSION['user_id'],
    $_SESSION['user_name']);

header('Content-Type: application/json');
echo json_encode(['token' => $jwt]);
