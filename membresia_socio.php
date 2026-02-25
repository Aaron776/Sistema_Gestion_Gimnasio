<?php
require_once "autorizacion/auth.php"; // valida login y arranca sesión

// Verificar que tenga rol de socio
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'socio') {
    header("Location: acceso_denegado.php"); // si no lo mandamos al acceso denegado
    exit();
}
require_once "templates/header.php";
include_once "conexion/bd.php";

$id_socio = $_SESSION['id_usuario']; // id del socio logueado

// Verificar si el socio tiene membresía
$sql = $conexion->prepare("SELECT membresia_usuario.id as id_membresia, membresia_usuario.fecha_inicio as fecha_inicio, membresia_usuario.fecha_fin as fecha_fin,membresias.nombre as nombre_membresia FROM membresia_usuario INNER JOIN membresias ON membresia_usuario.id_membresia = membresias.id WHERE membresia_usuario.id_usuario = :id_socio AND membresia_usuario.estado = 'activa'");
$sql->bindParam(':id_socio', $id_socio, PDO::PARAM_INT);
$sql->execute();
$membresia = $sql->fetch(PDO::FETCH_OBJ);

// Fecha inicio de la membresía del este socio
if($membresia){
    $fecha_inicio_membresia = $membresia->fecha_inicio;
}else{
    $fecha_inicio_membresia = null;
}

// Calcular horas transcurridas desde el inicio de la membresía
$inicio = new DateTime($fecha_inicio_membresia);
$ahora = new DateTime();
$intervalo = $inicio->diff($ahora);
$horas_transcurridas = ($intervalo->days * 24) + $intervalo->h;
// Permitir cancelar solo si han pasado menos de 48 horas
$mostrar_cancelar = $horas_transcurridas <= 48;

