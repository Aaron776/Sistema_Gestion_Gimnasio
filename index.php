<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PowerFit Gym - Transforma tu cuerpo, transforma tu vida</title>
    <style>
        /* Variables de colores y fuentes */
        :root {
            --primary: #2c3e50;
            --secondary: #e74c3c;
            --accent: #3498db;
            --light: #ecf0f1;
            --dark: #2c3e50;
            --success: #2ecc71;
            --font-main: 'Montserrat', sans-serif;
            --font-secondary: 'Open Sans', sans-serif;
        }

        /* Reset y estilos base */
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
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: var(--font-main);
            font-weight: 700;
            margin-bottom: 1rem;
        }

        a {
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
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

        .btn-secondary {
            background-color: transparent;
            color: var(--light);
            border: 2px solid var(--light);
        }

        .btn-secondary:hover {
            background-color: var(--light);
            color: var(--dark);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        /* Header y Navegación */
        header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            padding: 20px 0;
            transition: all 0.3s ease;
        }

        header.scrolled {
            background-color: rgba(26, 26, 46, 0.95);
            padding: 15px 0;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-family: var(--font-main);
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--light);
        }

        .logo span {
            color: var(--secondary);
        }

        .nav-links {
            display: flex;
            list-style: none;
        }

        .nav-links li {
            margin-left: 30px;
        }

        .nav-links a {
            font-weight: 600;
            font-size: 0.9rem;
            letter-spacing: 1px;
            position: relative;
        }

        .nav-links a:after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            background: var(--secondary);
            left: 0;
            bottom: -5px;
            transition: width 0.3s ease;
        }

        .nav-links a:hover:after {
            width: 100%;
        }

        .auth-buttons {
            display: flex;
            gap: 15px;
        }

        .auth-buttons .btn {
            padding: 8px 20px;
            font-size: 0.85rem;
        }

        /* Hero Section */
        .hero {
            height: 100vh;
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('https://images.unsplash.com/photo-1534438327276-14e5300c3a48?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80') no-repeat center center/cover;
            display: flex;
            align-items: center;
            text-align: center;
            position: relative;
        }

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
        }

        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .hero-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 2rem;
        }

        /* Features Section */
        .features {
            padding: 100px 0;
            background-color: var(--dark);
        }

        .section-title {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-title h2 {
            font-size: 2.5rem;
            position: relative;
            display: inline-block;
            padding-bottom: 15px;
        }

        .section-title h2:after {
            content: '';
            position: absolute;
            width: 70px;
            height: 3px;
            background: var(--secondary);
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
        }

        .feature-card {
            background-color: rgba(255, 255, 255, 0.05);
            padding: 40px 30px;
            border-radius: 10px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .feature-icon {
            font-size: 3rem;
            color: var(--secondary);
            margin-bottom: 20px;
        }

        .feature-card h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
        }

        /* CTA Section */
        .cta {
            padding: 100px 0;
            background: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.8)), url('https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80') no-repeat center center/cover;
            text-align: center;
        }

        .cta h2 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }

        .cta p {
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto 30px;
            opacity: 0.9;
        }

        /* Footer */
        footer {
            background-color: #0f0f1a;
            padding: 60px 0 30px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-column h3 {
            font-size: 1.3rem;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
        }

        .footer-column h3:after {
            content: '';
            position: absolute;
            width: 40px;
            height: 2px;
            background: var(--secondary);
            bottom: 0;
            left: 0;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 10px;
        }

        .footer-links a {
            opacity: 0.8;
        }

        .footer-links a:hover {
            opacity: 1;
            color: var(--secondary);
        }

        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .social-links a:hover {
            background-color: var(--secondary);
            transform: translateY(-3px);
        }

        .copyright {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.9rem;
            opacity: 0.7;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 20px;
            }

            .nav-links {
                margin-top: 20px;
            }

            .nav-links li {
                margin: 0 15px;
            }

            .hero h1 {
                font-size: 2.5rem;
            }

            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }

            .auth-buttons {
                margin-top: 20px;
            }
        }

        @media (max-width: 576px) {
            .nav-links {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }

            .hero h1 {
                font-size: 2rem;
            }

            .section-title h2 {
                font-size: 2rem;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header y Navegación -->
    <header>
        <div class="container">
            <nav class="navbar">
                <a href="#" class="logo">Power<span>Fit</span></a>
                <ul class="nav-links">
                    <li><a href="#">Inicio</a></li>
                    <li><a href="#">Servicios</a></li>
                    <li><a href="#">Clases</a></li>
                    <li><a href="#">Entrenadores</a></li>
                    <li><a href="#">Precios</a></li>
                    <li><a href="#">Contacto</a></li>
                </ul>
                <div class="auth-buttons">
                    <a href="login.php" class="btn btn-secondary">Iniciar Sesión</a>
                    <a href="registro.php" class="btn btn-primary">Registrarse</a>
                </div>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Transforma tu cuerpo, transforma tu vida</h1>
                <p>En PowerFit Gym te ayudamos a alcanzar tus objetivos fitness con entrenamientos personalizados, equipos de última generación y un ambiente motivador.</p>
                <div class="hero-buttons">
                    <a href="#" class="btn btn-primary">Comenzar Ahora</a>
                    <a href="#" class="btn btn-secondary">Ver Servicios</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <div class="section-title">
                <h2>Nuestros Servicios</h2>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-dumbbell"></i>
                    </div>
                    <h3>Entrenamiento Personalizado</h3>
                    <p>Programas de entrenamiento diseñados específicamente para tus objetivos y nivel de condición física.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Clases Grupales</h3>
                    <p>Desde yoga hasta HIIT, nuestras clases grupales te mantendrán motivado y desafiado.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-heartbeat"></i>
                    </div>
                    <h3>Asesoría Nutricional</h3>
                    <p>Planes de alimentación personalizados para complementar tu rutina de ejercicios.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <h2>¿Listo para comenzar tu transformación?</h2>
            <p>Únete a nuestra comunidad fitness y descubre todo tu potencial. Tu primera clase es completamente gratis.</p>
            <a href="#" class="btn btn-primary">Obtener Membresía</a>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>PowerFit Gym</h3>
                    <p>Transformando vidas a través del fitness desde 2010. Nuestra misión es ayudarte a alcanzar tu mejor versión.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="footer-column">
                    <h3>Enlaces Rápidos</h3>
                    <ul class="footer-links">
                        <li><a href="#">Inicio</a></li>
                        <li><a href="#">Servicios</a></li>
                        <li><a href="#">Clases</a></li>
                        <li><a href="#">Entrenadores</a></li>
                        <li><a href="#">Precios</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Horarios</h3>
                    <ul class="footer-links">
                        <li>Lunes - Viernes: 5:00 AM - 11:00 PM</li>
                        <li>Sábados: 7:00 AM - 9:00 PM</li>
                        <li>Domingos: 8:00 AM - 8:00 PM</li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Contacto</h3>
                    <ul class="footer-links">
                        <li><i class="fas fa-map-marker-alt"></i> Av. Fitness 123, Ciudad</li>
                        <li><i class="fas fa-phone"></i> (123) 456-7890</li>
                        <li><i class="fas fa-envelope"></i> info@powerfitgym.com</li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; 2023 PowerFit Gym. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <script>
        // Efecto de navbar al hacer scroll
        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // Animación suave para los enlaces
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
</body>
</html>