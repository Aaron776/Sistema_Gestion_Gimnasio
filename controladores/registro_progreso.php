<?php
session_start();
require_once '../conexion/bd.php';

// Asegurarse de que el usuario tenga permisos de entrenador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'entrenador') {
    die('Acceso no autorizado. Se requieren permisos de entrenador.');
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_socio']) && isset($_POST['fecha_registro']) && isset($_POST['peso']) && isset($_POST['grasa_corporal']) && isset($_POST['masa_muscular'])){
     if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        // Token inválido, rechazar el registro
        die('Acceso no autorizado.');
    }

    $id_socio=trim($_POST['id_socio']);
    $fecha_registro=trim($_POST['fecha_registro']);
    $peso=trim($_POST['peso']);
    $grasa_corporal=trim($_POST['grasa_corporal']);
    $masa_muscular=trim($_POST['masa_muscular']);
    $errores=[];

    // VALIDACIONES Y SANITIZACIONES
    if(empty($id_socio)){
        $errores[] = 'El id del socio es obligatorio';
    }elseif(!is_numeric($id_socio)){
        $errores[] = 'El id del socio debe ser un numero';
    }elseif($id_socio<1){
        $errores[] = 'El id del socio debe ser mayor a 0';
    }

    if(empty($fecha_registro)){
        $errores[] = 'La fecha es obligatoria';
    }

    if(empty($peso)){
        $errores[] = 'El peso es obligatorio';
    }elseif(!is_numeric($peso)){
        $errores[] = 'El peso debe ser un numero';
    }elseif($peso<1){
        $errores[] = 'El peso debe ser mayor a 0';
    }

    if(empty($grasa_corporal)){
        $errores[] = 'La grasa corporal es obligatoria';
    }elseif(!is_numeric($grasa_corporal)){
        $errores[] = 'La grasa corporal debe ser un numero';
    }elseif($grasa_corporal<1){
        $errores[] = 'La grasa corporal debe ser mayor a 0';
    }

    if(empty($masa_muscular)){
        $errores[] = 'La masa muscular es obligatoria';
    }elseif(!is_numeric($masa_muscular)){
        $errores[] = 'La masa muscular debe ser un numero';
    }elseif($masa_muscular<1){
        $errores[] = 'La masa muscular debe ser mayor a 0';
    }


    // Verificar si existe ese socio
    if(empty($errores)){
        $sql = $conexion->prepare("SELECT * FROM usuarios WHERE id = :id LIMIT 1");
        $sql->bindParam(':id', $id_socio,PDO::PARAM_STR);
        $sql->execute();
        $socio_encontrado = $sql->fetch();
        if(!$socio_encontrado){
            $errores[] = 'El id del socio no existe';
        }
    }

    if(empty($errores)){
        $sql=$conexion->prepare("INSERT INTO progreso (id_socio, peso, grasa_corporal, masa_muscular, fecha_registro) VALUES (:id_socio, :fecha_registro, :peso, :grasa_corporal, :masa_muscular)");
        $sql->bindParam(':id_socio', $id_socio, PDO::PARAM_STR);
        $sql->bindParam(':fecha_registro', $fecha_registro, PDO::PARAM_STR);
        $sql->bindParam(':peso', $peso, PDO::PARAM_STR);
        $sql->bindParam(':grasa_corporal', $grasa_corporal, PDO::PARAM_STR);
        $sql->bindParam(':masa_muscular', $masa_muscular, PDO::PARAM_STR);
        $sql->execute();

        $_SESSION['exito'] = 'Progreso registrado con exito';
        header('Location: ../registro_progreso.php');
        exit;
    }else{
        $_SESSION['errores'] = $errores;
        header('Location: ../registro_progreso.php');
        exit;
    }
}else{
    $_SESSION['errores'] = ['Error al enviar el formulario'];
    header('Location: ../registro_progreso.php');
    exit;
}


?>