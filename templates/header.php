<?php
// Comprobar el estado actual de la sesión
if (session_status() === PHP_SESSION_NONE) { // Si no hay ninguna sesión activa
    session_start(); // Inicia una nueva sesión o reanuda la existente
}

// Si no hay sesión iniciada, redirigir al login
if (!isset($_SESSION['rol'])) {
    header("Location: login.php");
    exit;
}
require_once "conexion/bd.php";

$id_socio = $_SESSION['id_usuario'];

// Verificar si el usuario con rol socio que se loguea tiene una membresia activa
$sql = $conexion->prepare("SELECT * FROM membresia_usuario WHERE id_usuario = :id_usuario  AND estado = 'activa'");
$sql->bindParam(':id_usuario', $id_socio, PDO::PARAM_INT);
$sql->execute();
$membresia_socio = $sql->fetch(PDO::FETCH_OBJ);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PowerFit Gym - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #dee2e6;
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
        }

        .breadcrumb li:not(:last-child):after {
            content: "/";
            margin: 0 8px;
        }

        /* Tarjetas de Métricas */
        .metrics {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .metric-card {
            background-color: var(--card);
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            display: flex;
            align-items: center;
            transition: transform 0.3s;
            border-left: 4px solid var(--secondary);
        }

        .metric-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .metric-icon {
            width: 60px;
            height: 60px;
            border-radius: 8px;
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

        /* Gráficos y Tablas */
        .dashboard-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }

        .chart-container,
        .recent-activity,
        .quick-actions {
            background-color: var(--card);
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .chart-container h2,
        .recent-activity h2,
        .quick-actions h2 {
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

        .activity-list {
            list-style: none;
        }

        .activity-item {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: white;
            font-size: 1rem;
        }

        .activity-info h4 {
            font-size: 0.95rem;
            margin-bottom: 3px;
            color: var(--dark);
        }

        .activity-info p {
            font-size: 0.8rem;
            color: var(--primary);
            margin: 0;
        }

        .activity-time {
            font-size: 0.7rem;
            color: #6c757d;
        }

        /* Quick Actions */
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
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
        }

        .action-btn:hover {
            background: var(--secondary);
            color: white;
            transform: translateY(-3px);
        }

        .action-btn i {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .action-btn span {
            font-size: 0.9rem;
            font-weight: 600;
        }

        /* Tabla de Miembros */
        .members-table {
            background-color: var(--card);
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-top: 20px;
        }

        .members-table h2 {
            font-size: 1.3rem;
            margin-bottom: 15px;
            color: var(--dark);
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            font-family: var(--font-main);
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: var(--dark);
            font-family: var(--font-main);
        }

        .table tr:hover {
            background-color: #f8f9fa;
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

        /* Footer */
        .footer {
            background-color: var(--header);
            color: white;
            padding: 20px;
            text-align: center;
            margin-top: auto;
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

            .metrics {
                grid-template-columns: 1fr;
            }

            .actions-grid {
                grid-template-columns: 1fr;
            }

            .header-title {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h3>Power<span>Fit</span></h3>
                <p style="font-size: 0.9rem; margin-top: 5px;">Panel de Control</p>
            </div>
            <div class="sidebar-menu">
                <ul>
                    <?php if ($_SESSION['rol'] == 'admin') { ?>
                        <li><a href="dash_admin.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                        <li><a href="gestion_usuarios.php"><i class="fas fa-users"></i> Gestion Usuarios</a></li>
                        <li><a href="gestion_membresias.php"><i class="fas fa-dumbbell"></i> Gestion Membresias</a></li>
                        <li><a href="listado_solicitudes_cancelar_membresias.php"><i class="fas fa-user-xmark"></i> Solicitudes de Cancelación de Membresias</a></li>
                        <li><a href="gestion_clases.php"><i class="fas fa-calendar-alt"></i> Gestion Clases</a></li>
                        <li><a href="gestion_pagos.php"><i class="fas fa-money-bill-wave"></i> Gestion Pagos</a></li>
                        <li><a href="gestion_socios.php"><i class="fas fa-user-shield"></i> Gestion Socios</a></li>
                        <li><a href="gestion_entrenadores.php"><i class="fas fa-dumbbell"></i> Gestion Entrenadores</a></li>
                    <?php } elseif ($_SESSION['rol'] == 'socio') { ?>
                        <li><a href="dash_cliente.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                        <li><a href="membresia_socio.php"><i class="fas fa-dumbbell"></i> Mi Membresia</a></li>
                        <?php if ($membresia_socio == true) { ?>
                            <li><a href="clases_socio.php"><i class="fas fa-book"></i> Mis Clases</a></li>
                            <li><a href="asistencia_socio.php"><i class="fas fa-clipboard-list"></i> Mi Asistencia</a></li>
                            <li><a href="rutinas_socio.php"><i class="fas fa-book"></i> Mis Rutinas</a></li>
                        <?php } ?>
                    <?php } elseif ($_SESSION['rol'] == 'entrenador') { ?>
                        <li><a href="dash_entrenador.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                        <li><a href="clases_entrenador.php"><i class="fas fa-book"></i> Mis Clases</a></li>
                        <li><a href="asistencia_entrenador.php"><i class="fas fa-clipboard-list"></i> Mi Asistencia</a></li>
                        <li><a href="asignacion_rutinas.php"><i class="fas fa-book"></i> Asignacion Rutinas</a></li>
                        <li><a href="siguimientos_entrenador.php"><i class="fas fa-chart-line"></i> Siguimientos Socios</a></li>
                    <?php } ?>
                    <li><a href="configuracion_cuenta.php"><i class="fas fa-cog"></i> Configuración</a></li>
                    <li><a href="controladores/logout.php" style="color: var(--secondary);"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
                </ul>
            </div>
        </div>

        <!-- Contenido Principal -->
        <div class="main-content">
            <!-- Header -->
            <?php include('sidebar.php'); ?>