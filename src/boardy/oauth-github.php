<?php
require_once __DIR__ . '/functions/jwt.php';
session_start();

$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;

$params = http_build_query([
    'client_id' => 'Ov23liw16aNwoMpW3r14',
    'redirect_uri' => 'https://student.terramorf.ai-info.ru/oauth-callback.php',
    'scope' => 'read:user',
    'state' => $state,
]);

header("Location: https://github.com/login/oauth/authorize?$params");
exit;
