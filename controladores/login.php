<?php
session_start();
require_once '../conexion/bd.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST'  && isset($_POST['email']) && isset($_POST['password'])) {
    // Validación de token CSRF
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['errores'] = ['Error de validación CSRF. Intente de nuevo.'];
        header('Location: ../login.php');
        exit;
    }
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $errores = [];

    // Inicializar contador de intentos si no existe
    if (!isset($_SESSION['intentos'])) {
        $_SESSION['intentos'] = 0;
    }

    // VALIDACIONES Y SANITIZACIONES
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores['email'] = 'El email no es valido';
    }

    if (empty($password)) {
        $errores['password'] = 'La contraseña es obligatoria';
    }

    // Validar intentos fallidos
    if ($_SESSION['intentos'] >= 5) {
        if (!isset($_SESSION['bloqueado_tiempo'])) {
            $_SESSION['bloqueado_tiempo'] = time();
        }
        $tiempo_transcurrido = time() - $_SESSION['bloqueado_tiempo'];
        $tiempo_espera = 300; // 5 minutos

        if ($tiempo_transcurrido < $tiempo_espera) {
            $restante = $tiempo_espera - $tiempo_transcurrido;
            $_SESSION['errores'] = ["Demasiados intentos. Intenta en $restante segundos."];
            header('Location: ../login.php');
            exit();
        } else {
            $_SESSION['intentos'] = 0;
            unset($_SESSION['bloqueado_tiempo']);
        }
    }

    // Si no existe errores procede a ingresar al sistema
    if (empty($errores)) {
        // Obtener el usuario de la base de datos
        $sql = $conexion->prepare("SELECT id,nombre,apellido,email,rol,password FROM usuarios WHERE email=:email LIMIT 1");
        $sql->bindParam(':email', $email, PDO::PARAM_STR);
        $sql->execute();
        $usuario = $sql->fetch(PDO::FETCH_OBJ);

        if ($usuario == true && password_verify($password, $usuario->password)) {
            $_SESSION['id_usuario'] = $usuario->id;
            $_SESSION['nombre'] = $usuario->nombre;
            $_SESSION['apellido'] = $usuario->apellido;
            $_SESSION['email'] = $usuario->email;
            $_SESSION['rol'] = $usuario->rol;
            $_SESSION['logueado'] = true;

            // Reinicia el contador de intentos fallidos
            $_SESSION['intentos'] = 0;

            switch ($usuario->rol) {
                case 'admin':
                    header("Location: ../dash_admin.php");
                    exit();
                case 'entrenador':
                    header("Location: ../dash_entrenador.php");
                    exit();
                case 'socio':
                    header("Location: ../dash_cliente.php");
                    exit();
                default:
                    header("Location: ../login.php");
                    exit();
            }
        } else {
            // Credenciales incorrectas
            $_SESSION['intentos']++;
            if ($_SESSION['intentos'] >= 5) {
                $_SESSION['bloqueado_tiempo'] = time();
                $errores[] = "Demasiados intentos fallidos. Tu cuenta ha sido bloqueada por 5 minutos.";
            } else {
                $errores[] = "Email o contraseña incorrectos.";
            }
            $_SESSION['errores'] = $errores;
            header("Location: ../login.php");
            exit();
        }
    } else {
        $_SESSION['errores'] = $errores;
        header('Location: ../login.php');
        exit;
    }
} else {
    $_SESSION['errores'] = ['Error al enviar el formulario'];
    header('Location: ../login.php');
    exit;
}
