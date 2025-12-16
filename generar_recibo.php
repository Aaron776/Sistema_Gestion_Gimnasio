<?php
require_once "autorizacion/auth.php"; // valida login y arranca sesión

// Generar token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Verificar que tenga rol de admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: index.php"); // si no lo mandamos al login
    exit();
}

require_once "templates/header.php";
include_once "conexion/bd.php";

$id_pago = isset($_GET['id_pago']) ? intval($_GET['id_pago']) : 0;
if (!isset($id_pago) || $id_pago <= 0) {
    $_SESSION['errores'] = "ID de pago inválido.";
    header("Location: gestion_pagos.php");
    exit();
}
// Obtener el pago desde la base de datos de cada socio
$sql = $conexion->prepare("SELECT pagos.id as id_pago, usuarios.id as id_socio, usuarios.email as email_socio, usuarios.telefono as telefono_socio, CONCAT(usuarios.nombre,' ',usuarios.apellido) as nombre_socio,membresias.nombre as nombre_membresia,monto,fecha_pago,metodo_pago FROM pagos INNER JOIN usuarios ON pagos.id_usuario = usuarios.id INNER JOIN membresias ON pagos.id_membresia = membresias.id WHERE pagos.id = :id_pago");
$sql->bindParam(':id_pago', $id_pago, PDO::PARAM_INT);
$sql->execute();
$pago = $sql->fetch(PDO::FETCH_OBJ);

