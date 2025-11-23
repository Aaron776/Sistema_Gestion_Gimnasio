<?php
session_start();
require_once "../conexion/bd.php";

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_clase']) && isset ($_POST['nombre']) && isset($_POST['descripcion']) && isset($_POST['cupo']) && isset($_POST['horario']) && isset($_POST['entrenador'])){
    $nombre = trim(filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING));
    $id_clase = trim(filter_input(INPUT_POST, 'id_clase', FILTER_SANITIZE_NUMBER_INT));
    $descripcion = trim(filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING));
    $cupo = trim(filter_input(INPUT_POST, 'cupo', FILTER_SANITIZE_NUMBER_INT));
    $horario = trim(filter_input(INPUT_POST, 'horario', FILTER_SANITIZE_STRING));
    $entrenador = trim(filter_input(INPUT_POST, 'entrenador', FILTER_SANITIZE_NUMBER_INT));
    $errores = [];

    // VALIDACIONES Y SANITIZACIONES
    if(empty($nombre)){
        $errores[] = 'El nombre es obligatorio';
    }elseif(!preg_match('/^[\p{L}\p{N}\s,.!?-]+$/u', $nombre)){
        $errores[] = 'El nombre solo puede contener letras, números, espacios y caracteres especiales como , . ! ? -';
    }elseif(strlen($nombre) > 50){
        $errores[] = 'El nombre no puede tener más de 50 caracteres';
    }elseif(strlen($nombre) < 2){
        $errores[] = 'El nombre debe tener al menos 2 caracteres';
    }

    if(empty($id_clase)){
        $errores[] = 'El ID de la clase es obligatorio';
    }elseif(!is_numeric($id_clase)){
        $errores[] = 'El ID de la clase debe ser numérico';
    }elseif(!filter_var($id_clase, FILTER_VALIDATE_INT)){
        $errores[] = 'El ID de la clase debe ser un entero';
    }elseif($id_clase <= 0){
        $errores[] = 'El ID de la clase debe ser mayor a 0';
    }elseif($id_clase != $_POST['id_clase']){
        $errores[] = 'El ID de la clase no coincide con el valor enviado';
    }

    if(empty($descripcion)){
        $errores[] = 'La descripción es obligatoria';
    }elseif(!preg_match('/^[\p{L}\p{N}\s,.!?-]+$/u', $descripcion)){
        $errores[] = 'La descripción solo puede contener letras, números, espacios y caracteres especiales como , . ! ? -';
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

    // Verificar si esa clase existe para modificarla
    if(empty($errores)){
        $sql = $conexion->prepare("SELECT * FROM clases WHERE id = :id_clase");
        $sql->bindValue(':id_clase', $id_clase, PDO::PARAM_INT);
        $sql->execute();
        $clase = $sql->fetch(PDO::FETCH_OBJ);

        if(!$clase){
            $errores[] = 'La clase no existe en la base de datos.';
        } 
    }


    if(empty($errores)){
        $sql = $conexion->prepare("UPDATE clases SET nombre = :nombre, descripcion = :descripcion, cupo = :cupo, horario = :horario, id_entrenador = :entrenador WHERE id = :id_clase");
        $sql->bindParam(':id_clase', $id_clase, PDO::PARAM_INT);
        $sql->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $sql->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
        $sql->bindParam(':cupo', $cupo, PDO::PARAM_INT);
        $sql->bindParam(':horario', $horario, PDO::PARAM_STR);
        $sql->bindParam(':entrenador', $entrenador, PDO::PARAM_INT);
        $sql->execute();

        $_SESSION['exito'] = 'Clase actualizada con éxito';
        unset($_SESSION['id_clase_edicion']);
        header('Location: ../gestion_clases.php');
        exit;
    }else{
        $_SESSION['errores'] = $errores;
        header('Location: ../editar_clase.php');
        exit;
    }
}else{
    $_SESSION['errores'] = ['Error al enviar el formulario'];
    header('Location: ../editar_clase.php');
    exit;
}

?>
