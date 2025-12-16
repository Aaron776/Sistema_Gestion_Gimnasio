<?php
session_start();
require_once '../conexion/bd.php';

// Asegurarse de que el usuario tenga permisos de entrenador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'entrenador') {
    die('Acceso no autorizado. Se requieren permisos de entrenador.');
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['fecha']) && isset($_POST['hora_entrada']) && isset($_POST['hora_salida']) && isset($_POST['id_entrenador'])){
     if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        // Token inválido, rechazar el registro
        die('Acceso no autorizado.');
    }

    $fecha=trim($_POST['fecha']);
    $hora_entrada=trim($_POST['hora_entrada']);
    $hora_salida=trim($_POST['hora_salida']);
    $id_entrenador=trim($_POST['id_entrenador']);
    $errores=[];

    // VALIDACIONES Y SANITIZACIONES
   if(empty($fecha)){
       $errores[]='La fecha es obligatoria';
   }elseif(!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $fecha)){
       $errores[]='La fecha debe tener el formato YYYY-MM-DD';
   }

   if(empty($hora_entrada)){
       $errores[]='La hora de entrada es obligatoria';
   }elseif(!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $hora_entrada)){
       $errores[]='La hora de entrada debe tener el formato HH:MM';
   }

   if(empty($hora_salida)){
       $errores[]='La hora de salida es obligatoria';
   }elseif(!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $hora_salida)){
       $errores[]='La hora de salida debe tener el formato HH:MM';
   }

   if(empty($id_entrenador)){
       $errores[]='El id del entrenador es obligatorio';
   }elseif(!is_numeric($id_entrenador)){
       $errores[]='El id del entrenador debe ser un numero';
   }elseif($id_entrenador!=$_SESSION['id_usuario']){
       $errores[]='El id del entrenador no coincide con el del usuario logueado';
   }elseif($id_entrenador<=0){
       $errores[]='El id del entrenador debe ser mayor a 0';
   }


    if(empty($errores)){
        $sql=$conexion->prepare("INSERT INTO asistencia (fecha, hora_entrada, hora_salida, id_usuario) VALUES (:fecha, :hora_entrada, :hora_salida, :id_usuario)");
        $sql->bindParam(':fecha', $fecha, PDO::PARAM_STR);
        $sql->bindParam(':hora_entrada', $hora_entrada, PDO::PARAM_STR);
        $sql->bindParam(':hora_salida', $hora_salida, PDO::PARAM_STR);
        $sql->bindParam(':id_usuario', $id_entrenador, PDO::PARAM_INT);    
        $sql->execute();

        $_SESSION['exito'] = 'Asistencia registrada con exito';
        header('Location: ../asistencia_entrenador.php');
        exit;
    }else{
        $_SESSION['errores'] = $errores;
        header('Location: ../asistencia_entrenador.php');
        exit;
    }
}else{
    $_SESSION['errores'] = ['Error al enviar el formulario'];
    header('Location: ../asistencia_entrenador.php');
    exit;
}


?>