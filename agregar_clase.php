<?php
require_once "autorizacion/auth.php"; // valida login y arranca sesión

// Generar token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Verificar que tenga rol de admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: index.php");
    exit();
}

require_once "templates/header.php";
require_once "conexion/bd.php";

// Obtener la lista de entrenadores
$sql = $conexion->prepare("SELECT id as id_entrenador,CONCAT(nombre,' ',apellido) as nombre FROM usuarios WHERE rol = 'entrenador'");
$sql->execute();
$entrenadores = $sql->fetchAll(PDO::FETCH_OBJ);
?>
<style>
    /* Estilos específicos de la página */

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

    .class-form-container {
        background-color: var(--card);
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        padding: 30px;
        margin-bottom: 20px;
        max-width: 800px;
        margin: 0 auto;
    }

    /* Sobrescribir page-header para tener flex */
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
        <h1>Crear Nueva Clase</h1>
        <a href="gestion_clases.html" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver a Clases
        </a>
    </div>

    <form id="classForm" action="controladores/agregar_clase.php" method="POST">
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
        <!-- Información Básica -->
        <div class="form-group">
            <label class="form-label" for="nombre">Nombre de la Clase *</label>
            <input type="text"
                class="form-control"
                id="nombre"
                name="nombre"
                required
                placeholder="Ej: Yoga Matutino, HIIT Cardio, etc.">
            <div class="form-help">El nombre debe ser descriptivo y atractivo para los miembros.</div>
        </div>

        <div class="form-group">
            <label class="form-label" for="descripcion">Descripción *</label>
            <textarea class="form-control form-textarea"
                id="descripcion"
                name="descripcion"
                required
                placeholder="Describe la clase, intensidad, beneficios, nivel requerido..."></textarea>
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
                        max="30"
                        required
                        placeholder="20">
                </div>
                <div class="form-help">Número máximo de participantes (1-30).</div>
            </div>

            <div class="form-group">
                <label class="form-label" for="duracion">Horario*</label>
                <div class="input-with-icon">
                    <i class="fas fa-clock input-icon"></i>
                    <input type="time"
                        class="form-control"
                        id="horario"
                        name="horario"
                        required>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="entrenador">Instructor *</label>
            <select class="form-select" id="entrenador" name="entrenador" required>
                <option value="">Seleccione un instructor</option>
                <?php foreach ($entrenadores as $item): ?>
                    <option value="<?php echo $item->id_entrenador; ?>"><?php echo $item->nombre; ?></option>
                <?php endforeach; ?>
            </select>
            <div class="form-help">Seleccione el instructor asignado para esta clase.</div>
        </div>

        <!-- Acciones del Formulario -->
        <div class="form-actions">
            <button type="button" class="btn btn-secondary" onclick="cancelar()">
                <i class="fas fa-times"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Guardar Clase
            </button>
        </div>
    </form>
</div>

<?php
require_once "templates/footer.php";
?>