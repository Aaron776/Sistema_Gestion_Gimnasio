<?php
session_start();
require_once '../conexion/bd.php';

// Asegurarse de que el usuario tenga permisos de administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    die('Acceso no autorizado. Se requieren permisos de administrador.');
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_membresia'])){
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        // Token inválido, rechazar el registro
        die('Acceso no autorizado.');
    }

    $id_membresia = trim($_POST['id_membresia']);
    $errores=[];

    // Validaciones y sanitizaciones
    if (empty($id_membresia)) {
        $errores[] = 'El id de la membresia es obligatorio';
    }elseif(!is_numeric($id_membresia)){
        $errores[] = 'El id de la membresia debe ser numérico';
    }elseif(!filter_var($id_membresia, FILTER_VALIDATE_INT)){
        $errores[] = 'El id de la membresia debe ser un entero';
    }elseif($id_membresia <= 0){
        $errores[] = 'El id de la membresia debe ser mayor a 0';
    } 

    // Verificarsi si esa membresia existe en la base de datos
    if(empty($errores)){
        $sql=$conexion->prepare("SELECT id FROM membresias WHERE id=:id LIMIT 1");
        $sql->bindParam(':id', $id_membresia, PDO::PARAM_INT);
        $sql->execute();
        $membresia = $sql->fetch(PDO::FETCH_OBJ);

        if (!$membresia) {
            $errores[] = 'La membresia no existe en la base de datos';
        }
    }

    if(empty($errores)){
        $sql=$conexion->prepare("DELETE FROM membresias WHERE id=:id");
        $sql->bindParam(':id', $id_membresia, PDO::PARAM_INT);
        $sql->execute();

        $_SESSION['exito'] = 'La membresia se ha eliminado con exito';
        header('Location: ../gestion_membresias.php');
        exit;
    }else{
        $_SESSION['errores'] = $errores;
        header('Location: ../gestion_membresias.php');
        exit;
    }
}else{
    $_SESSION['errores'] = ['Error al enviar el formulario'];
    header('Location: ../gestion_membresias.php');
    exit;
}



?>