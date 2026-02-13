<?php
require_once "autorizacion/auth.php"; // valida login y arranca sesión

// Verificar que tenga rol de admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: index.php"); // si no lo mandamos al login
    exit();
}

require_once "templates/header.php";
include_once "conexion/bd.php";

$id_socio = $_GET['id_socio'] ?? null;

// Validar que exista el ID
if (empty($id_socio) || !is_numeric($id_socio) || $id_socio <= 0) {
    header("Location: gestion_socios.php"); // si no lo mandamos al listado de socios
    exit();
}

// Paginación
$registros_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Contar total de asistencias del socio
$sqlCount = $conexion->prepare("SELECT COUNT(*) FROM asistencia WHERE id_usuario = :id_socio");
$sqlCount->bindParam(':id_socio', $id_socio, PDO::PARAM_INT);
$sqlCount->execute();
$total_asistencias = $sqlCount->fetchColumn();
$total_paginas = max(1, ceil($total_asistencias / $registros_por_pagina));

if ($pagina_actual > $total_paginas) {
    $pagina_actual = $total_paginas;
    $offset = ($pagina_actual - 1) * $registros_por_pagina;
}

// Obtener registro de asistencias del socio
$sql = $conexion->prepare("SELECT fecha, hora_entrada, hora_salida FROM asistencia WHERE id_usuario = :id_socio ORDER BY fecha DESC, hora_entrada DESC LIMIT :limit OFFSET :offset");
$sql->bindParam(':id_socio', $id_socio, PDO::PARAM_INT);
$sql->bindValue(':limit', $registros_por_pagina, PDO::PARAM_INT);
$sql->bindValue(':offset', $offset, PDO::PARAM_INT);
$sql->execute();
$asistencias = $sql->fetchAll(PDO::FETCH_OBJ);

