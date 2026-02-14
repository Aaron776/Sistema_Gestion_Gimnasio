<?php
require_once "autorizacion/auth.php"; // valida login y arranca sesión

// Generar token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Verificar que tenga rol de admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: acceso_denegado.php");
    exit();
}

require_once "templates/header.php";
include_once "conexion/bd.php";

// Paginación
$registros_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Contar total de clases
$sqlCount = $conexion->prepare("SELECT COUNT(*) FROM clases INNER JOIN usuarios ON clases.id_entrenador = usuarios.id WHERE usuarios.rol = 'entrenador'");
$sqlCount->execute();
$total_clases = $sqlCount->fetchColumn();
$total_paginas = max(1, ceil($total_clases / $registros_por_pagina));

if ($pagina_actual > $total_paginas) {
    $pagina_actual = $total_paginas;
    $offset = ($pagina_actual - 1) * $registros_por_pagina;
}

// Obtener clases de la base de datos 
$sql = $conexion->prepare("SELECT clases.id as id_clase,clases.nombre as nombre_clase,clases.descripcion as descripcion_clase,CONCAT(usuarios.nombre,' ',usuarios.apellido) as nombre_entrenador,horario,cupo FROM clases INNER JOIN usuarios ON clases.id_entrenador = usuarios.id WHERE usuarios.rol = 'entrenador' ORDER BY clases.id DESC LIMIT :limit OFFSET :offset");
$sql->bindValue(':limit', $registros_por_pagina, PDO::PARAM_INT);
$sql->bindValue(':offset', $offset, PDO::PARAM_INT);
$sql->execute();
$clases = $sql->fetchAll(PDO::FETCH_OBJ);

