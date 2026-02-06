<?php
session_start();

// Configuración de Facebook OAuth
$app_id = '1400678928112493';
$redirect_uri = 'http://localhost/Sistemas_Web_PHP/Sistema_Gestion_Gimnasio/controladores/facebook_callback.php';
$scope = 'email,public_profile';

// Generar estado para CSRF protection
$state = bin2hex(random_bytes(16));
$_SESSION['facebook_oauth_state'] = $state;

// URL de autorización de Facebook
$auth_url = "https://www.facebook.com/v18.0/dialog/oauth?" . http_build_query([
    'client_id' => $app_id,
    'redirect_uri' => $redirect_uri,
    'scope' => $scope,
    'state' => $state,
    'response_type' => 'code'
]);

header("Location: $auth_url");
exit;
