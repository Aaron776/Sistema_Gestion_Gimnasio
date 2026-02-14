<?php
require_once "autorizacion/auth.php"; // valida login y arranca sesión

// Generar token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Verificar que tenga rol de admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: acceso_denegado.php"); // si no lo mandamos al acceso denegado
    exit();
}

require_once "templates/header.php";
include_once "conexion/bd.php";

// Paginación
$registros_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Contar total de usuarios
$sqlCount = $conexion->prepare("SELECT COUNT(*) FROM usuarios WHERE rol != 'socio'");
$sqlCount->execute();
$total_usuarios = $sqlCount->fetchColumn();
$total_paginas = max(1, ceil($total_usuarios / $registros_por_pagina));

if ($pagina_actual > $total_paginas) {
    $pagina_actual = $total_paginas;
    $offset = ($pagina_actual - 1) * $registros_por_pagina;
}

// Obtener usuarios de la base de datos
$sql = $conexion->prepare("SELECT id as id_usuario,nombre,apellido,email,telefono,rol,fecha_registro FROM usuarios WHERE rol != 'socio' ORDER BY id DESC LIMIT :limit OFFSET :offset");
$sql->bindValue(':limit', $registros_por_pagina, PDO::PARAM_INT);
$sql->bindValue(':offset', $offset, PDO::PARAM_INT);
$sql->execute();
$usuarios = $sql->fetchAll(PDO::FETCH_OBJ);
?>

