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
/* --- BLOQUE DE ESTILOS NUEVO Y AISLADO PARA LA PÁGINA DE RECIBO --- */
:root {
    --r-bg: #f7fafc;
    --r-ink: #111827;
    --r-muted: #6b7280;
    --r-accent: #ef4444;
    --r-success: #10b981;
    --r-white: #ffffff;
    --r-radius: 12px;
    --r-font: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;
}

/* Wrapper de la página (aislado) */
#receiptPage { padding: 26px 12px; background: transparent; font-family: var(--r-font); color: var(--r-ink); }
#receiptPage .page-inner { max-width: 780px; margin: 0 auto; }
#receiptPage .receipt-container { width: 100%; margin: 18px auto; box-sizing: border-box; }

/* Acciones (botones) */
#receiptPage .receipt-actions { display:flex; gap:10px; justify-content:center; align-items:center; flex-wrap:wrap; margin-bottom:16px; }
#receiptPage .btn { display:inline-flex; align-items:center; gap:8px; padding:10px 14px; border-radius:8px; font-weight:700; cursor:pointer; border:none; background:transparent; color:var(--r-ink); }
#receiptPage .btn-primary { background:var(--r-accent); color:white; box-shadow:0 6px 18px rgba(239,68,68,0.12); }
#receiptPage .btn-print { background:var(--r-success); color:white; }
#receiptPage .btn-secondary { background:#64748b; color:white; }

/* Tarjeta de recibo */
#receiptPage .receipt { background:var(--r-white); border-radius:var(--r-radius); padding:22px; box-shadow:0 10px 30px rgba(15,23,42,0.06); overflow:hidden; }
#receiptPage .receipt::before { content:''; display:block; height:6px; width:100%; background:linear-gradient(90deg,var(--r-accent),#f97316); margin:0 0 12px 0; border-radius:6px; }

#receiptPage .receipt-header { text-align:center; margin-bottom:12px; }
#receiptPage .gym-logo { display:flex; gap:10px; align-items:center; justify-content:center; }
#receiptPage .gym-logo i { font-size:1.6rem; color:var(--r-accent); }
#receiptPage .gym-name { font-size:1.2rem; font-weight:800; letter-spacing:-0.2px; }
#receiptPage .receipt-title { font-weight:700; font-size:1.05rem; margin-top:6px; }
#receiptPage .receipt-subtitle { color:var(--r-muted); font-size:0.9rem; }

/* Info grid */
#receiptPage .receipt-info { display:grid; grid-template-columns:1fr; gap:10px; margin:12px 0; }
@media (min-width:640px){ #receiptPage .receipt-info{ grid-template-columns:1fr 1fr; } }
#receiptPage .info-label{ font-size:0.72rem; color:var(--r-muted); font-weight:800; text-transform:uppercase; }
#receiptPage .info-value{ font-weight:800; color:var(--r-ink); }

/* Member */
#receiptPage .member-info{ display:flex; gap:12px; align-items:center; padding:12px; background:linear-gradient(180deg,#fbfbfb,#f1f5f9); border-radius:8px; }
#receiptPage .member-avatar{ width:54px;height:54px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,var(--r-accent),#f97316);color:white;font-weight:800; }
#receiptPage .member-details h3{ margin:0; font-size:1rem; }
#receiptPage .member-details p{ margin:0; color:var(--r-muted); font-size:0.9rem; }

/* Detalles de pago */
#receiptPage .payment-details{ padding:12px; background:#fbfbfb; border-radius:8px; margin-top:12px; }
#receiptPage .detail-row{ display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f1f1f1; }
#receiptPage .detail-row:last-child{ border-bottom:none; }
#receiptPage .detail-label{ font-weight:700; color:var(--r-muted); }
#receiptPage .detail-value{ font-weight:800; }
#receiptPage .amount{ color:var(--r-success); font-size:1.05rem; font-weight:900; }
#receiptPage .membership-type{ background:var(--r-ink); color:white; padding:6px 12px; border-radius:16px; font-weight:800; font-size:0.8rem; }

/* QR y Footer */
#receiptPage .qr-code{ text-align:center; margin-top:12px; }
#receiptPage .receipt-footer{ text-align:center; margin-top:14px; padding-top:12px; border-top:1px dashed #f1f1f1; color:var(--r-muted); }

/* Optimización para impresión */
@media print{
    #receiptPage .receipt { box-shadow:none; padding:0; border:none; }
    #receiptPage .receipt-actions, #receiptPage .btn { display:none !important; }
    body{ background:white !important; }
}

/* Móvil */
@media (max-width:480px){
    #receiptPage .receipt{ padding:14px; }
    #receiptPage .member-info{ flex-direction:column; text-align:center; }
}

</style>

    <div id="receiptPage" class="page-inner">
        <div class="receipt-container">
        <!-- Botones de Acción -->
        <div class="receipt-actions">
            <button class="btn btn-secondary" onclick="volver()">
                <i class="fas fa-arrow-left"></i> Volver
            </button>
            <button class="btn btn-print" onclick="window.print()">
                <i class="fas fa-print"></i> Imprimir Recibo
            </button>
            <a href="facturasPDF/recibo.php?id_pago=<?php echo htmlspecialchars($pago->id_pago); ?>" type="button" class="btn btn-primary" target="_blank" rel="noopener noreferrer">
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