?>
<style>
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

    .page-header h1 {
        font-size: 1.8rem;
        color: var(--dark);
        margin-bottom: 0;
        font-family: var(--font-main);
    }

    .btn-edit {
        background-color: var(--info);
        color: white;
    }

    .btn-edit:hover {
        background-color: #138496;
    }

    .btn-delete {
        background-color: var(--secondary);
        color: white;
    }

    .btn-delete:hover {
        background-color: #c0392b;
    }

    .btn-view {
        background-color: var(--success);
        color: white;
    }

    .btn-view:hover {
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

    /* Tabla */
    .table-container {
        overflow-x: auto;
        border-radius: 8px;
        border: 1px solid #dee2e6;
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

    /* Badges y Estados */
    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-active {
        background: #d4edda;
        color: #155724;
    }

    .status-inactive {
        background: #f8d7da;
        color: #721c24;
    }

    .status-full {
        background: #fff3cd;
        color: #856404;
    }

    .type-badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
        background: #e9ecef;
        color: #495057;
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

    /* Sin datos */
    .no-data {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
    }

    .no-data i {
        font-size: 4rem;
        margin-bottom: 20px;
        display: block;
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

    /* Información de la clase */
    .class-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .class-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1rem;
    }

    .class-details h4 {
        margin: 0;
        font-weight: 600;
        color: var(--dark);
        font-size: 0.95rem;
    }

    .class-details p {
        margin: 2px 0 0 0;
        color: #6c757d;
        font-size: 0.85rem;
    }

    /* Modal */
    .modal {
        display: none;
        position: fixed;
        z-index: 1050;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
        background-color: white;
        margin: 5% auto;
        padding: 0;
        border-radius: 8px;
        width: 90%;
        max-width: 500px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        animation: modalShow 0.3s;
    }

    @keyframes modalShow {
        from {
            opacity: 0;
            transform: translateY(-50px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .modal-header {
        padding: 15px 20px;
        background: var(--primary);
        color: white;
        border-radius: 8px 8px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header h3 {
        margin: 0;
        font-family: var(--font-main);
    }

    .close {
        color: white;
        font-size: 1.5rem;
        font-weight: bold;
        cursor: pointer;
        background: none;
        border: none;
    }

    .close:hover {
        color: #ccc;
    }

    .modal-body {
        padding: 20px;
        text-align: center;
    }

    .modal-icon {
        font-size: 3rem;
        color: var(--secondary);
        margin-bottom: 15px;
    }

    .modal-footer {
        padding: 15px 20px;
        background: #f8f9fa;
        border-radius: 0 0 8px 8px;
        display: flex;
        justify-content: center;
        gap: 10px;
    }

    .btn-cancel {
        background: #6c757d;
        color: white;
    }

    .btn-cancel:hover {
        background: #5a6268;
    }

    /* Botón principal */
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
        .classes-container {
            padding: 20px;
        }

        .page-header {
            flex-direction: column;
            gap: 15px;
            align-items: flex-start;
        }

        .filters-section {
            flex-direction: column;
            align-items: stretch;
        }

        .search-box,
        .filter-select {
            min-width: 100%;
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
</style>

<div class="classes-container">
    <div class="page-header">
        <h1>Clases del Gimnasio</h1>
        <a href="agregar_clase.php" type="button" class="btn btn-primary" id="btnNuevaClase">
            <i class="fas fa-plus"></i> Nueva Clase
        </a>
    </div>

    <!-- Filtros y Búsqueda -->
    <div class="filters-section">
        <div class="search-box">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="search-input" placeholder="Buscar clases..." id="searchInput">
        </div>
        <select class="filter-select" id="typeFilter">
            <option value="">Todos los tipos</option>
            <option value="yoga">Yoga</option>
            <option value="cardio">Cardio</option>
            <option value="strength">Fuerza</option>
            <option value="dance">Baile</option>
        </select>
        <select class="filter-select" id="instructorFilter">
            <option value="">Todos los instructores</option>
            <option value="1">Ana Rodríguez</option>
            <option value="2">Carlos Méndez</option>
            <option value="3">María González</option>
        </select>
    </div>

    <!-- Tabla de Clases -->
    <div class="table-container">
        <table class="table" id="classesTable">
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

            <?php if (isset($_SESSION['exito'])) : ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?= $_SESSION['exito']; ?>
                </div>
                <?php unset($_SESSION['exito']); ?>
            <?php endif; ?>
            <thead>
                <tr>
                    <th>Clase</th>
                    <th>Instructor</th>
                    <th>Horario</th>
                    <th>Capacidad</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($clases)): ?>
                    <tr>
                        <td colspan="5">
                            <div class="no-data">
                                <i class="fas fa-chalkboard-teacher"></i>
                                <h3>No hay clases registradas</h3>
                                <p>Aún no se han registrado clases en el sistema. Puedes agregar una nueva haciendo clic en el botón "Nueva Clase".</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                <?php foreach ($clases as $item) : ?>
                    <tr>
                        <td>
                            <div class="class-info">
                                <?php if ($item->nombre_clase === 'Yoga' || $item->nombre_clase === 'Pilates' || $item->nombre_clase === 'Stretching'): ?>
                                    <div class="class-icon" style="background: #17a2b8;">
                                        <i class="fas fa-spa"></i>
                                    </div>
                                <?php elseif ($item->nombre_clase === 'Cardio' || $item->nombre_clase === 'CrossFit' || $item->nombre_clase === 'Spinning' || $item->nombre_clase === 'HIIT' || $item->nombre_clase === 'Kickboxing'): ?>
                                    <div class="class-icon" style="background: #dc3545;">
                                        <i class="fas fa-running"></i>
                                    </div>
                                <?php elseif ($item->nombre_clase === 'Aeróbicos' || $item->nombre_clase === 'Calistenia' || $item->nombre_clase === 'Body Pump'): ?>
                                    <div class="class-icon" style="background: #ffc107;">
                                        <i class="fas fa-dumbbell"></i>
                                    </div>
                                <?php elseif ($item->nombre_clase === 'Zumba' || $item->nombre_clase === 'Funcional'): ?>
                                    <div class="class-icon" style="background: #6610f2;">
                                        <i class="fas fa-dance"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="class-details">
                                    <h4><?php echo htmlspecialchars($item->nombre_clase); ?></h4>
                                </div>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($item->nombre_entrenador); ?></td>
                        <td>
                            <div>Lun, Mie, Vie,Dom</div>
                            <div style="color: #6c757d; font-size: 0.85rem;"><?php echo htmlspecialchars($item->horario); ?></div>
                        </td>
                        <td>
                            <div><?php echo htmlspecialchars($item->cupo); ?></div>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <form action="editar_clase.php" method="POST" style="display: inline-block;">
                                    <input type="hidden" name="id_clase" value="<?= htmlspecialchars($item->id_clase); ?>">
                                    <button type="submit" class="btn btn-sm btn-edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </form>
                                <form action="controladores/eliminar_clase.php" method="POST" class="formEliminar" style="display: inline-block;">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="id_clase" value="<?= htmlspecialchars($item->id_clase); ?>">
                                    <button type="submit" class="btn btn-sm btn-delete formEliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    <?php endforeach; ?>
                <?php endif; ?>
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
        Mostrando <?= $total_clases > 0 ? $offset + 1 : 0 ?>-<?= min($offset + $registros_por_pagina, $total_clases) ?> de <?= $total_clases ?> clases | Página <?= $pagina_actual ?> de <?= $total_paginas ?>
    </div>

    <script>
        // Inicialización
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle sidebar
            document.querySelector('.toggle-sidebar').addEventListener('click', function() {
                document.querySelector('.sidebar').classList.toggle('active');
                document.querySelector('.main-content').classList.toggle('active');
            });

            // Filtros y búsqueda
            document.getElementById('searchInput').addEventListener('input', filtrarClases);
            document.getElementById('typeFilter').addEventListener('change', filtrarClases);
            document.getElementById('statusFilter').addEventListener('change', filtrarClases);
            document.getElementById('instructorFilter').addEventListener('change', filtrarClases);
        });

        // Filtrar clases
        function filtrarClases() {
            const search = document.getElementById('searchInput').value.toLowerCase();
            const typeFilter = document.getElementById('typeFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;
            const instructorFilter = document.getElementById('instructorFilter').value;
            const rows = document.querySelectorAll('#classesTable tbody tr');

            rows.forEach(row => {
                const className = row.cells[0].textContent.toLowerCase();
                const instructor = row.cells[1].textContent.toLowerCase();
                // Note: The original code for type and status filter was using cells[5] and cells[6] which
                // are out of bounds for the current table structure.
                // Assuming type and status might be inferred from class name or not used in current filtering.
                // For now, I'll comment them out to avoid errors. If these filters are needed,
                // the actual columns for type and status need to be identified or added to the table.
                // const type = row.cells[5].textContent.toLowerCase();
                // const status = row.cells[6].textContent.toLowerCase();

                const matchesSearch = className.includes(search) || instructor.includes(search);
                const matchesType = true; // Placeholder, adjust if type column is found
                const matchesStatus = true; // Placeholder, adjust if status column is found
                const matchesInstructor = !instructorFilter || instructor.includes(instructorFilter.toLowerCase());

                row.style.display = matchesSearch && matchesType && matchesStatus && matchesInstructor ? '' : 'none';
            });
        }
    </script>
    <?php require_once "templates/footer.php"; ?>