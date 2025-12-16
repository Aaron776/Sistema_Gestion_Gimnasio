<?php
session_start();
require_once '../conexion/bd.php';


if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nombre']) && isset($_POST['apellido']) && isset($_POST['email']) && isset($_POST['telefono']) && isset($_POST['password'])){
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $password = trim($_POST['password']);
    $errores=[];

    // VALIDACIONES Y SANITIZACIONES
    if(empty($nombre) || !preg_match('/^[a-zA-Z\s]+$/',$nombre)){
        $errores['nombre'] = 'El nombre no es valido';
    }

    if(empty($apellido) || !preg_match('/^[a-zA-Z\s]+$/', $apellido)){
        $errores['apellido'] = 'El apellido no es valido';
    }

    if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)){
        $errores['email'] = 'El email no es valido';
    }

    if(empty($telefono) || !preg_match('/^[0-9]{10}$/', $telefono)){
        $errores['telefono'] = 'El telefono debe tener exactamente 10 dígitos numéricos';
    }

    if(empty($password) || strlen($password) < 5){
        $errores['password'] = 'La contraseña no es valida';
    }

    // Verificar si existe un usario con el mismo correo
    if(empty($errores)){
        $sql = $conexion->prepare("SELECT * FROM usuarios WHERE email = :email AND telefono=:telefono LIMIT 1");
        $sql->bindParam(':email', $email,PDO::PARAM_STR);
        $sql->bindParam(':telefono', $telefono, PDO::PARAM_STR);
        $sql->execute();
        $usuario_encontrado = $sql->fetch();
        if($usuario_encontrado){
            $errores[] = 'Un usuario con ese email y/o telefono ya existe';
        } 
    }

    // Si no existe errores guardar en la base de datos
    if(empty($errores)){
        $password_hash = password_hash($password, PASSWORD_BCRYPT); // encriptar contraseña
        $sql=$conexion->prepare("INSERT INTO usuarios(nombre, apellido, email, telefono, password) VALUES(:nombre, :apellido, :email, :telefono, :password)");
        $sql->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $sql->bindParam(':apellido', $apellido, PDO::PARAM_STR);
        $sql->bindParam(':email', $email, PDO::PARAM_STR);
        $sql->bindParam(':telefono', $telefono, PDO::PARAM_STR);
        $sql->bindParam(':password', $password_hash, PDO::PARAM_STR);
        $sql->execute();

        $_SESSION['exito'] = 'Usuario registrado con exito';
        header('Location: ../registro.php');
        exit;
    }else{
        $_SESSION['errores'] = $errores;
        header('Location: ../registro.php');
        exit;
    }
}else{
    $_SESSION['errores'] = ['Error al enviar el formulario'];
    header('Location: ../registro.php');
    exit;
}


?>