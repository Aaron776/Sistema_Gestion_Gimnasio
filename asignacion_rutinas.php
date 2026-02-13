<?php
require_once "autorizacion/auth.php"; // valida login y arranca sesión

// Generar token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Verificar que tenga rol de entrenador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'entrenador') {
    header("Location: index.php"); // si no lo mandamos al login
    exit();
}
require_once "templates/header.php";
include_once "conexion/bd.php";

$id_entrenador = $_SESSION['id_usuario']; // Obtenemos el id del entrenador que esta logueado

// Obtener socios
$sql = $conexion->prepare("SELECT id as id_socio,CONCAT(nombre,' ',apellido) as nombre FROM usuarios WHERE rol = 'socio' ");
$sql->execute();
$socios = $sql->fetchAll(PDO::FETCH_OBJ);
?>
<style>
    

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

    /* Formulario Principal */
    .form-container {
        background-color: var(--card);
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        padding: 30px;
        margin-bottom: 30px;
    }

    .form-container h2 {
        font-size: 1.5rem;
        margin-bottom: 25px;
        color: var(--dark);
        border-bottom: 1px solid #eee;
        padding-bottom: 15px;
        font-family: var(--font-main);
    }

    .form-container h2 i {
        color: var(--accent);
        margin-right: 10px;
    }

    /* Estilos de formulario */
    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 25px;
        margin-bottom: 25px;
    }

    .form-group {
        margin-bottom: 25px;
    }

    .form-group.full-width {
        grid-column: 1 / -1;
    }

    .form-group label {
        display: block;
        margin-bottom: 10px;
        font-weight: 600;
        color: var(--dark);
        font-size: 0.95rem;
    }

    .form-group label .required {
        color: var(--danger);
    }

    .form-group .label-info {
        font-size: 0.85rem;
        color: #6c757d;
        font-weight: normal;
        margin-top: 5px;
    }

    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 1rem;
        font-family: var(--font-secondary);
        transition: all 0.3s;
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

    /* Textarea para descripción */
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

    textarea.form-control {
        min-height: 200px;
        resize: vertical;
        line-height: 1.6;
    }

    /* Información del socio seleccionado */
    .socio-info-card {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-top: 10px;
        border-left: 4px solid var(--accent);
        display: none;
    }

    .socio-info-card.active {
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

    .socio-info-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .socio-info-header h3 {
        color: var(--dark);
        font-size: 1.3rem;
        font-family: var(--font-main);
    }

    .socio-badge {
        background-color: var(--success);
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .socio-details {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
        margin-top: 15px;
    }

    .socio-detail-item {
        display: flex;
        align-items: center;
    }

    .socio-detail-item i {
        margin-right: 10px;
        color: var(--accent);
        font-size: 1.1rem;
        width: 20px;
        text-align: center;
    }

    .socio-detail-item span {
        font-weight: 600;
        color: var(--dark);
        margin-right: 5px;
    }

    /* Sección de ejercicios */
    .ejercicios-section {
        margin-top: 30px;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }

    .section-header h3 {
        font-size: 1.3rem;
        color: var(--dark);
        font-family: var(--font-main);
    }

    .section-header h3 i {
        color: var(--accent);
        margin-right: 10px;
    }

    .add-ejercicio-btn {
        background-color: var(--accent);
        color: white;
        border: none;
        border-radius: 6px;
        padding: 8px 15px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        transition: background-color 0.3s;
    }

    .add-ejercicio-btn:hover {
        background-color: #2980b9;
    }

    .add-ejercicio-btn i {
        margin-right: 5px;
    }

    .ejercicios-list {
        margin-top: 20px;
    }

    .ejercicio-item {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 15px;
        border-left: 4px solid var(--warning);
    }

    .ejercicio-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .ejercicio-title {
        font-weight: 600;
        color: var(--dark);
        font-size: 1.1rem;
    }

    .remove-ejercicio {
        background: none;
        border: none;
        color: var(--danger);
        cursor: pointer;
        font-size: 1.2rem;
    }

    .ejercicio-details {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
    }

    .detail-group {
        margin-bottom: 10px;
    }

    .detail-group label {
        display: block;
        font-size: 0.85rem;
        color: #6c757d;
        margin-bottom: 5px;
    }

    .detail-group input {
        width: 100%;
        padding: 8px 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 0.95rem;
    }

    /* Botones */
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        margin-top: 40px;
        padding-top: 25px;
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

    /* Footer */
    /* Responsive */
    @media (max-width: 992px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .socio-details,
        .ejercicio-details {
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
<div class="page-header">
    <h1>Asignar Nueva Rutina a Socio</h1>
</div>
<div class="form-container">
    <h2><i class="fas fa-dumbbell"></i> Información de la Rutina</h2>

    <form id="rutina-form" method="post" action="controladores/asignacion_rutinas.php">
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
        <div class="form-row">
            <div class="form-group">
                <label for="socio_id">Socio <span class="required">*</span></label>
                <div class="form-select-wrapper">
                    <select class="form-control" id="socio_id" name="id_socio" required>
                        <option value="">-- Seleccione un socio --</option>
                        <?php foreach ($socios as $item) { ?>
                            <option value="<?php echo $item->id_socio; ?>"><?php echo $item->nombre; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <p class="label-info">Seleccione el socio al que se le asignará la rutina</p>
            </div>

            <div class="form-group">
                <label for="fecha_asignacion">Fecha de Asignación <span class="required">*</span></label>
                <input type="date" class="form-control" id="fecha_asignacion" name="fecha_asignacion" required>
                <p class="label-info">Fecha en la que se asigna la rutina</p>
            </div>
        </div>

        <div class="form-group full-width">
            <label for="descripcion">Descripción de la Rutina <span class="required">*</span></label>
            <div class="textarea-wrapper">
                <textarea class="form-control" id="descripcion" name="descripcion" rows="8" placeholder="Describa la rutina de ejercicios, incluyendo frecuencia, objetivos, restricciones, etc..." required></textarea>
                <div class="char-count" id="char-count">0/2000</div>
            </div>
            <p class="label-info">Incluya todos los detalles importantes de la rutina asignada</p>
        </div>

        <div class="form-actions">
            <button type="button" class="btn btn-secondary" id="reset-btn">
                <i class="fas fa-redo"></i> Limpiar Formulario
            </button>
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Asignar Rutina
            </button>
        </div>
    </form>
</div>
</div>
</div>
<?php
require_once 'templates/footer.php';
?>