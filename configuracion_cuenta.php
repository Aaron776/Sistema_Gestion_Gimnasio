<?php
require_once "autorizacion/auth.php"; // valida login y arranca sesión

// Generar token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Verificar que tenga rol de socio, entrenador o administrador
$roles_permitidos = ['socio', 'entrenador', 'admin'];
if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], $roles_permitidos)) {
    header("Location: acceso_denegado.php"); // si no lo mandamos al acceso denegado
    exit();
}
require_once "templates/header.php";
include_once "conexion/bd.php";

$id_usuario = $_SESSION['id_usuario']; // obtener id del usuario actual

// Obtener datos del usuario
$sql = $conexion->prepare("SELECT nombre,apellido,email,telefono FROM usuarios WHERE id = :id_usuario");
$sql->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
$sql->execute();
$usuario = $sql->fetch(PDO::FETCH_OBJ);

if (!$usuario) {
    $_SESSION['errores'] = ['Usuario no encontrado'];
    header("Location: dash_cliente.php");
    exit();
}
?>
<style>
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

    .content {
        padding: 20px;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: calc(100vh - 140px);
        width: 100%;
    }

    .card {
        background-color: var(--card);
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        padding: 30px;
        width: 100%;
        max-width: 600px;
    }

    .card-header {
        margin-bottom: 30px;
        text-align: center;
    }

    .card-header h2 {
        font-size: 1.5rem;
        color: var(--dark);
        font-family: var(--font-main);
        margin-bottom: 10px;
    }

    .card-header p {
        color: #6c757d;
        font-size: 0.95rem;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-row {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
    }

    .form-row .form-group {
        flex: 1;
        margin-bottom: 0;
    }

    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: var(--dark);
        font-family: var(--font-main);
    }

    .form-label .required {
        color: var(--secondary);
    }

    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        font-family: var(--font-secondary);
        font-size: 1rem;
        transition: all 0.3s;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    }

    .form-control.error {
        border-color: var(--secondary);
    }

    .form-control.success {
        border-color: var(--success);
    }

    .form-help {
        font-size: 0.85rem;
        color: #6c757d;
        margin-top: 5px;
        display: block;
    }

    .form-actions {
        display: flex;
        gap: 15px;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #dee2e6;
    }

    .btn {
        padding: 12px 24px;
        border-radius: 4px;
        border: none;
        cursor: pointer;
        font-size: 1rem;
        font-weight: 600;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-family: var(--font-main);
        flex: 1;
    }

    .btn-primary {
        background-color: var(--primary);
        color: white;
    }

    .btn-primary:hover {
        background-color: #1a252f;
    }

    .btn-secondary {
        background-color: #6c757d;
        color: white;
    }

    .btn-secondary:hover {
        background-color: #545b62;
    }

    .btn-outline {
        background-color: transparent;
        color: var(--primary);
        border: 1px solid var(--primary);
    }

    .btn-outline:hover {
        background-color: var(--primary);
        color: white;
    }

    .avatar-section {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-bottom: 30px;
    }

    .avatar-container {
        position: relative;
        margin-bottom: 15px;
    }

    .user-avatar-large {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background-color: var(--secondary);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 2rem;
        font-family: var(--font-main);
        border: 4px solid white;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .avatar-change {
        position: absolute;
        bottom: 0;
        right: 0;
        background-color: var(--accent);
        color: white;
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        border: 3px solid white;
    }

    .avatar-name {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--dark);
        font-family: var(--font-main);
    }

    .form-message {
        padding: 12px 15px;
        border-radius: 4px;
        margin-bottom: 20px;
        display: none;
    }

    .form-message.success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
        display: block;
    }

    .form-message.error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
        display: block;
    }

    @media (max-width: 768px) {
        .main-content {
            margin-left: 0;
        }

        .content {
            padding: 15px;
        }

        .card {
            padding: 20px;
        }

        .form-row {
            flex-direction: column;
            gap: 20px;
        }

        .form-actions {
            flex-direction: column;
        }

        .btn {
            width: 100%;
        }
    }
</style>
<div class="content">
    <div class="card">
        <!-- Encabezado del formulario -->
        <div class="card-header">
            <h2>Actualizar Información Personal</h2>
            <p>Modifica tus datos de contacto y personales</p>
        </div>

        <!-- Avatar y nombre actual -->
        <div class="avatar-section">
            <div class="avatar-container">
                <div class="user-avatar-large"><?php echo htmlspecialchars(substr($usuario->nombre, 0, 2)); ?></div>
            </div>
            <div class="avatar-name"><?php echo htmlspecialchars($usuario->nombre . ' ' . $usuario->apellido) ; ?></div>
        </div>

        <!-- Formulario -->
        <form id="updateForm" method="POST" action="controladores/configuracion_cuenta.php">
            <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($id_usuario); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
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
            
            <div class="form-row">
                <div class="form-group">
                    <label for="nombre" class="form-label">
                        Nombre <span class="required">*</span>
                    </label>
                    <input
                        type="text"
                        id="nombre"
                        name="nombre"
                        class="form-control"
                        placeholder="Ingresa tu nombre"
                        value="<?php echo htmlspecialchars($usuario->nombre);?>"
                        required>
                    <span class="form-help">Tu nombre tal como aparece en tu identificación</span>
                </div>

                <div class="form-group">
                    <label for="apellido" class="form-label">
                        Apellido <span class="required">*</span>
                    </label>
                    <input
                        type="text"
                        id="apellido"
                        name="apellido"
                        class="form-control"
                        placeholder="Ingresa tu apellido"
                        value="<?php echo htmlspecialchars($usuario->apellido); ?>"
                        required>
                    <span class="form-help">Tu apellido paterno</span>
                </div>
            </div>

            <div class="form-group">
                <label for="email" class="form-label">
                    Email <span class="required">*</span>
                </label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control"
                    placeholder="ejemplo@correo.com"
                    value="<?php echo htmlspecialchars($usuario->email); ?>"
                    required>
                <span class="form-help">Este email se utilizará para notificaciones y recuperación de cuenta</span>
            </div>

            <div class="form-group">
                <label for="telefono" class="form-label">
                    Teléfono <span class="required">*</span>
                </label>
                <input
                    type="tel"
                    id="telefono"
                    name="telefono"
                    class="form-control"
                    placeholder="+52 123 456 7890"
                    value="<?php echo htmlspecialchars($usuario->telefono); ?>"
                    required>
                <span class="form-help">Incluye código de país y área</span>
            </div>

            <!-- Botones de acción -->
            <div class="form-actions">
                <button type="button" class="btn btn-outline" id="btnCancelar">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="submit" class="btn btn-primary" id="btnGuardar">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once "templates/footer.php"; ?>