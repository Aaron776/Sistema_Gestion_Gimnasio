<?php
// Verifica si no hay una sesión iniciada todavía
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Inicia la sesión si aún no existe
}

// Comprueba si el usuario no está logueado o la variable 'logueado' no es true
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: index.php"); // Redirige al login (o página principal)
    exit(); // Detiene la ejecución del script después de la redirección
}
?>

<!--
Resumen del archivo:
Este archivo se encarga de proteger páginas que requieren que el usuario esté autenticado. 
1. Se asegura de que la sesión esté iniciada.
2. Verifica si el usuario tiene la sesión activa y está logueado.
3. Si no, lo redirige a la página de inicio y detiene la ejecución del script.
--> 