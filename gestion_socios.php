<?php
require_once "autorizacion/auth.php"; // valida login y arranca sesión

// Verificar que tenga rol de admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: acceso_denegado.php"); // si no lo mandamos al acceso denegado
    exit();
}

// Generar token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once "templates/header.php";
include_once "conexion/bd.php";

// Paginación
$registros_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Contar total de socios
$sqlCount = $conexion->prepare("SELECT COUNT(*) FROM usuarios WHERE rol='socio'");
$sqlCount->execute();
$total_socios = $sqlCount->fetchColumn();
$total_paginas = max(1, ceil($total_socios / $registros_por_pagina));

if ($pagina_actual > $total_paginas) {
    $pagina_actual = $total_paginas;
    $offset = ($pagina_actual - 1) * $registros_por_pagina;
}

// Obtener la lista de socios desde la base de datos (incluye socios sin membresía)
$sql = $conexion->prepare("SELECT usuarios.id as id_socio,CONCAT(usuarios.nombre,' ',usuarios.apellido) as nombre_socio,usuarios.email as email,usuarios.telefono as telefono,membresias.nombre as membresia FROM usuarios LEFT JOIN membresia_usuario ON membresia_usuario.id_usuario = usuarios.id LEFT JOIN membresias ON membresia_usuario.id_membresia = membresias.id WHERE usuarios.rol='socio' ORDER BY usuarios.id DESC LIMIT :limit OFFSET :offset");
$sql->bindValue(':limit', $registros_por_pagina, PDO::PARAM_INT);
$sql->bindValue(':offset', $offset, PDO::PARAM_INT);
$sql->execute();
$socios = $sql->fetchAll(PDO::FETCH_OBJ);
?>
<style>
    .members-container {
        background-color: var(--card);
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        padding: 25px;
        margin-bottom: 20px;
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

    .btn-info {
        background-color: var(--info);
        color: white;
    }

    .btn-info:hover {
        background-color: #138496;
    }

    .btn-danger {
        background-color: var(--secondary);
        color: white;
    }

    .btn-danger:hover {
        background-color: #c0392b;
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

    /* Badges de Membresía */
    .membership-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .membership-premium {
        background: linear-gradient(135deg, #ffd700, #ffed4e);
        color: #856404;
    }

    .membership-anual {
        background: linear-gradient(135deg, #6f42c1, #8e63d2);
        color: white;
    }

    .membership-mensual {
        background: linear-gradient(135deg, #20c997, #3dd4ad);
        color: white;
    }

    .membership-trimestral {
        background: linear-gradient(135deg, #fd7e14, #fd9843);
        color: white;
    }

    .membership-none {
        background: linear-gradient(135deg, #6c757d, #868e96);
        color: white;
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

    /* Acciones */
    .action-buttons {
        display: flex;
        gap: 8px;
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

    .btn-attendance {
        background-color: var(--info);
        color: white;
    }

    .btn-attendance:hover {
        background-color: #138496;
    }

    .btn-delete {
        background-color: var(--secondary);
        color: white;
    }

    .btn-delete:hover {
        background-color: #c0392b;
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

    /* Modal de Confirmación */
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
        margin: 10% auto;
        padding: 0;
        border-radius: 8px;
        width: 90%;
        max-width: 400px;
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
        font-size: 1.2rem;
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
        .members-container {
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

        .members-container {
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
<div class="members-container">
    <div class="page-header">
        <h1>Socios del Gimnasio</h1>
    </div>

    <!-- Filtros y Búsqueda -->
    <div class="filters-section">
        <div class="search-box">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="search-input" placeholder="Buscar socio..." id="searchInput">
        </div>
        <select class="filter-select" id="membershipFilter">
            <option value="">Todas las membresías</option>
            <option value="premium">Premium</option>
            <option value="anual">Anual</option>
            <option value="mensual">Mensual</option>
            <option value="trimestral">Trimestral</option>
            <option value="none">Sin Membresía</option>
        </select>
        <select class="filter-select" id="statusFilter">
            <option value="">Todos los estados</option>
            <option value="active">Activos</option>
            <option value="inactive">Inactivos</option>
        </select>
    </div>

    <!-- Tabla de Socios -->
    <div class="table-container">
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
        <table class="table" id="membersTable">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Membresía</th>
                    <th>Asistencias</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($socios)) { ?>
                    <tr>
                        <td colspan="6">
                            <div class="no-data">
                                <i class="fas fa-users-slash"></i>
                                <h3>No se encontraron socios</h3>
                                <p>No hay registros de socios para mostrar en este momento.</p>
                            </div>
                        </td>
                    </tr>
                <?php } else { ?>
                    <?php foreach ($socios as $item) { ?>
                        <tr>
                            <td>
                                <div class="member-info">
                                    <div class="member-avatar"><?php echo htmlspecialchars(substr($item->nombre_socio, 0, 2)); ?></div>
                                    <div class="member-details">
                                        <h4><?php echo htmlspecialchars($item->nombre_socio); ?></h4>
                                        <p>#SOC-<?php echo htmlspecialchars($item->id_socio); ?></p>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($item->email); ?></td>
                            <td><?php echo htmlspecialchars($item->telefono); ?></td>
                            <td>
                                <?php if ($item->membresia == 'Mensual') { ?>
                                    <span class="membership-badge membership-mensual">Mensual</span>
                                <?php } elseif ($item->membresia == 'Anual') { ?>
                                    <span class="membership-badge membership-anual">Anual</span>
                                <?php } elseif ($item->membresia == 'Trimestral') { ?>
                                    <span class="membership-badge membership-trimestral">Trimestral</span>
                                <?php } elseif ($item->membresia == 'Premium') { ?>
                                    <span class="membership-badge membership-premium">Premium</span>
                                <?php } else { ?>
                                    <span class="membership-badge membership-none">Sin Membresía</span>
                                <?php } ?>
                            </td>
                            <td>
                                <?php if (!is_null($item->membresia)) { ?>
                                    <a href="gestion_asistencias_socio.php?id_socio=<?php echo htmlspecialchars($item->id_socio); ?>" class="btn btn-info btn-sm">
                                        <i class="fas fa-calendar-check"></i> Ver Registro
                                    </a>
                                <?php } ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <form method="post" action="controladores/eliminar_socio.php">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <input type="hidden" name="id_socio" value="<?php echo htmlspecialchars($item->id_socio); ?>">
                                        <button type="submit" class="btn-icon btn-delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
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
        Mostrando <?= $total_socios > 0 ? $offset + 1 : 0 ?>-<?= min($offset + $registros_por_pagina, $total_socios) ?> de <?= $total_socios ?> socios | Página <?= $pagina_actual ?> de <?= $total_paginas ?>
    </div>

    <script>
        // Variables globales
        let socioActualId = null;

        // Inicialización
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle sidebar
            document.querySelector('.toggle-sidebar').addEventListener('click', function() {
                document.querySelector('.sidebar').classList.toggle('active');
                document.querySelector('.main-content').classList.toggle('active');
            });


            // Filtros y búsqueda
            document.getElementById('searchInput').addEventListener('input', filtrarSocios);
            document.getElementById('membershipFilter').addEventListener('change', filtrarSocios);
            document.getElementById('statusFilter').addEventListener('change', filtrarSocios);
        });

        // Filtrar socios
        function filtrarSocios() {
            const search = document.getElementById('searchInput').value.toLowerCase();
            const membershipFilter = document.getElementById('membershipFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;
            const rows = document.querySelectorAll('#membersTable tbody tr');

            rows.forEach(row => {
                const memberName = row.cells[0].textContent.toLowerCase();
                const membership = row.cells[3].textContent.toLowerCase();

                const matchesSearch = memberName.includes(search);
                let matchesMembership = true;

                if (membershipFilter) {
                    if (membershipFilter === 'none') {
                        matchesMembership = membership.includes('sin');
                    } else {
                        matchesMembership = membership.includes(membershipFilter);
                    }
                }
                // En una aplicación real, aquí se verificaría el estado del socio

                row.style.display = matchesSearch && matchesMembership ? '' : 'none';
            });
        }



        // Estadísticas rápidas (podrían mostrarse en el header)
        function calcularEstadisticas() {
            const totalSocios = document.querySelectorAll('#membersTable tbody tr').length;
            console.log(`Total de socios: ${totalSocios}`);

            // Aquí podrías agregar más cálculos según sea necesario
        }

        // Inicializar estadísticas
        calcularEstadisticas();
    </script>
    <?php require_once "templates/footer.php"; ?>