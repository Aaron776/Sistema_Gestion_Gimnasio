<?php
require_once "autorizacion/auth.php"; // valida login y arranca sesión

// Verificar que tenga rol de socio
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'socio') {
    header("Location: index.php"); // si no lo mandamos al login
    exit();
}
require_once "templates/header.php";
include_once "conexion/bd.php";

$id_socio = $_SESSION['id_usuario']; // id del socio logueado

// Paginación
$registros_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Cantidad de rutinas asignadas a ese usuario
$sqlCount = $conexion->prepare("SELECT COUNT(*) FROM rutinas WHERE id_socio = :id_socio");
$sqlCount->bindParam(':id_socio', $id_socio, PDO::PARAM_INT);
$sqlCount->execute();
$cantidad_rutinas = $sqlCount->fetchColumn();
$total_paginas = max(1, ceil($cantidad_rutinas / $registros_por_pagina));

if ($pagina_actual > $total_paginas) {
    $pagina_actual = $total_paginas;
    $offset = ($pagina_actual - 1) * $registros_por_pagina;
}

// Obtener rutina del socio
$sql = $conexion->prepare("SELECT rutinas.descripcion as descripcion,rutinas.fecha_asignacion as fecha_asignacion,CONCAT(usuarios.nombre,' ',usuarios.apellido) as nombre_entrenador FROM rutinas INNER JOIN usuarios ON rutinas.id_entrenador=usuarios.id WHERE rutinas.id_socio = :id_socio ORDER BY rutinas.fecha_asignacion DESC LIMIT :limit OFFSET :offset");
$sql->bindParam(':id_socio', $id_socio, PDO::PARAM_INT);
$sql->bindValue(':limit', $registros_por_pagina, PDO::PARAM_INT);
$sql->bindValue(':offset', $offset, PDO::PARAM_INT);
$sql->execute();
$rutinas = $sql->fetchAll(PDO::FETCH_OBJ);
?>

