<?php
require_once "autorizacion/auth.php"; // valida login y arranca sesión

// Verificar que tenga rol de admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: index.php"); // si no lo mandamos al login
    exit();
}

require_once "templates/header.php";
include_once "conexion/bd.php";

// Obtener lista de solicitudes de cancelación de membresías
$sql = $conexion->prepare("SELECT solicitud_cancelacion.id as id_solicitud, solicitud_cancelacion.estado as estado, solicitud_cancelacion.motivo as motivo, solicitud_cancelacion.fecha_solicitud as fecha_solicitud,CONCAT(usuarios.nombre, ' ', usuarios.apellido) as nombre_socio, membresias.nombre as nombre_membresia FROM solicitud_cancelacion INNER JOIN membresia_usuario ON solicitud_cancelacion.id_membresia_usuario=membresia_usuario.id INNER JOIN usuarios ON membresia_usuario.id_usuario=usuarios.id INNER JOIN membresias ON membresia_usuario.id_membresia=membresias.id");
$sql->execute();
$solicitudes = $sql->fetchAll(PDO::FETCH_OBJ);

// Obtener total de solicitudes
$sql = $conexion->prepare("SELECT COUNT(*) as cantidad FROM solicitud_cancelacion");
$sql->execute();
$cantidad_solicitudes = $sql->fetch(PDO::FETCH_OBJ);

// Obtener cantidad de solictiudes rechazadas
$sql = $conexion->prepare("SELECT COUNT(*) as cantidad FROM solicitud_cancelacion WHERE estado = 'rechazada'");
$sql->execute();
$cantidad_solicitudes_rechazadas = $sql->fetch(PDO::FETCH_OBJ);

// Obtener cantidad de solicitudes pendientes
$sql = $conexion->prepare("SELECT COUNT(*) as cantidad FROM solicitud_cancelacion WHERE estado = 'pendiente'");
$sql->execute();
$cantidad_solicitudes_pendientes = $sql->fetch(PDO::FETCH_OBJ);

