<?php
session_start();
require_once '../conexion/bd.php';

// Asegurarse de que el usuario tenga permisos de entrenador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'entrenador') {
    die('Acceso no autorizado. Se requieren permisos de entrenador.');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_entrenador']) && isset($_POST['id_socio']) && isset($_POST['descripcion']) && isset($_POST['fecha_asignacion'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        // Token inválido, rechazar el registro
        die('Acceso no autorizado.');
    }
    $id_entrenador = trim($_POST['id_entrenador']);
    $id_socio = trim($_POST['id_socio']);
    $descripcion = trim($_POST['descripcion']);
    $fecha_asignacion = trim($_POST['fecha_asignacion']);
    $errores = [];

    // VALIDACIONES Y SANITIZACIONES
    if (empty($id_entrenador)) {
        $errores[] = 'El id del entrenador es obligatorio';
    } elseif (!is_numeric($id_entrenador)) {
        $errores[] = 'El id del entrenador debe ser un numero';
    } elseif ($id_entrenador < 1) {
        $errores[] = 'El id del entrenador debe ser mayor a 0';
    }

    if (empty($id_socio)) {
        $errores[] = 'El id del socio es obligatorio';
    } elseif (!is_numeric($id_socio)) {
        $errores[] = 'El id del socio debe ser un numero';
    } elseif ($id_socio < 1) {
        $errores[] = 'El id del socio debe ser mayor a 0';
    }

    if (empty($descripcion)) {
        $errores[] = 'La descripcion es obligatoria';
    } elseif (!is_string($descripcion)) {
        $errores[] = 'La descripcion debe ser un string';
    } elseif (strlen($descripcion) < 10) {
        $errores[] = 'La descripcion debe tener al menos 10 caracteres';
    }

    if (empty($fecha_asignacion)) {
        $errores[] = 'La fecha de asignacion es obligatoria';
    } elseif (!is_string($fecha_asignacion)) {
        $errores[] = 'La fecha de asignacion debe ser un string';
    } elseif (!DateTime::createFromFormat('Y-m-d', $fecha_asignacion)) {
        $errores[] = 'La fecha de asignacion debe ser una fecha valida';
    } elseif ($fecha_asignacion < date('Y-m-d')) {
        $errores[] = 'La fecha de asignacion debe ser mayor o igual a la fecha actual';
    }


    if (empty($errores)) {
        $sql = $conexion->prepare("INSERT INTO rutinas(id_entrenador, id_socio, descripcion, fecha_asignacion) VALUES (:id_entrenador, :id_socio, :descripcion, :fecha_asignacion)");
        $sql->bindParam(':id_entrenador', $id_entrenador, PDO::PARAM_STR);
        $sql->bindParam(':id_socio', $id_socio, PDO::PARAM_STR);
        $sql->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
        $sql->bindParam(':fecha_asignacion', $fecha_asignacion, PDO::PARAM_STR);
        $sql->execute();
        
        $_SESSION['exito'] = 'Rutina asignada con exito';
        header('Location: ../asignacion_rutinas.php');
        exit;
    }else{
        $_SESSION['errores'] = $errores;
        header('Location: ../asignacion_rutinas.php');
        exit;
    }
}else{
    $_SESSION['errores'] = ['Error al enviar el formulario'];
    header('Location: ../asignacion_rutinas.php');
    exit;
}
