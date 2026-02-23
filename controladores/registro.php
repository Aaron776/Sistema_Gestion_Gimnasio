<?php
session_start();
require_once '../conexion/bd.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nombre']) && isset($_POST['apellido']) && isset($_POST['email']) && isset($_POST['telefono']) && isset($_POST['password'])) {
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['errores'] = ['Error de validación CSRF. Intente de nuevo.'];
        header('Location: ../registro.php');
        exit;
    }
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $password = trim($_POST['password']);
    $errores = [];

    // VALIDACIONES Y SANITIZACIONES
    if (empty($nombre) || !preg_match('/^[\p{L}\s\']+$/u', $nombre)) {
        $errores['nombre'] = 'El nombre no es válido. Solo se permiten letras, espacios y apóstrofes.';
    }

    if (empty($apellido) || !preg_match('/^[\p{L}\s\']+$/u', $apellido)) {
        $errores['apellido'] = 'El apellido no es válido. Solo se permiten letras, espacios y apóstrofes.';
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores['email'] = 'El email no es válido.';
    } elseif (strlen($email) > 255) {
        $errores['email'] = 'El email es demasiado largo.';
    }

    if (empty($telefono) || !preg_match('/^[0-9]{10}$/', $telefono)) {
        $errores['telefono'] = 'El teléfono debe tener exactamente 10 dígitos numéricos';
    }

    if (empty($password) || strlen($password) < 5) {
        $errores['password'] = 'La contraseña debe tener al menos 5 caracteres.';
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $password)) {
        $errores[] = "La contraseña debe contener al menos una mayúscula, una minúscula y un número.";
    }

    // Verificar si existe un usuario con el mismo correo
    if (empty($errores)) {
        $sql = $conexion->prepare("SELECT * FROM usuarios WHERE email = :email OR telefono=:telefono LIMIT 1");
        $sql->bindParam(':email', $email, PDO::PARAM_STR);
        $sql->bindParam(':telefono', $telefono, PDO::PARAM_STR);
        $sql->execute();
        $usuario_encontrado = $sql->fetch();
        if ($usuario_encontrado) {
            $errores[] = 'Un usuario con ese email y/o telefono ya existe';
        }
    }

    // Si no existe errores guardar en la base de datos
    if (empty($errores)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT); // encriptar contraseña
        $sql = $conexion->prepare("INSERT INTO usuarios(nombre, apellido, email, telefono, password) VALUES(:nombre, :apellido, :email, :telefono, :password)");
        $sql->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $sql->bindParam(':apellido', $apellido, PDO::PARAM_STR); // BUG CORREGIDO: Se estaba guardando un string literal
        $sql->bindParam(':email', $email, PDO::PARAM_STR);
        $sql->bindParam(':telefono', $telefono, PDO::PARAM_STR);
        $sql->bindParam(':password', $password_hash, PDO::PARAM_STR);
        if ($sql->execute()) {
            $id_usuario = $conexion->lastInsertId(); // Obtener el ID del último usuario recién registrado

            // Iniciar sesión automáticamente si se registra un socio nuevo
            $_SESSION['id_usuario'] = $id_usuario;
            $_SESSION['nombre'] = $nombre;
            $_SESSION['apellido'] = $apellido;
            $_SESSION['email'] = $email;
            $_SESSION['rol'] = 'socio'; // Por defecto es socio segun la lógica del sistema
            $_SESSION['logueado'] = true;

            header('Location: ../dash_cliente.php');
            exit;
        } else {
            $_SESSION['errores'] = ['Error al registrar el usuario'];
            header('Location: ../registro.php');
            exit;
        }
    } else {
        $_SESSION['errores'] = $errores;
        header('Location: ../registro.php');
        exit;
    }
} else {
    $_SESSION['errores'] = ['Error al enviar el formulario'];
    header('Location: ../registro.php');
    exit;
}