// Obtener cantidad de solicitudes aprobadas
$sql = $conexion->prepare("SELECT COUNT(*) as cantidad FROM solicitud_cancelacion WHERE estado = 'aprobada'");
$sql->execute();
$cantidad_solicitudes_aprobadas = $sql->fetch(PDO::FETCH_OBJ);
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
        --danger: #e74c3c;
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

    .sidebar-menu .badge {
        position: absolute;
        right: 20px;
        background: var(--secondary);
        color: white;
        border-radius: 10px;
        padding: 2px 8px;
        font-size: 0.7rem;
    }

    /* Contenido Principal */
    .main-content {
        flex: 1;
        margin-left: 250px;
        transition: all 0.3s;
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

    .user-info {
        margin-right: 15px;
        text-align: right;
    }

    .user-name {
        font-weight: 600;
        color: white;
        font-family: var(--font-main);
    }

    .user-role {
        font-size: 0.8rem;
        color: #c2c7d0;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: var(--secondary);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-family: var(--font-main);
    }

    .notification-bell {
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

    /* Contenido */
    .content {
        padding: 20px;
    }

    .page-header {
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 1px solid #dee2e6;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .page-header h1 {
        font-size: 1.8rem;
        color: var(--dark);
        margin-bottom: 5px;
        font-family: var(--font-main);
    }

    .breadcrumb {
        display: flex;
        list-style: none;
        padding: 0;
        margin: 0;
        font-size: 0.9rem;
        color: var(--primary);
        width: 100%;
    }

    .breadcrumb li:not(:last-child):after {
        content: "/";
        margin: 0 8px;
    }

    /* Contadores */
    .counters {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .counter-card {
        background-color: var(--card);
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        border-left: 4px solid var(--accent);
    }

    .counter-card.pendientes {
        border-left-color: var(--warning);
    }

    .counter-card.procesadas {
        border-left-color: var(--success);
    }

    .counter-card.rechazadas {
        border-left-color: var(--danger);
    }

    .counter-icon {
        width: 60px;
        height: 60px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        font-size: 1.8rem;
        color: white;
    }

    .counter-icon.pendientes {
        background-color: var(--warning);
    }

    .counter-icon.procesadas {
        background-color: var(--success);
    }

    .counter-icon.rechazadas {
        background-color: var(--danger);
    }

    .counter-icon.todas {
        background-color: var(--accent);
    }

    .counter-info h3 {
        font-size: 2rem;
        margin-bottom: 5px;
        color: var(--dark);
        font-family: var(--font-main);
    }

    .counter-info p {
        color: #6c757d;
        margin: 0;
    }

    /* Filtros */
    .filters-bar {
        background-color: var(--card);
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        padding: 20px;
        margin-bottom: 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .search-box {
        position: relative;
        flex: 1;
        min-width: 250px;
    }

    .search-box input {
        width: 100%;
        padding: 12px 15px 12px 45px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 1rem;
        transition: all 0.3s;
    }

    .search-box input:focus {
        border-color: var(--accent);
        outline: none;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
    }

    .search-box i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
    }

    .filters-group {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }

    .filter-select {
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 1rem;
        background-color: white;
        min-width: 150px;
    }

    .filter-select:focus {
        border-color: var(--accent);
        outline: none;
    }

    /* Tabla de solicitudes */
    .requests-table-container {
        background-color: var(--card);
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        padding: 25px;
        margin-bottom: 30px;
        overflow-x: auto;
    }

    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 15px;
    }

    .table-header h2 {
        font-size: 1.5rem;
        color: var(--dark);
        font-family: var(--font-main);
    }

    .table-header h2 i {
        color: var(--danger);
        margin-right: 10px;
    }

    .refresh-btn {
        background-color: var(--accent);
        color: white;
        border: none;
        border-radius: 6px;
        padding: 10px 20px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        transition: all 0.3s;
    }

    .refresh-btn:hover {
        background-color: #2980b9;
    }

    .refresh-btn i {
        margin-right: 8px;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1000px;
    }

    .table th,
    .table td {
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid #dee2e6;
        vertical-align: top;
    }

    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
        color: var(--dark);
        font-family: var(--font-main);
        position: sticky;
        top: 0;
    }

    .table tr:hover {
        background-color: #f8f9fa;
    }

    .table tr:last-child td {
        border-bottom: none;
    }

    /* Columnas específicas */
    .col-nombre {
        width: 180px;
    }

    .col-membresia {
        width: 150px;
    }

    .col-motivo {
        min-width: 300px;
        max-width: 400px;
    }

    .col-fecha {
        width: 150px;
    }

    .col-estado {
        width: 180px;
        text-align: center;
    }

    .col-accion {
        width: 150px;
        text-align: center;
    }

    /* Estilos para el contenido */
    .socio-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .socio-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: var(--accent);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-family: var(--font-main);
        flex-shrink: 0;
    }

    .socio-details h4 {
        font-weight: 600;
        margin-bottom: 3px;
        color: var(--dark);
    }

    .socio-details p {
        font-size: 0.85rem;
        color: #6c757d;
        margin: 0;
    }

    .membresia-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-block;
        text-align: center;
    }

    .membresia-basica {
        background: #e3f2fd;
        color: #1565c0;
    }

    .membresia-intermedia {
        background: #e8f5e9;
        color: #2e7d32;
    }

    .membresia-premium {
        background: #fff3e0;
        color: #ef6c00;
    }

    .motivo-text {
        max-height: 100px;
        overflow-y: auto;
        padding-right: 10px;
        font-size: 0.95rem;
        line-height: 1.4;
    }

    .motivo-text::-webkit-scrollbar {
        width: 6px;
    }

    .motivo-text::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    .motivo-text::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 3px;
    }

    .motivo-text::-webkit-scrollbar-thumb:hover {
        background: #999;
    }

    /* Estado de la solicitud */
    .estado-badge {
        padding: 8px 15px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-block;
        text-align: center;
        min-width: 120px;
    }

    .estado-pendiente {
        background: #fff3cd;
        color: #856404;
    }

    .estado-procesada {
        background: #d4edda;
        color: #155724;
    }

    .estado-rechazada {
        background: #f8d7da;
        color: #721c24;
    }

    /* Botón de acción */
    .action-btn {
        padding: 6px 12px;
        border-radius: 6px;
        border: none;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-width: 110px;
    }

    .action-btn i {
        font-size: 0.9rem;
    }

    .btn-procesar {
        background-color: var(--success);
        color: white;
    }

    .btn-procesar:hover {
        background-color: #27ae60;
        transform: translateY(-2px);
    }

    .btn-rechazar {
        background-color: var(--danger);
        color: white;
    }

    .btn-rechazar:hover {
        background-color: #c0392b;
        transform: translateY(-2px);
    }

    .btn-aceptar {
        background-color: var(--accent);
        color: white;
    }

    .btn-aceptar:hover {
        background-color: #2980b9;
        transform: translateY(-2px);
    }

    .btn-revertir {
        background-color: var(--warning);
        color: white;
    }

    .btn-revertir:hover {
        background-color: #e67e22;
        transform: translateY(-2px);
    }

    .btn-disabled {
        background-color: #6c757d;
        color: white;
        cursor: not-allowed;
    }

    .btn-disabled:hover {
        transform: none;
        background-color: #6c757d;
    }

    /* Paginación */
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        margin-top: 30px;
        flex-wrap: wrap;
    }

    .pagination-btn {
        padding: 10px 15px;
        border: 1px solid #ddd;
        background-color: white;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s;
        font-weight: 600;
        color: var(--dark);
        text-decoration: none;
    }

    .pagination-btn:hover:not(.disabled) {
        background-color: var(--accent);
        color: white;
        border-color: var(--accent);
    }

    .pagination-btn.active {
        background-color: var(--accent);
        color: white;
        border-color: var(--accent);
    }

    .pagination-btn.disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .page-info {
        font-size: 0.9rem;
        color: #6c757d;
    }

    /* Sin resultados */
    .no-results {
        text-align: center;
        padding: 40px 20px;
        color: #6c757d;
    }

    .no-results i {
        font-size: 3rem;
        margin-bottom: 15px;
        color: #dee2e6;
    }

    .no-results h3 {
        font-size: 1.3rem;
        margin-bottom: 10px;
        color: var(--dark);
    }

    /* Footer */
    .footer {
        background-color: var(--header);
        color: white;
        padding: 20px;
        text-align: center;
        margin-top: 30px;
    }

    /* Modal */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 2000;
        justify-content: center;
        align-items: center;
        animation: fadeIn 0.3s ease-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    .modal {
        background-color: white;
        border-radius: 12px;
        width: 90%;
        max-width: 500px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        animation: slideUp 0.3s ease-out;
    }

    @keyframes slideUp {
        from {
            transform: translateY(30px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .modal-header {
        padding: 25px 30px;
        border-bottom: 1px solid #eee;
        display: flex;
        align-items: center;
    }

    .modal-header i {
        font-size: 2rem;
        margin-right: 15px;
    }

    .modal-header h3 {
        font-size: 1.5rem;
        color: var(--dark);
        font-family: var(--font-main);
    }

    .modal-body {
        padding: 30px;
    }

    .modal-body p {
        color: #6c757d;
        line-height: 1.6;
        margin-bottom: 20px;
    }

    .solicitud-info {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .info-item {
        margin-bottom: 10px;
        display: flex;
    }

    .info-label {
        font-weight: 600;
        min-width: 120px;
        color: var(--dark);
    }

    .modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        padding: 20px 30px;
        border-top: 1px solid #eee;
    }

    .modal-btn {
        padding: 12px 25px;
        border-radius: 8px;
        border: none;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        min-width: 120px;
    }

    .modal-btn-secondary {
        background-color: #f8f9fa;
        color: #6c757d;
        border: 1px solid #dee2e6;
    }

    .modal-btn-secondary:hover {
        background-color: #e9ecef;
    }

    .modal-btn-primary {
        background: linear-gradient(135deg, var(--success), #27ae60);
        color: white;
        border: none;
    }

    .modal-btn-primary:hover {
        background: linear-gradient(135deg, #27ae60, var(--success));
    }

    .modal-btn-danger {
        background: linear-gradient(135deg, var(--danger), #c0392b);
        color: white;
        border: none;
    }

    .modal-btn-danger:hover {
        background: linear-gradient(135deg, #c0392b, var(--danger));
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .counters {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 992px) {
        .filters-bar {
            flex-direction: column;
            align-items: stretch;
        }

        .search-box {
            min-width: 100%;
        }

        .filters-group {
            width: 100%;
            justify-content: space-between;
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

        .header-title {
            display: none;
        }

        .counters {
            grid-template-columns: 1fr;
        }

        .table-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .refresh-btn {
            width: 100%;
            justify-content: center;
        }

        .modal-actions {
            flex-direction: column;
        }

        .modal-btn {
            width: 100%;
        }
    }

    @media (max-width: 576px) {
        .filters-group {
            flex-direction: column;
        }

        .filter-select {
            width: 100%;
        }

        .pagination {
            flex-direction: column;
        }

        .socio-info {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<div class="page-header">
    <h1>Gestión de Cancelaciones</h1>
</div>

<!-- Contadores -->
<div class="counters">
    <div class="counter-card">
        <div class="counter-icon todas">
            <i class="fas fa-list"></i>
        </div>
        <div class="counter-info">
            <h3 id="total-solicitudes"><?php echo htmlspecialchars($cantidad_solicitudes->cantidad); ?></h3>
            <p>Total Solicitudes</p>
        </div>
    </div>
    <div class="counter-card pendientes">
        <div class="counter-icon pendientes">
            <i class="fas fa-clock"></i>
        </div>
        <div class="counter-info">
            <h3 id="pendientes-count"><?php echo htmlspecialchars($cantidad_solicitudes_pendientes->cantidad); ?></h3>
            <p>Solicitudes Pendientes</p>
        </div>
    </div>
    <div class="counter-card procesadas">
        <div class="counter-icon procesadas">
            <i class="fas fa-check"></i>
        </div>
        <div class="counter-info">
            <h3 id="procesadas-count"><?php echo htmlspecialchars($cantidad_solicitudes_aprobadas->cantidad); ?></h3>
            <p>Solicitudes Procesadas</p>
        </div>
    </div>
    <div class="counter-card rechazadas">
        <div class="counter-icon rechazadas">
            <i class="fas fa-times"></i>
        </div>
        <div class="counter-info">
            <h3 id="rechazadas-count"><?php echo htmlspecialchars($cantidad_solicitudes_rechazadas->cantidad); ?></h3>
            <p>Solicitudes Rechazadas</p>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="filters-bar">
    <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="search-input" placeholder="Buscar por nombre del socio...">
    </div>
    <div class="filters-group">
        <select class="filter-select" id="status-filter">
            <option value="">Todos los estados</option>
            <option value="pendiente">Pendientes</option>
            <option value="procesada">Procesadas</option>
            <option value="rechazada">Rechazadas</option>
        </select>
        <select class="filter-select" id="membresia-filter">
            <option value="">Todas las membresías</option>
            <option value="basica">Básica</option>
            <option value="intermedia">Intermedia</option>
            <option value="premium">Premium</option>
        </select>
        <button class="action-btn btn-procesar" id="export-btn">
            <i class="fas fa-file-export"></i> Exportar
        </button>
    </div>
</div>

<!-- Tabla de solicitudes -->
<div class="requests-table-container">
    <div class="table-header">
        <h2><i class="fas fa-list"></i> Lista de Solicitudes</h2>
        <button class="refresh-btn" id="refresh-btn">
            <i class="fas fa-sync-alt"></i> Actualizar
        </button>
    </div>

    <div class="table-responsive">
        <table class="table" id="requests-table">
            <thead>
                <tr>
                    <th class="col-nombre">Nombre del Socio</th>
                    <th class="col-membresia">Membresía</th>
                    <th class="col-motivo">Motivo</th>
                    <th class="col-fecha">Fecha de Solicitud</th>
                    <th class="col-estado">Estado</th>
                    <th class="col-accion">Acción</th>
                </tr>
            </thead>
            <tbody id="requests-body">
                <?php foreach ($solicitudes as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item->nombre_socio); ?></td>
                        <td><?php echo htmlspecialchars($item->nombre_membresia); ?></td>
                        <td><?php echo htmlspecialchars($item->motivo); ?></td>
                        <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($item->fecha_solicitud))); ?></td>
                        <td>
                            <?php
                            $estadoClass = '';
                            if ($item->estado == 'pendiente') {
                                $estadoClass = 'estado-pendiente';
                            } elseif ($item->estado == 'procesada' || $item->estado == 'aprobada') {
                                $estadoClass = 'estado-procesada';
                            } elseif ($item->estado == 'rechazada') {
                                $estadoClass = 'estado-rechazada';
                            }
                            ?>
                            <span class="estado-badge <?php echo $estadoClass; ?>">
                                <?php echo htmlspecialchars(ucfirst($item->estado)); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($item->estado == 'pendiente'): ?>
                                <a href="editar_solicitud_cancelar_membresia_socio.php?id_solicitud=<?php echo $item->id_solicitud; ?>" class="action-btn btn-aceptar">
                                    <i class="fas fa-edit"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Sin resultados -->
    <div id="no-results" class="no-results" style="display: none;">
        <i class="fas fa-search"></i>
        <h3>No se encontraron solicitudes</h3>
        <p>No hay solicitudes de cancelación que coincidan con los filtros aplicados.</p>
    </div>

    <!-- Paginación -->
    <div class="pagination">
        <button class="pagination-btn" id="prev-page" disabled>
            <i class="fas fa-chevron-left"></i> Anterior
        </button>
        <span class="page-info" id="page-info">Página 1 de 1</span>
        <button class="pagination-btn" id="next-page" disabled>
            Siguiente <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</div>


<script>
    // Variables de paginación
    let currentPage = 1;
    const itemsPerPage = 5;
    let filteredSolicitudes = [...solicitudesData];
    let currentSolicitudId = null;

    // Actualizar controles de paginación
    function actualizarPaginacion() {
        const totalPages = Math.ceil(filteredSolicitudes.length / itemsPerPage);
        const prevBtn = document.getElementById('prev-page');
        const nextBtn = document.getElementById('next-page');
        const pageInfo = document.getElementById('page-info');

        // Actualizar estado de botones
        prevBtn.disabled = currentPage === 1;
        prevBtn.classList.toggle('disabled', currentPage === 1);

        nextBtn.disabled = currentPage === totalPages || totalPages === 0;
        nextBtn.classList.toggle('disabled', currentPage === totalPages || totalPages === 0);

        // Actualizar información de página
        pageInfo.textContent = totalPages === 0 ? 'Sin resultados' : `Página ${currentPage} de ${totalPages}`;
    }

    // Navegar a página anterior
    document.getElementById('prev-page').addEventListener('click', function() {
        if (currentPage > 1) {
            currentPage--;
            renderSolicitudes();
        }
    });

    // Navegar a página siguiente
    document.getElementById('next-page').addEventListener('click', function() {
        const totalPages = Math.ceil(filteredSolicitudes.length / itemsPerPage);
        if (currentPage < totalPages) {
            currentPage++;
            renderSolicitudes();
        }
    });

    // Abrir modal para cambiar estado
    function openChangeStatusModal(solicitudId, action) {
        currentSolicitudId = solicitudId;
        const solicitud = solicitudesData.find(s => s.id === solicitudId);

        if (!solicitud) return;

        const modal = document.getElementById('change-status-modal');
        const modalTitle = document.getElementById('modal-title');
        const modalMessage = document.getElementById('modal-message');
        const solicitudInfo = document.getElementById('modal-solicitud-info');
        const rejectContainer = document.getElementById('reject-reason-container');
        const modalConfirm = document.getElementById('modal-confirm');

        // Configurar según la acción
        if (action === 'procesar') {
            modalTitle.textContent = 'Procesar Solicitud';
            modalMessage.textContent = '¿Estás seguro de que deseas procesar esta solicitud de cancelación?';
            modalConfirm.className = 'modal-btn modal-btn-primary';
            modalConfirm.innerHTML = '<i class="fas fa-check"></i> Procesar';
            rejectContainer.style.display = 'none';
        } else if (action === 'rechazar') {
            modalTitle.textContent = 'Rechazar Solicitud';
            modalMessage.textContent = '¿Estás seguro de que deseas rechazar esta solicitud de cancelación?';
            modalConfirm.className = 'modal-btn modal-btn-danger';
            modalConfirm.innerHTML = '<i class="fas fa-times"></i> Rechazar';
            rejectContainer.style.display = 'block';
        } else if (action === 'revertir') {
            modalTitle.textContent = 'Revertir Estado';
            modalMessage.textContent = '¿Estás seguro de que deseas revertir esta solicitud a estado pendiente?';
            modalConfirm.className = 'modal-btn modal-btn-primary';
            modalConfirm.innerHTML = '<i class="fas fa-undo"></i> Revertir';
            rejectContainer.style.display = 'none';
        }

        // Mostrar información de la solicitud
        solicitudInfo.innerHTML = `
                <div class="info-item">
                    <span class="info-label">Socio:</span>
                    <span>${solicitud.socioNombre}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Membresía:</span>
                    <span>${getMembresiaText(solicitud.membresia)}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Estado actual:</span>
                    <span class="${getEstadoClass(solicitud.estado)}" style="padding: 4px 10px; border-radius: 4px;">
                        ${getEstadoText(solicitud.estado)}
                    </span>
                </div>
            `;

        // Limpiar campo de motivo de rechazo
        document.getElementById('reject-reason').value = '';

        // Configurar acción del botón confirmar
        modalConfirm.onclick = function() {
            cambiarEstadoSolicitud(solicitudId, action);
            modal.style.display = 'none';
        };

        // Mostrar modal
        modal.style.display = 'flex';
    }

    // Cambiar estado de la solicitud
    function cambiarEstadoSolicitud(solicitudId, action) {
        const solicitudIndex = solicitudesData.findIndex(s => s.id === solicitudId);
        if (solicitudIndex === -1) return;

        const motivoRechazo = document.getElementById('reject-reason').value.trim();

        if (action === 'procesar') {
            solicitudesData[solicitudIndex].estado = 'procesada';
            mostrarNotificacion(`Solicitud #${solicitudId} procesada exitosamente.`);
        } else if (action === 'rechazar') {
            solicitudesData[solicitudIndex].estado = 'rechazada';
            if (motivoRechazo) {
                solicitudesData[solicitudIndex].motivoRechazo = motivoRechazo;
            }
            mostrarNotificacion(`Solicitud #${solicitudId} rechazada exitosamente.`);
        } else if (action === 'revertir') {
            solicitudesData[solicitudIndex].estado = 'pendiente';
            mostrarNotificacion(`Solicitud #${solicitudId} revertida a pendiente.`);
        }

        // Registrar en consola (simulación de backend)
        console.log('=== CAMBIO DE ESTADO ===');
        console.log('Solicitud ID:', solicitudId);
        console.log('Nuevo estado:', solicitudesData[solicitudIndex].estado);
        console.log('Acción:', action);
        if (motivoRechazo) console.log('Motivo rechazo:', motivoRechazo);

        // Actualizar vista
        filtrarSolicitudes();
    }

    // Mostrar notificación
    function mostrarNotificacion(mensaje) {
        // Crear elemento de notificación
        const notification = document.createElement('div');
        notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background-color: var(--success);
                color: white;
                padding: 15px 20px;
                border-radius: 6px;
                box-shadow: 0 3px 10px rgba(0,0,0,0.2);
                z-index: 3000;
                display: flex;
                align-items: center;
                gap: 10px;
                animation: slideIn 0.3s ease-out;
            `;

        notification.innerHTML = `
                <i class="fas fa-check-circle"></i>
                <span>${mensaje}</span>
            `;

        document.body.appendChild(notification);

        // Remover después de 3 segundos
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease-in';
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }

    // Función para exportar datos
    document.getElementById('export-btn').addEventListener('click', function() {
        const exportData = filteredSolicitudes.map(solicitud => ({
            'ID Solicitud': solicitud.id,
            'Nombre Socio': solicitud.socioNombre,
            'ID Socio': solicitud.socioId,
            'Membresía': getMembresiaText(solicitud.membresia),
            'Motivo': solicitud.motivo,
            'Fecha Solicitud': formatDate(solicitud.fechaSolicitud),
            'Estado': getEstadoText(solicitud.estado)
        }));

        console.log('=== DATOS EXPORTADOS ===');
        console.table(exportData);

        mostrarNotificacion(`Datos exportados (${exportData.length} registros)`);
    });

    // Refrescar datos
    document.getElementById('refresh-btn').addEventListener('click', function() {
        // Simular refresco de datos
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Actualizando...';
        this.disabled = true;

        setTimeout(() => {
            filtrarSolicitudes();
            this.innerHTML = '<i class="fas fa-sync-alt"></i> Actualizar';
            this.disabled = false;
            mostrarNotificacion('Datos actualizados correctamente');
        }, 1000);
    });

    // Cerrar modal
    document.getElementById('modal-cancel').addEventListener('click', function() {
        document.getElementById('change-status-modal').style.display = 'none';
    });

    // Cerrar modal al hacer clic fuera
    document.getElementById('change-status-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            this.style.display = 'none';
        }
    });

    // Inicialización
    document.addEventListener('DOMContentLoaded', function() {
        // Configurar eventos de filtro
        document.getElementById('search-input').addEventListener('input', filtrarSolicitudes);
        document.getElementById('status-filter').addEventListener('change', filtrarSolicitudes);
        document.getElementById('membresia-filter').addEventListener('change', filtrarSolicitudes);

        // Cargar datos iniciales
        actualizarContadores();
        renderSolicitudes();

        // Agregar estilos de animación
        const style = document.createElement('style');
        style.textContent = `
                @keyframes slideIn {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                
                @keyframes slideOut {
                    from { transform: translateX(0); opacity: 1; }
                    to { transform: translateX(100%); opacity: 0; }
                }
            `;
        document.head.appendChild(style);
    });
</script>
<?php require_once "templates/footer.php"; ?>