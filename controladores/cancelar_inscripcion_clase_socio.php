<?php
session_start();
require_once "../conexion/bd.php";

// Asegurarse de que el usuario tenga permisos de socio
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'socio') {
    die('Acceso no autorizado. Se requieren permisos de socio.');
}

if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id_inscripcion"])){
     if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        // Token inválido, rechazar el registro
        die('Acceso no autorizado.');
    }

    $id_inscripcion = trim($_POST["id_inscripcion"]);
    $errores = [];

    //Validaciones y Sanitizaciones
    
    if(empty($id_inscripcion)){
        $errores[] = 'El id de la inscripcion es obligatorio';
    }elseif(!is_numeric($id_inscripcion)){
        $errores[] = 'El id de la inscripcion debe ser un numero';
    }elseif($id_inscripcion <= 0){
        $errores[] = 'El id de la inscripcion debe ser mayor a 0';
    }

    // Validar que exista esa inscripcion
    if(empty($errores)){
        $sql = $conexion->prepare("SELECT * FROM inscripciones WHERE id = :id_inscripcion");
        $sql->bindParam(':id_inscripcion', $id_inscripcion, PDO::PARAM_INT);
        $sql->execute();
        $inscripcion = $sql->fetch(PDO::FETCH_OBJ);

        if(!$inscripcion){
            $errores[] = 'La inscripcion no existe';
        }
    }

    // Si no hay errores, registrar la inscripcion
    if(empty($errores)){
        $sql = $conexion->prepare("UPDATE inscripciones SET estado = 'cancelado' WHERE id = :id_inscripcion");
        $sql->bindParam(':id_inscripcion', $id_inscripcion, PDO::PARAM_INT);
        $sql->execute();

        $_SESSION['exito'] = 'Inscripcion cancelada exitosamente';
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