<?php
session_start();
require_once '../conexion/bd.php';

if($_SERVER['REQUEST_METHOD'] == 'POST'  && isset($_POST['email']) && isset($_POST['password'])){
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $errores=[];

    // Inicializar contador de intentos si no existe
    if (!isset($_SESSION['intentos'])) {
        $_SESSION['intentos'] = 0;
    }

    // VALIDACIONES Y SANITIZACIONES
    if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)){
        $errores['email'] = 'El email no es valido';
    }

    if(empty($password)){
        $errores['password'] = 'La contraseña es obligatoria';
    }

    if ($_SESSION['intentos'] >= 5) {
        $_SESSION['errores'] = ["Demasiados intentos fallidos. Intenta nuevamente más tarde."];
        header("Location: ../login.php");
        exit();
    }

    // Si no existe errores procede a ingresar al sistema
    if(empty($errores)){
        // Obtener el usuario de la base de datos
        $sql=$conexion->prepare("SELECT id,nombre,apellido,email,rol,password FROM usuarios WHERE email=:email LIMIT 1");
        $sql->bindParam(':email', $email, PDO::PARAM_STR);
        $sql->execute();
        $usuario = $sql->fetch(PDO::FETCH_OBJ);

        if($usuario==true && password_verify($password, $usuario->password)){
            $_SESSION['id_usuario'] = $usuario->id;
            $_SESSION['nombre'] = $usuario->nombre;
            $_SESSION['apellido'] = $usuario->apellido;
            $_SESSION['email'] = $usuario->email;
            $_SESSION['rol'] = $usuario->rol;
            $_SESSION['logueado']=true;

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
            $errores[] = "Email o contraseña incorrectos.";
            $_SESSION['errores'] = $errores;
            header("Location: ../login.php");
            exit();
        }
    }else{
        $_SESSION['errores'] = $errores;
        header('Location: ../login.php');
        exit;
    }
}else{
    $_SESSION['errores'] = ['Error al enviar el formulario'];
    header('Location: ../login.php');
    exit;
}


?>