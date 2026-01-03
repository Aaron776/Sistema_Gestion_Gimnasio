<?php
session_start();
require_once "../conexion/bd.php";

// Asegurarse de que el usuario tenga permisos de socio
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'socio') {
    die('Acceso no autorizado. Se requieren permisos de socio.');
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_membresia_usuario']) && isset($_POST['motivo'])){
     if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        // Token inválido, rechazar el registro
        die('Acceso no autorizado.');
    }

    $id_membresia_usuario = $_POST['id_membresia_usuario'];
    $motivo = $_POST['motivo'];
    $errores = [];

    // Validaciones
    if (empty($id_membresia_usuario)) {
        $errores[] = 'El id de la membresia es obligatorio';
    }elseif(!is_numeric($id_membresia_usuario)){
        $errores[] = 'El id de la membresia debe ser un número';
    }elseif($id_membresia_usuario <= 0){
        $errores[] = 'El id de la membresia debe ser mayor a 0';
    }

    if (empty($motivo)) {
        $errores[] = 'El motivo de la cancelación es obligatorio';
    }elseif(!is_string($motivo)){
        $errores[] = 'El motivo de la cancelación debe ser una cadena de texto';
    }elseif(strlen($motivo) < 20){
        $errores[] = 'El motivo de la cancelación debe tener al menos 20 caracteres';
    }

    // Verificar si exite esa membresia asignada a ese socio
    if(empty($errores)){
        $sql=$conexion->prepare("SELECT * FROM membresia_usuario WHERE id = :id_membresia_usuario");
        $sql->bindParam(':id_membresia_usuario', $id_membresia_usuario,PDO::PARAM_INT);
        $sql->execute();
        $membresia = $sql->fetch(PDO::FETCH_OBJ);

        if (empty($membresia)) {
            $errores[] = 'No se encontró la membresia del socio';
        }
    }

    if (empty($errores)) {
        try {
            $sql = "INSERT INTO solicitud_cancelacion (id_membresia_usuario, motivo) VALUES (:id_membresia_usuario, :motivo)";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':id_membresia_usuario', $id_membresia_usuario,PDO::PARAM_INT);
            $stmt->bindParam(':motivo', $motivo,PDO::PARAM_STR);
            $stmt->execute();

            $_SESSION['exito'] = 'Solicitud de cancelación enviada correctamente';
            header("Location: ../solicitud_cancelacion_membresia_socio.php");
            exit();
        } catch (PDOException $e) {
            $_SESSION['errores'] = ['Error al enviar el formulario'];
            header("Location: ../solicitud_cancelacion_membresia_socio.php");
            exit();
        }
    } else {
        $_SESSION['errores'] = $errores;
        header("Location: ../solicitud_cancelacion_membresia_socio.php");
        exit();
    }
}else{
    $_SESSION['errores'] = ['Error al enviar el formulario'];
    header("Location: ../solicitud_cancelacion_membresia_socio.php");
    exit();
}





?>