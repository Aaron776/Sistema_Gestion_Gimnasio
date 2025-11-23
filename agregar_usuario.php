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
?>

<style>
    /* Estilos del formulario de agregar usuario */
    .content {
        min-height: calc(100vh - 150px);
        /* deja espacio para el footer */
        display: flex;
        justify-content: center;
        align-items: flex-start;
        padding: 40px 20px;
    }

    .user-form-container {
        background-color: var(--card);
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        padding: 30px;
        width: 100%;
        max-width: 600px;
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

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: var(--dark);
        font-size: 0.95rem;
    }

    .form-control,
    .form-select {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        background-color: white;
    }

    .form-control:focus,
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
        gap: 15px;
    }

    .password-toggle {
        position: relative;
    }

    .password-toggle-icon {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #6c757d;
    }

    .password-toggle-icon:hover {
        color: var(--secondary);
    }

    .form-actions {
        display: flex;
        gap: 15px;
        justify-content: flex-end;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #dee2e6;
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

    /* Responsivo */
    @media (max-width: 768px) {
        .form-row {
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

    footer {
        position: relative;
        width: 100%;
        bottom: 0;
        margin-top: auto;
    }
</style>

<div class="content">
    <div class="user-form-container">
        <div class="page-header">
            <h1>Agregar Usuario</h1>
        </div>

        <form method="POST" action="controladores/agregar_usuario.php" id="userForm">
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
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="nombre">Nombre *</label>
                    <input type="text" class="form-control" id="nombre" name="nombre"
                        value="<?php echo isset($nombre) ? htmlspecialchars($nombre) : ''; ?>"
                        required placeholder="Ingrese el nombre">
                </div>

                <div class="form-group">
                    <label class="form-label" for="apellido">Apellido *</label>
                    <input type="text" class="form-control" id="apellido" name="apellido"
                        value="<?php echo isset($apellido) ? htmlspecialchars($apellido) : ''; ?>"
                        required placeholder="Ingrese el apellido">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Telefono *</label>
                <input type="text" class="form-control" id="telefono" name="telefono"
                    value="<?php echo isset($telefono) ? htmlspecialchars($telefono) : ''; ?>"
                    required placeholder="099999999">
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email *</label>
                <input type="email" class="form-control" id="email" name="email"
                    value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"
                    required placeholder="usuario@ejemplo.com">
                <div class="form-help">El email debe ser único para cada usuario.</div>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Contraseña *</label>
                <div class="password-toggle">
                    <input type="password" class="form-control" id="password" name="password"
                        required minlength="5" placeholder="Mínimo 6 caracteres">
                    <span class="password-toggle-icon" onclick="togglePassword()">
                        <i class="fas fa-eye" id="passwordIcon"></i>
                    </span>
                </div>
                <div class="form-help">La contraseña debe tener al menos 6 caracteres.</div>
            </div>

            <div class="form-group">
                <label class="form-label" for="rol">Rol *</label>
                <select class="form-select" id="rol" name="rol" required>
                    <option value="">Seleccione un rol</option>
                    <option value="admin" <?php echo (isset($rol) && $rol === 'administrador') ? 'selected' : ''; ?>>Administrador</option>
                    <option value="entrenador" <?php echo (isset($rol) && $rol === 'entrenador') ? 'selected' : ''; ?>>Entrenador</option>
                </select>
                <div class="form-help">
                    <strong>Administrador:</strong> Acceso completo al sistema.<br>
                    <strong>Entrenador:</strong> Puede gestionar clases y miembros.
                </div>
            </div>

            <div class="form-actions">
                <a href="gestion_usuarios.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar Usuario
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function togglePassword() {
        const input = document.getElementById('password');
        const icon = document.getElementById('passwordIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }
</script>

<?php require_once 'templates/footer.php'; ?>