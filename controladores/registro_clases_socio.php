<?php
session_start();
require_once "../conexion/bd.php";

// Asegurarse de que el usuario tenga permisos de socio
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'socio') {
    die('Acceso no autorizado. Se requieren permisos de socio.');
}

if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id_socio"]) && isset($_POST["clase"])){
     if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        // Token inválido, rechazar el registro
        die('Acceso no autorizado.');
    }

    $id_socio = trim($_POST["id_socio"]);
    $id_clase = trim($_POST["clase"]);
    $errores = [];

    //Validaciones y Sanitizaciones
    
    if(empty($id_socio)){
        $errores[] = 'El id del socio es obligatorio';
    }elseif(!is_numeric($id_socio)){
        $errores[] = 'El id del socio debe ser un numero';
    }elseif($id_socio <= 0){
        $errores[] = 'El id del socio debe ser mayor a 0';
    }

    if(empty($id_clase)){
        $errores[] = 'El id de la clase es obligatorio';
    }elseif(!is_numeric($id_clase)){
        $errores[] = 'El id de la clase debe ser un numero';
    }elseif($id_clase <= 0){
        $errores[] = 'El id de la clase debe ser mayor a 0';
    }

    // Validar que exista esa clases
    if(empty($errores)){
        $sql = $conexion->prepare("SELECT * FROM clases WHERE id = :id_clase");
        $sql->bindParam(':id_clase', $id_clase, PDO::PARAM_INT);
        $sql->execute();
        $clase = $sql->fetch(PDO::FETCH_OBJ);

        if(!$clase){
            $errores[] = 'La clase no existe';
        }
    }

    // Validar que exista ese socio
    if(empty($errores)){
        $sql = $conexion->prepare("SELECT * FROM usuarios WHERE id = :id_socio");
        $sql->bindParam(':id_socio', $id_socio, PDO::PARAM_INT);
        $sql->execute();
        $socio = $sql->fetch(PDO::FETCH_OBJ);

        if(!$socio){
            $errores[] = 'El socio no existe';
        }
    }

    // Validar que el socio no este inscrito en esa clase
    if(empty($errores)){
        $sql = $conexion->prepare("SELECT * FROM inscripciones WHERE id_socio = :id_socio AND id_clase = :id_clase");
        $sql->bindParam(':id_socio', $id_socio, PDO::PARAM_INT);
        $sql->bindParam(':id_clase', $id_clase, PDO::PARAM_INT);
        $sql->execute();
        $inscripcion = $sql->fetch(PDO::FETCH_OBJ);

        if($inscripcion){
            $errores[] = 'El socio ya esta inscrito en esa clase';
        }
    }

    // Si no hay errores, registrar la inscripcion
    if(empty($errores)){
        $sql = $conexion->prepare("INSERT INTO inscripciones (id_clase, id_socio) VALUES (:id_clase, :id_socio)");
        $sql->bindParam(':id_clase', $id_clase, PDO::PARAM_INT);
        $sql->bindParam(':id_socio', $id_socio, PDO::PARAM_INT);
        $sql->execute();

        $_SESSION['exito'] = 'Inscripcion exitosa';
        header('Location: ../clases_socio.php');
        exit;
    }else{
        $_SESSION['errores'] = $errores;
        header('Location: ../clases_socio.php');
        exit;
    }
    
}else{
    $_SESSION['errores'] = ['Error al enviar el formulario'];
    header('Location: ../clases_socio.php');
    exit;
}


?>