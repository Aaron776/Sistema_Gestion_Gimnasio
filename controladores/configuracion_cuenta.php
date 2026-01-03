<?php
session_start();
require_once "../conexion/bd.php";

if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["nombre"]) && isset($_POST["apellido"]) && isset($_POST["email"]) && isset($_POST["telefono"]) && isset($_POST["id_usuario"])){
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Acceso no autorizado');
        exit();
    }

    $nombre = trim($_POST["nombre"]);
    $apellido = trim($_POST["apellido"]);
    $email = trim($_POST["email"]);
    $telefono = trim($_POST["telefono"]);
    $id_usuario = trim($_POST["id_usuario"]);
    $errores = [];

    // Validaciones y Sanitizacion

    if(empty($nombre) || !preg_match('/^[a-zA-Z\s]+$/', $nombre)){
        $errores[] = "El nombre no es valido";
    }

    if(empty($apellido) || !preg_match('/^[a-zA-Z\s]+$/', $apellido)){
        $errores[] = "El apellido no es valido";
    }

   if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)){
        $errores['email'] = 'El email no es valido';
    }

    if(empty($telefono) || !preg_match('/^[0-9]{10}$/', $telefono)){
        $errores['telefono'] = 'El telefono no puede estar vacío y debe tener exactamente 10 dígitos numéricos';
    }

    if(empty($id_usuario) || !is_numeric($id_usuario) || !filter_var($id_usuario, FILTER_VALIDATE_INT)){
        $errores[] = "El id del usuario no es valido";
    }

    // Verificar si ese usuario con ese email y telefono existe
    $sql = $conexion->prepare("SELECT * FROM usuarios WHERE email = :email AND telefono = :telefono AND id = :id_usuario");
    $sql->bindParam(':email', $email, PDO::PARAM_STR);
    $sql->bindParam(':telefono', $telefono, PDO::PARAM_STR);
    $sql->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $sql->execute();
    $usuario_encontrado = $sql->fetch();
    if(!$usuario_encontrado){
        $errores[] = "El usuario no existe";
    }

    if(empty($errores)){
        $sql = $conexion->prepare("UPDATE usuarios SET nombre = :nombre, apellido = :apellido, email = :email, telefono = :telefono WHERE id = :id_usuario");
        $sql->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $sql->bindParam(':apellido', $apellido, PDO::PARAM_STR);
        $sql->bindParam(':email', $email, PDO::PARAM_STR);
        $sql->bindParam(':telefono', $telefono, PDO::PARAM_STR);
        $sql->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $sql->execute();

        $_SESSION['exito'] = "Datos actualizados correctamente";
        header("Location: ../configuracion_cuenta.php");
        exit();
    }else{
        $_SESSION['errores'] = $errores;
        header("Location: ../configuracion_cuenta.php");
        exit();
    }





}else{
    $_SESSION['errores'] = ['Error al enviar el formulario'];
    header("Location: ../configuracion_cuenta.php");
    exit();
}





?>