<?php
session_start();

// Configuración de GitHub OAuth
$client_id = 'Ov23lifjnaK1bHrQpcKP';
$redirect_uri = 'http://localhost/Sistemas_Web_PHP/Sistema_Gestion_Gimnasio/controladores/github_callback.php';
$scope = 'user:email';

// Generar estado para CSRF protection
$state = bin2hex(random_bytes(16));
$_SESSION['github_oauth_state'] = $state;

// URL de autorización de GitHub
$auth_url = "https://github.com/login/oauth/authorize?" . http_build_query([
    'client_id' => $client_id,
    'redirect_uri' => $redirect_uri,
    'scope' => $scope,
    'state' => $state
]);

header("Location: $auth_url");
exit;