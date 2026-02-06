<?php
// Configurar cookies de sesión para OAuth (debe coincidir con github_login.php)
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => false, // Cambiar a true en producción con HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();
require_once '../config/oauth_config.php';
require_once '../conexion/bd.php';

// Verificar estado CSRF
if (!isset($_GET['state'])) {
    $_SESSION['errores'] = ['Error de seguridad: No se recibió el parámetro de estado'];
    header('Location: ../login.php');
    exit;
}

if (!isset($_SESSION['oauth_state'])) {
    $_SESSION['errores'] = ['Error de seguridad: La sesión expiró. Por favor, intenta nuevamente.'];
    header('Location: ../login.php');
    exit;
}

if ($_GET['state'] !== $_SESSION['oauth_state']) {
    $_SESSION['errores'] = ['Error de seguridad: El estado no coincide'];
    header('Location: ../login.php');
    exit;
}

// Limpiar el estado usado
unset($_SESSION['oauth_state']);

if (!isset($_GET['code'])) {
    $_SESSION['errores'] = ['Error de autorización'];
    header('Location: ../login.php');
    exit;
}

try {
    // Obtener token
    $ch = curl_init('https://github.com/login/oauth/access_token');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'client_id' => GITHUB_CLIENT_ID,
            'client_secret' => GITHUB_CLIENT_SECRET,
            'code' => $_GET['code'],
            'redirect_uri' => GITHUB_REDIRECT_URI
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Accept: application/json']
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    $tokenData = json_decode($response, true);

    if (!isset($tokenData['access_token'])) {
        throw new Exception('No se obtuvo token');
    }

    // Obtener usuario
    $ch = curl_init('https://api.github.com/user');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $tokenData['access_token'],
            'User-Agent: PowerFit-Gym'
        ]
    ]);
    $userData = json_decode(curl_exec($ch), true);
    curl_close($ch);

    // Obtener email si es necesario
    if (empty($userData['email'])) {
        $ch = curl_init('https://api.github.com/user/emails');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $tokenData['access_token'],
                'User-Agent: PowerFit-Gym'
            ]
        ]);
        $emails = json_decode(curl_exec($ch), true);
        curl_close($ch);

        foreach ($emails as $email) {
            if ($email['primary'] && $email['verified']) {
                $userData['email'] = $email['email'];
                break;
            }
        }
    }

    if (empty($userData['email'])) {
        throw new Exception('No se pudo obtener email');
    }

    // Buscar usuario existente
    $sql = $conexion->prepare("SELECT * FROM usuarios WHERE email = ? OR github_id = ?");
    $sql->execute([$userData['email'], $userData['id']]);
    $usuario = $sql->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        // Actualizar github_id si no existe
        if (empty($usuario['github_id'])) {
            $update = $conexion->prepare("UPDATE usuarios SET github_id = ? WHERE id = ?");
            $update->execute([$userData['id'], $usuario['id']]);
        }
    } else {
        // Crear nuevo usuario
        $nombre_completo = $userData['name'] ?? $userData['login'];
        $partes = explode(' ', $nombre_completo, 2);

        $insert = $conexion->prepare("
            INSERT INTO usuarios (nombre, apellido, email, github_id, rol, fecha_registro) 
            VALUES (?, ?, ?, ?, 'socio', NOW())
        ");
        $insert->execute([
            $partes[0],
            $partes[1] ?? '',
            $userData['email'],
            $userData['id']
        ]);

        $sql = $conexion->prepare("SELECT * FROM usuarios WHERE id = ?");
        $sql->execute([$conexion->lastInsertId()]);
        $usuario = $sql->fetch(PDO::FETCH_ASSOC);
    }

    // Iniciar sesión
    $_SESSION['id_usuario'] = $usuario['id'];
    $_SESSION['nombre'] = $usuario['nombre'];
    $_SESSION['apellido'] = $usuario['apellido'];
    $_SESSION['email'] = $usuario['email'];
    $_SESSION['rol'] = $usuario['rol'];
    $_SESSION['logueado'] = true;

    // Redirigir según rol
    switch ($usuario['rol']) {
        case 'admin':
            header('Location: ../dash_admin.php');
            break;
        case 'entrenador':
            header('Location: ../dash_entrenador.php');
            break;
        default:
            header('Location: ../dash_cliente.php');
    }
    exit;
} catch (Exception $e) {
    $_SESSION['errores'] = ['Error: ' . $e->getMessage()];
    header('Location: ../login.php');
    exit;
}
