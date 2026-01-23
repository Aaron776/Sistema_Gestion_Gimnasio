<?php
session_start();
require_once '../conexion/bd.php';

// Asegurarse de que el usuario tenga permisos de administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    die('Acceso no autorizado. Se requieren permisos de administrador.');
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_usuario']) && isset($_POST['telefono']) && isset($_POST['nombre']) && isset($_POST['apellido']) && isset($_POST['email'])  && isset($_POST['rol'])){
     if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        // Token inválido, rechazar el registro
        die('Acceso no autorizado.');
    }

    $id_usuario=trim($_POST['id_usuario']);
    $nombre=trim($_POST['nombre']);
    $apellido=trim($_POST['apellido']);
    $email=trim($_POST['email']);
    $rol=trim($_POST['rol']);
    $telefono=trim($_POST['telefono']);
    $errores=[];

    // VALIDACIONES Y SANITIZACIONES
    if(empty($nombre)){
        $errores[] = 'El nombre es obligatorio';
    }elseif(!preg_match('/^[a-zA-Z\s]+$/', $nombre)){
        $errores[] = 'El nombre solo puede contener letras y espacios';
    }

    if(empty($id_usuario)){
        $errores[] = 'El id es obligatorio';
    }elseif(!is_numeric($id_usuario)){
        $errores[] = 'El id debe ser numérico';
    }elseif(!filter_var($id_usuario, FILTER_VALIDATE_INT)){
        $errores[] = 'El id debe ser un entero';
    }elseif($id_usuario <= 0){
        $errores[] = 'El id debe ser mayor a 0';
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


    // Verificar si existe un usuario con el mismo correo o telefono que sea diferente al que se actualiza en ese moomento
    if(empty($errores)){
        $sql = $conexion->prepare("SELECT * FROM usuarios WHERE (email = :email OR telefono = :telefono) AND id != :id_usuario LIMIT 1");
        $sql->bindParam(':email', $email,PDO::PARAM_STR);
        $sql->bindParam(':telefono', $telefono, PDO::PARAM_STR);
        $sql->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $sql->execute();
        $usuario_encontrado = $sql->fetch();
        if($usuario_encontrado){
            $errores[] = 'Otro usuario con ese email o telefono ya existe';
        }
    }

    if(empty($errores)){
        $sql=$conexion->prepare("UPDATE usuarios SET nombre = :nombre, apellido = :apellido, email = :email, telefono = :telefono, rol = :rol WHERE id = :id");
        $sql->bindParam(':id', $id_usuario, PDO::PARAM_INT);
        $sql->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $sql->bindParam(':apellido', $apellido, PDO::PARAM_STR);
        $sql->bindParam(':email', $email, PDO::PARAM_STR);
        $sql->bindParam(':telefono', $telefono, PDO::PARAM_STR);
        $sql->bindParam(':rol', $rol, PDO::PARAM_STR);
        $sql->execute();

        $_SESSION['exito'] = 'Usuario actualizado con exito';
        header('Location: ../gestion_usuarios.php');
        exit;
    }else{
       $_SESSION['errores'] = $errores;
        header('Location: ../editar_usuario.php?id_usuario=' . $id_usuario);
        exit;
    }
}else{
    $_SESSION['errores'] = ['Error al enviar el formulario'];
    header('Location: ../editar_usuario.php?id_usuario=' . $id_usuario);
    exit;
}
?>
