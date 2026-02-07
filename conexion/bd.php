<?php
$envFile = __DIR__ . '/../config/.env';

if (!file_exists($envFile)) {
    die("❌ Error: No se encuentra el archivo de configuración .env");
}

$env = parse_ini_file($envFile);

if ($env === false) {
    die("❌ Error: No se pudo leer el archivo de configuración .env");
}

$host = $env['DB_HOST'];
$dbname = $env['DB_NAME'];
$user = $env['DB_USER'];
$pass = $env['DB_PASS'];

try {
    $conexion = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //echo "✅ Conexión a MySQL exitosa!";
} catch (PDOException $e) {
    die("❌ Error al conectar a MySQL: " . $e->getMessage());
}
