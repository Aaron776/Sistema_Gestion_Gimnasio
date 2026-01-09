<?php
require_once "autorizacion/auth.php"; // valida login y arranca sesión

// Generar token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Verificar que tenga rol de socio
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'socio') {
    header("Location: index.php"); // si no lo mandamos al login
    exit();
}
require_once "templates/header.php";
include_once "conexion/bd.php";

$id_socio = $_SESSION['id_usuario']; // Obtenemos el id del socio que esta logueado

if (!isset($id_socio) || $id_socio === null || !is_numeric($id_socio)) {
    header("Location: dash_cliente.php");
    exit();
}

?>
<style>
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
        max-width: 500px;
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

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: var(--dark);
        font-family: var(--font-main);
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

    .btn-outline {
        background-color: transparent;
        color: var(--primary);
        border: 1px solid var(--primary);
    }

    .btn-outline:hover {
        background-color: var(--primary);
        color: white;
    }
</style>

<div class="content">
    <div class="card">
        <div class="card-header">
            <h2>Cambiar Contraseña</h2>
        </div>

        <form action="controladores/actualizar_password.php" method="POST">
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
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="id_socio" value="<?php echo $id_socio; ?>">

            <div class="form-group">
                <label for="password_actual" class="form-label">Contraseña Actual</label>
                <input type="password" name="password_actual" id="password_actual" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="password_nueva" class="form-label">Nueva Contraseña</label>
                <input type="password" name="password_nueva" id="password_nueva" class="form-control" required>
            </div>

            <div class="form-actions">
                <a href="dash_cliente.php" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<?php
require_once "templates/footer.php";
?>