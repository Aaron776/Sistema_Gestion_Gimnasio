<?php
// Configurar cookies de sesión para OAuth
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => false, // Cambiar a true en producción con HTTPS
    'httponly' => true,
    'samesite' => 'Lax' // Permite cookies en redirects desde sitios externos
]);

session_start();
require_once '../config/oauth_config.php';

$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;

$params = [
    'client_id' => GITHUB_CLIENT_ID,
    'redirect_uri' => GITHUB_REDIRECT_URI,
    'scope' => 'user:email',
    'state' => $state
];

header('Location: https://github.com/login/oauth/authorize?' . http_build_query($params));
exit;
