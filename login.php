<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PowerFit Gym - Iniciar Sesión</title>
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
            line-height: 1.6;
            color: var(--light);
            background-color: #1a1a2e;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('https://images.unsplash.com/photo-1534438327276-14e5300c3a48?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80') no-repeat center center/cover;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 1000px;
        }

        .auth-container {
            display: flex;
            width: 100%;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        }

        .auth-left {
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .auth-right {
            flex: 1;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .logo {
            font-family: var(--font-main);
            font-size: 2rem;
            font-weight: 800;
            color: var(--light);
            margin-bottom: 20px;
            text-align: center;
        }

        .logo span {
            color: var(--secondary);
        }

        .auth-form {
            width: 100%;
        }

        .form-title {
            font-family: var(--font-main);
            font-size: 1.8rem;
            margin-bottom: 10px;
            color: var(--light);
        }

        .form-subtitle {
            margin-bottom: 30px;
            opacity: 0.8;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .form-input {
            width: 100%;
            padding: 12px 15px;
            border: none;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.1);
            color: var(--light);
            font-size: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--secondary);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
        }

        .form-input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            font-size: 0.9rem;
            cursor: pointer;
            position: relative;
            padding-left: 30px;
        }

        .checkbox-label input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }

        .checkmark {
            position: absolute;
            left: 0;
            height: 20px;
            width: 20px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .checkbox-label:hover input ~ .checkmark {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .checkbox-label input:checked ~ .checkmark {
            background-color: var(--secondary);
            border-color: var(--secondary);
        }

        .checkmark:after {
            content: "";
            position: absolute;
            display: none;
        }

        .checkbox-label input:checked ~ .checkmark:after {
            display: block;
        }

        .checkbox-label .checkmark:after {
            left: 6px;
            top: 2px;
            width: 5px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .forgot-password {
            color: var(--secondary);
            font-size: 0.9rem;
            text-decoration: none;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            border-radius: 30px;
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
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .auth-switch {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9rem;
        }

        .auth-switch a {
            color: var(--secondary);
            font-weight: 600;
            text-decoration: none;
        }

        .auth-switch a:hover {
            text-decoration: underline;
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 25px 0;
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
        }

        .divider::before,
        .divider::after {
            content: "";
            flex: 1;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .divider span {
            padding: 0 15px;
        }

        .social-login {
            display: flex;
            gap: 15px;
        }

        .social-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            flex: 1;
            padding: 12px 20px;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .social-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .social-btn.google:hover {
            background: #db4437;
        }

        .social-btn.facebook:hover {
            background: #4267B2;
        }

        .welcome-title {
            font-family: var(--font-main);
            font-size: 2rem;
            margin-bottom: 20px;
        }

        .welcome-text {
            margin-bottom: 30px;
            opacity: 0.9;
            line-height: 1.7;
        }

        .auth-features {
            list-style: none;
            margin-bottom: 30px;
        }

        .auth-features li {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
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

        .auth-features i {
            margin-right: 10px;
            color: var(--secondary);
            font-size: 1.2rem;
            width: 20px;
            text-align: center;
        }

        .testimonial {
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid var(--secondary);
        }

        .testimonial-text {
            font-style: italic;
            margin-bottom: 15px;
            line-height: 1.6;
        }

        .testimonial-author strong {
            display: block;
            color: var(--light);
        }

        .testimonial-author span {
            font-size: 0.8rem;
            opacity: 0.7;
        }

        .form-message {
            padding: 12px;
            margin: 15px 0;
            border-radius: 5px;
            text-align: center;
            font-weight: 600;
            display: none;
        }

        .form-message.error {
            background: rgba(231, 76, 60, 0.2);
            border: 1px solid rgba(231, 76, 60, 0.5);
            color: #e74c3c;
            display: block;
        }

        .form-message.success {
            background: rgba(46, 204, 113, 0.2);
            border: 1px solid rgba(46, 204, 113, 0.5);
            color: #2ecc71;
            display: block;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .auth-container {
                flex-direction: column;
            }
            
            .auth-left, .auth-right {
                padding: 30px 20px;
            }
            
            .form-options {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .social-login {
                flex-direction: column;
            }
            
            .logo {
                font-size: 1.8rem;
            }
            
            .form-title {
                font-size: 1.5rem;
            }
            
            .welcome-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="auth-container">
            <!-- Panel izquierdo - Formulario de Login -->
            <div class="auth-left">
                <div class="logo">Power<span>Fit</span></div>
                <h2 class="form-title">Iniciar Sesión</h2>
                <p class="form-subtitle">Accede a tu cuenta para gestionar tu membresía</p>
                
                <form class="auth-form" id="loginForm" action="controladores/login.php" method="POST">
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
                    <div class="form-message" id="formMessage"></div>
                    
                    <div class="form-group">
                        <label class="form-label" for="loginEmail">Email</label>
                        <input type="email" class="form-input" name="email" id="loginEmail" placeholder="tu@email.com" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="loginPassword">Contraseña</label>
                        <input type="password" name="password" class="form-input" id="loginPassword" placeholder="Tu contraseña" required>
                    </div>
                    
                    <div class="form-options">
                        <label class="checkbox-label">
                            <input type="checkbox" id="rememberMe">
                            <span class="checkmark"></span>
                            Recordarme
                        </label>
                        <a href="#" class="forgot-password">¿Olvidaste tu contraseña?</a>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
                    
                    <div class="auth-switch">
                        ¿No tienes una cuenta? <a href="registro.php">Regístrate aquí</a>
                    </div>
                </form>
                
                <div class="divider">
                    <span>o continúa con</span>
                </div>
                
                <div class="social-login">
                    <a href="#" class="social-btn google">
                        <i class="fab fa-google"></i>
                        Google
                    </a>
                    <a href="#" class="social-btn facebook">
                        <i class="fab fa-facebook-f"></i>
                        Facebook
                    </a>
                </div>
            </div>
            
            <!-- Panel derecho - Información -->
            <div class="auth-right">
                <h2 class="welcome-title">¡Bienvenido de nuevo!</h2>
                <p class="welcome-text">Continúa tu viaje fitness con nosotros. Accede a tu perfil para ver tu progreso, reservar clases y gestionar tu membresía.</p>
                
                <ul class="auth-features">
                    <li><i class="fas fa-dumbbell"></i> Seguimiento de tu progreso</li>
                    <li><i class="fas fa-calendar-alt"></i> Reserva de clases</li>
                    <li><i class="fas fa-chart-line"></i> Estadísticas personalizadas</li>
                    <li><i class="fas fa-user-friends"></i> Comunidad fitness</li>
                </ul>
                
                <div class="testimonial">
                    <div class="testimonial-text">
                        "PowerFit transformó completamente mi approach al fitness. Los entrenadores son increíbles!"
                    </div>
                    <div class="testimonial-author">
                        <strong>Carlos Rodríguez</strong>
                        <span>Miembro desde 2022</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>