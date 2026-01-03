<?php
session_start();
require_once "../conexion/bd.php";

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    die("Acceso denegado");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_solicitud']) && isset($_POST['id_membresia_usuario']) && isset($_POST['estado'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        // Token inválido, rechazar el registro
        die('Acceso no autorizado.');
    }

    $id_solicitud = $_POST['id_solicitud'];
    $id_membresia_usuario = $_POST['id_membresia_usuario'];
    $estado = $_POST['estado'];
    $errores = [];

    // Validaciones
    if (empty($id_solicitud)) {
        $errores[] = "El ID de la solicitud es obligatorio.";
    }elseif(!is_numeric($id_solicitud)) {
        $errores[] = "El ID de la solicitud debe ser un número.";
    }elseif($id_solicitud < 1) {
        $errores[] = "El ID de la solicitud debe ser mayor a 0.";
    }

    if (empty($id_membresia_usuario)) {
        $errores[] = "El ID de la membresía es obligatorio.";
    }elseif(!is_numeric($id_membresia_usuario)) {
        $errores[] = "El ID de la membresía debe ser un número.";
    }elseif($id_membresia_usuario < 1) {
        $errores[] = "El ID de la membresía debe ser mayor a 0.";
    }
    
    $estados=['pendiente','aprobada','rechazada'];
    if (empty($estado)) {
        $errores[] = "El estado es obligatorio.";
    }elseif (!in_array($estado, $estados)) {
        $errores[] = "El estado debe ser 'pendiente', 'aprobada' o 'rechazada'.";
    }
    
    // Verificar que esa solicitud existe para poder editarla
    if(empty($errores)){
        $sql = $conexion->prepare("SELECT * FROM solicitud_cancelacion WHERE id = :id_solicitud");
        $sql->bindParam(':id_solicitud', $id_solicitud, PDO::PARAM_INT);
        $sql->execute();
        $solicitud = $sql->fetch(PDO::FETCH_OBJ);
        if(!$solicitud){
            $errores[]="Solicitud no encontrada.";
        }
    }

    // Actualizar la solicitud
    if(empty($errores)){
        $sql = $conexion->prepare("UPDATE solicitud_cancelacion SET estado = :estado WHERE id = :id_solicitud");
        $sql->bindParam(':id_solicitud', $id_solicitud, PDO::PARAM_INT);
        $sql->bindParam(':estado', $estado, PDO::PARAM_STR);
        $sql->execute();

        // Actualizar el estado de la membresía si la solicitud de cancelación es aprobada
        if($estado=='aprobada'){
            $sql = $conexion->prepare("UPDATE membresia_usuario SET estado = 'vencida' WHERE id = :id_membresia_usuario");
            $sql->bindParam(':id_membresia_usuario', $id_membresia_usuario, PDO::PARAM_INT);
            $sql->execute();
            $_SESSION['exito'] = "Membresía cancelada correctamente.";
        }

        $_SESSION['exito'] = "Solicitud actualizada correctamente.";
        header("Location: ../editar_solicitud_cancelar_membresia_socio.php?id_solicitud=" . $id_solicitud);
        exit();
    }else{
        $_SESSION['errores'] = $errores;
        header("Location: ../editar_solicitud_cancelar_membresia_socio.php?id_solicitud=" . $id_solicitud);
        exit();
    }



}else{
    $_SESSION['errores'] = ["Error al editar la solicitud"];
    header("Location: ../editar_solicitud_cancelar_membresia_socio.php?id_solicitud=" . $id_solicitud);
    exit();
}
?>