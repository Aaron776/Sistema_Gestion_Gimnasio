<?php
session_start();

// Generar token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PowerFit Gym - Recuperar Contraseña</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
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
            background-color: #1a1a2e;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('https://images.unsplash.com/photo-1534438327276-14e5300c3a48?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80') no-repeat center center/cover;
            padding: 20px;
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

        .recovery-container {
            width: 100%;
            max-width: 450px;
        }

        .recovery-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo h1 {
            font-family: var(--font-main);
            font-size: 2.2rem;
            color: var(--light);
            margin-bottom: 5px;
        }

        .logo h1 span {
            color: var(--secondary);
        }

        .logo p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
        }

        .recovery-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .recovery-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--secondary), var(--primary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 2rem;
            box-shadow: 0 8px 25px rgba(231, 76, 60, 0.3);
        }

        .recovery-title {
            font-family: var(--font-main);
            font-size: 1.6rem;
            color: var(--light);
            margin-bottom: 10px;
        }

        .recovery-subtitle {
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--light);
            font-size: 0.95rem;
        }

        .form-control {
            width: 100%;
            padding: 15px 20px;
            border: none;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            color: var(--light);
            font-size: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--secondary);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 15px 30px;
            border-radius: 8px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            font-family: var(--font-main);
            width: 100%;
            font-size: 1rem;
        }

        .btn-primary {
            background-color: var(--secondary);
            color: white;
        }

        .btn-primary:hover {
            background-color: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        .btn-secondary {
            background-color: transparent;
            color: var(--light);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .btn-secondary:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-color: var(--secondary);
        }

        .recovery-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .recovery-footer p {
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 15px;
        }

        .back-to-login {
            color: var(--secondary);
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
        }

        .back-to-login:hover {
            color: #c0392b;
            text-decoration: underline;
        }

        /* Estados del formulario */
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            text-align: center;
        }

        .alert-success {
            background: rgba(46, 204, 113, 0.2);
            border-color: rgba(46, 204, 113, 0.3);
            color: #2ecc71;
        }

        .alert-error {
            background: rgba(231, 76, 60, 0.2);
            border-color: rgba(231, 76, 60, 0.3);
            color: #e74c3c;
        }

        .alert-info {
            background: rgba(52, 152, 219, 0.2);
            border-color: rgba(52, 152, 219, 0.3);
            color: #3498db;
        }

        .form-help {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.6);
            margin-top: 8px;
        }

        /* Paso de verificación */
        .verification-step {
            display: none;
        }

        .verification-code {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin: 20px 0;
        }

        .code-input {
            width: 50px;
            height: 60px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            color: var(--light);
            transition: all 0.3s ease;
        }

        .code-input:focus {
            outline: none;
            border-color: var(--secondary);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
        }

        .resend-code {
            text-align: center;
            margin-top: 20px;
        }

        .resend-link {
            color: var(--secondary);
            cursor: pointer;
            text-decoration: none;
            font-weight: 600;
        }

        .resend-link:hover {
            text-decoration: underline;
        }

        .resend-timer {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
        }

        /* Paso de nueva contraseña */
        .password-strength {
            margin-top: 10px;
        }

        .strength-bar {
            height: 6px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
            overflow: hidden;
            margin-bottom: 5px;
        }

        .strength-level {
            height: 100%;
            width: 0%;
            background: var(--secondary);
            transition: all 0.3s ease;
        }

        .strength-text {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.6);
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
            color: rgba(255, 255, 255, 0.6);
        }

        .password-toggle-icon:hover {
            color: var(--secondary);
        }

        /* Animaciones */
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

        .fade-in {
            animation: fadeIn 0.5s ease;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .recovery-card {
                padding: 30px 20px;
            }

            .logo h1 {
                font-size: 1.8rem;
            }

            .recovery-title {
                font-size: 1.4rem;
            }

            .code-input {
                width: 45px;
                height: 55px;
                font-size: 1.3rem;
            }

            .btn {
                padding: 12px 25px;
            }
        }
    </style>
</head>
<body>
    <div class="recovery-container">
        <div class="recovery-card fade-in">
            <!-- Logo -->
            <div class="logo">
                <h1>Power<span>Fit</span></h1>
                <p>Sistema de Recuperación de Contraseña</p>
            </div>
            <div id="step1" class="recovery-step">
                <div class="recovery-header">
                    <div class="recovery-icon">
                        <i class="fas fa-key"></i>
                    </div>
                    <h2 class="recovery-title">¿Olvidaste tu contraseña?</h2>
                    <p class="recovery-subtitle">
                        Ingresa tu dirección de correo electrónico y te enviaremos un enlace para restablecer tu contraseña.
                    </p>
                </div>

                <div id="alertContainer">
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
                </div>

                <form id="recoveryForm" method="POST" action="controladores/olvidar_password.php">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    <div class="form-group">
                        <label class="form-label" for="email">Correo Electrónico</label>
                        <input type="email" 
                               class="form-control" 
                               id="email" 
                               name="email" 
                               required 
                               placeholder="tu@email.com">
                        <div class="form-help">Ingresa el mismo email que usaste para registrarte.</div>
                    </div>

                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-paper-plane"></i> Recuperar Contraseña
                    </button>
                </form>

                <div class="recovery-footer">
                    <p>¿Recordaste tu contraseña?</p>
                    <a href="login.php" class="back-to-login">
                        <i class="fas fa-arrow-left"></i> Volver al Inicio de Sesión
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>