if (!$pago) {
    $_SESSION['errores'] = "Pago no encontrado.";
    header("Location: gestion_pagos.php");
    exit();
}
?>
<style>

    .receipt-container {
        max-width: 500px;
        width: 100%;
    }

    .receipt-actions {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-bottom: 20px;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        font-family: var(--font-main);
        font-size: 0.95rem;
    }

    .btn-primary {
        background-color: var(--secondary);
        color: white;
        box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
    }

    .btn-primary:hover {
        background-color: #c0392b;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(231, 76, 60, 0.4);
    }

    .btn-secondary {
        background-color: #6c757d;
        color: white;
    }

    .btn-secondary:hover {
        background-color: #5a6268;
    }

    .btn-print {
        background-color: var(--success);
        color: white;
        box-shadow: 0 4px 15px rgba(46, 204, 113, 0.3);
    }

    .btn-print:hover {
        background-color: #27ae60;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(46, 204, 113, 0.4);
    }

    /* Recibo */
    .receipt {
        background: white;
        border-radius: 15px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
        padding: 30px;
        position: relative;
        overflow: hidden;
    }

    .receipt::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--secondary), var(--primary));
    }

    .receipt-header {
        text-align: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px dashed #e9ecef;
    }

    .gym-logo {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-bottom: 15px;
    }

    .gym-logo i {
        font-size: 2.5rem;
        color: var(--secondary);
    }

    .gym-name {
        font-family: var(--font-main);
        font-size: 1.8rem;
        font-weight: 800;
        color: var(--primary);
    }

    .gym-name span {
        color: var(--secondary);
    }

    .receipt-title {
        font-family: var(--font-main);
        font-size: 1.4rem;
        color: var(--dark);
        margin-bottom: 5px;
    }

    .receipt-subtitle {
        color: #6c757d;
        font-size: 0.9rem;
    }

    /* Información del Recibo */
    .receipt-info {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 25px;
    }

    .info-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .info-label {
        font-size: 0.8rem;
        color: #6c757d;
        text-transform: uppercase;
        font-weight: 600;
    }

    .info-value {
        font-size: 1rem;
        font-weight: 600;
        color: var(--dark);
        font-family: var(--font-main);
    }

    /* Detalles del Pago */
    .payment-details {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 25px;
        margin-bottom: 25px;
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #e9ecef;
    }

    .detail-row:last-child {
        border-bottom: none;
    }

    .detail-label {
        color: #6c757d;
        font-weight: 500;
    }

    .detail-value {
        font-weight: 600;
        color: var(--dark);
        font-family: var(--font-main);
    }

    .amount {
        font-size: 1.3rem;
        color: var(--success);
    }

    .membership-type {
        background: var(--primary);
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .payment-method {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .payment-icon {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: var(--info);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
    }

    /* Información del Socio */
    .member-info {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
        padding: 20px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 10px;
    }

    .member-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: bold;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .member-details h3 {
        font-family: var(--font-main);
        font-size: 1.3rem;
        color: var(--dark);
        margin-bottom: 5px;
    }

    .member-details p {
        color: #6c757d;
        font-size: 0.9rem;
    }

    /* Footer del Recibo */
    .receipt-footer {
        text-align: center;
        padding-top: 20px;
        border-top: 2px dashed #e9ecef;
    }

    .thank-you {
        font-family: var(--font-main);
        font-size: 1.1rem;
        color: var(--primary);
        margin-bottom: 10px;
    }

    .contact-info {
        color: #6c757d;
        font-size: 0.85rem;
        line-height: 1.4;
    }

    /* Código QR */
    .qr-code {
        text-align: center;
        margin: 20px 0;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 10px;
    }

    .qr-placeholder {
        width: 120px;
        height: 120px;
        background: linear-gradient(45deg, #e9ecef, #dee2e6);
        margin: 0 auto 10px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6c757d;
        font-size: 0.8rem;
    }

    .qr-text {
        font-size: 0.8rem;
        color: #6c757d;
    }

    /* Estilos para impresión */
    @media print {
        body {
            background: white !important;
            padding: 0;
        }

        .receipt-actions {
            display: none;
        }

        .receipt {
            box-shadow: none;
            border: 2px solid #dee2e6;
        }

        .btn {
            display: none;
        }
    }

    /* Responsive */
    @media (max-width: 480px) {
        .receipt {
            padding: 20px;
        }

        .receipt-info {
            grid-template-columns: 1fr;
            gap: 15px;
        }

        .member-info {
            flex-direction: column;
            text-align: center;
        }

        .receipt-actions {
            flex-direction: column;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>

    <div class="receipt-container">
        <!-- Botones de Acción -->
        <div class="receipt-actions">
            <button class="btn btn-secondary" onclick="volver()">
                <i class="fas fa-arrow-left"></i> Volver
            </button>
            <button class="btn btn-print">
                <i class="fas fa-print"></i> Imprimir Recibo
            </button>
            <a href="facturasPDF/recibo.php?id_pago=<?php echo htmlspecialchars($pago->id_pago); ?>" type="button" class="btn btn-primary">
                <i class="fas fa-download"></i> Descargar PDF
            </a>
        </div>

        <!-- Recibo -->
        <div class="receipt" id="receiptContent">
            <!-- Encabezado -->
            <div class="receipt-header">
                <div class="gym-logo">
                    <i class="fas fa-dumbbell"></i>
                    <div class="gym-name">Power<span>Fit</span></div>
                </div>
                <h1 class="receipt-title">RECIBO DE PAGO</h1>
                <p class="receipt-subtitle">Comprobante oficial de transacción</p>
            </div>

            <!-- Información del Recibo -->
            <div class="receipt-info">
                <div class="info-group">
                    <span class="info-label">Número de Recibo</span>
                    <span class="info-value">#REC-2024-00158</span>
                </div>
                <div class="info-group">
                    <span class="info-label">Fecha de Emisión</span>
                    <span class="info-value" id="fechaEmision"><?php echo htmlspecialchars(date('Y-m-d')); ?></span>
                </div>
            </div>

            <!-- Información del Socio -->
            <div class="member-info">
                <div class="member-avatar"><?php echo htmlspecialchars(substr($pago->nombre_socio, 0, 2)); ?></div>
                <div class="member-details">
                    <h3><?php echo htmlspecialchars($pago->nombre_socio); ?></h3>
                    <p>#SOC-<?php echo htmlspecialchars($pago->id_socio); ?> | <?php echo htmlspecialchars($pago->email_socio); ?></p>
                    <p><?php echo htmlspecialchars($pago->telefono_socio); ?></p>
                </div>
            </div>

            <!-- Detalles del Pago -->
            <div class="payment-details">
                <div class="detail-row">
                    <span class="detail-label">Membresía</span>
                    <span class="detail-value">
                        <span class="membership-type"><?php echo htmlspecialchars($pago->nombre_membresia); ?></span>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Monto Pagado</span>
                    <span class="detail-value amount">$<?php echo htmlspecialchars($pago->monto); ?> USD</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Fecha y Hora de Pago</span>
                    <span class="detail-value">
                        <?php 
                            // Formatear fecha y hora de forma legible
                            $fecha = new DateTime($pago->fecha_pago);
                            $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                            $dia = $fecha->format('d');
                            $mes = $meses[(int)$fecha->format('m') - 1];
                            $anio = $fecha->format('Y');
                            $hora = $fecha->format('H:i');
                            echo htmlspecialchars("$dia $mes $anio, $hora");
                            ?>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Método de Pago</span>
                    <span class="detail-value">
                        <div class="payment-method">
                            <div class="payment-icon">
                                <i class="fas fa-credit-card"></i>
                            </div>
                            <?php echo htmlspecialchars(ucfirst($pago->metodo_pago)); ?>
                        </div>
                    </span>
                </div>
            </div>

            <!-- Footer -->
            <div class="receipt-footer">
                <div class="thank-you">¡Gracias por su preferencia!</div>
                <div class="contact-info">
                    PowerFit Gym • Av. Fitness 123, Ciudad • (123) 456-7890<br>
                    info@powerfitgym.com • www.powerfitgym.com
                </div>
            </div>
        </div>
    </div>

    <script>

        // Función para volver
        function volver() {
            if (confirm('¿Está seguro de que desea salir? Los cambios no guardados se perderán.')) {
                window.location.href = 'gestion_pagos.php';
            }
        }
    </script>
    <?php include_once "templates/footer.php"; ?>