<style>
    /* === Estilos específicos para la gestión de usuarios === */
    .users-management {
        background-color: var(--card);
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        padding: 20px;
        margin-bottom: 20px;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #dee2e6;
    }

    .page-header h1 {
        font-size: 1.8rem;
        color: var(--dark);
        margin-bottom: 0;
        font-family: var(--font-main);
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

    .btn-sm {
        padding: 6px 12px;
        font-size: 0.8rem;
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

    .table-container {
        overflow-x: auto;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
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
        position: sticky;
        top: 0;
    }

    .table tr:hover {
        background-color: #f8f9fa;
    }

    .role-badge {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        background: #e9ecef;
        color: #495057;
    }

    .role-admin {
        background: #d1ecf1;
        color: #0c5460;
    }

    .role-user {
        background: #d4edda;
        color: #155724;
    }

    .role-trainer {
        background: #fff3cd;
        color: #856404;
    }

    .alert {
        padding: 10px 15px;
        margin-bottom: 15px;
        border-radius: 3px;
        font-size: 14px;
    }

    .alert-danger {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .action-buttons {
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
    }

    .search-filter {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .search-box {
        flex: 1;
        min-width: 250px;
    }

    .search-input,
    .filter-select {
        width: 100%;
        padding: 10px 15px;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        font-size: 0.9rem;
    }

    .no-data {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
    }

    .no-data i {
        font-size: 4rem;
        margin-bottom: 15px;
        display: block;
        color: #dee2e6;
    }

    .no-data h3 {
        font-size: 1.3rem;
        margin-bottom: 10px;
        color: #495057;
        font-family: var(--font-main);
    }

    .no-data p {
        font-size: 0.9rem;
        max-width: 400px;
        margin: 0 auto;
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
        .page-header {
            flex-direction: column;
            gap: 15px;
            align-items: flex-start;
        }

        .search-filter {
            flex-direction: column;
        }

        .search-box,
        .filter-select {
            min-width: 100%;
        }

        .action-buttons {
            flex-direction: column;
        }

        .table {
            font-size: 0.8rem;
        }

        .table th,
        .table td {
            padding: 8px 10px;
        }
    }
</style>

<!-- === Contenido principal === -->
<div class="content">
    <div class="users-management">
        <div class="page-header">
            <h1>Gestión de Usuarios</h1>
            <a href="agregar_usuario.php" type="button" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Nuevo Usuario
            </a>
        </div>

        <!-- Buscador y Filtros -->
        <div class="search-filter">
            <div class="search-box">
                <input type="text" class="search-input" placeholder="Buscar usuarios..." id="searchInput">
            </div>
            <select class="filter-select" id="roleFilter">
                <option value="">Todos los roles</option>
                <option value="admin">Administrador</option>
                <option value="user">Socios</option>
                <option value="trainer">Entrenador</option>
            </select>
        </div>

        <!-- Tabla de Usuarios -->
        <div class="table-container">
            <table class="table" id="usersTable">
                <?php if (isset($_SESSION['exito'])) : ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?= $_SESSION['exito']; ?>
                    </div>
                    <?php unset($_SESSION['exito']); ?>
                <?php endif; ?>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Teléfono</th>
                        <th>Fecha Registro</th>
                        <th>Acciones</th>
                    </tr>
</thead>
                <tbody>
                    <?php if (empty($usuarios)): ?>
                        <tr>
                            <td colspan="7">
                                <div class="no-data">
                                    <i class="fas fa-users"></i>
                                    <h3>No hay usuarios registrados</h3>
                                    <p>Aún no se han registrado usuarios en el sistema. Puedes agregar uno nuevo haciendo clic en el botón "Nuevo Usuario".</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($usuarios as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item->id_usuario); ?></td>
                                <td><?= htmlspecialchars($item->nombre . ' ' . $item->apellido); ?></td>
                                <td><?= htmlspecialchars($item->email); ?></td>
                                <td>
                                    <span class="role-badge 
                                        <?= $item->rol === 'admin' ? 'role-admin' : ($item->rol === 'trainer' ? 'role-trainer' : 'role-user'); ?>">
                                        <?= htmlspecialchars(ucfirst($item->rol)); ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($item->telefono); ?></td>
                                <td><?= htmlspecialchars(date('d/m/Y', strtotime($item->fecha_registro))); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="editar_usuario.php?id_usuario=<?= htmlspecialchars($item->id_usuario); ?>" type="button" class="btn btn-sm btn-edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <?php if($item->id_usuario!=$_SESSION['id_usuario']){?>
                                        <form action="controladores/eliminar_usuario.php" method="POST" class="formEliminar" style="display: inline-block;">
                                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                                            <input type="hidden" name="id_usuario" value="<?= htmlspecialchars($item->id_usuario); ?>">
                                            <button type="submit" class="btn btn-sm btn-delete formEliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        <?php } ?>
                                    </div>
                                </td>
                            </tr>
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
            Mostrando <?= $total_usuarios > 0 ? $offset + 1 : 0 ?>-<?= min($offset + $registros_por_pagina, $total_usuarios) ?> de <?= $total_usuarios ?> usuarios | Página <?= $pagina_actual ?> de <?= $total_paginas ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Buscar usuarios y filtrar por rol
        document.getElementById('searchInput').addEventListener('input', filterUsers);
        document.getElementById('roleFilter').addEventListener('change', filterUsers);
    });

    function filterUsers() {
        const search = document.getElementById('searchInput').value.toLowerCase();
        const roleFilter = document.getElementById('roleFilter').value;
        const rows = document.querySelectorAll('#usersTable tbody tr');

        rows.forEach(row => {
            const name = row.cells[1].textContent.toLowerCase();
            const email = row.cells[2].textContent.toLowerCase();
            const role = row.cells[3].textContent.toLowerCase();
            const matchesSearch = name.includes(search) || email.includes(search);
            const matchesRole = !roleFilter || role.includes(roleFilter);
            row.style.display = matchesSearch && matchesRole ? '' : 'none';
        });
    }
</script>
<script>
    document.querySelectorAll('.formEliminar').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: '¿Está seguro que desea eliminar este usuario?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit(); // enviar el formulario si confirma
                }
            });
        });
    });
</script>
<?php
require_once 'templates/footer.php';
?>