<style>
    /* Estilos específicos de Rutinas que no están en header.php */

    .card {
        background-color: var(--card);
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        padding: 20px;
        margin-bottom: 20px;
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
    }

    .card-header h2 {
        font-size: 1.3rem;
        color: var(--dark);
        font-family: var(--font-main);
    }

    .table-container {
        overflow-x: auto;
    }

    .table {
        min-width: 800px;
    }

    /* Badges específicos */
    .badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
        display: inline-block;
    }

    .badge-primary {
        background-color: var(--primary);
        color: white;
    }

    .badge-secondary {
        background-color: var(--secondary);
        color: white;
    }

    /* Botones */
    .action-buttons {
        display: flex;
        gap: 8px;
    }

    .btn {
        padding: 6px 12px;
        border-radius: 4px;
        border: none;
        cursor: pointer;
        font-size: 0.85rem;
        font-weight: 600;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        text-decoration: none;
        color: white;
    }

    .btn-sm {
        padding: 4px 8px;
        font-size: 0.8rem;
    }

    .btn-primary {
        background-color: var(--primary);
    }

    .btn-primary:hover {
        background-color: #1a252f;
    }

    .btn-secondary {
        background-color: var(--secondary);
    }

    .btn-secondary:hover {
        background-color: #c0392b;
    }

    .btn-info {
        background-color: var(--info);
    }

    .btn-info:hover {
        background-color: #138496;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #6c757d;
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 15px;
        color: #dee2e6;
    }

    .empty-state h3 {
        font-size: 1.3rem;
        margin-bottom: 10px;
        color: var(--dark);
    }

    .empty-state p {
        font-size: 1rem;
        margin-bottom: 20px;
    }

    /* Filtros */
    .filters {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .filter-group {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .filter-label {
        font-weight: 600;
        color: var(--dark);
    }

    .filter-select {
        padding: 8px 12px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        background-color: white;
        font-family: var(--font-secondary);
    }

    /* Detalles de Rutina */
    .rutina-details {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 6px;
        margin-top: 10px;
        border-left: 4px solid var(--accent);
    }

    .rutina-details h4 {
        color: var(--dark);
        margin-bottom: 10px;
        font-family: var(--font-main);
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

    @media (max-width: 768px) {
        .filters {
            flex-direction: column;
        }

        .filter-group {
            width: 100%;
            justify-content: space-between;
        }
    }
</style>
<div class="content">
    <div class="page-header">
        <div>
            <h1>Mis Rutinas Asignadas</h1>
        </div>
        <a href="reportesPDF/reporte_rutinas_socio.php?id_socio=<?php echo $id_socio; ?>" target="_blank" type="button" class="btn btn-primary">
            <i class="fas fa-download"></i> Exportar Rutinas
        </a>
    </div>

    <!-- Filtros -->
    <div class="filters">
        <div class="filter-group">
            <span class="filter-label">Ordenar por:</span>
            <select class="filter-select">
                <option value="recent">Más recientes</option>
                <option value="oldest">Más antiguas</option>
                <option value="name">Nombre A-Z</option>
            </select>
        </div>
    </div>

    <!-- Tabla de Rutinas -->
    <div class="card">
        <div class="card-header">
            <h2>Lista de Rutinas Asignadas</h2>
            <span class="badge badge-primary"><?php echo htmlspecialchars($cantidad_rutinas); ?> rutinas asignadas</span>
        </div>

        <?php if ($cantidad_rutinas == 0) { ?>
            <div class="empty-state">
                <i class="fas fa-dumbbell"></i>
                <h3>No hay rutinas asignadas</h3>
                <p>No hay rutinas asignadas para este socio.</p>
            </div>
        <?php } else { ?>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Descripción</th>
                            <th>Entrenador</th>
                            <th>Fecha Asignación</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rutinas as $item) { ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($item->descripcion); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($item->nombre_entrenador); ?></td>
                                <td><?php echo htmlspecialchars($item->fecha_asignacion); ?></td>
                                <td>
                                    <span class="status-badge status-active">Activa</span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i> Ver
                                        </button>
                                        <button class="btn btn-primary btn-sm">
                                            <i class="fas fa-print"></i> Imprimir
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>

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
            Mostrando <?= $cantidad_rutinas > 0 ? $offset + 1 : 0 ?>-<?= min($offset + $registros_por_pagina, $cantidad_rutinas) ?> de <?= $cantidad_rutinas ?> rutinas | Página <?= $pagina_actual ?> de <?= $total_paginas ?>
        </div>
    </div>
</div>

<script>
    // Filtros
    const filterSelect = document.querySelector('.filter-select');
    const tableBody = document.querySelector('.table tbody');

    if (filterSelect && tableBody) {
        filterSelect.addEventListener('change', function() {
            const sortType = this.value;
            const rows = Array.from(tableBody.querySelectorAll('tr'));

            rows.sort((a, b) => {
                let aValue, bValue;

                switch (sortType) {
                    case 'name':
                        aValue = a.cells[0].innerText.trim().toLowerCase();
                        bValue = b.cells[0].innerText.trim().toLowerCase();
                        return aValue.localeCompare(bValue);

                    case 'recent':
                    case 'oldest':
                        // Asumiendo formato de fecha YYYY-MM-DD o DD/MM/YYYY
                        aValue = parseDate(a.cells[2].innerText.trim());
                        bValue = parseDate(b.cells[2].innerText.trim());
                        return sortType === 'recent' ? bValue - aValue : aValue - bValue;

                    default:
                        return 0;
                }
            });

            // Reordenar DOM
            rows.forEach(row => tableBody.appendChild(row));
        });
    }

    function parseDate(dateStr) {
        // Intenta parsar fechas
        const timestamp = Date.parse(dateStr);
        if (!isNaN(timestamp)) return timestamp;

        // Si falla, intentar parse manual si es DD/MM/YYYY
        const parts = dateStr.split(/[-/]/);
        if (parts.length === 3) {
            // Asumiendo primero es día o año dependiendo de longitud
            if (parts[0].length === 4) {
                // YYYY-MM-DD
                return new Date(parts[0], parts[1] - 1, parts[2]).getTime();
            } else {
                // DD/MM/YYYY
                return new Date(parts[2], parts[1] - 1, parts[0]).getTime();
            }
        }
        return 0;
    }
</script>
<?php
require_once "templates/footer.php";
?>