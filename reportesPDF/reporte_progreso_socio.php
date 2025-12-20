<?php
require_once "../conexion/bd.php";
require_once "../vendor/autoload.php";

use Dompdf\Dompdf;
use Dompdf\Options;


// Validar ID
if (!isset($_GET['id_socio']) || !is_numeric($_GET['id_socio'])) {
    die("ID de socio inválido");
}

$id_socio = (int) $_GET['id_socio'];

/* =========================
   DATOS DEL SOCIO
========================= */
$sql = $conexion->prepare("
    SELECT 
        CONCAT(u.nombre,' ',u.apellido) AS nombre,
        u.email,
        m.nombre AS membresia
    FROM membresia_usuario mu
    INNER JOIN usuarios u ON mu.id_usuario = u.id
    INNER JOIN membresias m ON mu.id_membresia = m.id
    WHERE u.id = :id
");
$sql->bindParam(":id", $id_socio, PDO::PARAM_INT);
$sql->execute();
$socio = $sql->fetch(PDO::FETCH_OBJ);

if (!$socio) {
    die("Socio no encontrado");
}

/* =========================
   PROGRESO
========================= */
$sql = $conexion->prepare("
    SELECT peso, grasa_corporal, masa_muscular, fecha_registro
    FROM progreso
    WHERE id_socio = :id
    ORDER BY fecha_registro ASC
");
$sql->bindParam(":id", $id_socio, PDO::PARAM_INT);
$sql->execute();
$progreso = $sql->fetchAll(PDO::FETCH_OBJ);

/* =========================
   HTML DEL PDF
========================= */
$html = '
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 12px;
        color: #333;
    }
    h1 {
        text-align: center;
        margin-bottom: 10px;
    }
    .info {
        margin-bottom: 20px;
    }
    .info p {
        margin: 4px 0;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }
    th, td {
        border: 1px solid #ccc;
        padding: 8px;
        text-align: center;
    }
    th {
        background-color: #f0f0f0;
    }
    .footer {
        margin-top: 30px;
        text-align: center;
        font-size: 10px;
        color: #777;
    }
</style>
</head>
<body>

<h1>Historial de Progreso</h1>

<div class="info">
    <p><strong>Socio:</strong> '.$socio->nombre.'</p>
    <p><strong>Email:</strong> '.$socio->email.'</p>
    <p><strong>Membresía:</strong> '.$socio->membresia.'</p>
    <p><strong>Fecha de exportación:</strong> '.date("d/m/Y H:i").'</p>
</div>

<table>
<thead>
<tr>
    <th>Fecha</th>
    <th>Peso (kg)</th>
    <th>Grasa (%)</th>
    <th>Masa Muscular (kg)</th>
</tr>
</thead>
<tbody>';

foreach ($progreso as $item) {
    $html .= '
    <tr>
        <td>'.date("d/m/Y", strtotime($item->fecha_registro)).'</td>
        <td>'.$item->peso.'</td>
        <td>'.$item->grasa_corporal.'</td>
        <td>'.$item->masa_muscular.'</td>
    </tr>';
}

$html .= '
</tbody>
</table>

<div class="footer">
    Sistema de Gestión Fitness • Reporte generado automáticamente
</div>

</body>
</html>';

/* =========================
   DOMPDF
========================= */
$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

/* =========================
   DESCARGA
========================= */
$nombreArchivo = "progreso_socio_".$id_socio.".pdf";
$dompdf->stream($nombreArchivo, ["Attachment" => true]);
exit;
