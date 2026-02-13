<?php
// Comprobar el estado actual de la sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PowerFit Gym - Página No Encontrada</title>
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

        .error-code span {
            display: inline-block;
            animation: bounce 2s infinite;
        }

        .error-code span:nth-child(2) {
            animation-delay: 0.2s;
        }

        .error-code span:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-20px);
            }
        }

        .error-icon {
            font-size: 5rem;
            color: var(--warning);
            margin-bottom: 20px;
            animation: spin 10s linear infinite;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
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

        .search-box {
            background-color: white;
            border-radius: 50px;
            padding: 5px;
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }

        .search-box input {
            flex: 1;
            border: none;
            padding: 12px 20px;
            font-size: 1rem;
            border-radius: 50px;
            outline: none;
            font-family: var(--font-secondary);
        }

        .search-box button {
            background-color: var(--secondary);
            color: white;
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .search-box button:hover {
            background-color: var(--primary);
            transform: scale(1.1);
        }

        .error-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 20px;
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

        .quick-links {
            margin-top: 30px;
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .quick-link {
            color: var(--primary);
            text-decoration: none;
            font-size: 0.95rem;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .quick-link:hover {
            color: var(--secondary);
            transform: translateY(-2px);
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
            
            .quick-links {
                flex-direction: column;
                align-items: center;
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
            <i class="fas fa-search"></i>
        </div>
        <div class="error-code">
            <span>4</span>
            <span>0</span>
            <span>4</span>
        </div>
        <h1 class="error-title">¡Página no encontrada!</h1>
        <p class="error-message">
            Lo sentimos, la página que estás buscando no existe o ha sido movida.<br>
            Verifica la URL o utiliza el buscador para encontrar lo que necesitas.
        </p>

        <div class="search-box">
            <input type="text" placeholder="Buscar en el sitio..." id="searchInput">
            <button type="button" onclick="searchSite()">
                <i class="fas fa-search"></i>
            </button>
        </div>
        
        <div class="error-actions">
            <?php if (isset($_SESSION['rol'])): ?>
                <a href="dash_<?php echo $_SESSION['rol']; ?>.php" class="btn btn-primary">
                    <i class="fas fa-tachometer-alt"></i> Ir al Dashboard
                </a>
            <?php else: ?>
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-home"></i> Ir al Inicio
                </a>
            <?php endif; ?>
            
            <a href="javascript:history.back()" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Volver Atrás
            </a>
        </div>

        <div class="quick-links">
            <a href="gestion_usuarios.php" class="quick-link">
                <i class="fas fa-users"></i> Usuarios
            </a>
            <a href="gestion_clases.php" class="quick-link">
                <i class="fas fa-calendar-alt"></i> Clases
            </a>
            <a href="gestion_membresias.php" class="quick-link">
                <i class="fas fa-dumbbell"></i> Membresías
            </a>
            <a href="contacto.php" class="quick-link">
                <i class="fas fa-envelope"></i> Contacto
            </a>
        </div>
    </div>

    <script>
        function searchSite() {
            const searchTerm = document.getElementById('searchInput').value.trim();
            if (searchTerm) {
                // Aquí puedes implementar la lógica de búsqueda
                // Por ahora redirigimos a una página de búsqueda
                window.location.href = 'buscar.php?q=' + encodeURIComponent(searchTerm);
            }
        }

        // Permitir buscar con Enter
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchSite();
            }
        });
    </script>

    <!-- Efecto de fondo con pesas (decorativo) -->
    <div style="position: fixed; bottom: 20px; right: 20px; opacity: 0.1; font-size: 8rem; pointer-events: none;">
        <i class="fas fa-dumbbell"></i>
    </div>
</body>

</html>