// Obtener asistencias del mes actual
$sqlMes = $conexion->prepare("
    SELECT COUNT(*) AS total_mes 
    FROM asistencia 
    WHERE id_usuario = :id_socio
    AND MONTH(fecha) = MONTH(CURDATE())
    AND YEAR(fecha) = YEAR(CURDATE())
");
$sqlMes->bindParam(':id_socio', $id_socio, PDO::PARAM_INT);
$sqlMes->execute();
$statsMes = $sqlMes->fetch(PDO::FETCH_ASSOC);
$totalAsistenciasMes = $statsMes['total_mes'];

// Obtener promedio de horas por día del socio
$sqlProm = $conexion->prepare("
    SELECT hora_entrada, hora_salida 
    FROM asistencia
    WHERE id_usuario = :id_socio 
    AND hora_salida IS NOT NULL
");
$sqlProm->bindParam(':id_socio', $id_socio, PDO::PARAM_INT);
$sqlProm->execute();
$asistenciasHoras = $sqlProm->fetchAll(PDO::FETCH_ASSOC);

$totalHoras = 0;
$diasContados = 0;

foreach ($asistenciasHoras as $row) {
    $entrada = new DateTime($row['hora_entrada']);
    $salida = new DateTime($row['hora_salida']);
    $diff = $entrada->diff($salida);

    // Convertir a horas decimales
    $horas = $diff->h + ($diff->i / 60);

    $totalHoras += $horas;
    $diasContados++;
}

$promedioHoras = ($diasContados > 0) ? round($totalHoras / $diasContados, 2) : 0;
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

    .attendance-container {
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

    .page-header h1 {
        font-size: 1.8rem;
        color: var(--dark);
        margin-bottom: 0;
        font-family: var(--font-main);
    }

    .stats-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 25px;
    }

    .stat-card {
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        color: white;
        padding: 20px;
        border-radius: 8px;
        text-align: center;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        font-family: var(--font-main);
        margin-bottom: 5px;
    }

    .stat-label {
        font-size: 0.9rem;
        opacity: 0.9;
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

    /* Filtros y Búsqueda */
    .filters-section {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        flex-wrap: wrap;
        align-items: center;
    }

    .search-box {
        flex: 1;
        min-width: 250px;
        position: relative;
    }

    .search-input {
        width: 100%;
        padding: 10px 15px 10px 40px;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        font-size: 0.9rem;
    }

    .search-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
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

    .date-filters {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .date-input {
        padding: 10px 15px;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        font-size: 0.9rem;
    }

    /* Tabla */
    .table-container {
        overflow-x: auto;
        border-radius: 8px;
        border: 1px solid #dee2e6;
        margin-bottom: 20px;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        min-width: 800px;
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

    /* Información del Socio */
    .member-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .member-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--info);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 0.9rem;
    }

    .member-details h4 {
        margin: 0;
        font-weight: 600;
        color: var(--dark);
        font-size: 0.95rem;
    }

    .member-details p {
        margin: 2px 0 0 0;
        color: #6c757d;
        font-size: 0.85rem;
    }

    /* Estados de Asistencia */
    .attendance-status {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-present {
        background: #d4edda;
        color: #155724;
    }

    .status-absent {
        background: #f8d7da;
        color: #721c24;
    }

    .status-late {
        background: #fff3cd;
        color: #856404;
    }

    /* Tiempos */
    .time-cell {
        font-family: 'Courier New', monospace;
        font-weight: 600;
        color: var(--dark);
    }

    .time-in {
        color: var(--success);
    }

    .time-out {
        color: var(--secondary);
    }

    .duration {
        color: var(--info);
        font-weight: 600;
    }

    /* Membresía */
    .membership-badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
        background: #e9ecef;
        color: #495057;
    }

    .membership-premium {
        background: #fff3cd;
        color: #856404;
    }

    .membership-basic {
        background: #d1ecf1;
        color: #0c5460;
    }

    .membership-standard {
        background: #d4edda;
        color: #155724;
    }

    /* Acciones */
    .action-buttons {
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
    }

    .btn-sm {
        padding: 6px 12px;
        font-size: 0.8rem;
    }

    .btn-icon {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-checkin {
        background-color: var(--success);
        color: white;
    }

    .btn-checkin:hover {
        background-color: #218838;
    }

    .btn-checkout {
        background-color: var(--warning);
        color: white;
    }

    .btn-checkout:hover {
        background-color: #e0a800;
    }

    .btn-view {
        background-color: var(--info);
        color: white;
    }

    .btn-view:hover {
        background-color: #138496;
    }

    /* Sin datos */
    .no-data {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
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

    /* Paginación */
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 5px;
        margin-top: 20px;
        flex-wrap: wrap;
    }

    .pagination a,
    .pagination span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 36px;
        padding: 0 10px;
        border-radius: 5px;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 600;
        font-family: var(--font-main);
        transition: all 0.3s ease;
        border: 1px solid #dee2e6;
        color: var(--dark);
        background: white;
    }

    .pagination a:hover {
        background-color: var(--accent);
        color: white;
        border-color: var(--accent);
    }

    .pagination .active {
        background-color: var(--secondary);
        color: white;
        border-color: var(--secondary);
        pointer-events: none;
    }

    .pagination .disabled {
        color: #adb5bd;
        pointer-events: none;
        background: #f8f9fa;
    }

    .pagination-info {
        text-align: center;
        margin-top: 10px;
        font-size: 0.85rem;
        color: #6c757d;
    }

    /* Footer fijo */
    .footer {
        margin-top: auto;
        background-color: var(--header);
        color: #c2c7d0;
        text-align: center;
        padding: 15px 20px;
        font-size: 0.85rem;
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

        .attendance-container {
            padding: 20px;
        }

        .page-header {
            flex-direction: column;
            gap: 15px;
            align-items: flex-start;
        }

        .stats-cards {
            grid-template-columns: 1fr;
        }

        .filters-section {
            flex-direction: column;
            align-items: stretch;
        }

        .search-box,
        .filter-select,
        .date-input {
            min-width: 100%;
        }

        .date-filters {
            flex-direction: column;
        }

        .action-buttons {
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

        .attendance-container {
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
</style>
<div class="attendance-container">
    <div class="page-header">
        <h1>Control de Asistencia</h1>
        <button class="btn btn-primary" id="btnRegistroManual">
            <i class="fas fa-user-plus"></i> Registro Manual
        </button>
    </div>

    <!-- Tarjetas de Estadísticas -->
    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-value" id="totalAsistencias"><?php echo htmlspecialchars($totalAsistenciasMes); ?></div>
            <div class="stat-label">Asistencias Mes</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="promedioDia"><?php echo htmlspecialchars($promedioHoras); ?> h</div>
            <div class="stat-label">Promedio del Socio</div>
        </div>
    </div>

    <!-- Filtros y Búsqueda -->
    <div class="filters-section">
        <div class="search-box">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="search-input" placeholder="Buscar socio..." id="searchInput">
        </div>
        <select class="filter-select" id="statusFilter">
            <option value="">Todos los estados</option>
            <option value="present">En gimnasio</option>
            <option value="absent">Fuera</option>
        </select>
        <select class="filter-select" id="membershipFilter">
            <option value="">Todas las membresías</option>
            <option value="premium">Premium</option>
            <option value="standard">Estándar</option>
            <option value="basic">Básica</option>
        </select>
        <div class="date-filters">
            <input type="date" class="date-input" id="filterDate" value="2024-03-25">
            <button class="btn btn-secondary" onclick="aplicarFiltros()">
                <i class="fas fa-filter"></i> Filtrar
            </button>
        </div>
    </div>

    <!-- Tabla de Asistencia -->
    <div class="table-container">
        <table class="table" id="attendanceTable">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Hora de Entrada</th>
                    <th>Hora de Salida</th>
                    <th>Duración</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($asistencias)) { ?>
                    <tr>
                        <td colspan="5">
                            <div class="no-data">
                                <i class="fas fa-calendar-times"></i>
                                <h3>Sin registros de asistencia</h3>
                                <p>Este socio aún no tiene registros de asistencia en el sistema.</p>
                            </div>
                        </td>
                    </tr>
                <?php } else { ?>
                    <?php foreach ($asistencias as $item) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item->fecha); ?></td>
                            <td class="time-cell time-in"><?php echo htmlspecialchars($item->hora_entrada); ?></td>
                            <td class="time-cell time-out">
                                <?php if ($item->hora_salida) { ?>
                                    <?php echo htmlspecialchars($item->hora_salida); ?>
                                <?php } else { ?>
                                    -
                                <?php } ?>
                            </td>
                            <td class="duration">
                                <?php
                                if ($item->hora_salida) {
                                    $entrada = new DateTime($item->hora_entrada);
                                    $salida = new DateTime($item->hora_salida);
                                    $intervalo = $entrada->diff($salida);
                                    echo $intervalo->format('%hh %im');
                                } else {
                                    echo 'En curso';
                                }
                                ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon btn-view" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <?php $base_url = "?id_socio=" . urlencode($id_socio); ?>
    <div class="pagination">
        <a href="<?= $base_url ?>&pagina=1" class="<?= $pagina_actual <= 1 ? 'disabled' : '' ?>"><i class="fas fa-angle-double-left"></i></a>
        <a href="<?= $base_url ?>&pagina=<?= $pagina_actual - 1 ?>" class="<?= $pagina_actual <= 1 ? 'disabled' : '' ?>"><i class="fas fa-angle-left"></i></a>

        <?php
        $rango = 2;
        $inicio = max(1, $pagina_actual - $rango);
        $fin = min($total_paginas, $pagina_actual + $rango);

        if ($inicio > 1) {
            echo '<a href="' . $base_url . '&pagina=1">1</a>';
            if ($inicio > 2) echo '<span class="disabled">...</span>';
        }

        for ($i = $inicio; $i <= $fin; $i++) {
            if ($i == $pagina_actual) {
                echo '<span class="active">' . $i . '</span>';
            } else {
                echo '<a href="' . $base_url . '&pagina=' . $i . '">' . $i . '</a>';
            }
        }

        if ($fin < $total_paginas) {
            if ($fin < $total_paginas - 1) echo '<span class="disabled">...</span>';
            echo '<a href="' . $base_url . '&pagina=' . $total_paginas . '">' . $total_paginas . '</a>';
        }
        ?>

        <a href="<?= $base_url ?>&pagina=<?= $pagina_actual + 1 ?>" class="<?= $pagina_actual >= $total_paginas ? 'disabled' : '' ?>"><i class="fas fa-angle-right"></i></a>
        <a href="<?= $base_url ?>&pagina=<?= $total_paginas ?>" class="<?= $pagina_actual >= $total_paginas ? 'disabled' : '' ?>"><i class="fas fa-angle-double-right"></i></a>
    </div>
    <div class="pagination-info">
        Mostrando <?= $total_asistencias > 0 ? $offset + 1 : 0 ?>-<?= min($offset + $registros_por_pagina, $total_asistencias) ?> de <?= $total_asistencias ?> asistencias | Página <?= $pagina_actual ?> de <?= $total_paginas ?>
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

        // Botón registro manual
        document.getElementById('btnRegistroManual').addEventListener('click', function() {
            alert('Abriendo formulario de registro manual de asistencia');
            // window.location.href = 'registro_manual.html';
        });

        // Filtros y búsqueda
        document.getElementById('searchInput').addEventListener('input', filtrarAsistencias);
        document.getElementById('statusFilter').addEventListener('change', filtrarAsistencias);
        document.getElementById('membershipFilter').addEventListener('change', filtrarAsistencias);

        // Notificaciones
        document.querySelector('.notification-bell').addEventListener('click', function() {
            alert('Tienes 8 notificaciones:\n- 3 socios en gimnasio\n- 2 asistencias pendientes de registro\n- 1 membresía por vencer\n- 2 recordatorios de pago');
        });

        // Actualizar estadísticas
        actualizarEstadisticas();
    });

    // Filtrar asistencias
    function filtrarAsistencias() {
        const search = document.getElementById('searchInput').value.toLowerCase();
        const statusFilter = document.getElementById('statusFilter').value;
        const membershipFilter = document.getElementById('membershipFilter').value;
        const rows = document.querySelectorAll('#attendanceTable tbody tr');

        rows.forEach(row => {
            const memberName = row.cells[0].textContent.toLowerCase();
            const membership = row.cells[1].textContent.toLowerCase();
            const status = row.cells[6].textContent.toLowerCase();

            const matchesSearch = memberName.includes(search);
            const matchesStatus = !statusFilter || status.includes(statusFilter);
            const matchesMembership = !membershipFilter || membership.includes(membershipFilter);

            row.style.display = matchesSearch && matchesStatus && matchesMembership ? '' : 'none';
        });

        actualizarEstadisticas();
    }

    // Aplicar filtros de fecha
    function aplicarFiltros() {
        const filterDate = document.getElementById('filterDate').value;
        const rows = document.querySelectorAll('#attendanceTable tbody tr');

        rows.forEach(row => {
            const fechaText = row.cells[5].textContent;
            const fechaRow = parseFecha(fechaText);
            const fechaFiltro = new Date(filterDate);

            if (fechaRow.toDateString() === fechaFiltro.toDateString()) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        actualizarEstadisticas();
        alert(`Filtro aplicado para: ${new Date(filterDate).toLocaleDateString('es-ES')}`);
    }

    // Parsear fecha DD/MM/YYYY
    function parseFecha(fechaStr) {
        const [day, month, year] = fechaStr.split('/');
        return new Date(year, month - 1, day);
    }

    // Actualizar estadísticas
    function actualizarEstadisticas() {
        let totalAsistencias = 0;
        let sociosActivos = 0;
        let totalDuracion = 0;
        const rows = document.querySelectorAll('#attendanceTable tbody tr');

        rows.forEach(row => {
            if (row.style.display !== 'none') {
                const estado = row.cells[6].textContent.toLowerCase();
                const duracion = row.cells[4].textContent;

                if (estado.includes('presente') || estado.includes('en gimnasio')) {
                    totalAsistencias++;
                }

                if (estado.includes('en gimnasio')) {
                    sociosActivos++;
                }

                // Calcular duración total (simplificado)
                if (duracion !== '-' && duracion !== 'En curso') {
                    const horas = parseInt(duracion.split('h')[0]) || 0;
                    const minutos = parseInt(duracion.split('h')[1]?.split('m')[0]) || 0;
                    totalDuracion += horas + (minutos / 60);
                }
            }
        });

        const promedio = totalAsistencias > 0 ? (totalDuracion / totalAsistencias).toFixed(1) : 0;
        const picoHorario = Math.min(Math.round((sociosActivos / 30) * 100), 100); // 30 es capacidad máxima

        // Actualizar UI
        document.getElementById('totalAsistencias').textContent = totalAsistencias;
        document.getElementById('sociosActivos').textContent = sociosActivos;
        document.getElementById('promedioDia').textContent = `${promedio}h`;
        document.getElementById('picoHorario').textContent = `${picoHorario}%`;
    }

    // Funciones de botones de acción
    document.querySelectorAll('.btn-checkin').forEach(btn => {
        btn.addEventListener('click', function() {
            const memberName = this.closest('tr').querySelector('.member-details h4').textContent;
            const horaActual = new Date().toLocaleTimeString('es-ES', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });

            if (confirm(`¿Registrar entrada para ${memberName} a las ${horaActual}?`)) {
                alert(`Entrada registrada para ${memberName} a las ${horaActual}`);
                // Aquí iría la lógica para actualizar la base de datos
                location.reload();
            }
        });
    });

    document.querySelectorAll('.btn-checkout').forEach(btn => {
        btn.addEventListener('click', function() {
            const memberName = this.closest('tr').querySelector('.member-details h4').textContent;
            const horaActual = new Date().toLocaleTimeString('es-ES', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });

            if (confirm(`¿Registrar salida para ${memberName} a las ${horaActual}?`)) {
                alert(`Salida registrada para ${memberName} a las ${horaActual}`);
                // Aquí iría la lógica para actualizar la base de datos
                location.reload();
            }
        });
    });

    document.querySelectorAll('.btn-view').forEach(btn => {
        btn.addEventListener('click', function() {
            const memberName = this.closest('tr').querySelector('.member-details h4').textContent;
            alert(`Viendo historial de asistencia de: ${memberName}`);
            // window.location.href = `historial_asistencia.html?member=${encodeURIComponent(memberName)}`;
        });
    });

    // Simular datos en tiempo real
    function simularCambiosTiempoReal() {
        setInterval(() => {
            const sociosActivos = document.getElementById('sociosActivos');
            const valorActual = parseInt(sociosActivos.textContent);

            // Simular cambios aleatorios en el número de socios activos
            const cambio = Math.random() > 0.7 ? (Math.random() > 0.5 ? 1 : -1) : 0;
            const nuevoValor = Math.max(0, valorActual + cambio);

            if (nuevoValor !== valorActual) {
                sociosActivos.textContent = nuevoValor;
                sociosActivos.style.transform = 'scale(1.1)';
                setTimeout(() => {
                    sociosActivos.style.transform = 'scale(1)';
                }, 300);
            }
        }, 5000); // Actualizar cada 5 segundos
    }

    // Iniciar simulación de cambios en tiempo real
    simularCambiosTiempoReal();
</script>
<?php require_once "templates/footer.php"; ?>