<?php
require_once "autorizacion/auth.php"; // valida login y arranca sesión

// Generar token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Verificar que tenga rol de entrenador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'entrenador') {
    header("Location: acceso_denegado.php"); // si no lo mandamos al acceso denegado
    exit();
}
require_once "templates/header.php";
include_once "conexion/bd.php";

$id_entrenador = $_SESSION['id_usuario']; // Obtenemos el id del entrenador que esta logueado

// Obtener las clases que imparte el entrenador
$id_entrenador = $_SESSION['id_usuario'];
$sql = $conexion->prepare("SELECT id as id_clase,nombre FROM clases WHERE id_entrenador = :id_entrenador ORDER BY id DESC LIMIT 5");
$sql->bindParam(':id_entrenador', $id_entrenador, PDO::PARAM_INT);
$sql->execute();
$clases = $sql->fetchAll(PDO::FETCH_OBJ);
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
    }

    .alert {
        padding: 10px 15px;
        margin-bottom: 15px;
        border-radius: 3px;
        font-size: 14px;
    }

    .alert-danger {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
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

    .notification-bell {
        position: relative;
        margin-right: 20px;
        font-size: 1.2rem;
        color: white;
        cursor: pointer;
    }

    .notification-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background: var(--secondary);
        color: white;
        border-radius: 50%;
        width: 18px;
        height: 18px;
        font-size: 0.7rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Contenido */
    .content {
        padding: 20px;
    }

    .page-header {
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #dee2e6;
    }

    .page-header h1 {
        font-size: 1.8rem;
        color: var(--dark);
        margin-bottom: 5px;
        font-family: var(--font-main);
    }

    .breadcrumb {
        display: flex;
        list-style: none;
        padding: 0;
        margin: 0;
        font-size: 0.9rem;
        color: var(--primary);
    }

    .breadcrumb li:not(:last-child):after {
        content: "/";
        margin: 0 8px;
    }

    /* Mensajes de alerta */
    .alert {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        font-weight: 600;
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border-left: 4px solid var(--success);
    }

    .alert-error {
        background-color: #f8d7da;
        color: #721c24;
        border-left: 4px solid var(--danger);
    }

    .alert i {
        margin-right: 10px;
        font-size: 1.2rem;
    }

    /* Formulario de Asistencia */
    .asistencia-form-container {
        background-color: var(--card);
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        padding: 25px;
        margin-bottom: 30px;
    }

    .asistencia-form-container h2 {
        font-size: 1.5rem;
        margin-bottom: 20px;
        color: var(--dark);
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
        font-family: var(--font-main);
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: var(--dark);
    }

    .form-group label .required {
        color: var(--danger);
    }

    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 1rem;
        font-family: var(--font-secondary);
        transition: border-color 0.3s;
    }

    .form-control:focus {
        border-color: var(--accent);
        outline: none;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
    }

    .form-select-wrapper {
        position: relative;
    }

    .form-select-wrapper i {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--primary);
        pointer-events: none;
    }

    /* Tarjeta de información de clase */
    .clase-info-card {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        border-left: 4px solid var(--accent);
        display: none;
    }

    .clase-info-card.active {
        display: block;
        animation: fadeIn 0.5s;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .clase-info-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .clase-info-header h3 {
        color: var(--dark);
        font-size: 1.3rem;
        font-family: var(--font-main);
    }

    .clase-badge {
        background-color: var(--accent);
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .clase-details {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
        margin-top: 15px;
    }

    .clase-detail-item {
        display: flex;
        align-items: center;
    }

    .clase-detail-item i {
        margin-right: 10px;
        color: var(--accent);
        font-size: 1.1rem;
    }

    .clase-detail-item span {
        font-weight: 600;
        color: var(--dark);
    }

    /* Radio buttons para asistencia */
    .asistencia-options {
        display: flex;
        gap: 20px;
        margin-top: 10px;
    }

    .radio-option {
        display: flex;
        align-items: center;
    }

    .radio-option input[type="radio"] {
        margin-right: 8px;
        transform: scale(1.2);
    }

    .radio-label {
        display: flex;
        align-items: center;
        padding: 10px 15px;
        border-radius: 6px;
        border: 1px solid #ddd;
        cursor: pointer;
        transition: all 0.3s;
    }

    .radio-label:hover {
        background-color: #f0f0f0;
    }

    .radio-option input[type="radio"]:checked+.radio-label {
        border-color: var(--accent);
        background-color: rgba(52, 152, 219, 0.1);
        color: var(--accent);
        font-weight: 600;
    }

    .radio-label i {
        margin-right: 8px;
    }

    /* Textarea para observaciones */
    .textarea-wrapper {
        position: relative;
    }

    .char-count {
        position: absolute;
        bottom: 10px;
        right: 15px;
        font-size: 0.85rem;
        color: #6c757d;
    }

    /* Botones */
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }

    .btn {
        padding: 12px 25px;
        border-radius: 6px;
        border: none;
        font-weight: 600;
        font-family: var(--font-secondary);
        cursor: pointer;
        transition: all 0.3s;
        font-size: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .btn i {
        margin-right: 8px;
    }

    .btn-primary {
        background-color: var(--accent);
        color: white;
    }

    .btn-primary:hover {
        background-color: #2980b9;
        transform: translateY(-2px);
    }

    .btn-secondary {
        background-color: #6c757d;
        color: white;
    }

    .btn-secondary:hover {
        background-color: #5a6268;
    }

    .btn-success {
        background-color: var(--success);
        color: white;
    }

    .btn-success:hover {
        background-color: #27ae60;
        transform: translateY(-2px);
    }

    /* Historial de asistencias */
    .historial-container {
        background-color: var(--card);
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        padding: 25px;
        margin-top: 30px;
    }

    .historial-container h2 {
        font-size: 1.5rem;
        margin-bottom: 20px;
        color: var(--dark);
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
        font-family: var(--font-main);
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table th,
    .table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #dee2e6;
    }

    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
        color: var(--dark);
        font-family: var(--font-main);
    }

    .table tr:hover {
        background-color: #f8f9fa;
    }

    .asistencia-badge {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .asistencia-presente {
        background: #d4edda;
        color: #155724;
    }

    .asistencia-ausente {
        background: #f8d7da;
        color: #721c24;
    }

    .asistencia-tardia {
        background: #fff3cd;
        color: #856404;
    }

    /* Footer */
    .footer {
        background-color: var(--header);
        color: white;
        padding: 20px;
        text-align: center;
        margin-top: auto;
    }

    /* Responsive */
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

        .asistencia-options {
            flex-direction: column;
            gap: 10px;
        }

        .clase-details {
            grid-template-columns: 1fr;
        }

        .form-actions {
            flex-direction: column;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>
<div class="asistencia-form-container">
    <h2><i class="fas fa-calendar-check"></i> Nuevo Registro de Asistencia</h2>
    <form id="asistencia-form" method="POST" action="controladores/asistencia_entrenador.php">
        <?php if (isset($_SESSION['errores'])) : ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($_SESSION['errores'] as $error) : ?>
                        <li><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php unset($_SESSION['errores']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['exito'])) : ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= $_SESSION['exito']; ?>
            </div>
            <?php unset($_SESSION['exito']); ?>
        <?php endif; ?>
        <input type="hidden" id="id_entrenador" name="id_entrenador" value="<?php echo $id_entrenador; ?>">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <div class="form-group">
            <label>Fecha <span class="required">*</span></label>
            <input type="date" class="form-control" id="fecha" name="fecha" required>
        </div>
        <div class="form-group">
            <label>Hora de Entrada <span class="required">*</span></label>
            <input type="time" class="form-control" id="hora_entrada" name="hora_entrada" required>
        </div>
        <div class="form-group">
            <label>Hora de Salida <span class="required">*</span></label>
            <input type="time" class="form-control" id="hora_salida" name="hora_salida" required>
        </div>

        <div class="form-actions">
            <button type="reset" class="btn btn-secondary" id="reset-btn">
                <i class="fas fa-redo"></i> Limpiar
            </button>
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Registrar Asistencia
            </button>
        </div>
    </form>
</div>
<?php
require_once 'templates/footer.php';
?>