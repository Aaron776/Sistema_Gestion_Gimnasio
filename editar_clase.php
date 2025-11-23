<?php
require_once "autorizacion/auth.php";

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: index.php");
    exit();
}

require_once "templates/header.php";
include_once "conexion/bd.php";

// OBTENER ID DE LA CLASE DESDE POST O SESIÓN
if (isset($_POST['id_clase'])) {
    $_SESSION['id_clase_edicion'] = trim($_POST['id_clase']);
}

$id_clase = $_SESSION['id_clase_edicion'] ?? null;

// Validar que exista el ID
if (empty($id_clase) || !is_numeric($id_clase) || $id_clase <= 0) {
    $_SESSION['errores'] = ['Debe seleccionar una clase válida'];
    header("Location: gestion_clases.php");
    exit();
}

// Obtener lista de usuarios de rol entrenador
$sql_entrenadores = $conexion->prepare("SELECT id as id_usuario, nombre, apellido FROM usuarios WHERE rol = 'entrenador'");
$sql_entrenadores->execute();
$entrenadores = $sql_entrenadores->fetchAll(PDO::FETCH_OBJ);

// Obtener los detalles de la clase a editar
$sql = $conexion->prepare("SELECT clases.id as id_clase,clases.nombre,clases.descripcion,clases.horario,clases.cupo,usuarios.nombre as nombre_entrenador,usuarios.apellido as apellido_entrenador,clases.id_entrenador as id_entrenador FROM clases INNER JOIN usuarios ON clases.id_entrenador = usuarios.id WHERE clases.id = :id");
$sql->bindParam(':id', $id_clase, PDO::PARAM_INT);
$sql->execute();
$clase = $sql->fetch(PDO::FETCH_OBJ);

