<?php
session_start();
require_once '../conexion/bd.php';

// Asegurarse de que el usuario tenga permisos de administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    die('Acceso no autorizado. Se requieren permisos de administrador.');
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['telefono']) && isset($_POST['nombre']) && isset($_POST['apellido']) && isset($_POST['email']) && isset($_POST['password']) && isset($_POST['rol'])){
     if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        // Token inválido, rechazar el registro
        die('Acceso no autorizado.');
    }

    $nombre=trim($_POST['nombre']);
    $apellido=trim($_POST['apellido']);
    $email=trim($_POST['email']);
    $password=trim($_POST['password']);
    $rol=trim($_POST['rol']);
    $telefono=trim($_POST['telefono']);
    $errores=[];

    // VALIDACIONES Y SANITIZACIONES
    if(empty($nombre)){
        $errores[] = 'El nombre es obligatorio';
    }elseif(!preg_match('/^[a-zA-Z\s]+$/', $nombre)){
        $errores[] = 'El nombre solo puede contener letras y espacios';
    }

    if(empty($apellido)){
        $errores[] = 'El apellido es obligatorio';
    }elseif(!preg_match('/^[a-zA-Z\s]+$/', $apellido)){
        $errores[] = 'El apellido solo puede contener letras y espacios';
    }

    if(empty($email)){
        $errores[] = 'El email es obligatorio';
    }elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $errores[] = 'El email no es valido';
    }

    if(empty($password)){
        $errores[] = 'La contraseña es obligatoria';
    }elseif(strlen($password) < 5){
        $errores[] = 'La contraseña debe tener al menos 5 caracteres';
    }

    $roles=['admin','entrenador'];
    if(empty($rol)){
        $errores[] = 'El rol es obligatorio';
    }elseif(!in_array($rol, $roles)){
        $errores[] = 'El rol no es valido';
    }

    if(empty($telefono)){
        $errores[] = 'El telefono es obligatorio';
    }elseif(!preg_match('/^[0-9]{10}$/', $telefono)){
        $errores[] = 'El telefono debe tener exactamente 10 digitos numericos';
    }


    // Verificar si existe un usuario con el mismo correo o telefono
    if(empty($errores)){
        $sql = $conexion->prepare("SELECT * FROM usuarios WHERE email = :email OR telefono = :telefono LIMIT 1");
        $sql->bindParam(':email', $email,PDO::PARAM_STR);
        $sql->bindParam(':telefono', $telefono, PDO::PARAM_STR);
        $sql->execute();
        $usuario_encontrado = $sql->fetch();
        if($usuario_encontrado){
            $errores[] = 'El email o el telefono ya estan registrados';
        }
    }

    if(empty($errores)){
        $password_hash = password_hash($password, PASSWORD_DEFAULT); // encriptar contraseña
        $sql=$conexion->prepare("INSERT INTO usuarios (nombre, apellido, email, password, telefono, rol) VALUES (:nombre, :apellido, :email, :password, :telefono, :rol)");
        $sql->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $sql->bindParam(':apellido', $apellido, PDO::PARAM_STR);
        $sql->bindParam(':email', $email, PDO::PARAM_STR);
        $sql->bindParam(':telefono', $telefono, PDO::PARAM_STR);
        $sql->bindParam(':password', $password_hash, PDO::PARAM_STR);
        $sql->bindParam(':rol', $rol, PDO::PARAM_STR);
        $sql->execute();

        $_SESSION['exito'] = 'Usuario registrado con exito';
        header('Location: ../gestion_usuarios.php');
        exit;
    }else{
        $_SESSION['errores'] = $errores;
        header('Location: ../agregar_usuario.php');
        exit;
    }
}else{
    $_SESSION['errores'] = ['Error al enviar el formulario'];
    header('Location: ../agregar_usuario.php');
    exit;
}


?>