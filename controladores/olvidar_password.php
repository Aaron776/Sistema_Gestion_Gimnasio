<?php
session_start();
require_once '../conexion/bd.php';
require_once '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        // Token inválido, rechazar el registro
        die('Acceso no autorizado.');
    }

    // Recibir los datos del formulario
    $email = trim($_POST['email']);
    $errores = [];

    // VALIDACIONES Y SANITIZACIONES
    if (empty($email)) {
        $errores[] = "El email es obligatorio.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El email no es válido.";
    } elseif (strlen($email) > 100) {
        $errores['email'] = 'El email es demasiado largo';
    }

    // Verificar si ese usuario existe en la base de datos
    if (empty($errores)) {
        $sql = $conexion->prepare("SELECT * FROM usuarios WHERE email = :email");
        $sql->bindParam(':email', $email, PDO::PARAM_STR);
        $sql->execute();
        $usuario = $sql->fetch(PDO::FETCH_ASSOC);
        if ($usuario == false) {
            $errores[] = "No existe ningún usuario con ese email.";
        }
    }

    if (empty($errores)) {

        // Seleccionar al usuario de la base de datos y verificar su rol que sea socio
        $sql = $conexion->prepare("SELECT rol,email FROM usuarios WHERE email = :email");
        $sql->bindParam(':email', $email);
        $sql->execute();
        $usuario = $sql->fetch(PDO::FETCH_OBJ);

        if ($usuario->rol == "socio") {
            $nuevaPassword = substr(bin2hex(random_bytes(4)), 0, 8);
            // Hashear el código correcto
            $codigoHasheado = password_hash($nuevaPassword, PASSWORD_DEFAULT);
            $sql = $conexion->prepare("UPDATE usuarios SET password = :password WHERE email = :email");
            $sql->bindParam(':password', $codigoHasheado);
            $sql->bindParam(':email', $email);
            $sql->execute();
            // Enviar correo con PHPMailer
            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'aronortiz759@gmail.com'; // Tu correo
                $mail->Password   = 'swtqxrzc fvcs pspz';    // Contraseña o App Password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = 465;
                $mail->CharSet    = 'UTF-8';

                $mail->setFrom('aronortiz759@gmail.com', 'POWERFIT GYM');
                $mail->addAddress($usuario->email);

                $mail->isHTML(true);
                $mail->Subject = 'Recuperación de contraseña - POWERFIT GYM';
                $mail->Body    = "
                    <h2>Recuperación de contraseña</h2>
                    <p>Hola, has solicitado recuperar tu contraseña.</p>
                    <p>Tu nueva contraseña es: <b>{$nuevaPassword}</b></p>
                    <p>Te recomendamos cambiarla después de iniciar sesión.</p>
                ";

                $mail->send();
                $_SESSION['exito'] = "¡Correo enviado! Por favor revisa tu bandeja de entrada.";
                header("Location: ../olvidar_password.php");
                exit();
            } catch (Exception $e) {
                $_SESSION['errores'] = ["Error al enviar el correo: {$mail->ErrorInfo}"];
                header("Location: ../olvidar_password.php");
                exit();
            }
        } else {
            $_SESSION['errores'] = ['Esta funcion esta permitida solo para el socio, en caso de ser entrenador, contactarse con el administrador'];
            header("Location: ../olvidar_password.php");
            exit();
        }
    } else {
        $_SESSION['errores'] = $errores;
        header("Location: ../olvidar_password.php");
        exit();
    }
} else {
    $_SESSION['errores'] = ['Error en el envío del formulario. Por favor, inténtelo de nuevo.'];
    header("Location: ../olvidar_password.php");
    exit();
}
