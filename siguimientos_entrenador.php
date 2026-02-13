<?php
require_once "autorizacion/auth.php"; // valida login y arranca sesión

// Verificar que tenga rol de entrenador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'entrenador') {
    header("Location: index.php"); // si no lo mandamos al login
    exit();
}
require_once "templates/header.php";
include_once "conexion/bd.php";

// Paginación
$registros_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Obtener cantidad de usuarios con rol de socio
$sqlCount = $conexion->prepare("SELECT COUNT(*) FROM membresia_usuario INNER JOIN usuarios ON membresia_usuario.id_usuario = usuarios.id WHERE usuarios.rol = 'socio'");
$sqlCount->execute();
$cantidad_socios = $sqlCount->fetchColumn();
$total_paginas = max(1, ceil($cantidad_socios / $registros_por_pagina));

if ($pagina_actual > $total_paginas) {
    $pagina_actual = $total_paginas;
    $offset = ($pagina_actual - 1) * $registros_por_pagina;
}

// Obtener todos los usuarios con rol de socio
$sql = $conexion->prepare("SELECT usuarios.id as id_socio, usuarios.email as email_socio, CONCAT(usuarios.nombre, ' ', usuarios.apellido) as nombre_socio,membresias.nombre as nombre_membresia FROM membresia_usuario INNER JOIN usuarios ON membresia_usuario.id_usuario = usuarios.id INNER JOIN membresias ON membresia_usuario.id_membresia = membresias.id WHERE usuarios.rol = 'socio' ORDER BY usuarios.id DESC LIMIT :limit OFFSET :offset");
$sql->bindValue(':limit', $registros_por_pagina, PDO::PARAM_INT);
$sql->bindValue(':offset', $offset, PDO::PARAM_INT);
$sql->execute();
$socios = $sql->fetchAll(PDO::FETCH_OBJ);
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

    /* Filtros y búsqueda */
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

    .counter-card.success {
        border-left-color: var(--success);
    }

    .counter-card.warning {
        border-left-color: var(--warning);
    }

    .counter-card.info {
        border-left-color: var(--info);
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

    .counter-icon.success {
        background-color: var(--success);
    }

    .counter-icon.warning {
        background-color: var(--warning);
    }

    .counter-icon.info {
        background-color: var(--info);
    }

    .counter-icon.primary {
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

    /* Tabla de socios */
    .socios-table-container {
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
        color: var(--accent);
        margin-right: 10px;
    }

    .add-socio-btn {
        background-color: var(--success);
        color: white;
        border: none;
        border-radius: 6px;
        padding: 10px 20px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        transition: all 0.3s;
        text-decoration: none;
    }

    .add-socio-btn:hover {
        background-color: #27ae60;
        transform: translateY(-2px);
    }

    .add-socio-btn i {
        margin-right: 8px;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1000px;
    }

    .table th,
    .table td {
        padding: 10px;
        text-align: left;
        border-bottom: 1px solid #dee2e6;
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
    .col-id {
        width: 80px;
        font-weight: 600;
        color: var(--accent);
    }

    .col-name {
        min-width: 200px;
    }

    .col-contact {
        min-width: 180px;
    }

    .col-status {
        width: 120px;
    }

    .col-actions {
        width: 180px;
        text-align: center;
    }

    /* Estado de socio */
    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-block;
        text-align: center;
        min-width: 80px;
    }

    .status-active {
        background: #d4edda;
        color: #155724;
    }

    .status-inactive {
        background: #f8d7da;
        color: #721c24;
    }

    .status-pending {
        background: #fff3cd;
        color: #856404;
    }

    /* Acciones */
    .action-buttons {
        display: flex;
        gap: 8px;
        justify-content: center;
    }

    .action-btn {
        padding: 8px 12px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 0.85rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
        border: none;
        cursor: pointer;
    }

    .action-btn i {
        margin-right: 5px;
    }

    .btn-sm {
        padding: 4px 8px;
        font-size: 0.70rem;
    }

    .btn-progress {
        background-color: var(--accent);
        color: white;
    }

    .btn-progress:hover {
        background-color: #2980b9;
        transform: translateY(-2px);
    }

    .btn-view {
        background-color: var(--success);
        color: white;
    }

    .btn-view:hover {
        background-color: #27ae60;
        transform: translateY(-2px);
    }

    .btn-edit {
        background-color: var(--warning);
        color: white;
    }

    .btn-edit:hover {
        background-color: #e67e22;
        transform: translateY(-2px);
    }

    /* Avatar de socio */
    .socio-avatar {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .avatar-circle {
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
    }

    .socio-info h4 {
        font-weight: 600;
        margin-bottom: 3px;
        color: var(--dark);
    }

    .socio-info p {
        font-size: 0.85rem;
        color: #6c757d;
        margin: 0;
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

        .add-socio-btn {
            width: 100%;
            justify-content: center;
        }

        .action-buttons {
            flex-direction: column;
            gap: 5px;
        }

        .action-btn {
            width: 100%;
            justify-content: center;
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
    }
</style>
<div class="page-header">
    <h1>Gestión de Socios</h1>
</div>

<!-- Filtros y búsqueda -->
<div class="filters-bar">
    <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="search-input" placeholder="Buscar socio por nombre, email o ID...">
    </div>
    <div class="filters-group">
        <select class="filter-select" id="status-filter">
            <option value="">Todos los estados</option>
            <option value="active">Activos</option>
            <option value="inactive">Inactivos</option>
            <option value="pending">Pendientes</option>
        </select>
        <select class="filter-select" id="membresia-filter">
            <option value="">Todos los tipos</option>
            <option value="premium">Premium</option>
            <option value="intermedia">Intermedia</option>
            <option value="basica">Básica</option>
        </select>
        <button class="action-btn btn-progress" id="export-btn">
            <i class="fas fa-file-export"></i> Exportar
        </button>
    </div>
</div>

<!-- Contadores -->
<div class="counters">
    <div class="counter-card">
        <div class="counter-icon primary">
            <i class="fas fa-users"></i>
        </div>
        <div class="counter-info">
            <h3 id="total-socios"><?php echo htmlspecialchars($cantidad_socios); ?></h3>
            <p>Total de Socios</p>
        </div>
    </div>
    <div class="counter-card success">
        <div class="counter-icon success">
            <i class="fas fa-user-check"></i>
        </div>
        <div class="counter-info">
            <h3 id="active-socios"><?php echo htmlspecialchars($cantidad_socios); ?></h3>
            <p>Socios Activos</p>
        </div>
    </div>
</div>

<!-- Tabla de socios -->
<div class="socios-table-container">
    <div class="table-header">
        <h2><i class="fas fa-list"></i> Lista de Socios</h2>
        <a href="registro_progreso.php" class="add-socio-btn">
            <i class="fas fa-plus"></i> Registrar Progreso
        </a>
    </div>

    <div class="table-responsive">
        <table class="table" id="socios-table">
            <thead>
                <tr>
                    <th class="col-id">ID</th>
                    <th class="col-name">Socio</th>
                    <th>Contacto</th>
                    <th class="col-contact">Membresía</th>
                    <th class="col-actions">Acciones</th>
                </tr>
            </thead>
            <tbody id="socios-body">
                <?php foreach ($socios as $item) { ?>
                <tr>
                    <td>SOC-<?php echo htmlspecialchars($item->id_socio); ?></td>
                    <td><?php echo htmlspecialchars($item->nombre_socio); ?></td>
                    <td><?php echo htmlspecialchars($item->email_socio); ?></td>
                    <td><?php echo htmlspecialchars($item->nombre_membresia); ?></td>
                    <td>
                        <a href="seguimiento_progreso_socio.php?id_socio=<?php echo htmlspecialchars($item->id_socio); ?>" class="action-btn btn-view btn-sm">
                            <i class="fas fa-eye"></i> Ver Progresos
                        </a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="pagination">
        <a href="?pagina=1" class="<?= $pagina_actual <= 1 ? 'disabled' : '' ?>"><i class="fas fa-angle-double-left"></i></a>
        <a href="?pagina=<?= $pagina_actual - 1 ?>" class="<?= $pagina_actual <= 1 ? 'disabled' : '' ?>"><i class="fas fa-angle-left"></i></a>

        <?php
        $rango = 2;
        $inicio = max(1, $pagina_actual - $rango);
        $fin = min($total_paginas, $pagina_actual + $rango);

        if ($inicio > 1) {
            echo '<a href="?pagina=1">1</a>';
            if ($inicio > 2) echo '<span class="disabled">...</span>';
        }

        for ($i = $inicio; $i <= $fin; $i++) {
            if ($i == $pagina_actual) {
                echo '<span class="active">' . $i . '</span>';
            } else {
                echo '<a href="?pagina=' . $i . '">' . $i . '</a>';
            }
        }

        if ($fin < $total_paginas) {
            if ($fin < $total_paginas - 1) echo '<span class="disabled">...</span>';
            echo '<a href="?pagina=' . $total_paginas . '">' . $total_paginas . '</a>';
        }
        ?>

        <a href="?pagina=<?= $pagina_actual + 1 ?>" class="<?= $pagina_actual >= $total_paginas ? 'disabled' : '' ?>"><i class="fas fa-angle-right"></i></a>
        <a href="?pagina=<?= $total_paginas ?>" class="<?= $pagina_actual >= $total_paginas ? 'disabled' : '' ?>"><i class="fas fa-angle-double-right"></i></a>
    </div>
    <div class="pagination-info">
        Mostrando <?= $cantidad_socios > 0 ? $offset + 1 : 0 ?>-<?= min($offset + $registros_por_pagina, $cantidad_socios) ?> de <?= $cantidad_socios ?> socios | Página <?= $pagina_actual ?> de <?= $total_paginas ?>
    </div>
</div>

<script>
    // Filtrar socios
    function filtrarSocios() {
        const searchTerm = document.getElementById('search-input').value.toLowerCase();
        const statusFilter = document.getElementById('status-filter').value;
        const membresiaFilter = document.getElementById('membresia-filter').value;

        filteredSocios = sociosData.filter(socio => {
            // Filtro de búsqueda
            const matchesSearch = searchTerm === '' ||
                socio.nombre.toLowerCase().includes(searchTerm) ||
                socio.email.toLowerCase().includes(searchTerm) ||
                socio.id.toLowerCase().includes(searchTerm);

            // Filtro de estado
            const matchesStatus = statusFilter === '' || socio.estado === statusFilter;

            // Filtro de membresía
            const matchesMembresia = membresiaFilter === '' ||
                socio.membresia.toLowerCase() === membresiaFilter.toLowerCase();

            return matchesSearch && matchesStatus && matchesMembresia;
        });

        currentPage = 1;
        actualizarContadores();
        renderSocios();
    }

    // Actualizar contadores
    function actualizarContadores() {
        const total = filteredSocios.length;
        const active = filteredSocios.filter(s => s.estado === 'active').length;
        const pending = filteredSocios.filter(s => s.estado === 'pending').length;

        // Contar progresos de esta semana
        const today = new Date();
        const weekAgo = new Date(today);
        weekAgo.setDate(weekAgo.getDate() - 7);

        const progressCount = filteredSocios.reduce((count, socio) => {
            if (socio.ultimoProgreso && socio.ultimoProgreso !== 'Nunca') {
                const lastProgress = new Date(socio.ultimoProgreso);
                if (lastProgress >= weekAgo) {
                    return count + 1;
                }
            }
            return count;
        }, 0);

        document.getElementById('total-socios').textContent = total;
        document.getElementById('active-socios').textContent = active;
        document.getElementById('pending-socios').textContent = pending;
        document.getElementById('progress-count').textContent = progressCount;
    }


</script>
<?php require_once 'templates/footer.php'; ?>