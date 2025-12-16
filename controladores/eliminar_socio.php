<?php
session_start();

// Verificar que tenga rol de admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: index.php"); // si no lo mandamos al login
    exit();
}

include_once "../conexion/bd.php";

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_socio'])){
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        // Token inválido, rechazar el registro
        die('Acceso no autorizado.');
    }

    $id_socio = trim($_POST['id_socio']);
    $errores=[];

    //vAlñidacions y Sanitizaciones
    if (empty($id_socio)) {
        $errores[] = 'El id del socio es obligatorio';
    }elseif(!is_numeric($id_socio)){
        $errores[] = 'El id del socio debe ser numérico';
    }elseif(!filter_var($id_socio, FILTER_VALIDATE_INT)){
        $errores[] = 'El id del socio debe ser un entero';
    }elseif($id_socio <= 0){
        $errores[] = 'El id del socio debe ser mayor a 0';
    } 

    // Verificar si ese socio existe en la base de datos y si es socio
    if(empty($errores)){
        $sql=$conexion->prepare("SELECT id FROM usuarios WHERE id=:id AND rol='socio' LIMIT 1");
        $sql->bindParam(':id', $id_socio, PDO::PARAM_INT);
        $sql->execute();
        $socio = $sql->fetch(PDO::FETCH_OBJ);

        if (!$socio) {
            $errores[] = 'El socio no existe en la base de datos o no es socio.';
        }
    }

    if(empty($errores)){
        $sql=$conexion->prepare("DELETE FROM usuarios WHERE id=:id");
        $sql->bindParam(':id', $id_socio, PDO::PARAM_INT);
        $sql->execute();

        $_SESSION['exito'] = 'El socio se ha eliminado con exito';
        header("Location: ../gestion_socios.php");
        exit;
    }else{
        $_SESSION['errores'] = $errores;
        header("Location: ../gestion_socios.php");
        exit;
    }
}else{
    $_SESSION['errores'] = "No se ha seleccionado ningún usuario.";
    header("Location: ../index.php"); // si no lo mandamos al login
    exit();
}
?>