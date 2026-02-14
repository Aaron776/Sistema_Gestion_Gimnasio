<?php
    require_once "autorizacion/auth.php"; // valida login y arranca sesión

    // Verificar que tenga rol de entrenador
    if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'entrenador') {
        header("Location: acceso_denegado.php"); // si no lo mandamos al acceso denegado
        exit();
    }
    require_once "templates/header.php";
    include_once "conexion/bd.php";


    // Obtener las clases que imparte el entrenador
    $id_entrenador = $_SESSION['id_usuario'];
    $sql = $conexion->prepare("SELECT nombre,horario,cupo FROM clases WHERE id_entrenador = :id_entrenador ORDER BY id DESC LIMIT 5");
    $sql->bindParam(':id_entrenador', $id_entrenador, PDO::PARAM_INT);
    $sql->execute();
    $clases = $sql->fetchAll(PDO::FETCH_OBJ);
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

     .trainer-stats {
         display: flex;
         justify-content: center;
         gap: 15px;
         margin-top: 10px;
     }

     .stat-item {
         text-align: center;
     }

     .stat-number {
         font-weight: bold;
         font-size: 1.1rem;
     }

     .stat-label {
         font-size: 0.7rem;
         opacity: 0.8;
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

     .current-time {
         margin-right: 20px;
         font-size: 0.9rem;
         color: #c2c7d0;
     }

     /* Contenido */
     .content {
         padding: 20px;
     }

     /* Wrapper para centrar y limitar ancho del contenido */
     .page-inner {
         max-width: 1100px;
         margin: 0 auto;
         padding: 0 10px;
     }

     .classes-container {
         background-color: var(--card);
         border-radius: 8px;
         box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
         padding: 25px;
         margin-bottom: 20px;
     }

     .page-header {
         display: flex;
         justify-content: space-between;
         align-items: center;
         margin-bottom: 25px;
         padding-bottom: 15px;
         border-bottom: 1px solid #dee2e6;
     }

     /* Contenedor de acciones en el encabezado */
     .page-header-actions {
         display: flex;
         align-items: center;
         gap: 10px;
         margin-left: auto;
     }

     .page-header h1 {
         font-size: 1.8rem;
         color: var(--dark);
         margin-bottom: 0;
         font-family: var(--font-main);
     }

     .view-toggle {
         display: flex;
         gap: 10px;
         margin-left: auto;
         margin-right: 20px;
     }

     .view-btn {
         background: none;
         border: none;
         font-size: 1.2rem;
         color: #6c757d;
         cursor: pointer;
         transition: all 0.3s;
         padding: 5px;
     }

     .view-btn.active {
         color: var(--secondary);
     }

     .view-btn:hover {
         color: var(--primary);
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

     .btn-success {
         background-color: var(--success);
         color: white;
     }

     .btn-success:hover {
         background-color: #218838;
     }

     /* Filtros */
     .filters-section {
         display: flex;
         gap: 15px;
         margin-bottom: 20px;
         flex-wrap: wrap;
         align-items: center;
     }

     .filter-select {
         padding: 10px 15px;
         border: 1px solid #dee2e6;
         border-radius: 5px;
         background: white;
         font-size: 0.9rem;
         min-width: 150px;
         cursor: pointer;
     }

     .date-filter {
         display: flex;
         align-items: center;
         gap: 10px;
     }

     .date-input {
         padding: 10px 15px;
         border: 1px solid #dee2e6;
         border-radius: 5px;
         font-size: 0.9rem;
     }

     /* Vista de Lista (Tabla) */
     .table-container {
         overflow-x: auto;
         border-radius: 8px;
         border: 1px solid #dee2e6;
         margin-bottom: 20px;
     }

     .table {
         width: 100%;
         border-collapse: collapse;
         min-width: 0; /* Permitir que se adapte en contenedores pequeños; la tabla se scrollea si hace falta */
     }

     .table th {
         background-color: #f8f9fa;
         padding: 15px 12px;
         text-align: left;
         font-weight: 600;
         color: var(--dark);
         border-bottom: 2px solid #dee2e6;
         font-family: var(--font-main);
         font-size: 0.9rem;
         position: sticky;
         top: 0;
     }

     .table td {
         padding: 12px;
         border-bottom: 1px solid #dee2e6;
         vertical-align: middle;
     }

     .table tr:hover {
         background-color: #f8f9fa;
     }

     .table tr:last-child td {
         border-bottom: none;
     }

     /* Vista de Tarjetas (Grid) */
     .grid-container {
         display: grid;
         grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
         gap: 20px;
         margin-bottom: 20px;
         align-items: start;
     }

     .class-card {
         background: white;
         border-radius: 10px;
         box-shadow: 0 3px 15px rgba(0, 0, 0, 0.1);
         padding: 20px;
         transition: all 0.3s;
         border-left: 4px solid var(--secondary);
     }

     .class-card:hover {
         transform: translateY(-5px);
         box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
     }

     .class-header {
         display: flex;
         justify-content: space-between;
         align-items: flex-start;
         margin-bottom: 15px;
     }

     .class-title {
         font-size: 1.3rem;
         font-weight: 700;
         color: var(--dark);
         margin-bottom: 5px;
         font-family: var(--font-main);
     }

     .class-type {
         padding: 4px 12px;
         border-radius: 20px;
         font-size: 0.75rem;
         font-weight: 600;
         text-transform: uppercase;
     }

     .type-yoga {
         background: #d1ecf1;
         color: #0c5460;
     }

     .type-cardio {
         background: #d4edda;
         color: #155724;
     }

     .type-strength {
         background: #f8d7da;
         color: #721c24;
     }

     .type-dance {
         background: #fff3cd;
         color: #856404;
     }

     .class-schedule {
         display: flex;
         align-items: center;
         gap: 10px;
         margin-bottom: 15px;
         color: #6c757d;
         font-size: 0.9rem;
     }

     .class-details {
         margin-bottom: 20px;
     }

     .detail-row {
         display: flex;
         justify-content: space-between;
         align-items: center;
         padding: 8px 0;
         border-bottom: 1px solid #f0f0f0;
     }

     .detail-row:last-child {
         border-bottom: none;
     }

     .detail-label {
         color: #6c757d;
         font-weight: 500;
     }

     .detail-value {
         font-weight: 600;
         color: var(--dark);
         font-family: var(--font-main);
     }

     .class-actions {
         display: flex;
         gap: 10px;
         justify-content: flex-end;
         padding-top: 15px;
         border-top: 1px solid #e9ecef;
     }

     .btn-sm {
         padding: 8px 16px;
         font-size: 0.85rem;
     }

     .btn-attendance {
         background-color: var(--info);
         color: white;
     }

     .btn-attendance:hover {
         background-color: #138496;
     }

     .btn-view {
         background-color: var(--success);
         color: white;
     }

     .btn-view:hover {
         background-color: #218838;
     }

     /* Badges y Estados */
     .status-badge {
         padding: 6px 12px;
         border-radius: 20px;
         font-size: 0.8rem;
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

     .status-cancelled {
         background: #f8d7da;
         color: #721c24;
     }

     .capacity-info {
         display: flex;
         align-items: center;
         gap: 5px;
     }

     .capacity-bar {
         flex: 1;
         height: 6px;
         background: #e9ecef;
         border-radius: 3px;
         overflow: hidden;
     }

     .capacity-fill {
         height: 100%;
         background: var(--success);
         transition: width 0.3s;
     }

     /* Sin datos */
     .no-data {
         text-align: center;
         padding: 60px 20px;
         color: #6c757d;
         grid-column: 1 / -1;
     }

     .no-data i {
         font-size: 4rem;
         margin-bottom: 20px;
         color: #dee2e6;
     }

     .no-data h3 {
         font-size: 1.5rem;
         margin-bottom: 10px;
         color: #495057;
     }

     .no-data p {
         font-size: 1rem;
         margin-bottom: 20px;
     }

     /* Responsive */
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

         .classes-container {
             padding: 20px;
         }

         .page-header {
             flex-direction: column;
             gap: 15px;
             align-items: flex-start;
         }

         .page-header-actions {
             width: 100%;
             display: flex;
             justify-content: space-between;
             gap: 10px;
         }

         .page-header-left h1 {
             margin-bottom: 0;
         }

         .view-toggle {
             margin: 0;
             align-self: flex-end;
         }

         .filters-section {
             flex-direction: column;
             align-items: stretch;
         }

         .filter-select,
         .date-input {
             min-width: 100%;
         }

         .grid-container {
             grid-template-columns: 1fr;
         }

         .class-actions {
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

         .classes-container {
             padding: 15px;
         }

         .table {
             font-size: 0.8rem;
         }

         .table th,
         .table td {
             padding: 8px 6px;
         }
     }

     /* Animaciones */
     .fade-in {
         animation: fadeIn 0.5s ease-in;
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
 </style>

 <!-- Contenido -->
 <div class="content">
     <div class="page-inner">
     <div class="classes-container">
         <!-- Encabezado -->
         <div class="page-header">
             <div class="page-header-left">
                 <h1>Clases Asignadas</h1>
             </div>
             <div class="page-header-actions">
                 <div class="view-toggle">
                     <button class="view-btn active" id="viewGrid" onclick="switchView('grid')">
                         <i class="fas fa-th-large"></i>
                     </button>
                     <button class="view-btn" id="viewList" onclick="switchView('list')">
                         <i class="fas fa-list"></i>
                     </button>
                 </div>
                 <button class="btn btn-primary" onclick="verCalendario()">
                     <i class="fas fa-calendar"></i> Ver Calendario
                 </button>
             </div>
         </div>

         <!-- Filtros -->
         <div class="filters-section">
             <select class="filter-select" id="statusFilter" onchange="filtrarClases()">
                 <option value="">Todas las clases</option>
                 <option value="upcoming">Próximas</option>
                 <option value="in-progress">En curso</option>
                 <option value="completed">Completadas</option>
             </select>
             <select class="filter-select" id="typeFilter" onchange="filtrarClases()">
                 <option value="">Todos los tipos</option>
                 <option value="yoga">Yoga</option>
                 <option value="cardio">Cardio</option>
                 <option value="strength">Fuerza</option>
                 <option value="dance">Baile</option>
             </select>
             <div class="date-filter">
                 <input type="date" class="date-input" id="dateFilter" onchange="filtrarClases()">
             </div>
             <button class="btn btn-secondary" onclick="resetFilters()">
                 <i class="fas fa-redo"></i> Limpiar Filtros
             </button>
         </div>

         <!-- Vista de Grid (Visible por defecto) -->
         <div class="grid-container" id="gridView">
             <?php foreach ($clases as $item): ?>
                 <div class="class-card">
                     <div class="class-header">
                         <div>
                             <h3 class="class-title"><?php echo htmlspecialchars($item->nombre); ?></h3>
                         </div>
                     </div>
                     <div class="class-schedule">
                         <i class="far fa-calendar"></i>
                         <span>Lun, Mie, Vie</span>
                         <i class="far fa-clock"></i>
                         <span>Hora de Inicio: <?php echo date("g:i A", strtotime($item->horario)); ?></span>
                     </div>
                     <div class="class-details">
                         <div class="detail-row">
                             <span class="detail-label">Cupo</span>
                             <span class="detail-value"><?php echo htmlspecialchars($item->cupo); ?> Personas</span>
                         </div>
                     </div>
                     <div class="class-actions">
                         <button class="btn btn-sm btn-attendance" onclick="registrarAsistencia(1)">
                             <i class="fas fa-clipboard-check"></i> Asistencia
                         </button>
                         <button class="btn btn-sm btn-view" onclick="verDetalles(1)">
                             <i class="fas fa-eye"></i> Ver
                         </button>
                     </div>
                 </div>
             <?php endforeach; ?>
         </div>
     </div>
     </div>
 </div>
 <script>
   function switchView(mode) {
     const grid = document.getElementById('gridView');
     const list = document.getElementById('listView');
     document.getElementById('viewGrid').classList.toggle('active', mode === 'grid');
     document.getElementById('viewList').classList.toggle('active', mode === 'list');
     if (grid) grid.style.display = (mode === 'grid' ? 'grid' : 'none');
     if (list) list.style.display = (mode === 'list' ? 'block' : 'none');
   }
   function verCalendario() {
     // Cambia a la ruta real si tienes una página de calendario
     window.location.href = 'calendario_clases.php';
   }
   function filtrarClases() {
     // Filtro simple: sólo reinicia por ahora
     // Implementar filtrado dinámico si se desea
     console.log('Filtrar clases (implementar lógica si es necesario)');
   }
   function resetFilters() {
     document.getElementById('statusFilter').value = '';
     document.getElementById('typeFilter').value = '';
     document.getElementById('dateFilter').value = '';
     filtrarClases();
   }
   function registrarAsistencia(id) {
     alert('Registrar asistencia (id clase: ' + id + ')');
   }
   function verDetalles(id) {
     // Redirigir a la página de detalles si existe
     window.location.href = 'editar_clase.php?id=' + id;
   }
 </script>
 <?php require_once "templates/footer.php"; ?>