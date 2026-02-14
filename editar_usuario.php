<?php
require_once "autorizacion/auth.php"; // valida login y arranca sesión

// Generar token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Verificar que tenga rol de admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: acceso_denegado.php"); // si no lo mandamos al acceso denegado
    exit();
}
require_once "templates/header.php";
include_once "conexion/bd.php";

// Obtener el id del usuario a editar
$id_usuario = isset($_GET['id_usuario']) ? trim($_GET['id_usuario']) : null;

// Validación estricta
if(empty($id_usuario) || !is_numeric($id_usuario) || 
   !filter_var($id_usuario, FILTER_VALIDATE_INT) || $id_usuario <= 0){
    $_SESSION['errores'] = ['ID de usuario inválido'];
    header("Location: gestion_usuarios.php");
    exit();
}

// Obtener los detalles del usuario a editar
$sql = $conexion->prepare("SELECT id as id_usuario,nombre,apellido,email,telefono,rol,fecha_registro FROM usuarios WHERE id = :id");
$sql->bindParam(':id', $id_usuario,PDO::PARAM_INT);
$sql->execute();
$usuario = $sql->fetch(PDO::FETCH_OBJ);

if (!$usuario) {
     $_SESSION['errores'] = ['Usuario no encontrado'];
    header("Location: gestion_usuarios.php");
    exit();
}
?>

<style>
    /* Estilos específicos para el formulario de editar usuario */
    .user-form-container {
        background-color: var(--card);
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        padding: 30px;
        margin-bottom: 20px;
        max-width: 600px;
        margin: 0 auto;
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

    .user-info-badge {
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

    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        background-color: white;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--secondary);
        box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
    }

    .form-select {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        font-size: 0.95rem;
        background-color: white;
        cursor: pointer;
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

    .alert ul {
        margin: 0;
        padding-left: 20px;
    }

    .form-actions {
        display: flex;
        gap: 15px;
        justify-content: flex-end;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #dee2e6;
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

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }

    .status-badge {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .status-active {
        background: #d4edda;
        color: #155724;
    }

    .status-inactive {
        background: #f8d7da;
        color: #721c24;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .user-form-container {
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

        .form-actions {
            flex-direction: column;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 480px) {
        .page-header h1 {
            font-size: 1.5rem;
        }

        .user-form-container {
            padding: 15px;
        }
    }
</style>

    <!-- Contenido -->
    <div class="content">
        <div class="user-form-container">
            <div class="page-header">
                <h1>Editar Usuario</h1>
                <div>
                    <div class="user-info-badge">
                        <i class="fas fa-user"></i>
                        ID: <?php echo htmlspecialchars($usuario->id_usuario); ?>
                    </div>
                    <a href="gestion_usuarios.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver a Usuarios
                    </a>
                </div>
            </div>

            <!-- Formulario -->
            <form method="POST" action="controladores/editar_usuario.php" id="userForm">
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
                <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($usuario->id_usuario); ?>">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="nombre">Nombre *</label>
                        <input type="text"
                            class="form-control"
                            id="nombre"
                            name="nombre"
                            value="<?php echo htmlspecialchars($usuario->nombre); ?>"
                            required
                            placeholder="Ingrese el nombre">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="apellido">Apellido *</label>
                        <input type="text"
                            class="form-control"
                            id="apellido"
                            name="apellido"
                            value="<?php echo htmlspecialchars($usuario->apellido); ?>"
                            required
                            placeholder="Ingrese el apellido">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email *</label>
                    <input type="email"
                        class="form-control"
                        id="email"
                        name="email"
                        value="<?php echo htmlspecialchars($usuario->email); ?>"
                        required
                        placeholder="usuario@ejemplo.com">
                    <div class="form-help">El email debe ser único para cada usuario.</div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="telefono">Teléfono</label>
                    <input type="tel"
                        class="form-control"
                        id="telefono"
                        name="telefono"
                        value="<?php echo htmlspecialchars($usuario->telefono); ?>"
                        placeholder="+1 234 567 8900">
                    <div class="form-help">Formato internacional recomendado.</div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="rol">Rol *</label>
                        <select class="form-select" id="rol" name="rol" required>
                            <option value="">Seleccione un rol</option>
                            <option value="admin" <?php echo ($usuario->rol === 'admin') ? 'selected' : ''; ?>>Administrador</option>
                            <option value="entrenador" <?php echo ($usuario->rol === 'entrenador') ? 'selected' : ''; ?>>Entrenador</option>
                        </select>
                        <div class="form-help">
                            <strong>Administrador:</strong> Acceso completo al sistema.<br>
                            <strong>Entrenador:</strong> Puede gestionar clases y miembros.
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Actualizar Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle sidebar
        document.querySelector('.toggle-sidebar').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
            document.querySelector('.main-content').classList.toggle('active');
        });

        // Validación del formulario
        document.getElementById('userForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const rol = document.getElementById('rol').value;
            const estado = document.getElementById('estado').value;

            if (password && password.length < 6) {
                e.preventDefault();
                alert('La contraseña debe tener al menos 6 caracteres.');
                return;
            }

            if (!rol) {
                e.preventDefault();
                alert('Por favor, seleccione un rol.');
                return;
            }

            if (!estado) {
                e.preventDefault();
                alert('Por favor, seleccione un estado.');
                return;
            }
        });

        // Mostrar/ocultar contraseña
        window.togglePassword = function() {
            const passwordInput = document.getElementById('password');
            const passwordIcon = document.getElementById('passwordIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                passwordIcon.className = 'fas fa-eye';
            }
        }

        // Validación en tiempo real
        const inputs = document.querySelectorAll('input[required], select[required]');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
        });

        function validateField(field) {
            if (!field.value.trim()) {
                field.style.borderColor = '#e74c3c';
            } else {
                field.style.borderColor = '#dee2e6';
            }

            // Validación específica para email
            if (field.type === 'email' && field.value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(field.value)) {
                    field.style.borderColor = '#e74c3c';
                } else {
                    field.style.borderColor = '#dee2e6';
                }
            }

            // Validación específica para contraseña (solo si se completa)
            if (field.id === 'password' && field.value) {
                if (field.value.length < 6) {
                    field.style.borderColor = '#e74c3c';
                } else {
                    field.style.borderColor = '#dee2e6';
                }
            }
        }

        // Formateo automático de teléfono
        const telefonoInput = document.getElementById('telefono');
        telefonoInput.addEventListener('input', function(e) {
            // Remover cualquier carácter que no sea número o +
            let value = e.target.value.replace(/[^\d+]/g, '');

            // Limitar a 15 caracteres
            if (value.length > 15) {
                value = value.substring(0, 15);
            }

            e.target.value = value;
        });
    });
</script>

<?php
require_once 'templates/footer.php';
?>
