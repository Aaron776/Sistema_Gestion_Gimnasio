<?php
require_once "../autorizacion/auth.php";
require_once "../conexion/bd.php";
require_once '../vendor/autoload.php'; // Dompdf

// Evitar mostrar mensajes, para que no corrompan el PDF
error_reporting(0);
@ini_set('display_errors', '0');

use Dompdf\Dompdf;
use Dompdf\Options;

// Verificar rol admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Validar id_pago
$id_pago = isset($_GET['id_pago']) ? intval($_GET['id_pago']) : 0;
if ($id_pago <= 0) {
    die("ID de pago inválido.");
}

// Obtener datos del pago
$sql = $conexion->prepare("
    SELECT pagos.id as id_pago, usuarios.id as id_socio,
           usuarios.email as email_socio, usuarios.telefono as telefono_socio,
           CONCAT(usuarios.nombre,' ',usuarios.apellido) as nombre_socio,
           membresias.nombre as nombre_membresia, monto, fecha_pago, metodo_pago
    FROM pagos
    INNER JOIN usuarios ON pagos.id_usuario = usuarios.id
    INNER JOIN membresias ON pagos.id_membresia = membresias.id
    WHERE pagos.id = :id_pago
");
$sql->bindParam(':id_pago', $id_pago, PDO::PARAM_INT);
$sql->execute();
$pago = $sql->fetch(PDO::FETCH_OBJ);

if (!$pago) {
    die("El pago no existe.");
}

// --- Generamos el HTML del PDF ---
ob_start();
?>

<html>
<head>
    <style>
        body { font-family: DejaVu Sans, sans-serif; padding: 20px; }
        .title { text-align: center; font-size: 20px; font-weight: bold; }
        .section { margin-top: 20px; }
        .label { font-weight: bold; color: #555; }
        .value { font-size: 14px; margin-bottom: 8px; }
        .box {
            border: 1px solid #ccc; padding: 15px; border-radius: 8px;
            background: #f7f7f7; margin-bottom: 15px;
        }
    </style>
</head>
<body>

<div class="title">RECIBO DE PAGO - PowerFit Gym</div>

<div class="section box">
    <div class="label">Número de recibo:</div>
    <div class="value">#REC-<?php echo $pago->id_pago; ?></div>

    <div class="label">Fecha de emisión:</div>
    <div class="value"><?php echo date("Y-m-d"); ?></div>
</div>

<div class="section box">
    <div class="label">Socio:</div>
    <div class="value"><?php echo htmlspecialchars($pago->nombre_socio); ?></div>

    <div class="label">Email:</div>
    <div class="value"><?php echo htmlspecialchars($pago->email_socio); ?></div>

    <div class="label">Teléfono:</div>
    <div class="value"><?php echo htmlspecialchars($pago->telefono_socio); ?></div>
</div>

<div class="section box">
    <div class="label">Membresía:</div>
    <div class="value"><?php echo htmlspecialchars($pago->nombre_membresia); ?></div>

    <div class="label">Monto:</div>
    <div class="value">$<?php echo htmlspecialchars($pago->monto); ?> USD</div>

    <div class="label">Fecha de Pago:</div>
    <div class="value"><?php echo htmlspecialchars($pago->fecha_pago); ?></div>

    <div class="label">Método de Pago:</div>
    <div class="value"><?php echo ucfirst(htmlspecialchars($pago->metodo_pago)); ?></div>
</div>

</body>
</html>

<?php
$html = ob_get_clean();

// Configuración de dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isPhpEnabled', true); // Necesario para la imagen en base64
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);

// Tamaño y orientación
$dompdf->setPaper('A4', 'portrait');

// Renderizar el PDF
$dompdf->render();

// Limpiar cualquier buffer de salida previo que pueda haber impreso texto
while (ob_get_level() > 0) {
    ob_end_clean();
}

$pdfOutput = $dompdf->output();

// Validar la estructura del PDF (debe comenzar por %PDF-)
if (substr($pdfOutput, 0, 4) === '%PDF') {
    // Enviar el PDF al navegador
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="recibo_'.$id_pago.'.pdf"');
    header('Content-Length: ' . strlen($pdfOutput));
    echo $pdfOutput;
} else {
    // Esto debería ejecutarse solo en un entorno de desarrollo para depuración
    // En un entorno de producción, simplemente podrías mostrar un error genérico
    file_put_contents(__DIR__."/recibo_error_output_$id_pago.txt", $pdfOutput);
    echo "Error generando el PDF. Se generó un archivo de depuración para revisión.";
}

exit;
?>
