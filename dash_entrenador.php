 <?php
    require_once "autorizacion/auth.php"; // valida login y arranca sesión

    // Verificar que tenga rol de entrenador
    if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'entrenador') {
        header("Location: index.php"); // si no lo mandamos al login
        exit();
    }
    require_once "templates/header.php";
    include_once "conexion/bd.php";
    ?>
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
         overflow-x: hidden;
     }

     /* Layout Principal */
     .wrapper {
         display: flex;
         min-height: 100vh;
     }

     /* Sidebar */
     .sidebar {
         width: 250px;
         background-color: var(--sidebar);
         color: white;
         transition: all 0.3s;
         position: fixed;
         height: 100vh;
         z-index: 1000;
         box-shadow: 3px 0 10px rgba(0, 0, 0, 0.1);
     }

     .trainer-profile {
         padding: 25px 20px;
         background: linear-gradient(135deg, var(--primary), var(--secondary));
         text-align: center;
         border-bottom: 1px solid rgba(255, 255, 255, 0.1);
     }

     .trainer-avatar {
         width: 80px;
         height: 80px;
         border-radius: 50%;
         background: white;
         color: var(--secondary);
         display: flex;
         align-items: center;
         justify-content: center;
         font-size: 2rem;
         font-weight: bold;
         margin: 0 auto 15px;
         border: 4px solid rgba(255, 255, 255, 0.2);
     }

     .trainer-name {
         font-family: var(--font-main);
         font-size: 1.2rem;
         margin-bottom: 5px;
     }

     .trainer-specialty {
         font-size: 0.85rem;
         opacity: 0.9;
         margin-bottom: 10px;
     }

     .trainer-rating {
         color: var(--warning);
         font-size: 0.9rem;
     }

     .sidebar-header {
         padding: 20px;
         background-color: rgba(0, 0, 0, 0.2);
         text-align: center;
         border-bottom: 1px solid #2c3e50;
     }

     .sidebar-header h3 {
         color: white;
         margin: 0;
         font-size: 1.5rem;
         font-family: var(--font-main);
     }

     .sidebar-header h3 span {
         color: var(--secondary);
     }

     .sidebar-menu {
         padding: 10px 0;
     }

     .sidebar-menu ul {
         list-style: none;
     }

     .sidebar-menu li {
         position: relative;
     }

     .sidebar-menu a {
         display: flex;
         align-items: center;
         padding: 12px 20px;
         color: #c2c7d0;
         text-decoration: none;
         transition: all 0.3s;
         font-size: 0.95rem;
     }

     .sidebar-menu a:hover {
         color: white;
         background-color: var(--sidebar-hover);
     }

     .sidebar-menu a.active {
         color: white;
         background-color: var(--secondary);
         border-left: 4px solid var(--secondary);
     }

     .sidebar-menu i {
         margin-right: 10px;
         width: 20px;
         text-align: center;
         font-size: 1.1rem;
     }

     /* Contenido Principal */
     .main-content {
         flex: 1;
         margin-left: 250px;
         transition: all 0.3s;
         display: flex;
         flex-direction: column;
         min-height: 100vh;
     }

     /* Header */
     .header {
         background-color: var(--header);
         padding: 15px 20px;
         box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
         display: flex;
         justify-content: space-between;
         align-items: center;
         color: white;
     }

     .toggle-sidebar {
         background: none;
         border: none;
         font-size: 1.5rem;
         color: white;
         cursor: pointer;
         margin-right: 15px;
     }

     .header-left {
         display: flex;
         align-items: center;
     }

     .header-title {
         font-family: var(--font-main);
         font-size: 1.5rem;
     }

     .user-menu {
         display: flex;
         align-items: center;
     }

     .schedule-notification {
         position: relative;
         margin-right: 20px;
         font-size: 1.2rem;
         color: white;
         cursor: pointer;
     }

     .notification-badge {
         position: absolute;
         top: -5px;
         right: -5px;
         background: var(--secondary);
         color: white;
         border-radius: 50%;
         width: 18px;
         height: 18px;
         font-size: 0.7rem;
         display: flex;
         align-items: center;
         justify-content: center;
     }

     .current-time {
         margin-right: 20px;
         font-size: 0.9rem;
         color: #c2c7d0;
     }

     /* Contenido */
     .content {
         padding: 20px;
     }

     .dashboard-container {
         padding: 20px;
     }

     .welcome-section {
         background: linear-gradient(135deg, var(--primary), var(--secondary));
         color: white;
         border-radius: 10px;
         padding: 30px;
         margin-bottom: 30px;
         box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
     }

     .welcome-content {
         display: flex;
         justify-content: space-between;
         align-items: center;
     }

     .welcome-text h1 {
         font-family: var(--font-main);
         font-size: 2rem;
         margin-bottom: 10px;
     }

     .welcome-text p {
         opacity: 0.9;
         font-size: 1rem;
     }

     .next-class {
         background: rgba(255, 255, 255, 0.1);
         backdrop-filter: blur(10px);
         padding: 20px;
         border-radius: 8px;
         min-width: 300px;
     }

     .next-class h3 {
         font-family: var(--font-main);
         margin-bottom: 10px;
         font-size: 1.2rem;
     }

     .class-info {
         display: flex;
         align-items: center;
         gap: 15px;
         margin-bottom: 15px;
     }

     .class-icon {
         width: 50px;
         height: 50px;
         border-radius: 8px;
         background: white;
         color: var(--secondary);
         display: flex;
         align-items: center;
         justify-content: center;
         font-size: 1.5rem;
     }

     .class-details h4 {
         font-size: 1.1rem;
         margin-bottom: 5px;
     }

     .class-details p {
         font-size: 0.9rem;
         opacity: 0.8;
     }

     /* Métricas del Entrenador */
     .trainer-metrics {
         display: grid;
         grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
         gap: 20px;
         margin-bottom: 30px;
     }

     .metric-card {
         background-color: var(--card);
         border-radius: 10px;
         box-shadow: 0 3px 15px rgba(0, 0, 0, 0.1);
         padding: 25px;
         display: flex;
         align-items: center;
         transition: all 0.3s;
         border-left: 4px solid var(--secondary);
     }

     .metric-card:hover {
         transform: translateY(-5px);
         box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
     }

     .metric-icon {
         width: 60px;
         height: 60px;
         border-radius: 10px;
         display: flex;
         align-items: center;
         justify-content: center;
         margin-right: 15px;
         font-size: 1.5rem;
         color: white;
     }

     .metric-info h3 {
         font-size: 1.8rem;
         margin-bottom: 5px;
         color: var(--dark);
         font-family: var(--font-main);
     }

     .metric-info p {
         color: var(--primary);
         margin: 0;
         font-weight: 600;
     }

     .metric-trend {
         font-size: 0.8rem;
         margin-top: 5px;
     }

     .trend-up {
         color: var(--success);
     }

     .trend-down {
         color: var(--secondary);
     }

     .bg-primary {
         background-color: var(--primary);
     }

     .bg-success {
         background-color: var(--success);
     }

     .bg-warning {
         background-color: var(--warning);
     }

     .bg-info {
         background-color: var(--info);
     }

     .bg-secondary {
         background-color: var(--secondary);
     }

     /* Dashboard Content */
     .dashboard-content {
         display: grid;
         grid-template-columns: 2fr 1fr;
         gap: 20px;
     }

     .chart-container,
     .today-schedule,
     .recent-members {
         background-color: var(--card);
         border-radius: 10px;
         box-shadow: 0 3px 15px rgba(0, 0, 0, 0.1);
         padding: 20px;
         margin-bottom: 20px;
     }

     .chart-container h2,
     .today-schedule h2,
     .recent-members h2 {
         font-size: 1.3rem;
         margin-bottom: 15px;
         color: var(--dark);
         border-bottom: 1px solid #eee;
         padding-bottom: 10px;
         font-family: var(--font-main);
     }

     .chart-wrapper {
         height: 300px;
         position: relative;
     }

     /* Horario del Día */
     .schedule-list {
         list-style: none;
     }

     .schedule-item {
         display: flex;
         align-items: center;
         padding: 15px;
         border-bottom: 1px solid #f0f0f0;
         transition: all 0.3s;
     }

     .schedule-item:hover {
         background-color: #f8f9fa;
     }

     .schedule-item:last-child {
         border-bottom: none;
     }

     .schedule-time {
         min-width: 80px;
         text-align: center;
         padding: 8px;
         background: var(--primary);
         color: white;
         border-radius: 6px;
         margin-right: 15px;
         font-weight: 600;
     }

     .schedule-info h4 {
         font-size: 0.95rem;
         margin-bottom: 3px;
         color: var(--dark);
     }

     .schedule-info p {
         font-size: 0.8rem;
         color: var(--primary);
         margin: 0;
     }

     .schedule-status {
         margin-left: auto;
         padding: 4px 8px;
         border-radius: 20px;
         font-size: 0.7rem;
         font-weight: 600;
         text-transform: uppercase;
     }

     .status-upcoming {
         background: #d1ecf1;
         color: #0c5460;
     }

     .status-in-progress {
         background: #d4edda;
         color: #155724;
     }

     .status-completed {
         background: #e2e3e5;
         color: #383d41;
     }

     /* Miembros Recientes */
     .members-list {
         list-style: none;
     }

     .member-item {
         display: flex;
         align-items: center;
         padding: 12px 0;
         border-bottom: 1px solid #f0f0f0;
     }

     .member-item:last-child {
         border-bottom: none;
     }

     .member-avatar {
         width: 40px;
         height: 40px;
         border-radius: 50%;
         display: flex;
         align-items: center;
         justify-content: center;
         margin-right: 15px;
         color: white;
         font-size: 1rem;
         font-weight: bold;
     }

     .member-info h4 {
         font-size: 0.95rem;
         margin-bottom: 3px;
         color: var(--dark);
     }

     .member-info p {
         font-size: 0.8rem;
         color: var(--primary);
         margin: 0;
     }

     .member-goal {
         margin-left: auto;
         font-size: 0.8rem;
         color: #6c757d;
     }

     /* Quick Actions */
     .quick-actions {
         background-color: var(--card);
         border-radius: 10px;
         box-shadow: 0 3px 15px rgba(0, 0, 0, 0.1);
         padding: 20px;
         margin-top: 20px;
     }

     .quick-actions h2 {
         font-size: 1.3rem;
         margin-bottom: 15px;
         color: var(--dark);
         border-bottom: 1px solid #eee;
         padding-bottom: 10px;
         font-family: var(--font-main);
     }

     .actions-grid {
         display: grid;
         grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
         gap: 15px;
     }

     .action-btn {
         display: flex;
         flex-direction: column;
         align-items: center;
         justify-content: center;
         padding: 20px 10px;
         background: #f8f9fa;
         border-radius: 8px;
         text-decoration: none;
         color: var(--dark);
         transition: all 0.3s;
         text-align: center;
         border: none;
         cursor: pointer;
     }

     .action-btn:hover {
         background: var(--secondary);
         color: white;
         transform: translateY(-3px);
         box-shadow: 0 5px 15px rgba(231, 76, 60, 0.2);
     }

     .action-btn i {
         font-size: 1.8rem;
         margin-bottom: 10px;
     }

     .action-btn span {
         font-size: 0.9rem;
         font-weight: 600;
     }

     /* Responsive */
     @media (max-width: 992px) {
         .dashboard-content {
             grid-template-columns: 1fr;
         }

         .actions-grid {
             grid-template-columns: repeat(2, 1fr);
         }
     }

     @media (max-width: 768px) {
         .sidebar {
             margin-left: -250px;
         }

         .sidebar.active {
             margin-left: 0;
         }

         .main-content {
             margin-left: 0;
         }

         .main-content.active {
             margin-left: 250px;
         }

         .welcome-content {
             flex-direction: column;
             gap: 20px;
             text-align: center;
         }

         .next-class {
             min-width: 100%;
         }

         .trainer-metrics {
             grid-template-columns: 1fr;
         }

         .actions-grid {
             grid-template-columns: 1fr;
         }

         .header-title {
             display: none;
         }
     }

     @media (max-width: 480px) {
         .welcome-section {
             padding: 20px;
         }

         .dashboard-container {
             padding: 10px;
         }
     }
 </style>

 <!-- Contenido -->
 <div class="content">
     <div class="dashboard-container">
         <!-- Sección de Bienvenida -->
         <div class="welcome-section">
             <div class="welcome-content">
                 <div class="welcome-text">
                     <h1>¡Buenos días, <?php echo htmlspecialchars($_SESSION['nombre']); ?> <?php echo htmlspecialchars($_SESSION['apellido']); ?>!</h1>
                     <p>Hoy tienes 4 clases programadas y 3 sesiones personalizadas.</p>
                 </div>
                 <div class="next-class">
                     <h3>Próxima Clase</h3>
                     <div class="class-info">
                         <div class="class-icon">
                             <i class="fas fa-dumbbell"></i>
                         </div>
                         <div class="class-details">
                             <h4>Fuerza Funcional</h4>
                             <p>Sala Principal • 10:45 AM</p>
                             <p>12/15 alumnos confirmados</p>
                         </div>
                     </div>
                 </div>
             </div>
         </div>

         <!-- Métricas del Entrenador -->
         <div class="trainer-metrics">
             <div class="metric-card">
                 <div class="metric-icon bg-secondary">
                     <i class="fas fa-users"></i>
                 </div>
                 <div class="metric-info">
                     <h3>42</h3>
                     <p>Alumnos Activos</p>
                     <div class="metric-trend trend-up">
                         <i class="fas fa-arrow-up"></i> 3 esta semana
                     </div>
                 </div>
             </div>
             <div class="metric-card">
                 <div class="metric-icon bg-success">
                     <i class="fas fa-calendar-check"></i>
                 </div>
                 <div class="metric-info">
                     <h3>18</h3>
                     <p>Clases esta Semana</p>
                     <div class="metric-trend trend-up">
                         <i class="fas fa-arrow-up"></i> 2 más que la anterior
                     </div>
                 </div>
             </div>
             <div class="metric-card">
                 <div class="metric-icon bg-warning">
                     <i class="fas fa-chart-line"></i>
                 </div>
                 <div class="metric-info">
                     <h3>94%</h3>
                     <p>Asistencia Promedio</p>
                     <div class="metric-trend trend-up">
                         <i class="fas fa-arrow-up"></i> 5% este mes
                     </div>
                 </div>
             </div>
             <div class="metric-card">
                 <div class="metric-icon bg-info">
                     <i class="fas fa-star"></i>
                 </div>
                 <div class="metric-info">
                     <h3>4.7</h3>
                     <p>Calificación Promedio</p>
                     <div class="metric-trend trend-up">
                         <i class="fas fa-arrow-up"></i> 0.2 este mes
                     </div>
                 </div>
             </div>
         </div>

         <!-- Contenido Principal del Dashboard -->
         <div class="dashboard-content">
             <div>
                 <!-- Gráfico de Asistencia -->
                 <div class="chart-container">
                     <h2>Asistencia Mensual</h2>
                     <div class="chart-wrapper">
                         <canvas id="attendanceChart"></canvas>
                     </div>
                 </div>

                 <!-- Acciones Rápidas -->
                 <div class="quick-actions">
                     <h2>Acciones Rápidas</h2>
                     <div class="actions-grid">
                         <button class="action-btn" onclick="nuevaRutina()">
                             <i class="fas fa-running"></i>
                             <span>Nueva Rutina</span>
                         </button>
                         <button class="action-btn" onclick="registrarAsistencia()">
                             <i class="fas fa-clipboard-check"></i>
                             <span>Registrar Asistencia</span>
                         </button>
                         <button class="action-btn" onclick="agendarClase()">
                             <i class="fas fa-calendar-plus"></i>
                             <span>Agendar Clase</span>
                         </button>
                         <button class="action-btn" onclick="enviarMensaje()">
                             <i class="fas fa-comment-medical"></i>
                             <span>Enviar Recordatorio</span>
                         </button>
                     </div>
                 </div>
             </div>

             <div>
                 <!-- Horario de Hoy -->
                 <div class="today-schedule">
                     <h2>Horario de Hoy</h2>
                     <ul class="schedule-list">
                         <li class="schedule-item">
                             <div class="schedule-time">09:00</div>
                             <div class="schedule-info">
                                 <h4>Yoga Matutino</h4>
                                 <p>Sala A • 15 alumnos</p>
                             </div>
                             <span class="schedule-status status-completed">Finalizada</span>
                         </li>
                         <li class="schedule-item">
                             <div class="schedule-time">10:45</div>
                             <div class="schedule-info">
                                 <h4>Fuerza Funcional</h4>
                                 <p>Sala Principal • 12 alumnos</p>
                             </div>
                             <span class="schedule-status status-in-progress">En curso</span>
                         </li>
                         <li class="schedule-item">
                             <div class="schedule-time">14:00</div>
                             <div class="schedule-info">
                                 <h4>Entrenamiento Personal</h4>
                                 <p>Juan Pérez • 60 min</p>
                             </div>
                             <span class="schedule-status status-upcoming">Próxima</span>
                         </li>
                         <li class="schedule-item">
                             <div class="schedule-time">16:30</div>
                             <div class="schedule-info">
                                 <h4>HIIT Cardio</h4>
                                 <p>Sala B • 20 alumnos</p>
                             </div>
                             <span class="schedule-status status-upcoming">Próxima</span>
                         </li>
                         <li class="schedule-item">
                             <div class="schedule-time">18:00</div>
                             <div class="schedule-info">
                                 <h4>Evaluación Física</h4>
                                 <p>María González • 45 min</p>
                             </div>
                             <span class="schedule-status status-upcoming">Próxima</span>
                         </li>
                     </ul>
                 </div>

                 <!-- Alumnos Recientes -->
                 <div class="recent-members">
                     <h2>Alumnos Recientes</h2>
                     <ul class="members-list">
                         <li class="member-item">
                             <div class="member-avatar" style="background: #3498db;">AJ</div>
                             <div class="member-info">
                                 <h4>Ana Jiménez</h4>
                                 <p>Entrenamiento Personal</p>
                             </div>
                             <div class="member-goal">
                                 <i class="fas fa-bullseye"></i> Perder peso
                             </div>
                         </li>
                         <li class="member-item">
                             <div class="member-avatar" style="background: #2ecc71;">RM</div>
                             <div class="member-info">
                                 <h4>Roberto Martínez</h4>
                                 <p>Fuerza Funcional</p>
                             </div>
                             <div class="member-goal">
                                 <i class="fas fa-bullseye"></i> Ganar masa
                             </div>
                         </li>
                         <li class="member-item">
                             <div class="member-avatar" style="background: #e74c3c;">LS</div>
                             <div class="member-info">
                                 <h4>Laura Sánchez</h4>
                                 <p>Yoga Matutino</p>
                             </div>
                             <div class="member-goal">
                                 <i class="fas fa-bullseye"></i> Flexibilidad
                             </div>
                         </li>
                         <li class="member-item">
                             <div class="member-avatar" style="background: #f39c12;">CP</div>
                             <div class="member-info">
                                 <h4>Carlos Pérez</h4>
                                 <p>HIIT Cardio</p>
                             </div>
                             <div class="member-goal">
                                 <i class="fas fa-bullseye"></i> Resistencia
                             </div>
                         </li>
                     </ul>
                 </div>
             </div>
         </div>
     </div>
 </div>
 </div>
 </div>

 <script>
     // Inicialización
     document.addEventListener('DOMContentLoaded', function() {
         // Toggle sidebar
         document.querySelector('.toggle-sidebar').addEventListener('click', function() {
             document.querySelector('.sidebar').classList.toggle('active');
             document.querySelector('.main-content').classList.toggle('active');
         });

         // Actualizar hora actual
         function updateTime() {
             const now = new Date();
             const timeString = now.toLocaleTimeString('es-ES', {
                 hour: '2-digit',
                 minute: '2-digit',
                 hour12: true
             });
             document.getElementById('currentTime').textContent = timeString;
         }

         updateTime();
         setInterval(updateTime, 60000); // Actualizar cada minuto

         // Notificaciones
         document.querySelector('.schedule-notification').addEventListener('click', function() {
             alert('Tienes 3 notificaciones:\n- Nueva solicitud de entrenamiento personal\n- Recordatorio: Clase de HIIT en 30 min\n- Alumno nuevo asignado a tu grupo');
         });

         // Gráfico de asistencia
         const ctx = document.getElementById('attendanceChart').getContext('2d');
         const attendanceChart = new Chart(ctx, {
             type: 'bar',
             data: {
                 labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
                 datasets: [{
                     label: 'Asistencia',
                     data: [35, 42, 38, 45, 40, 30, 25],
                     backgroundColor: 'rgba(231, 76, 60, 0.8)',
                     borderColor: 'rgba(231, 76, 60, 1)',
                     borderWidth: 1,
                     borderRadius: 5
                 }]
             },
             options: {
                 responsive: true,
                 maintainAspectRatio: false,
                 plugins: {
                     legend: {
                         display: false
                     }
                 },
                 scales: {
                     y: {
                         beginAtZero: true,
                         grid: {
                             drawBorder: false
                         },
                         ticks: {
                             callback: function(value) {
                                 return value + ' alumnos';
                             }
                         }
                     },
                     x: {
                         grid: {
                             display: false
                         }
                     }
                 }
             }
         });
     });

     // Funciones de acciones rápidas
     function nuevaRutina() {
         alert('Redirigiendo a creación de nueva rutina');
         // window.location.href = 'nueva_rutina.html';
     }

     function registrarAsistencia() {
         alert('Abriendo registro de asistencia para la clase actual');
         // window.location.href = 'registrar_asistencia.html';
     }

     function agendarClase() {
         alert('Abriendo calendario para agendar nueva clase');
         // window.location.href = 'agendar_clase.html';
     }

     function enviarMensaje() {
         alert('Abriendo panel de mensajes para enviar recordatorios');
         // window.location.href = 'mensajes.html';
     }

     // Simulación de datos en tiempo real
     setInterval(() => {
         // Simular actualización de métricas
         const metricCards = document.querySelectorAll('.metric-card');
         metricCards.forEach(card => {
             card.style.transform = 'translateY(0)';
             setTimeout(() => {
                 card.style.transform = 'translateY(-5px)';
             }, 100);
         });
     }, 30000); // Cada 30 segundos
 </script>
 <?php require_once('templates/footer.php'); ?>