if (!$clase) {
    die("Clase no encontrada.");
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

    /* Layout Principal */
    .wrapper {
        display: flex;
        min-height: 100vh;
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

    .class-form-container {
        background-color: var(--card);
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        padding: 30px;
        margin-bottom: 20px;
        max-width: 800px;
        margin: 0 auto;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 1px solid #dee2e6;
    }

    .page-header h1 {
        font-size: 1.8rem;
        color: var(--dark);
        margin-bottom: 0;
        font-family: var(--font-main);
    }

    .class-info-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 15px;
        background: #f8f9fa;
        border-radius: 20px;
        font-size: 0.9rem;
        color: var(--dark);
        margin-top: 10px;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: 5px;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        font-family: var(--font-main);
        font-size: 0.9rem;
    }

    .btn-primary {
        background-color: var(--secondary);
        color: white;
    }

    .btn-primary:hover {
        background-color: #c0392b;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .btn-secondary {
        background-color: #6c757d;
        color: white;
    }

    .btn-secondary:hover {
        background-color: #5a6268;
    }

    /* Estilos del Formulario */
    .form-group {
        margin-bottom: 25px;
    }

    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: var(--dark);
        font-size: 0.95rem;
    }

    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        background-color: white;
        font-family: var(--font-secondary);
    }

    .form-control:focus {
        outline: none;
        border-color: var(--secondary);
        box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
    }

    .form-textarea {
        min-height: 100px;
        resize: vertical;
    }

    .form-select {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        font-size: 0.95rem;
        background-color: white;
        cursor: pointer;
        font-family: var(--font-secondary);
    }

    .form-select:focus {
        outline: none;
        border-color: var(--secondary);
        box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
    }

    .form-help {
        font-size: 0.85rem;
        color: #6c757d;
        margin-top: 5px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .input-with-icon {
        position: relative;
    }

    .input-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
    }

    .input-with-icon .form-control {
        padding-left: 45px;
    }

    .form-actions {
        display: flex;
        gap: 15px;
        justify-content: flex-end;
        margin-top: 40px;
        padding-top: 25px;
        border-top: 1px solid #e9ecef;
    }

    .alert {
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
        border: 1px solid transparent;
    }

    .alert-success {
        background-color: #d4edda;
        border-color: #c3e6cb;
        color: #155724;
    }

    .alert-error {
        background-color: #f8d7da;
        border-color: #f5c6cb;
        color: #721c24;
    }

    /* Horarios Section */
    .schedules-section {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-top: 10px;
    }

    .schedules-title {
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 15px;
        font-size: 1rem;
    }

    .schedule-item {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr auto;
        gap: 10px;
        align-items: end;
        margin-bottom: 15px;
        padding: 15px;
        background: white;
        border-radius: 5px;
        border: 1px solid #e9ecef;
    }

    .schedule-actions {
        display: flex;
        gap: 5px;
    }

    .btn-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-add {
        background: var(--success);
        color: white;
    }

    .btn-add:hover {
        background: #218838;
    }

    .btn-remove {
        background: var(--secondary);
        color: white;
    }

    .btn-remove:hover {
        background: #c0392b;
    }

    /* Estado de la Clase */
    .status-section {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-top: 20px;
    }

    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-active {
        background: #d4edda;
        color: #155724;
    }

    .status-inactive {
        background: #f8d7da;
        color: #721c24;
    }

    /* Vista Previa */
    .preview-section {
        margin-top: 30px;
        padding: 25px;
        background: #f8f9fa;
        border-radius: 8px;
        border: 2px dashed #dee2e6;
    }

    .preview-title {
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 15px;
        font-size: 1.1rem;
    }

    .preview-card {
        background: white;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        max-width: 400px;
        margin: 0 auto;
    }

    .preview-header {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 15px;
    }

    .preview-icon {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
        background: var(--info);
    }

    .preview-name {
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 5px;
        font-family: var(--font-main);
    }

    .preview-details {
        color: #6c757d;
        font-size: 0.9rem;
    }

    .preview-info {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        margin-top: 15px;
    }

    .preview-item {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .preview-label {
        font-size: 0.8rem;
        color: #6c757d;
        text-transform: uppercase;
        font-weight: 600;
    }

    .preview-value {
        font-weight: 600;
        color: var(--dark);
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

        .class-form-container {
            padding: 20px;
            margin: 10px;
        }

        .page-header {
            flex-direction: column;
            gap: 15px;
            align-items: flex-start;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .schedule-item {
            grid-template-columns: 1fr;
            gap: 10px;
        }

        .form-actions {
            flex-direction: column;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }

        .preview-info {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 480px) {
        .page-header h1 {
            font-size: 1.5rem;
        }

        .class-form-container {
            padding: 15px;
        }
    }
</style>

<div class="class-form-container">
    <div class="page-header">
        <h1>Editar Clase</h1>
        <div>
            <div class="class-info-badge">
                <i class="fas fa-info-circle"></i>
                ID: CLS-<?php echo htmlspecialchars($clase->id_clase); ?> | Creada: 15/03/2024
            </div>
            <a href="gestion_clases.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver a Clases
            </a>
        </div>
    </div>

    <form id="classForm" action="controladores/editar_clase.php" method="POST">
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
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="hidden" name="id_clase" value="<?php echo htmlspecialchars($clase->id_clase); ?>">
        <div class="form-group">
            <label class="form-label" for="nombre">Nombre de la Clase *</label>
            <input type="text"
                class="form-control"
                id="nombre"
                name="nombre"
                required
                value="<?php echo htmlspecialchars($clase->nombre); ?>"
                placeholder="Ej: Yoga Matutino, HIIT Cardio, etc.">
            <div class="form-help">El nombre debe ser descriptivo y atractivo para los miembros.</div>
        </div>

        <div class="form-group">
            <label class="form-label" for="descripcion">Descripción *</label>
            <textarea class="form-control form-textarea"
                id="descripcion"
                name="descripcion"
                required
                placeholder="Describe la clase, intensidad, beneficios, nivel requerido..."><?php echo htmlspecialchars($clase->descripcion); ?></textarea>
            <div class="form-help">Una descripción clara ayuda a los miembros a elegir la clase adecuada.</div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="cupo">Cupo Máximo *</label>
                <div class="input-with-icon">
                    <i class="fas fa-users input-icon"></i>
                    <input type="number"
                        class="form-control"
                        id="cupo"
                        name="cupo"
                        min="1"
                        
                        required
                        value="<?php echo htmlspecialchars($clase->cupo); ?>"
                        placeholder="20">
                </div>
                <div class="form-help">Número máximo de participantes (1-50).</div>
            </div>

            <div class="form-group">
                <label class="form-label" for="duracion">Horario *</label>
                <div class="input-with-icon">
                    <i class="fas fa-clock input-icon"></i>
                    <input type="time"
                        class="form-control"
                        id="horario"
                        name="horario"
                        required
                        value="<?php echo htmlspecialchars($clase->horario); ?>">
                </div>
                <div class="form-help">Duración en minutos (15-180).</div>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="entrenador">Instructor *</label>
            <select class="form-select" id="entrenador" name="entrenador" required>
                <option value="">Seleccione un instructor</option>
                <?php foreach ($entrenadores as $item): ?>
                    <option value="<?php echo $item->id_usuario; ?>"
                        <?php if ($clase->id_entrenador == $item->id_usuario) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($item->nombre . ' ' . $item->apellido); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="form-help">Seleccione el instructor asignado para esta clase.</div>
        </div>

        <!-- Acciones del Formulario -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Actualizar Clase
            </button>
        </div>
    </form>
</div>
</div>
</div>
</div>
<?php
require_once "templates/footer.php";
?>