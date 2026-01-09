<?php
session_start();
require_once "../conexion/bd.php";


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id_socio"]) && isset($_POST["password_actual"]) && isset($_POST["password_nueva"])){
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        // Token inválido, rechazar el registro
        die('Acceso no autorizado.');
    }

    $password_actual = trim($_POST["password_actual"]);
    $id_socio = trim($_POST["id_socio"]);
    $password_nueva = trim($_POST["password_nueva"]);
    $errores=[];

    // VALIDACIONES Y SANITIZACIONES
    if(empty($password_actual)){
        $errores[] = 'La contraseña actual es obligatoria';
    }

    if(empty($id_socio)){
        $errores[] = 'El id es obligatorio';
    }elseif(!is_numeric($id_socio)){
        $errores[] = 'El id debe ser numérico';
    }elseif(!filter_var($id_socio, FILTER_VALIDATE_INT)){
        $errores[] = 'El id debe ser un entero';
    }elseif($id_socio <= 0){
        $errores[] = 'El id debe ser mayor a 0';
    }

    if(empty($password_nueva)){
        $errores[] = 'La contraseña nueva es obligatoria';
    }elseif(strlen($password_nueva) < 5){
        $errores[] = 'La contraseña nueva debe tener al menos 5 caracteres';
    }

    // Verificar que las contraseñas sean diferentes
    if (!empty($password_actual) && !empty($password_nueva) && $password_actual === $password_nueva) {
        $errores[] = "La nueva contraseña debe ser diferente a la actual.";
    }
    

    if (empty($errores)) {
        try {
            // Obtener contraseña actual de la base de datos
            $sql = $conexion->prepare("SELECT password FROM usuarios WHERE id = :id");
            $sql->bindParam(':id', $id_socio);
            $sql->execute();
            $usuario = $sql->fetch(PDO::FETCH_OBJ);

            if (!$usuario) {
                error_log("Intento de cambio de contraseña con ID inexistente: $id_socio");
                $errores[] = "Usuario no encontrado.";
                header("Location: ../actualizarPassword.php");
                exit();
            }

            // Verificar contraseña actual
            if (password_verify($password_actual, $usuario->password)) {
                // Contraseña actual correcta, proceder a actualizar
                $password_encriptada = password_hash($password_nueva, PASSWORD_BCRYPT);
                
                $sql_update = $conexion->prepare("UPDATE usuarios SET password = :password WHERE id = :id");
                $sql_update->bindParam(':password', $password_encriptada);
                $sql_update->bindParam(':id', $id_socio);
                $sql_update->execute();

                // Log de éxito
                error_log("Contraseña cambiada exitosamente - Usuario ID: $id_socio - IP: " . $_SERVER['REMOTE_ADDR']);
                
                // Mensaje de éxito
                $_SESSION['exito'] = "Contraseña actualizada correctamente";
                header("Location: ../actualizarPassword.php");
                exit();
                
            } else {
                // Contraseña actual incorrecta
                error_log("Intento fallido de cambio de contraseña - Usuario ID: $id_socio - IP: " . $_SERVER['REMOTE_ADDR']);
                $_SESSION['errores'] = "La contraseña actual es incorrecta.";
                header("Location: ../actualizarPassword.php");
                exit();
            }

        } catch (PDOException $e) {
            error_log("Error BD en cambio de contraseña: " . $e->getMessage() . " - Usuario ID: $id_socio");
            $_SESSION['errores'] = "Error interno del sistema. Intenta más tarde.";
            header("Location: ../actualizarPassword.php");
            exit();
        }
        
    } else {
        // Errores de validación
        $_SESSION['errores'] = $errores;
        header("Location: ../actualizarPassword.php");
        exit();
    }
    
}else{
    $_SESSION['errores'] = ["Error al enviar el formulario"];       
    header("Location: ../actualizarPassword.php");
    exit();
}
?>