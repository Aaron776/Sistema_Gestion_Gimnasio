<?php
session_start();
require_once '../conexion/bd.php';

// Asegurarse de que el usuario tenga permisos de administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    die('Acceso no autorizado. Se requieren permisos de administrador.');
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_clase'])){
    
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        // Token inválido, rechazar el registro
        die('Acceso no autorizado.');
    }

    $id_clase = trim($_POST['id_clase']);
    $errores = [];

    //Validaciones
    if(empty($id_clase)){
        $errores[] = 'El ID de la clase es obligatorio.';
    }elseif(!is_numeric($id_clase)){
        $errores[] = 'El ID de la clase debe ser numérico.';
    }elseif(!filter_var($id_clase, FILTER_VALIDATE_INT)){
        $errores[] = 'El ID de la clase debe ser un entero.';
    }elseif($id_clase <= 0){
        $errores[] = 'El ID de la clase debe ser mayor a 0.';
    }

    // Verificar si esa clase existe en la base de datos
    if(empty($errores)){
        $sql = $conexion->prepare("SELECT * FROM clases WHERE id = :id_clase");
        $sql->bindValue(':id_clase', $id_clase, PDO::PARAM_INT);
        $sql->execute();
        $clase = $sql->fetch(PDO::FETCH_OBJ);

        if(!$clase){
            $errores[] = 'La clase no existe en la base de datos.';
        } 
    }

    if(empty($errores)){
        $sql = $conexion->prepare("DELETE FROM clases WHERE id = :id_clase");
        $sql->bindValue(':id_clase', $id_clase, PDO::PARAM_INT);
        $sql->execute();

        $_SESSION['exito'] = 'La clase se ha eliminado con exito.';
        header('Location: ../gestion_clases.php');
        exit;
    }else{
        $_SESSION['errores'] =  $errores;
        header('Location: ../gestion_clases.php');
        exit;
    }

}else{
    $_SESSION['errores'] = ['Error al enviar el formulario'];
    header('Location: ../gestion_clases.php');
    exit;
}


?>