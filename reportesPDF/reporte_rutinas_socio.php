<?php
session_start();

require_once "../conexion/bd.php";
require_once "../vendor/autoload.php";

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit;
}

$id_socio = $_SESSION['id_usuario'];

// Obtener rutinas
$sql = $conexion->prepare("
SELECT
rutinas.descripcion,
rutinas.fecha_asignacion,
CONCAT(usuarios.nombre,' ',usuarios.apellido) AS entrenador
FROM rutinas
INNER JOIN usuarios ON rutinas.id_entrenador = usuarios.id
WHERE rutinas.id_socio = :id_socio
");
$sql->bindParam(':id_socio', $id_socio, PDO::PARAM_INT);
$sql->execute();
$rutinas = $sql->fetchAll(PDO::FETCH_OBJ);

// HTML del PDF
ob_start();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Rutinas</title>
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
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background-color: #2c3e50;
            color: #fff;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        th {
            text-align: center;
        }

        .estado {
            text-align: center;
            font-weight: bold;
            color: green;
        }

        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 10px;
            color: #777;
        }
    </style>
</head>

<body>

    <h1>Reporte de Rutinas Asignadas</h1>

    <div class="info">
        <strong>Socio:</strong> <?= htmlspecialchars($_SESSION['nombre'] . ' ' . $_SESSION['apellido'] ?? '') ?><br>
        <strong>Fecha de generación:</strong> <?= date('d/m/Y H:i') ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>Descripción</th>
                <th>Entrenador</th>
                <th>Fecha Asignación</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($rutinas) == 0): ?>
                <tr>
                    <td colspan="4" style="text-align:center;">No hay rutinas asignadas</td>
                </tr>
            <?php else: ?>
                <?php foreach ($rutinas as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r->descripcion) ?></td>
                        <td><?= htmlspecialchars($r->entrenador) ?></td>
                        <td style="text-align:center;"><?= htmlspecialchars($r->fecha_asignacion) ?></td>
                        <td class="estado">Activa</td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        Sistema de Gestión de Gimnasio
    </div>

</body>

</html>
<?php
$html = ob_get_clean();

// Configuración DOMPDF
$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape'); // horizontal para tablas
$dompdf->render();

// Mostrar PDF en navegador
$dompdf->stream("rutinas_socio.pdf", ["Attachment" => false]);
exit;
