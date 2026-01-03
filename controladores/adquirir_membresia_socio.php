<?php
session_start();
require_once "../conexion/bd.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['membresia']) && isset($_POST['id_socio']) && isset($_POST['fecha_inicio']) && isset($_POST['fecha_fin']) && isset($_POST['monto']) && isset($_POST['metodo_pago'])) {
    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Acceso no autorizado. Token CSRF inválido.');
    }

    $id_membresia = $_POST['membresia'];
    $id_socio = $_POST['id_socio'];

    // Sanitizar fechas
    $fecha_inicio = trim($_POST['fecha_inicio']);
    $fecha_fin = trim($_POST['fecha_fin']);

    $monto = $_POST['monto'];
    $metodo_pago = $_POST['metodo_pago'];
    $errores = [];

    // Validaciones y Sanitizacion de IDs
    if (empty($id_membresia)) {
        $errores[] = "Debes seleccionar una membresia";
    } elseif (!is_numeric($id_membresia)) {
        $errores[] = "La membresia debe ser un número";
    } elseif ($id_membresia < 1) {
        $errores[] = "La membresia debe ser mayor a 0";
    }

    if (empty($id_socio)) {
        $errores[] = "Debes seleccionar un socio";
    } elseif (!is_numeric($id_socio)) {
        $errores[] = "El socio debe ser un número";
    } elseif ($id_socio < 1) {
        $errores[] = "El socio debe ser mayor a 0";
    }

    // Validación de Fecha de Inicio
    if (empty($fecha_inicio)) {
        $errores[] = "Debes seleccionar una fecha de inicio";
    } else {
        // Validar formato de fecha (YYYY-MM-DD)
        if (!DateTime::createFromFormat('Y-m-d', $fecha_inicio)) {
            $errores[] = "El formato de la fecha de inicio no es válido";
        } elseif ($fecha_inicio < date('Y-m-d')) {
            // Validar que no sea anterior a la fecha actual
            $errores[] = "La fecha de inicio no puede ser anterior a la fecha actual";
        }
    }

    // Validación de Fecha de Fin
    if (empty($fecha_fin)) {
        $errores[] = "Debes seleccionar una fecha de fin";
    } else {
        // Validar formato de fecha (YYYY-MM-DD)
        if (!DateTime::createFromFormat('Y-m-d', $fecha_fin)) {
            $errores[] = "El formato de la fecha de fin no es válido";
        } else {
            $ts_inicio = strtotime($fecha_inicio);
            $ts_fin = strtotime($fecha_fin);

            // Validar que la fecha de fin sea posterior a la fecha de inicio
            if ($ts_fin <= $ts_inicio) {
                $errores[] = "La fecha de fin ($fecha_fin) debe ser posterior a la fecha de inicio ($fecha_inicio)";
            }

            // Validar diferencia de días
            $diferencia_segundos = $ts_fin - $ts_inicio;
            $diferencia_dias = round($diferencia_segundos / (60 * 60 * 24));

            if ($diferencia_dias < 7) {
                $errores[] = "La membresía debe tener una duración mínima de 7 días";
            }
            if ($diferencia_dias > 730) {
                $errores[] = "La membresía no puede tener una duración mayor a 2 años";
            }
        }
    }

    // Validación de Monto
    if (empty($monto)) {
        $errores[] = "Debes ingresar un monto";
    } else {
        // Validar que sea un número válido
        if (!is_numeric($monto)) {
            $errores[] = "El monto debe ser un valor numérico válido";
        } else {
            $monto_float = floatval($monto);
            // Validar que sea positivo
            if ($monto_float <= 0) {
                $errores[] = "El monto debe ser mayor a 0";
            }
            // Validar que tenga máximo 2 decimales
            if (round($monto_float, 2) != $monto_float) {
                $errores[] = "El monto solo puede tener hasta 2 decimales";
            }
            // Validar rango razonable (entre $1 y $10,000)
            if ($monto_float < 1) {
                $errores[] = "El monto mínimo es de $1.00";
            }
            if ($monto_float > 10000) {
                $errores[] = "El monto no puede exceder los $10,000.00";
            }
        }
    }

    $metodos=['efectivo', 'transferencia', 'tarjeta'];
    // Validación de Método de Pago
    if (empty($metodo_pago)) {
        $errores[] = "Debes seleccionar un método de pago";
    } elseif (!in_array($metodo_pago, $metodos)) {
        $errores[] = "El método de pago seleccionado no es válido";
    }

    // Si no hay errores, proceder con el registro de la membresía y el pago
    if (empty($errores)) {
        $sql = $conexion->prepare("INSERT INTO membresia_usuario (id_usuario, id_membresia, fecha_inicio, fecha_fin) VALUES (:id_usuario, :id_membresia, :fecha_inicio, :fecha_fin)");
        $sql->bindParam(':id_usuario', $id_socio, PDO::PARAM_INT);
        $sql->bindParam(':id_membresia', $id_membresia, PDO::PARAM_INT);
        $sql->bindParam(':fecha_inicio', $fecha_inicio, PDO::PARAM_STR);
        $sql->bindParam(':fecha_fin', $fecha_fin, PDO::PARAM_STR);
        $sql->execute();

        // Insertar datos de Pago
        $sql = $conexion->prepare("INSERT INTO pagos (id_usuario,id_membresia, monto, metodo_pago) VALUES (:id_usuario, :id_membresia, :monto, :metodo_pago)");
        $sql->bindParam(':id_usuario', $id_socio, PDO::PARAM_INT);
        $sql->bindParam(':id_membresia', $id_membresia, PDO::PARAM_INT);
        $sql->bindParam(':monto', $monto, PDO::PARAM_STR);
        $sql->bindParam(':metodo_pago', $metodo_pago, PDO::PARAM_STR);
        $sql->execute();

        $_SESSION['exito'] = "Membresía adquirida exitosamente";
        header("Location: ../adquirir_membresia_socio.php");
        exit();
    } else {
        $_SESSION['errores'] = $errores;
        header("Location: ../adquirir_membresia_socio.php");
        exit();
    }
} else {
    $_SESSION['errores'] = ["No se pudo enviar el formulario"];
    header("Location: ../adquirir_membresia_socio.php");
    exit();
}
