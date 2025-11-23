<?php
session_start();
require_once '../conexion/bd.php';

// Asegurarse de que el usuario tenga permisos de administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    die('Acceso no autorizado. Se requieren permisos de administrador.');
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nombre']) && isset($_POST['descripcion']) && isset($_POST['duracion']) && isset($_POST['precio'])){

    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Acceso no autorizado. Token CSRF inválido.');
    }

    // Sanitizar y trim a las entradas
    $nombre = trim(filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING));
    $descripcion = trim(filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING));
    $duracion = trim(filter_input(INPUT_POST, 'duracion', FILTER_SANITIZE_NUMBER_INT));
    $precio = trim(filter_input(INPUT_POST, 'precio', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
    $errores = [];

    // VALIDACIONES Y SANITIZACIONES
    if(empty($nombre)){
        $errores[] = 'El nombre es obligatorio';
    }elseif(!preg_match('/^[a-zA-Z\s]+$/', $nombre)){
        $errores[] = 'El nombre solo puede contener letras y espacios';
    }

    if(empty($descripcion)){
        $errores[] = 'La descripción es obligatoria';
    }

    if(empty($duracion)){
        $errores[] = 'La duración es obligatoria';
    }elseif(!is_numeric($duracion)){
        $errores[] = 'La duración debe ser un número';
    }elseif(!filter_var($duracion, FILTER_VALIDATE_INT) || $duracion <= 0){
        $errores[] = 'La duración debe ser un entero';
    }

    if(empty($precio)){
        $errores[] = 'El precio es obligatorio';
    }elseif(!is_numeric($precio)){
        $errores[] = 'El precio debe ser un número';
    }elseif(!filter_var($precio, FILTER_VALIDATE_FLOAT) || $precio <= 0){
        $errores[] = 'El precio debe ser un número decimal';
    }

    if(empty($errores)){
        $stmt = $conexion->prepare("INSERT INTO membresias (nombre, descripcion, duracion_dias, precio) VALUES (:nombre, :descripcion, :duracion, :precio)");
        $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $stmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
        $stmt->bindParam(':duracion', $duracion, PDO::PARAM_INT);
        $stmt->bindParam(':precio', $precio, PDO::PARAM_STR); 
        $stmt->execute();
        $_SESSION['exito'] = 'Membresía agregada correctamente';
        header("Location: ../agregar_membresia.php");
        exit();
    }else{
        $_SESSION['errores'] = $errores;
        // Guardar los datos del formulario en la sesión para repoblar el formulario
        $_SESSION['form_data'] = $_POST;
        header("Location: ../agregar_membresia.php");
        exit();
    }
}else{
    $_SESSION['errores'] = ['Error al enviar el formulario'];
    header("Location: ../agregar_membresia.php");
    exit();

}


?>