?>
<style>
    :root {
        --primary: #2c3e50;
        --secondary: #e74c3c;
        --accent: #3498db;
        --light: #ecf0f1;
        --dark: #2c3e50;
        --success: #2ecc71;
        --warning: #f39c12;
        --info: #17a2b8;
        --sidebar: #1a252f;
        --sidebar-hover: #2c3e50;
        --header: #2c3e50;
        --content: #ecf0f1;
        --card: #ffffff;
        --font-main: 'Montserrat', sans-serif;
        --font-secondary: 'Open Sans', sans-serif;
        --danger: #e74c3c;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: var(--font-secondary);
        background-color: var(--content);
        overflow-x: hidden;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    /* Layout Principal */
    .wrapper {
        display: flex;
        min-height: 100vh;
        width: 100%;
    }

    /* Sidebar */
    .sidebar {
        width: 250px;
        background-color: var(--sidebar);
        color: white;
        transition: all 0.3s;
        position: fixed;
        height: 100vh;
        z-index: 1000;
        box-shadow: 3px 0 10px rgba(0, 0, 0, 0.1);
    }

    .sidebar-header {
        padding: 20px;
        background-color: rgba(0, 0, 0, 0.2);
        text-align: center;
        border-bottom: 1px solid #2c3e50;
    }

    .sidebar-header h3 {
        color: white;
        margin: 0;
        font-size: 1.5rem;
        font-family: var(--font-main);
    }

    .sidebar-header h3 span {
        color: var(--secondary);
    }

    .sidebar-menu {
        padding: 10px 0;
    }

    .sidebar-menu ul {
        list-style: none;
    }

    .sidebar-menu li {
        position: relative;
    }

    .sidebar-menu a {
        display: flex;
        align-items: center;
        padding: 12px 20px;
        color: #c2c7d0;
        text-decoration: none;
        transition: all 0.3s;
        font-size: 0.95rem;
    }

    .sidebar-menu a:hover {
        color: white;
        background-color: var(--sidebar-hover);
    }

    .sidebar-menu a.active {
        color: white;
        background-color: var(--secondary);
        border-left: 4px solid var(--secondary);
    }

    .sidebar-menu i {
        margin-right: 10px;
        width: 20px;
        text-align: center;
        font-size: 1.1rem;
    }

    /* Contenido Principal */
    .main-content {
        flex: 1;
        margin-left: 250px;
        transition: all 0.3s;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    /* Header */
    .header {
        background-color: var(--header);
        padding: 15px 20px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: white;
        flex-shrink: 0;
    }

    .toggle-sidebar {
        background: none;
        border: none;
        font-size: 1.5rem;
        color: white;
        cursor: pointer;
        margin-right: 15px;
    }

    .header-left {
        display: flex;
        align-items: center;
    }

    .header-title {
        font-family: var(--font-main);
        font-size: 1.5rem;
    }

    .user-menu {
        display: flex;
        align-items: center;
    }

    .user-info {
        margin-right: 15px;
        text-align: right;
    }

    .user-name {
        font-weight: 600;
        color: white;
        font-family: var(--font-main);
    }

    .user-role {
        font-size: 0.8rem;
        color: #c2c7d0;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: var(--secondary);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-family: var(--font-main);
    }

    /* Contenido Principal - Centrado */
    .content {
        padding: 20px;
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: calc(100vh - 140px);
        /* Ajuste para header y footer */
    }

    /* Contenedor de membresía */
    .membership-container {
        width: 100%;
        max-width: 800px;
        margin: 0 auto;
        padding: 40px 20px;
    }

    /* Mensaje centrado */
    .centered-message {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 40px;
        background-color: var(--card);
        border-radius: 12px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        animation: fadeIn 0.6s ease-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .message-icon {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 30px;
        font-size: 3.5rem;
        color: white;
    }

    .message-icon.success {
        background: linear-gradient(135deg, var(--success), #27ae60);
    }

    .message-icon.warning {
        background: linear-gradient(135deg, var(--warning), #e67e22);
    }

    .message-icon.info {
        background: linear-gradient(135deg, var(--accent), #2980b9);
    }

    .message-content h1 {
        font-size: 2.2rem;
        color: var(--dark);
        margin-bottom: 20px;
        font-family: var(--font-main);
    }

    .message-content p {
        font-size: 1.1rem;
        color: #6c757d;
        line-height: 1.6;
        margin-bottom: 30px;
        max-width: 500px;
    }

    /* Botón principal */
    .primary-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 18px 40px;
        font-size: 1.2rem;
        font-weight: 700;
        color: white;
        background: linear-gradient(135deg, var(--success), #27ae60);
        border: none;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none;
        min-width: 250px;
        box-shadow: 0 4px 15px rgba(46, 204, 113, 0.3);
        font-family: var(--font-main);
        margin-top: 20px;
    }

    .primary-btn:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(46, 204, 113, 0.4);
        background: linear-gradient(135deg, #27ae60, var(--success));
    }

    .primary-btn:active {
        transform: translateY(-2px);
    }

    .primary-btn i {
        margin-right: 12px;
        font-size: 1.4rem;
    }

    /* Botón alternativo */
    .secondary-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 25px;
        font-size: 1rem;
        font-weight: 600;
        color: var(--dark);
        background: #f8f9fa;
        border: 2px solid #dee2e6;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none;
        margin-top: 20px;
    }

    .secondary-btn:hover {
        background: #e9ecef;
        border-color: var(--accent);
        color: var(--accent);
    }

    /* Botón de peligro */
    .danger-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 25px;
        font-size: 1rem;
        font-weight: 600;
        color: white;
        background: var(--danger);
        border: 2px solid var(--danger);
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none;
        margin-top: 20px;
    }

    .danger-btn:hover {
        background: #c0392b;
        border-color: #c0392b;
        transform: translateY(-2px);
    }

    /* Información de membresía actual */
    .current-membership {
        background: linear-gradient(135deg, var(--accent), #2980b9);
        color: white;
        padding: 30px;
        border-radius: 12px;
        margin-bottom: 30px;
        box-shadow: 0 4px 20px rgba(52, 152, 219, 0.2);
    }

    .current-membership-header {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 15px;
    }

    .current-membership-header h2 {
        font-size: 1.8rem;
        font-family: var(--font-main);
    }

    .membership-badge {
        background: rgba(255, 255, 255, 0.2);
        padding: 8px 20px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.9rem;
        backdrop-filter: blur(10px);
    }

    .membership-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .detail-item {
        display: flex;
        flex-direction: column;
    }

    .detail-label {
        font-size: 0.9rem;
        opacity: 0.9;
        margin-bottom: 5px;
    }

    .detail-value {
        font-size: 1.2rem;
        font-weight: 600;
    }

    /* Tarjetas de membresías disponibles */
    .plans-container {
        margin-top: 40px;
    }

    .plans-header {
        text-align: center;
        margin-bottom: 40px;
    }

    .plans-header h2 {
        font-size: 2rem;
        color: var(--dark);
        margin-bottom: 15px;
        font-family: var(--font-main);
    }

    .plans-header p {
        color: #6c757d;
        font-size: 1.1rem;
        max-width: 600px;
        margin: 0 auto;
    }

    .plans-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 30px;
        margin-top: 30px;
    }

    .plan-card {
        background-color: var(--card);
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.3s;
        border: 2px solid transparent;
        position: relative;
        overflow: hidden;
    }

    .plan-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        border-color: var(--accent);
    }

    .plan-card.popular {
        border-color: var(--success);
        position: relative;
    }

    .popular-badge {
        position: absolute;
        top: 0;
        right: 0;
        background: linear-gradient(135deg, var(--success), #27ae60);
        color: white;
        padding: 8px 20px;
        font-size: 0.85rem;
        font-weight: 600;
        border-radius: 0 12px 0 12px;
    }

    .plan-header {
        text-align: center;
        margin-bottom: 25px;
        padding-bottom: 20px;
        border-bottom: 1px solid #eee;
    }

    .plan-name {
        font-size: 1.5rem;
        color: var(--dark);
        margin-bottom: 10px;
        font-family: var(--font-main);
    }

    .plan-price {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--accent);
        font-family: var(--font-main);
    }

    .plan-period {
        font-size: 1rem;
        color: #6c757d;
        margin-left: 5px;
    }

    .plan-features {
        list-style: none;
        margin-bottom: 30px;
    }

    .plan-features li {
        padding: 10px 0;
        display: flex;
        align-items: center;
    }

    .plan-features li i {
        margin-right: 10px;
        color: var(--success);
        font-size: 1.1rem;
    }

    .plan-btn {
        display: block;
        width: 100%;
        padding: 15px;
        text-align: center;
        background: var(--accent);
        color: white;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.3s;
        border: none;
        cursor: pointer;
    }

    .plan-btn:hover {
        background: #2980b9;
        transform: translateY(-2px);
    }

    /* Footer */
    .footer {
        background-color: var(--header);
        color: white;
        padding: 20px;
        text-align: center;
        margin-top: auto;
        flex-shrink: 0;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .plans-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .membership-details {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .sidebar {
            margin-left: -250px;
        }

        .sidebar.active {
            margin-left: 0;
        }

        .main-content {
            margin-left: 0;
        }

        .main-content.active {
            margin-left: 250px;
        }

        .header-title {
            display: none;
        }

        .plans-grid {
            grid-template-columns: 1fr;
        }

        .membership-details {
            grid-template-columns: 1fr;
        }

        .centered-message {
            padding: 30px 20px;
            margin: 0 15px;
        }

        .message-icon {
            width: 100px;
            height: 100px;
            font-size: 2.8rem;
        }

        .message-content h1 {
            font-size: 1.8rem;
        }
    }

    @media (max-width: 576px) {
        .current-membership-header {
            flex-direction: column;
            text-align: center;
        }

        .primary-btn {
            width: 100%;
            padding: 16px 20px;
            font-size: 1.1rem;
        }

        .plan-card {
            padding: 25px 20px;
        }
    }
</style>

<div class="membership-container">
    <?php if ($membresia) { ?>
        <div id="membership-status">
            <!-- Este contenido se mostrará si el socio YA tiene membresía -->
            <div class="centered-message">
                <div class="message-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="message-content">
                    <h1>¡Ya tienes una membresía activa!</h1>
                    <p>Actualmente cuentas con una membresía. Disfruta de todos los beneficios que ofrece nuestro gimnasio.</p>

                    <div class="current-membership">
                        <div class="current-membership-header">
                            <h2>Tu Membresía Actual</h2>
                        </div>

                        <div class="membership-details">
                            <div class="detail-item">
                                <span class="detail-label">Tipo de Membresía</span>
                                <span class="detail-value"><?php echo htmlspecialchars($membresia->nombre_membresia); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Fecha de Inicio</span>
                                <span class="detail-value"><?php echo htmlspecialchars($membresia->fecha_inicio); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Fecha de Vencimiento</span>
                                <span class="detail-value"><?php echo htmlspecialchars($membresia->fecha_fin); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Estado</span>
                                <span class="detail-value" style="color: var(--success);">Activa</span>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap; align-items: flex-start;">
                        <a href="clases_socio.php" class="secondary-btn">
                            <i class="fas fa-calendar-alt"></i> Ver mis clases
                        </a>
                        <?php if ($mostrar_cancelar): ?>
                            <div style="display: flex; flex-direction: column; align-items: center;">
                                <a href="solicitud_cancelacion_membresia_socio.php" class="danger-btn">
                                    <i class="fas fa-times-circle"></i> Cancelar Membresía
                                </a>
                                <p style="color: #e74c3c; font-size: 0.85rem; margin-top: 8px; max-width: 300px; text-align: center;">
                                    <i class="fas fa-exclamation-triangle"></i> Tienes un máximo de 48 horas desde tu inscripción para cancelar.
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php } else { ?>
        <!-- Adquirir membresía -->
        <div id="acquire-membership">
            <!-- Este contenido se mostrará si el socio NO tiene membresía -->
            <div class="centered-message">
                <div class="message-icon info">
                    <i class="fas fa-dumbbell"></i>
                </div>
                <div class="message-content">
                    <h1>¡Únete a PowerFit Gym!</h1>
                    <p>Adquiere una membresía para acceder a todas nuestras instalaciones, clases y beneficios exclusivos.</p>

                    <a href="adquirir_membresia_socio.php" type="button" class="primary-btn" id="acquire-btn">
                        <i class="fas fa-credit-card"></i> Adquirir Membresía
                    </a>

                    <p style="font-size: 0.9rem; color: #6c757d; margin-top: 20px;">
                        <i class="fas fa-info-circle"></i> Puedes seleccionar entre diferentes planes según tus necesidades.
                    </p>
                </div>
            </div>
        <?php } ?>
        </div>
</div>
</div>
</div>
<?php require_once "templates/footer.php"; ?>