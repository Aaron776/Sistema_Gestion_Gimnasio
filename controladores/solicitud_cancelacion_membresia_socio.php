<?php
session_start();
require_once "../conexion/bd.php";

// Asegurarse de que el usuario tenga permisos de socio
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'socio') {
    die('Acceso no autorizado. Se requieren permisos de socio.');
}





?>