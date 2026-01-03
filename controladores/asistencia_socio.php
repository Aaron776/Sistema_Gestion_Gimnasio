<?php
session_start();
require_once '../conexion/bd.php';

// Asegurarse de que el usuario tenga permisos de socio
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'socio') {
    die('Acceso no autorizado. Se requieren permisos de socio.');
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['fecha']) && isset($_POST['hora_entrada']) && isset($_POST['hora_salida']) && isset($_POST['id_socio'])){
     if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        // Token inválido, rechazar el registro
        die('Acceso no autorizado.');
    }

    $fecha=trim($_POST['fecha']);
    $hora_entrada=trim($_POST['hora_entrada']);
    $hora_salida=trim($_POST['hora_salida']);
    $id_socio=trim($_POST['id_socio']);
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

   if(empty($id_socio)){
       $errores[]='El id del socio es obligatorio';
   }elseif(!is_numeric($id_socio)){
       $errores[]='El id del socio debe ser un numero';
   }elseif($id_socio!=$_SESSION['id_usuario']){
       $errores[]='El id del socio no coincide con el del usuario logueado';
   }elseif($id_socio<=0){
       $errores[]='El id del socio debe ser mayor a 0';
   }

   // Verificar que el socio exista
   if(empty($errores)){
       $sql=$conexion->prepare("SELECT * FROM usuarios WHERE id = :id_socio AND rol = 'socio'");
       $sql->bindParam(':id_socio', $id_socio, PDO::PARAM_INT);
       $sql->execute();
       $socio = $sql->fetch(PDO::FETCH_OBJ);
       if(!$socio){
           $errores[]='El socio no existe';
       }
   }


    if(empty($errores)){
        $sql=$conexion->prepare("INSERT INTO asistencia (id_usuario,fecha,hora_entrada,hora_salida) VALUES (:id_usuario,:fecha,:hora_entrada,:hora_salida)");
        $sql->bindParam(':id_usuario', $id_socio, PDO::PARAM_INT);    
        $sql->bindParam(':fecha', $fecha, PDO::PARAM_STR);
        $sql->bindParam(':hora_entrada', $hora_entrada, PDO::PARAM_STR);
        $sql->bindParam(':hora_salida', $hora_salida, PDO::PARAM_STR);
        $sql->execute();
        
        $_SESSION['exito'] = 'Asistencia registrada con exito';
        header('Location: ../asistencia_socio.php');
        exit;
    }else{
        $_SESSION['errores'] = $errores;
        header('Location: ../asistencia_socio.php');
        exit;
    }
}else{
    $_SESSION['errores'] = ['Error al enviar el formulario'];
    header('Location: ../asistencia_socio.php');
    exit;
}


?>