<?php
require_once "autorizacion/auth.php"; // valida login y arranca sesión

// Verificar que tenga rol de admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: acceso_denegado.php"); // si no lo mandamos al acceso denegado
    exit();
}

// Generar token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once "templates/header.php";
include_once "conexion/bd.php";

$id_solicitud = $_GET['id_solicitud'];

if (!isset($id_solicitud) || !is_numeric($id_solicitud)) {
    header("Location: listado_solicitudes_cancelar_membresias.php");
    exit();
}

// Obtener solicitud de cancelación de membresía para editarla
$sql = $conexion->prepare("SELECT id as id_solicitud,estado,id_membresia_usuario FROM solicitud_cancelacion WHERE id = :id_solicitud");
$sql->bindParam(':id_solicitud', $id_solicitud, PDO::PARAM_INT);
$sql->execute();
$solicitud = $sql->fetch(PDO::FETCH_OBJ);

if (!$solicitud) {
    $_SESSION['errores'] = ['Solicitud de cancelación de membresía no encontrada'];
    header("Location: listado_solicitudes_cancelar_membresias.php");
    exit();
}
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
    }

    /* Contenedor del formulario */
    .form-container {
        width: 100%;
        max-width: 500px;
        margin: 0 auto;
        padding: 0 20px;
    }

    /* Card del formulario */
    .form-card {
        background-color: var(--card);
        border-radius: 12px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        padding: 40px;
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

    .form-header {
        text-align: center;
        margin-bottom: 40px;
    }

    .form-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--accent), #2980b9);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        color: white;
        font-size: 2.5rem;
    }

    .form-header h1 {
        font-size: 1.8rem;
        color: var(--dark);
        margin-bottom: 10px;
        font-family: var(--font-main);
    }

    .form-header p {
        color: #6c757d;
        font-size: 1rem;
        line-height: 1.6;
    }

    /* Grupo de formulario */
    .form-group {
        margin-bottom: 30px;
    }

    .form-group label {
        display: block;
        margin-bottom: 12px;
        font-weight: 600;
        color: var(--dark);
        font-size: 1.1rem;
        font-family: var(--font-main);
    }

    .form-group label span {
        color: var(--danger);
    }

    /* Estilos para el select */
    .select-wrapper {
        position: relative;
    }

    .select-wrapper select {
        width: 100%;
        padding: 15px 20px;
        font-size: 1rem;
        font-family: var(--font-secondary);
        border: 2px solid #e9ecef;
        border-radius: 10px;
        background-color: white;
        appearance: none;
        cursor: pointer;
        transition: all 0.3s;
    }

    .select-wrapper select:focus {
        border-color: var(--accent);
        outline: none;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    }

    .select-wrapper i {
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--primary);
        pointer-events: none;
    }

    /* Botones */
    .form-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 30px;
        border-top: 1px solid #eee;
        flex-wrap: nowrap;
        gap: 15px;
    }

    .btn {
        padding: 10px 20px;
        border-radius: 8px;
        border: none;
        font-weight: 600;
        font-family: var(--font-secondary);
        cursor: pointer;
        transition: all 0.3s;
        font-size: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 120px;
    }

    .btn i {
        margin-right: 8px;
    }

    .btn-secondary {
        background-color: #6c757d;
        color: white;
    }

    .btn-secondary:hover {
        background-color: #5a6268;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--accent), #2980b9);
        color: white;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #2980b9, var(--accent));
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
    }

    .btn-primary:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none !important;
        box-shadow: none !important;
    }

    /* Alertas */
    .alert {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 30px;
        display: flex;
        align-items: center;
        font-weight: 600;
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border-left: 4px solid var(--success);
    }

    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border-left: 4px solid var(--danger);
    }

    .alert i {
        margin-right: 10px;
        font-size: 1.2rem;
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
        .form-container {
            padding: 0 15px;
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

        .form-card {
            padding: 30px 25px;
        }

        .form-header h1 {
            font-size: 1.6rem;
        }

        .form-actions {
            flex-direction: row;
            align-items: center;
        }

        .btn {
            width: auto;
            flex: 1;
        }
    }

    @media (max-width: 576px) {
        .form-card {
            padding: 25px 20px;
        }

        .form-icon {
            width: 70px;
            height: 70px;
            font-size: 2rem;
        }

        .form-header h1 {
            font-size: 1.4rem;
        }

        .form-header p {
            font-size: 0.95rem;
        }

        .form-group label {
            font-size: 1rem;
        }

        .select-wrapper select {
            padding: 12px 15px;
            font-size: 0.95rem;
        }
    }
</style>

<div class="content">
    <div class="form-container">
        <!-- Alertas -->
        <div id="alert-message" class="alert">
            <span id="alert-text"></span>
        </div>

        <!-- Card del formulario -->
        <div class="form-card">
            <div class="form-header">
                <div class="form-icon">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <h1>Cambiar Estado de Solicitud</h1>
                <p>Selecciona el nuevo estado para la solicitud de cancelación #<span id="solicitud-id"><?php echo $solicitud->id_solicitud; ?></span></p>
            </div>

            <form id="status-form" method="post" action="controladores/editar_solicitud_cancelar_membresia_socio.php">
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
                <input type="hidden" id="id_solicitud" name="id_solicitud" value="<?php echo $solicitud->id_solicitud; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="id_membresia_usuario" value="<?php echo $solicitud->id_membresia_usuario; ?>">
                <div class="form-group">
                    <label for="estado">Estado <span>*</span></label>
                    <div class="select-wrapper">
                        <select id="estado" name="estado" required>
                            <option value="">-- Seleccione un estado --</option>
                            <option value="pendiente" <?php echo $solicitud->estado === 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                            <option value="aprobada" <?php echo $solicitud->estado === 'aprobada' ? 'selected' : ''; ?>>Aprobada</option>
                            <option value="rechazada" <?php echo $solicitud->estado === 'rechazada' ? 'selected' : ''; ?>>Rechazada</option>
                        </select>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="listado_solicitudes_cancelar_membresias.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary" id="submit-btn">
                        <i class="fas fa-save"></i> Actualizar Estado
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once "templates/footer.php"; ?>