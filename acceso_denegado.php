
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PowerFit Gym - Acceso Denegado</title>
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
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .error-container {
            text-align: center;
            padding: 40px;
            max-width: 600px;
            animation: fadeIn 0.5s ease-in-out;
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

        .error-code {
            font-size: 12rem;
            font-weight: 800;
            font-family: var(--font-main);
            color: var(--secondary);
            line-height: 1;
            text-shadow: 5px 5px 0 rgba(231, 76, 60, 0.2);
            margin-bottom: 20px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
            100% {
                transform: scale(1);
            }
        }

        .error-icon {
            font-size: 5rem;
            color: var(--secondary);
            margin-bottom: 20px;
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }

        .error-title {
            font-size: 2.5rem;
            font-weight: 700;
            font-family: var(--font-main);
            color: var(--dark);
            margin-bottom: 15px;
        }

        .error-message {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .error-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            font-family: var(--font-main);
            text-decoration: none;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: #1a252f;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-secondary {
            background-color: var(--secondary);
            color: white;
        }

        .btn-secondary:hover {
            background-color: #c0392b;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-outline {
            background-color: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .btn-outline:hover {
            background-color: var(--primary);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .additional-info {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            color: #666;
            font-size: 0.95rem;
        }

        .additional-info i {
            color: var(--secondary);
            margin: 0 5px;
        }

        .gym-brand {
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 1.5rem;
            font-weight: 800;
            font-family: var(--font-main);
            color: var(--primary);
        }

        .gym-brand span {
            color: var(--secondary);
        }

        .gym-brand i {
            margin-right: 5px;
            color: var(--secondary);
        }

        @media (max-width: 768px) {
            .error-code {
                font-size: 8rem;
            }
            
            .error-title {
                font-size: 2rem;
            }
            
            .error-message {
                font-size: 1rem;
            }
            
            .error-container {
                padding: 20px;
            }
            
            .error-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>

<body>
    <div class="gym-brand">
        <i class="fas fa-dumbbell"></i> Power<span>Fit</span>
    </div>

    <div class="error-container">
        <div class="error-icon">
            <i class="fas fa-shield-alt"></i>
        </div>
        <div class="error-code">403</div>
        <h1 class="error-title">¡Acceso Denegado!</h1>
        <p class="error-message">
            Lo sentimos, no tienes permisos suficientes para acceder a esta página.<br>
            Si crees que esto es un error, contacta al administrador del sistema.
        </p>
        
        <div class="error-actions">
            
                <a href="login.php" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                </a>
            
            <a href="javascript:history.back()" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Volver Atrás
            </a>
            
            <a href="#" class="btn btn-secondary">
                <i class="fas fa-headset"></i> Contactar Soporte
            </a>
        </div>

        <div class="additional-info">
            <i class="fas fa-info-circle"></i> 
            Si necesitas acceder a esta sección, solicita los permisos correspondientes al administrador.
            <i class="fas fa-info-circle"></i>
        </div>
    </div>

    <!-- Efecto de fondo con pesas (decorativo) -->
    <div style="position: fixed; bottom: 20px; right: 20px; opacity: 0.1; font-size: 8rem; pointer-events: none;">
        <i class="fas fa-dumbbell"></i>
    </div>
</body>

</html>