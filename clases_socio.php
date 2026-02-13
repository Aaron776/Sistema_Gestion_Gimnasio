<?php
require_once "autorizacion/auth.php"; // valida login y arranca sesión

// Generar token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Verificar que tenga rol de socio
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'socio') {
    header("Location: index.php"); // si no lo mandamos al login
    exit();
}
require_once "templates/header.php";
include_once "conexion/bd.php";

$id_socio = $_SESSION['id_usuario']; // Obtenemos el id del socio que esta logueado

if (!isset($id_socio) || $id_socio === null || !is_numeric($id_socio)) {
    header("Location: dash_cliente.php");
    exit();
}

// Obtener clases de la base de datos
$sql = $conexion->prepare("SELECT id as id_clase, nombre FROM clases");
$sql->execute();
$clases = $sql->fetchAll(PDO::FETCH_OBJ);


// Obtener listado de clases del socio
$sql = $conexion->prepare("SELECT * FROM inscripciones WHERE id_socio = :id_socio");
$sql->bindParam(':id_socio', $id_socio, PDO::PARAM_INT);
$sql->execute();
$clases_socio = $sql->fetchAll(PDO::FETCH_OBJ);

// Paginación
$registros_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

// Contar total de clases inscritas
$sqlCount = $conexion->prepare("SELECT COUNT(*) FROM inscripciones WHERE id_socio = :id_socio AND estado='inscrito'");
$sqlCount->bindParam(':id_socio', $id_socio, PDO::PARAM_INT);
$sqlCount->execute();
$total_clases_socio = $sqlCount->fetchColumn();
$total_paginas = max(1, ceil($total_clases_socio / $registros_por_pagina));

if ($pagina_actual > $total_paginas) {
    $pagina_actual = $total_paginas;
    $offset = ($pagina_actual - 1) * $registros_por_pagina;
}

