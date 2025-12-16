<?php
require_once "../conexion/bd.php";
require_once "../vendor/autoload.php";

use Dompdf\Dompdf;
use Dompdf\Options;

// Opciones para cargar CSS y fuentes externas
$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Arial');
$dompdf = new Dompdf($options);

// Consultar pagos
$sql = $conexion->prepare("
    SELECT pagos.id AS id_pago,
           usuarios.id AS id_socio,
           CONCAT(usuarios.nombre,' ',usuarios.apellido) AS nombre_socio,
           membresias.nombre AS nombre_membresia,
           monto,
           fecha_pago,
           metodo_pago
    FROM pagos
    INNER JOIN usuarios ON pagos.id_usuario = usuarios.id
    INNER JOIN membresias ON pagos.id_membresia = membresias.id
    ORDER BY fecha_pago DESC
");
$sql->execute();
$pagos = $sql->fetchAll(PDO::FETCH_OBJ);

// Total general
$sql = $conexion->prepare("SELECT SUM(monto) AS total FROM pagos");
$sql->execute();
$totalGeneral = $sql->fetch(PDO::FETCH_OBJ)->total ?? 0;

// Fecha actual
$fechaActual = date("d/m/Y H:i");
ob_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte de Pagos</title>

<style>
    body {
        font-family: Arial, sans-serif;
        margin: 20px;
        font-size: 12px;
        color: #333;
    }
    h1 {
        text-align: center;
        font-size: 22px;
        color: #444;
    }
    .subtitulo {
        text-align: center;
        font-size: 14px;
        margin-bottom: 20px;
    }
    .info {
        margin-bottom: 20px;
        font-size: 12px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }
    th {
        background: #f0f0f0;
        padding: 8px;
        text-align: left;
        font-size: 12px;
        border-bottom: 1px solid #ddd;
    }
    td {
        padding: 6px;
        border-bottom: 1px solid #eee;
    }
    .badge {
        padding: 4px 6px;
        border-radius: 4px;
        font-size: 10px;
        color: white;
    }
    .cash { background: #27ae60; }
    .card { background: #2980b9; }
    .transfer { background: #7f8c8d; }

    .total-box {
        margin-top: 20px;
        padding: 12px;
        background: #e74c3c;
        color: white;
        font-size: 16px;
        text-align: right;
        border-radius: 5px;
        font-weight: bold;
    }

    footer {
        position: fixed;
        bottom: 0;
        left: 0;
        text-align: center;
        font-size: 10px;
        width: 100%;
        color: #999;
    }
</style>
</head>

<body>

<h1>Reporte General de Pagos</h1>
<p class="subtitulo">Generado automáticamente por el sistema</p>

<div class="info">
    <strong>Fecha:</strong> <?= $fechaActual ?><br>
    <strong>Total general recaudado:</strong> $<?= number_format($totalGeneral, 2) ?>
</div>

<table>
    <thead>
        <tr>
            <th>Socio</th>
            <th>Membresía</th>
            <th>Monto</th>
            <th>Fecha</th>
            <th>Método</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($pagos as $p): ?>
            <?php
                $fecha = date("d M Y, H:i", strtotime($p->fecha_pago));
                $badgeClass = $p->metodo_pago == "efectivo" ? "cash" :
                              ($p->metodo_pago == "tarjeta" ? "card" : "transfer");
            ?>
            <tr>
                <td><?= htmlspecialchars($p->nombre_socio) ?></td>
                <td><?= htmlspecialchars($p->nombre_membresia) ?></td>
                <td>$<?= number_format($p->monto, 2) ?></td>
                <td><?= $fecha ?></td>
                <td><span class="badge <?= $badgeClass ?>"><?= ucfirst($p->metodo_pago) ?></span></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="total-box">
    Total Recaudado: $<?= number_format($totalGeneral, 2) ?>
</div>

<footer>
    Sistema de Gestión de Pagos - <?= date("Y") ?>
</footer>

</body>
</html>

<?php
$html = ob_get_clean();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("reporte_pagos.pdf", ['Attachment' => true]);
