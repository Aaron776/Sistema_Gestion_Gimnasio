<?php
session_start();

// Asegurarse de que el usuario tenga permisos de administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    die('Acceso no autorizado. Se requieren permisos de administrador.');
}

require_once '../conexion/bd.php';
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_usuario'])){
    
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        // Token inválido, rechazar el registro
        die('Acceso no autorizado.');
    }

    $id_usuario = trim($_POST['id_usuario']);
    $errores=[];

    // Validaciones y sanitizaciones
    if (empty($id_usuario)) {
        $errores[] = 'El id del usuario es obligatorio';
    }elseif(!is_numeric($id_usuario)){
        $errores[] = 'El id del usuario debe ser numérico';
    }elseif(!filter_var($id_usuario, FILTER_VALIDATE_INT)){
        $errores[] = 'El id del usuario debe ser un entero';
    }elseif($id_usuario <= 0){
        $errores[] = 'El id del usuario debe ser mayor a 0';
    } 
    
    // Verificarsi ese usuario existe en la base de datos
    if(empty($errores)){
        $sql=$conexion->prepare("SELECT id FROM usuarios WHERE id=:id LIMIT 1");
        $sql->bindParam(':id', $id_usuario, PDO::PARAM_INT);
        $sql->execute();
        $usuario = $sql->fetch(PDO::FETCH_OBJ);

        if (!$usuario) {
            $errores[] = 'El usuario no existe en la base de datos';
        }
    }

    if(empty($errores)){
        $sql=$conexion->prepare("DELETE FROM usuarios WHERE id=:id");
        $sql->bindParam(':id', $id_usuario, PDO::PARAM_INT);
        $sql->execute();

        $_SESSION['exito'] = 'El usuario se ha eliminado con exito';
        header('Location: ../gestion_usuarios.php');
        exit;
    }else{
        $_SESSION['errores'] = $errores;
        header('Location: ../gestion_usuarios.php');
        exit;
    }
}else{
    $_SESSION['errores'] = ['Error al enviar el formulario'];
    header('Location: ../gestion_usuarios.php');
    exit;
}




?>