// Obtener el listado de las clases inscritas de ese socio
$sql = $conexion->prepare("SELECT inscripciones.fecha_inscripcion as fecha_inscripcion,inscripciones.id as id_inscripcion, clases.nombre as nombre_clase, CONCAT(usuarios.nombre, ' ', usuarios.apellido) as nombre_entrenador FROM inscripciones INNER JOIN clases ON inscripciones.id_clase = clases.id INNER JOIN usuarios ON clases.id_entrenador=usuarios.id WHERE inscripciones.id_socio = :id_socio AND inscripciones.estado='inscrito' ORDER BY inscripciones.id DESC LIMIT :limit OFFSET :offset");
$sql->bindParam(':id_socio', $id_socio, PDO::PARAM_INT);
$sql->bindValue(':limit', $registros_por_pagina, PDO::PARAM_INT);
$sql->bindValue(':offset', $offset, PDO::PARAM_INT);
$sql->execute();
$clases_socio = $sql->fetchAll(PDO::FETCH_OBJ);

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

    /* Contenido */
    .content {
        padding: 20px;
    }

    .page-header {
        margin-bottom: 30px;
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

    /* Contenedor principal */
    .main-container {
        max-width: 1000px;
        margin: 0 auto;
    }

    /* Formulario */
    .form-card {
        background-color: var(--card);
        border-radius: 12px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        padding: 30px;
        margin-bottom: 30px;
        animation: fadeIn 0.6s ease-out;
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

    .form-header {
        text-align: center;
        margin-bottom: 30px;
    }

    .form-icon {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--success), #27ae60);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        color: white;
        font-size: 2rem;
    }

    .form-header h2 {
        font-size: 1.8rem;
        color: var(--dark);
        margin-bottom: 10px;
        font-family: var(--font-main);
    }

    .form-header p {
        color: #6c757d;
        font-size: 1rem;
        line-height: 1.6;
        max-width: 600px;
        margin: 0 auto;
    }

    /* Grupo de formulario */
    .form-group {
        margin-bottom: 25px;
    }

    .form-group label {
        display: block;
        margin-bottom: 12px;
        font-weight: 600;
        color: var(--dark);
        font-size: 1.1rem;
        font-family: var(--font-main);
    }

    .form-group label span {
        color: var(--danger);
    }

    /* Estilos para el select */
    .select-wrapper {
        position: relative;
    }

    .select-wrapper select {
        width: 100%;
        padding: 15px 20px;
        font-size: 1rem;
        font-family: var(--font-secondary);
        border: 2px solid #e9ecef;
        border-radius: 10px;
        background-color: white;
        appearance: none;
        cursor: pointer;
        transition: all 0.3s;
    }

    .select-wrapper select:focus {
        border-color: var(--accent);
        outline: none;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    }

    .select-wrapper i {
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--primary);
        pointer-events: none;
    }

    /* Botones */
    .form-actions {
        display: flex;
        justify-content: center;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }

    .btn {
        padding: 14px 40px;
        border-radius: 8px;
        border: none;
        font-weight: 600;
        font-family: var(--font-secondary);
        cursor: pointer;
        transition: all 0.3s;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 200px;
    }

    .btn i {
        margin-right: 10px;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--success), #27ae60);
        color: white;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #27ae60, var(--success));
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(46, 204, 113, 0.3);
    }

    .btn-primary:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none !important;
        box-shadow: none !important;
    }

    .btn-danger {
        background: linear-gradient(135deg, var(--danger), #c0392b);
        color: white;
    }

    .btn-danger:hover {
        background: linear-gradient(135deg, #c0392b, var(--danger));
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
    }

    .btn-danger:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none !important;
        box-shadow: none !important;
    }

    /* Alertas */
    .alert {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        font-weight: 600;
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border-left: 4px solid var(--success);
    }

    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border-left: 4px solid var(--danger);
    }

    .alert i {
        margin-right: 10px;
        font-size: 1.2rem;
    }

    /* Tabla de clases registradas */
    .classes-table-container {
        background-color: var(--card);
        border-radius: 12px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        padding: 30px;
        overflow-x: auto;
    }

    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
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

    .table {
        width: 100%;
        border-collapse: collapse;
        min-width: 600px;
    }

    .table th,
    .table td {
        padding: 15px;
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

    .table tr:last-child td {
        border-bottom: none;
    }

    /* Columnas específicas */
    .col-clase {
        width: 250px;
    }

    .col-entrenador {
        width: 200px;
    }

    .col-fecha {
        width: 150px;
    }

    /* Estilos para las filas */
    .clase-info {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .clase-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        color: white;
        flex-shrink: 0;
    }

    .clase-icon.yoga {
        background-color: #9b59b6;
    }

    .clase-icon.crossfit {
        background-color: var(--secondary);
    }

    .clase-icon.spinning {
        background-color: var(--accent);
    }

    .clase-icon.funcional {
        background-color: var(--warning);
    }

    .clase-icon.zumba {
        background-color: #e74c3c;
    }

    .clase-details h4 {
        font-weight: 600;
        margin-bottom: 5px;
        color: var(--dark);
    }

    .clase-details p {
        font-size: 0.85rem;
        color: #6c757d;
        margin: 0;
    }

    .entrenador-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .entrenador-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: var(--accent);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 0.9rem;
        flex-shrink: 0;
    }

    .entrenador-name {
        font-weight: 600;
        color: var(--dark);
    }

    /* Sin clases registradas */
    .no-classes {
        text-align: center;
        padding: 40px 20px;
        color: #6c757d;
    }

    .no-classes i {
        font-size: 3rem;
        margin-bottom: 15px;
        color: #dee2e6;
    }

    .no-classes h3 {
        font-size: 1.3rem;
        margin-bottom: 10px;
        color: var(--dark);
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

    /* Footer */
    .footer {
        margin-top: auto;
        background-color: var(--header);
        color: #c2c7d0;
        text-align: center;
        padding: 15px 20px;
        font-size: 0.85rem;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .main-container {
            padding: 0 15px;
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

        .form-card,
        .classes-table-container {
            padding: 25px 20px;
        }

        .form-header h2 {
            font-size: 1.6rem;
        }

        .table-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .clase-info {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }

        .entrenador-info {
            flex-direction: column;
            align-items: flex-start;
            gap: 5px;
        }
    }

    @media (max-width: 576px) {
        .form-header h2 {
            font-size: 1.4rem;
        }

        .form-icon {
            width: 60px;
            height: 60px;
            font-size: 1.8rem;
        }

        .btn {
            width: 100%;
            padding: 12px 20px;
            font-size: 1rem;
        }

        .table-header h2 {
            font-size: 1.3rem;
        }

        .clase-icon {
            width: 40px;
            height: 40px;
            font-size: 1.1rem;
        }
    }
</style>
<div class="page-header">
    <h1>Registro en Clases</h1>

</div>

<div class="main-container">
    <!-- Formulario para registrar en clase -->
    <div class="form-card">
        <div class="form-header">
            <div class="form-icon">
                <i class="fas fa-calendar-plus"></i>
            </div>
            <h2>Registrarse en Nueva Clase</h2>
            <p>Selecciona una clase disponible para registrarte. Puedes ver tus clases registradas en la tabla inferior.</p>
        </div>

        <form id="register-class-form" method="POST" action="controladores/registro_clases_socio.php">
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
            <input type="hidden" id="id_socio" name="id_socio" value="<?php echo $id_socio; ?>">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="form-group">
                <label for="clase">Seleccionar Clase <span>*</span></label>
                <div class="select-wrapper">
                    <select id="clase" name="clase" required>
                        <option value="">-- Selecciona una clase --</option>
                        <?php foreach ($clases as $item) { ?>
                            <option value="<?php echo $item->id_clase; ?>"><?php echo $item->nombre; ?></option>
                        <?php } ?>
                    </select>
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary" id="submit-btn">
                    <i class="fas fa-calendar-check"></i> Registrar en Clase
                </button>
            </div>
        </form>
    </div>

    <!-- Tabla de clases registradas -->
    <div class="classes-table-container">
        <div class="table-header">
            <h2><i class="fas fa-list-check"></i> Mis Clases Registradas</h2>
        </div>
        <div class="table-responsive">
            <?php if (empty($clases_socio)) { ?>
                <div class="no-classes">
                    <i class="fas fa-calendar-times"></i>
                    <h3>No tienes clases registradas</h3>
                </div>
            <?php } else { ?>
            <table class="table" id="classes-table">
                <thead>
                    <tr>
                        <th class="col-clase">Nombre de la Clase</th>
                        <th class="col-entrenador">Nombre del Entrenador</th>
                        <th class="col-fecha">Fecha de Registro</th>
                        <th class="col-acciones">Acciones</th>
                    </tr>
                </thead>
                <tbody id="classes-body">
                    <?php foreach ($clases_socio as $item) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item->nombre_clase); ?></td>
                            <td><?php echo htmlspecialchars($item->nombre_entrenador); ?></td>
                            <td><?php echo htmlspecialchars($item->fecha_inscripcion); ?></td>
                            <td>
                                <form method="POST" action="controladores/cancelar_inscripcion_clase_socio.php">
                                    <input type="hidden" name="id_inscripcion" value="<?php echo $item->id_inscripcion; ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-times"></i> Cancelar Inscripcion
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <?php } ?>
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
            Mostrando <?= $total_clases_socio > 0 ? $offset + 1 : 0 ?>-<?= min($offset + $registros_por_pagina, $total_clases_socio) ?> de <?= $total_clases_socio ?> clases | Página <?= $pagina_actual ?> de <?= $total_paginas ?>
        </div>
    </div>
</div>
</div>
</div>

<?php require_once "templates/footer.php"; ?>