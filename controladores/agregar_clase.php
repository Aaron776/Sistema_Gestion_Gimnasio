<?php
session_start();
require_once "../conexion/bd.php";

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre']) && isset($_POST['descripcion']) && isset($_POST['cupo']) && isset($_POST['horario']) && isset($_POST['entrenador'])){
    
    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Acceso no autorizado. Token CSRF inválido.');
    }

    $nombre = trim(filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING));
    $descripcion = trim(filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING));
    $cupo = trim(filter_input(INPUT_POST, 'cupo', FILTER_SANITIZE_NUMBER_INT));
    $horario = trim(filter_input(INPUT_POST, 'horario', FILTER_SANITIZE_STRING));
    $entrenador = trim(filter_input(INPUT_POST, 'entrenador', FILTER_SANITIZE_NUMBER_INT));
    $errores = [];

    // VALIDACIONES Y SANITIZACIONES
    if(empty($nombre)){
        $errores[] = 'El nombre es obligatorio';
    }elseif(!preg_match('/^[a-zA-Z\s]+$/', $nombre)){
        $errores[] = 'El nombre solo puede contener letras y espacios';
    }elseif(strlen($nombre) > 50){
        $errores[] = 'El nombre no puede tener más de 50 caracteres';
    }elseif(strlen($nombre) < 5){
        $errores[] = 'El nombre debe tener al menos 5 caracteres';
    }

    if(empty($descripcion)){
        $errores[] = 'La descripción es obligatoria';
    }elseif(!preg_match('/^[a-zA-Z\s]+$/', $descripcion)){
        $errores[] = 'La descripción solo puede contener letras y espacios';
    }elseif(strlen($descripcion) > 255){
        $errores[] = 'La descripción no puede tener más de 255 caracteres';
    }elseif(strlen($descripcion) < 10){
        $errores[] = 'La descripción debe tener al menos 10 caracteres';
    }

    if(empty($cupo)){
        $errores[] = 'El cupo es obligatorio';
    }elseif(!is_numeric($cupo)){
        $errores[] = 'El cupo debe ser un número';
    }elseif($cupo <= 0){
        $errores[] = 'El cupo debe ser mayor a 0';
    }elseif($cupo>30){
        $errores[] = 'El cupo no puede ser mayor a 30';
    }

    if(empty($horario)){
        $errores[] = 'El horario es obligatorio';
    }

    if(empty($entrenador)){
        $errores[] = 'El entrenador es obligatorio';
    }elseif(!is_numeric($entrenador)){
        $errores[] = 'El entrenador debe ser un número';
    }

    if(empty($errores)){
        $sql = $conexion->prepare("INSERT INTO clases (nombre, descripcion, horario, cupo , id_entrenador) VALUES (:nombre, :descripcion, :horario, :cupo, :entrenador)");
        $sql->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $sql->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
        $sql->bindParam(':cupo', $cupo, PDO::PARAM_INT);
        $sql->bindParam(':horario', $horario, PDO::PARAM_STR);
        $sql->bindParam(':entrenador', $entrenador, PDO::PARAM_INT);
        $sql->execute();

        $_SESSION['exito'] = 'Clase agregada correctamente';
        header('Location: ../gestion_clases.php');
        exit;
    }else{
        $_SESSION['errores'] = $errores;
        header('Location: ../agregar_clase.php');
        exit;
    }
}else{
    $_SESSION['errores'] = ['Error al enviar el formulario'];
    header('Location: ../agregar_clase.php');
    exit;
}

?>