<?php
session_start();
include_once __DIR__ . '/../conexion/bd.php';

// Configuración
$app_id = '1400678928112493';
$app_secret = 'ecf37e5a63c97defef059ccfe92d4c1c';
$redirect_uri = 'http://localhost/Sistemas_Web_PHP/Sistema_Gestion_Gimnasio/controladores/facebook_callback.php';

// Verificar estado para prevenir CSRF
if (!isset($_GET['state']) || $_GET['state'] !== $_SESSION['facebook_oauth_state']) {
    $_SESSION['errores'] = ["Error de seguridad en la autenticación con Facebook."];
    header("Location: ../login.php");
    exit;
}

// Verificar que tenemos el código
if (!isset($_GET['code'])) {
    // Usuario canceló o hubo error
    if (isset($_GET['error'])) {
        $_SESSION['errores'] = ["Autenticación cancelada: " . htmlspecialchars($_GET['error_description'] ?? 'Error desconocido')];
    } else {
        $_SESSION['errores'] = ["No se recibió el código de autorización de Facebook."];
    }
    header("Location: ../login.php");
    exit;
}

$code = $_GET['code'];

// Intercambiar código por token de acceso
$token_url = 'https://graph.facebook.com/v18.0/oauth/access_token?' . http_build_query([
    'client_id' => $app_id,
    'client_secret' => $app_secret,
    'redirect_uri' => $redirect_uri,
    'code' => $code
]);

$ch = curl_init($token_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200) {
    $_SESSION['errores'] = ["Error al obtener el token de acceso de Facebook."];
    header("Location: ../login.php");
    exit;
}

$token_info = json_decode($response, true);

if (!isset($token_info['access_token'])) {
    $_SESSION['errores'] = ["Error: No se recibió el token de acceso."];
    header("Location: ../login.php");
    exit;
}

$access_token = $token_info['access_token'];

// Obtener información del usuario
$user_url = 'https://graph.facebook.com/v18.0/me?' . http_build_query([
    'fields' => 'id,name,email,picture',
    'access_token' => $access_token
]);

$ch = curl_init($user_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
$user_response = curl_exec($ch);
curl_close($ch);

$facebook_user = json_decode($user_response, true);

// Verificar que tenemos email
if (empty($facebook_user['email'])) {
    $_SESSION['errores'] = ["No se pudo obtener el email de Facebook. Asegúrate de dar permiso de email."];
    header("Location: ../login.php");
    exit;
}

$email = $facebook_user['email'];
$nombre = $facebook_user['name'];
$facebook_id = $facebook_user['id'];

// Buscar o crear usuario
$sql = $conexion->prepare("SELECT id, email, nombre, apellido, rol FROM usuarios WHERE email = :email OR facebook_id = :facebook_id");
$sql->bindParam(':email', $email, PDO::PARAM_STR);
$sql->bindParam(':facebook_id', $facebook_id, PDO::PARAM_STR);
$sql->execute();
$usuario = $sql->fetch(PDO::FETCH_OBJ);

if ($usuario) {
    // Usuario existe, actualizar facebook_id si no lo tiene
    if (empty($usuario->facebook_id)) {
        $update_sql = $conexion->prepare("UPDATE usuarios SET facebook_id = :facebook_id WHERE id = :id");
        $update_sql->bindParam(':facebook_id', $facebook_id, PDO::PARAM_STR);
        $update_sql->bindParam(':id', $usuario->id, PDO::PARAM_INT);
        $update_sql->execute();
    }

    // Iniciar sesión
    $_SESSION['logueado'] = true;
    $_SESSION['id_usuario'] = $usuario->id;
    $_SESSION['email'] = $usuario->email;
    $_SESSION['nombre'] = $usuario->nombre;
    $_SESSION['apellido'] = $usuario->apellido;
    $_SESSION['rol'] = $usuario->rol;
} else {
    // Crear nuevo usuario
    $rol = 'socio'; // Rol por defecto

    // Separar nombre en nombre y apellido si es posible
    $nombre_completo = explode(' ', $nombre, 2);
    $nombre = $nombre_completo[0];
    $apellido = isset($nombre_completo[1]) ? $nombre_completo[1] : '';

    $insert_sql = $conexion->prepare(
        "INSERT INTO usuarios (email, nombre, apellido, rol, facebook_id, password) 
         VALUES (:email, :nombre, :apellido, :rol, :facebook_id, :password)"
    );

    // Generar password aleatorio (no se usará pero el campo puede ser requerido)
    $random_password = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);

    $insert_sql->bindParam(':email', $email, PDO::PARAM_STR);
    $insert_sql->bindParam(':nombre', $nombre, PDO::PARAM_STR);
    $insert_sql->bindParam(':apellido', $apellido, PDO::PARAM_STR);
    $insert_sql->bindParam(':rol', $rol, PDO::PARAM_STR);
    $insert_sql->bindParam(':facebook_id', $facebook_id, PDO::PARAM_STR);
    $insert_sql->bindParam(':password', $random_password, PDO::PARAM_STR);

    if ($insert_sql->execute()) {
        $nuevo_id = $conexion->lastInsertId();

        $_SESSION['logueado'] = true;
        $_SESSION['id_usuario'] = $nuevo_id;
        $_SESSION['email'] = $email;
        $_SESSION['nombre'] = $nombre;
        $_SESSION['apellido'] = $apellido;
        $_SESSION['rol'] = $rol;
    } else {
        $_SESSION['errores'] = ["Error al crear el usuario con Facebook."];
        header("Location: ../login.php");
        exit;
    }
}

// Limpiar estado de sesión
unset($_SESSION['facebook_oauth_state']);

// Redirigir según el rol
switch ($_SESSION['rol']) {
    case 'admin':
        header("Location: ../dash_admin.php");
        exit();
    case 'entrenador':
        header("Location: ../dash_entrenador.php");
        exit();
    case 'socio':
        header("Location: ../dash_cliente.php");
        exit();
    default:
        header("Location: ../login.php");
        exit();
}
