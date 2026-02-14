<?php
require_once "autorizacion/auth.php"; // valida login y arranca sesión

// Verificar que tenga rol de entrenador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'entrenador') {
    header("Location: acceso_denegado.php"); 
    exit();
}
require_once "templates/header.php";
include_once "conexion/bd.php";

$id_socio = $_GET['id_socio'];

// Validación estricta
if (empty($id_socio) || !is_numeric($id_socio) || !filter_var($id_socio, FILTER_VALIDATE_INT) || $id_socio <= 0) {
    $_SESSION['errores'] = ['ID de socio inválido'];
    header("Location: seguimientos_entrenador.php");
    exit();
}

// Obtener informacion del socio
$sql = $conexion->prepare("SELECT usuarios.id as id_socio, usuarios.email as email_socio, CONCAT(usuarios.nombre, ' ', usuarios.apellido) as nombre_socio,membresias.nombre as nombre_membresia FROM membresia_usuario INNER JOIN usuarios ON membresia_usuario.id_usuario = usuarios.id INNER JOIN membresias ON membresia_usuario.id_membresia = membresias.id WHERE membresia_usuario.id_usuario = :id_socio");
$sql->bindParam(":id_socio", $id_socio, PDO::PARAM_INT);
$sql->execute();
$socio = $sql->fetch(PDO::FETCH_OBJ);

// Obtener datos del seguimiento ordenados por fecha
$sql = $conexion->prepare("SELECT peso, grasa_corporal, masa_muscular, fecha_registro FROM progreso WHERE id_socio = :id_socio ORDER BY fecha_registro ASC");
$sql->bindParam(":id_socio", $id_socio, PDO::PARAM_INT);
$sql->execute();
$seguimientos = $sql->fetchAll(PDO::FETCH_OBJ);

// Obtener dato de del Peso Corporal de este socio de su ultima fecha de registro
$sql = $conexion->prepare("SELECT peso FROM progreso WHERE id_socio = :id_socio ORDER BY fecha_registro DESC LIMIT 1");
$sql->bindParam(":id_socio", $id_socio, PDO::PARAM_INT);
$sql->execute();
$peso = $sql->fetch(PDO::FETCH_OBJ);

// Obtener dato de la Grasa Corporal de este socio de su ultima fecha de registro
$sql = $conexion->prepare("SELECT grasa_corporal FROM progreso WHERE id_socio = :id_socio ORDER BY fecha_registro DESC LIMIT 1");
$sql->bindParam(":id_socio", $id_socio, PDO::PARAM_INT);
$sql->execute();
$grasa_corporal = $sql->fetch(PDO::FETCH_OBJ);

// Obtener dato de la Masa Muscular de este socio de su ultima fecha de registro
$sql = $conexion->prepare("SELECT masa_muscular FROM progreso WHERE id_socio = :id_socio ORDER BY fecha_registro DESC LIMIT 1");
$sql->bindParam(":id_socio", $id_socio, PDO::PARAM_INT);
$sql->execute();
$masa_muscular = $sql->fetch(PDO::FETCH_OBJ);

// Preparar datos para los gráficos
$fechas = [];
$pesos = [];
$grasas = [];
$musculos = [];

foreach ($seguimientos as $item) {
    $fechas[] = date('d/m/Y', strtotime($item->fecha_registro));
    $pesos[] = floatval($item->peso);
    $grasas[] = floatval($item->grasa_corporal);
    $musculos[] = floatval($item->masa_muscular);
}
?>
<!-- Incluir Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    /* Estilos específicos de esta página */

    /* Información del socio */
    .socio-header {
        background-color: var(--card);
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        padding: 25px;
        margin-bottom: 30px;
        display: flex;
        align-items: center;
        gap: 25px;
        flex-wrap: wrap;
    }

    .socio-avatar-large {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background-color: var(--accent);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        font-weight: bold;
        font-family: var(--font-main);
        flex-shrink: 0;
    }

    .socio-info {
        flex: 1;
        min-width: 300px;
    }

    .socio-info h2 {
        font-size: 1.8rem;
        color: var(--dark);
        margin-bottom: 10px;
        font-family: var(--font-main);
    }

    .socio-meta {
        display: flex;
        gap: 30px;
        flex-wrap: wrap;
        margin-top: 15px;
    }

    .meta-item {
        display: flex;
        flex-direction: column;
    }

    .meta-label {
        font-size: 0.9rem;
        color: #6c757d;
        margin-bottom: 5px;
    }

    .meta-value {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--dark);
    }

    /* Estadísticas rápidas */
    .quick-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background-color: var(--card);
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        border-left: 4px solid var(--accent);
    }

    .stat-card.weight {
        border-left-color: var(--weight);
    }

    .stat-card.fat {
        border-left-color: var(--fat);
    }

    .stat-card.muscle {
        border-left-color: var(--muscle);
    }

    .stat-icon {
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

    .stat-icon.weight {
        background-color: var(--weight);
    }

    .stat-icon.fat {
        background-color: var(--fat);
    }

    .stat-icon.muscle {
        background-color: var(--muscle);
    }

    .stat-info h3 {
        font-size: 1.8rem;
        margin-bottom: 5px;
        color: var(--dark);
        font-family: var(--font-main);
    }

    .stat-info p {
        color: #6c757d;
        margin: 0;
        font-weight: 600;
    }

    .stat-trend {
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        margin-top: 5px;
    }

    .trend-up {
        color: var(--success);
    }

    .trend-down {
        color: var(--danger);
    }

    /* Gráficos */
    .charts-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
        gap: 25px;
        margin-bottom: 30px;
    }

    @media (max-width: 1100px) {
        .charts-container {
            grid-template-columns: 1fr;
        }
    }

    .chart-card {
        background-color: var(--card);
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        padding: 25px;
    }

    .chart-card h3 {
        font-size: 1.3rem;
        margin-bottom: 20px;
        color: var(--dark);
        border-bottom: 1px solid #eee;
        padding-bottom: 15px;
        font-family: var(--font-main);
    }

    .chart-card h3 i {
        color: var(--accent);
        margin-right: 10px;
    }

    .chart-wrapper {
        height: 300px;
        position: relative;
    }

    /* Estilos para los gráficos */
    .chart-container {
        position: relative;
        height: 100%;
        width: 100%;
    }

    /* Tabla de progreso */
    .progress-table-container {
        background-color: var(--card);
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        padding: 25px;
        margin-bottom: 30px;
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

    .table-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .action-btn {
        padding: 10px 20px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        transition: all 0.3s;
        border: none;
        cursor: pointer;
    }

    .action-btn i {
        margin-right: 8px;
    }

    .btn-primary {
        background-color: var(--accent);
        color: white;
    }

    .btn-primary:hover {
        background-color: #2980b9;
        transform: translateY(-2px);
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
        background-color: #27ae60;
        transform: translateY(-2px);
    }

    /* Tabla */
    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table th,
    .table td {
        padding: 15px;
        text-align: center;
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
    .col-peso {
        width: 150px;
        font-weight: 600;
    }

    .col-grasa {
        width: 150px;
    }

    .col-musculo {
        width: 150px;
    }

    .col-fecha {
        width: 150px;
    }

    .col-tendencia {
        width: 120px;
    }

    /* Estilos de métricas */
    .metric-value {
        font-size: 1.1rem;
        font-weight: 600;
    }

    .peso-value {
        color: var(--weight);
    }

    .grasa-value {
        color: var(--fat);
    }

    .musculo-value {
        color: var(--muscle);
    }

    /* Tendencia en tabla */
    .tendencia-cell {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
    }

    .tendencia-up {
        color: var(--success);
    }

    .tendencia-down {
        color: var(--danger);
    }

    .tendencia-neutral {
        color: #6c757d;
    }

    /* Filtros */
    .filters-bar {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 15px 20px;
        margin-bottom: 20px;
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
        align-items: center;
    }

    .filter-group {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .filter-group label {
        font-weight: 600;
        color: var(--dark);
        font-size: 0.9rem;
    }

    .filter-select {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 0.9rem;
        background-color: white;
        min-width: 150px;
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

    /* Sin datos */
    .no-data {
        text-align: center;
        padding: 40px 20px;
        color: #6c757d;
    }

    .no-data i {
        font-size: 3rem;
        margin-bottom: 15px;
        color: #dee2e6;
    }

    .no-data h3 {
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

    /* Responsive */
    @media (max-width: 992px) {
        .charts-container {
            grid-template-columns: 1fr;
        }

        .socio-header {
            flex-direction: column;
            text-align: center;
        }

        .socio-meta {
            justify-content: center;
        }

        .table-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .table-actions {
            width: 100%;
            justify-content: center;
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

        .quick-stats {
            grid-template-columns: 1fr;
        }

        .table {
            display: block;
            overflow-x: auto;
        }

        .filters-bar {
            flex-direction: column;
            align-items: stretch;
        }

        .filter-group {
            width: 100%;
            justify-content: space-between;
        }
    }

    @media (max-width: 576px) {
        .socio-meta {
            flex-direction: column;
            gap: 15px;
        }

        .table-actions {
            flex-direction: column;
        }

        .action-btn {
            width: 100%;
            justify-content: center;
        }

        .pagination {
            flex-direction: column;
        }
    }
</style>
<div class="content">
    <div class="page-header">
        <h1>Historial de Progreso</h1>
    </div>

    <!-- Información del socio -->
    <div class="socio-header">
        <div class="socio-avatar-large">
            <?php echo htmlspecialchars(substr($socio->nombre_socio, 0, 2)); ?>
        </div>
        <div class="socio-info">
            <h2><?php echo htmlspecialchars($socio->nombre_socio); ?></h2>
            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 10px;">
                <span class="meta-value" style="background-color: var(--accent); color: white; padding: 5px 15px; border-radius: 20px; font-size: 0.9rem;">
                    ID: <?php echo htmlspecialchars($socio->id_socio); ?>
                </span>
                <span class="meta-value" style="background-color: var(--success); color: white; padding: 5px 15px; border-radius: 20px; font-size: 0.9rem;">
                    Activo
                </span>
            </div>
            <div class="socio-meta">
                <div class="meta-item">
                    <span class="meta-label">Membresía</span>
                    <span class="meta-value" style="color: var(--accent);"><?php echo htmlspecialchars($socio->nombre_membresia); ?></span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Contacto</span>
                    <span class="meta-value"><?php echo htmlspecialchars($socio->email_socio); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas rápidas -->
    <?php if($peso && $grasa_corporal && $masa_muscular): ?>
    <div class="quick-stats">
        <div class="stat-card weight">
            <div class="stat-icon weight">
                <i class="fas fa-weight"></i>
            </div>
            <div class="stat-info">
                <h3 id="current-peso"><?php echo htmlspecialchars($peso->peso); ?> kg</h3>
                <p>Peso Actual</p>
                <!-- 
                    GUÍA PARA PRINCIPIANTES - CÁLCULO DE TENDENCIA (PESO):
                    
                    1. VALIDACIÓN (count > 1):
                       Primero preguntamos: "¿Tenemos más de un dato registrado?".
                       Si solo hay uno, no podemos comparar nada, así que no hacemos nada.

                    2. MATEMÁTICA SIMPLE (Resta):
                       - end($pesos): Toma el ÚLTIMO peso que se registró (el actual).
                       - reset($pesos): Toma el PRIMER peso que se registró (el inicial).
                       - Restamos (Actual - Inicial) para saber la diferencia total.
                       
                    3. INTERPRETACIÓN DEL RESULTADO:
                       - Si da NEGATIVO (< 0): Significa que bajó de peso (Flecha abajo).
                       - Si da POSITIVO (> 0): Significa que subió de peso (Flecha arriba).
                       - Si es 0: Se mantuvo igual.
                -->
                <?php if (count($pesos) > 1): ?>
                    <?php
                    $tendenciaPeso = end($pesos) - reset($pesos);
                    if ($tendenciaPeso < 0): ?>
                        <div class="stat-trend trend-down">
                            <i class="fas fa-arrow-down"></i> <?php echo number_format(abs($tendenciaPeso), 1); ?> kg desde el inicio
                        </div>
                    <?php elseif ($tendenciaPeso > 0): ?>
                        <div class="stat-trend trend-up">
                            <i class="fas fa-arrow-up"></i> +<?php echo number_format($tendenciaPeso, 1); ?> kg desde el inicio
                        </div>
                    <?php else: ?>
                        <div class="stat-trend" style="color: #6c757d;">
                            <i class="fas fa-minus"></i> Sin cambios desde el inicio
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="stat-card fat">
            <div class="stat-icon fat">
                <i class="fas fa-percentage"></i>
            </div>
            <div class="stat-info">
                <h3 id="current-grasa"><?php echo htmlspecialchars($grasa_corporal->grasa_corporal); ?> %</h3>
                <p>Grasa Corporal Actual</p>
                <!-- 
                    GUÍA PARA PRINCIPIANTES - CÁLCULO DE TENDENCIA (GRASA CORPORAL):
                    
                    1. VALIDACIÓN (count > 1):
                       Verificamos si hay suficientes registros para comparar.

                    2. CÁLCULO DE DIFERENCIA:
                       - end($grasas): Último porcentaje registrado.
                       - reset($grasas): Primer porcentaje registrado.
                       - La resta nos dice cuánto cambió desde el día 1.
                       
                    3. VISUALIZACIÓN:
                       - Negativo (< 0): Bajó grasa (Bueno o Malo según objetivo).
                       - Positivo (> 0): Subió grasa.
                       - Cero: Sin cambios.
                -->
                <?php if (count($grasas) > 1): ?>
                    <?php
                    $tendenciaGrasa = end($grasas) - reset($grasas);
                    if ($tendenciaGrasa < 0): ?>
                        <div class="stat-trend trend-down">
                            <i class="fas fa-arrow-down"></i> <?php echo number_format(abs($tendenciaGrasa), 1); ?> % desde el inicio
                        </div>
                    <?php elseif ($tendenciaGrasa > 0): ?>
                        <div class="stat-trend trend-up">
                            <i class="fas fa-arrow-up"></i> +<?php echo number_format($tendenciaGrasa, 1); ?> % desde el inicio
                        </div>
                    <?php else: ?>
                        <div class="stat-trend" style="color: #6c757d;">
                            <i class="fas fa-minus"></i> Sin cambios desde el inicio
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="stat-card muscle">
            <div class="stat-icon muscle">
                <i class="fas fa-dumbbell"></i>
            </div>
            <div class="stat-info">
                <h3 id="current-musculo"><?php echo htmlspecialchars($masa_muscular->masa_muscular); ?> kg</h3>
                <p>Masa Muscular Actual</p>
                <!-- 
                    GUÍA PARA PRINCIPIANTES - CÁLCULO DE TENDENCIA (MASA MUSCULAR):
                    
                    1. EL TIEMPO IMPORTA:
                       Igual que los anteriores, necesitamos al menos una medición "Antes" y una "Después".

                    2. LA FÓRMULA:
                       Progreso = (Músculo Hoy) - (Músculo Inicio)
                       
                    3. RESULTADO VISUAL:
                       - Positivo (> 0): ¡Ganó músculo! (Generalmente bueno).
                       - Negativo (< 0): Perdió músculo.
                       - Muestra la flecha correspondiente y el valor con number_format().
                -->
                <?php if (count($musculos) > 1): ?>
                    <?php
                    $tendenciaMusculo = end($musculos) - reset($musculos);
                    if ($tendenciaMusculo > 0): ?>
                        <div class="stat-trend trend-up">
                            <i class="fas fa-arrow-up"></i> +<?php echo number_format($tendenciaMusculo, 1); ?> kg desde el inicio
                        </div>
                    <?php elseif ($tendenciaMusculo < 0): ?>
                        <div class="stat-trend trend-down">
                            <i class="fas fa-arrow-down"></i> <?php echo number_format(abs($tendenciaMusculo), 1); ?> kg desde el inicio
                        </div>
                    <?php else: ?>
                        <div class="stat-trend" style="color: #6c757d;">
                            <i class="fas fa-minus"></i> Sin cambios desde el inicio
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php else: ?>
        <div class="quick-stats">
            <div class="stat-card weight">
                <div class="stat-icon weight">
                    <i class="fas fa-weight"></i>
                </div>
                <div class="stat-value weight-value">No hay datos</div>
                <div class="stat-trend">
                    <i class="fas fa-arrow-down trend-down"></i>
                    <span class="trend-down">No hay datos</span>
                </div>
                <p style="color: #6c757d; margin-top: 10px; font-size: 0.9rem;">Meta: No hay datos</p>
            </div>
            <div class="stat-card fat">
                <div class="stat-icon fat">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="stat-value fat-value">No hay datos</div>
                <div class="stat-trend">
                    <i class="fas fa-arrow-down trend-down"></i>
                    <span class="trend-down">No hay datos</span>
                </div>
                <p style="color: #6c757d; margin-top: 10px; font-size: 0.9rem;">Meta: No hay datos</p>
            </div>
            <div class="stat-card muscle">
                <div class="stat-icon muscle">
                    <i class="fas fa-dumbbell"></i>
                </div>
                <div class="stat-value muscle-value">No hay datos</div>
                <div class="stat-trend">
                    <i class="fas fa-arrow-down trend-down"></i>
                    <span class="trend-down">No hay datos</span>
                </div>
                <p style="color: #6c757d; margin-top: 10px; font-size: 0.9rem;">Meta: No hay datos</p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Gráficos -->
    <div class="charts-container">
        <div class="chart-card">
            <h3><i class="fas fa-chart-line"></i> Evolución del Peso</h3>
            <div class="chart-wrapper">
                <canvas id="pesoChart"></canvas>
            </div>
            <?php if (empty($pesos)): ?>
                <div class="no-data">
                    <i class="fas fa-chart-line"></i>
                    <h3>No hay datos de peso registrados</h3>
                    <p>Comienza a registrar mediciones para ver la evolución</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="chart-card">
            <h3><i class="fas fa-chart-area"></i> Evolución de la Grasa Corporal</h3>
            <div class="chart-wrapper">
                <canvas id="grasaCorporalChart"></canvas>
            </div>
            <?php if (empty($grasas)): ?>
                <div class="no-data">
                    <i class="fas fa-chart-area"></i>
                    <h3>No hay datos de grasa corporal registrados</h3>
                    <p>Comienza a registrar mediciones para ver la evolución</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tabla de progreso -->
    <div class="progress-table-container">
        <div class="table-header">
            <h2><i class="fas fa-history"></i> Historial de Mediciones</h2>
            <div class="table-actions">
                <a href="reportesPDF/reporte_progreso_socio.php?id_socio=<?php echo $id_socio; ?>" type="button" class="action-btn btn-primary" id="export-btn">
                    <i class="fas fa-file-export"></i> Exportar
                </a>
                <button class="action-btn btn-secondary" id="print-btn">
                    <i class="fas fa-print"></i> Imprimir
                </button>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filters-bar">
            <div class="filter-group">
                <label for="time-filter">Período:</label>
                <select class="filter-select" id="time-filter">
                    <option value="all">Todo el historial</option>
                    <option value="month">Último mes</option>
                    <option value="3months">Últimos 3 meses</option>
                    <option value="6months">Últimos 6 meses</option>
                    <option value="year">Último año</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="sort-filter">Ordenar por:</label>
                <select class="filter-select" id="sort-filter">
                    <option value="date-desc">Fecha (más reciente)</option>
                    <option value="date-asc">Fecha (más antigua)</option>
                    <option value="peso-desc">Peso (mayor a menor)</option>
                    <option value="peso-asc">Peso (menor a mayor)</option>
                </select>
            </div>
        </div>

        <!-- Tabla -->
        <div class="table-responsive">
            <table class="table" id="progress-table">
                <thead>
                    <tr>
                        <th class="col-fecha">Fecha de Registro</th>
                        <th class="col-peso">Peso (kg)</th>
                        <th class="col-grasa">Grasa Corporal (%)</th>
                        <th class="col-musculo">Masa Muscular (kg)</th>
                        <th class="col-tendencia">Tendencia</th>
                    </tr>
                </thead>
                <tbody>
                    <!--     
                        1. VARIABLES DE MEMORIA ($prevPeso): 
                           Creamos estas variables vacías (null) antes de empezar. Servirán para "recordar" 
                           el valor de la fila anterior mientras recorremos la lista.

                        2. EL BUCLE (foreach): 
                           Recorre cada registro de la base de datos uno por uno.

                        3. LA LÓGICA ($prevPeso !== null):
                           - En la primera vuelta, $prevPeso está vacío, así que no se hace comparación.
                           - Desde la segunda vuelta, $prevPeso tiene el valor de la vuelta anterior.
                             Es ahí donde comparamos si el peso subió (>) o bajó (<) respecto al anterior.

                        4. ACTUALIZAR LA MEMORIA ($prevPeso = $item->peso):
                           ¡IMPORTANTE! Al final de cada vuelta, guardamos el peso actual dentro de $prevPeso.
                           hacemos esto para que en la SIGUIENTE vuelta, este valor sirva como referencia.
                    -->
                    <?php
                    $prevPeso = null;
                    $prevGrasa = null;
                    $prevMusculo = null;
                    foreach ($seguimientos as $index => $item):
                        // Calcular tendencias
                        $tendenciaPeso = '';
                        $tendenciaIconoPeso = '';
                        $tendenciaClasePeso = '';

                        if ($prevPeso !== null) {
                            if ($item->peso > $prevPeso) {
                                $tendenciaPeso = '↑ ' . number_format($item->peso - $prevPeso, 1);
                                $tendenciaIconoPeso = 'fas fa-arrow-up';
                                $tendenciaClasePeso = 'tendencia-up';
                            } elseif ($item->peso < $prevPeso) {
                                $tendenciaPeso = '↓ ' . number_format($prevPeso - $item->peso, 1);
                                $tendenciaIconoPeso = 'fas fa-arrow-down';
                                $tendenciaClasePeso = 'tendencia-down';
                            } else {
                                $tendenciaPeso = '=';
                                $tendenciaIconoPeso = 'fas fa-minus';
                                $tendenciaClasePeso = 'tendencia-neutral';
                            }
                        }

                        // Actualizar valores anteriores
                        $prevPeso = $item->peso;
                        $prevGrasa = $item->grasa_corporal;
                        $prevMusculo = $item->masa_muscular;
                    ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($item->fecha_registro)); ?></td>
                            <td class="peso-value"><?php echo $item->peso; ?> kg</td>
                            <td class="grasa-value"><?php echo $item->grasa_corporal; ?> %</td>
                            <td class="musculo-value"><?php echo $item->masa_muscular; ?> kg</td>
                            <td>
                                <?php if ($index > 0): ?>
                                    <span class="tendencia-cell <?php echo $tendenciaClasePeso; ?>">
                                        <i class="<?php echo $tendenciaIconoPeso; ?>"></i>
                                        <?php echo $tendenciaPeso; ?> kg
                                    </span>
                                <?php else: ?>
                                    <span class="tendencia-cell" style="color: #6c757d;">
                                        <i class="fas fa-minus"></i> Inicio
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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
</div>

<script>
    // Datos para los gráficos
    const fechas = <?php echo json_encode($fechas); ?>;
    const pesos = <?php echo json_encode($pesos); ?>;
    const grasas = <?php echo json_encode($grasas); ?>;
    const musculos = <?php echo json_encode($musculos); ?>;

    // Gráfico de Evolución del Peso
    const pesoCtx = document.getElementById('pesoChart');
    if (pesoCtx && pesos.length > 0) {
        const pesoChart = new Chart(pesoCtx, {
            type: 'line',
            data: {
                labels: fechas,
                datasets: [{
                    label: 'Peso (kg)',
                    data: pesos,
                    backgroundColor: 'rgba(41, 128, 185, 0.1)',
                    borderColor: 'rgba(41, 128, 185, 1)',
                    borderWidth: 3,
                    tension: 0.3,
                    fill: true,
                    pointBackgroundColor: 'rgba(41, 128, 185, 1)',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: false,
                        grid: {
                            drawBorder: false
                        },
                        title: {
                            display: true,
                            text: 'Peso (kg)'
                        },
                        ticks: {
                            callback: function(value) {
                                return value + ' kg';
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Fecha de medición'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            font: {
                                size: 14
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Peso: ' + context.raw + ' kg';
                            }
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                elements: {
                    line: {
                        tension: 0.3
                    }
                }
            }
        });
    }

    // Gráfico de Evolución de la Grasa Corporal
    const grasaCtx = document.getElementById('grasaCorporalChart');
    if (grasaCtx && grasas.length > 0) {
        const grasaChart = new Chart(grasaCtx, {
            type: 'line',
            data: {
                labels: fechas,
                datasets: [{
                    label: 'Grasa Corporal (%)',
                    data: grasas,
                    backgroundColor: 'rgba(231, 76, 60, 0.1)',
                    borderColor: 'rgba(231, 76, 60, 1)',
                    borderWidth: 3,
                    tension: 0.3,
                    fill: true,
                    pointBackgroundColor: 'rgba(231, 76, 60, 1)',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: false,
                        grid: {
                            drawBorder: false
                        },
                        title: {
                            display: true,
                            text: 'Grasa Corporal (%)'
                        },
                        ticks: {
                            callback: function(value) {
                                return value + ' %';
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Fecha de medición'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            font: {
                                size: 14
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Grasa corporal: ' + context.raw + ' %';
                            }
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                elements: {
                    line: {
                        tension: 0.3
                    }
                }
            }
        });
    }

    // Si lo deseas, también puedes agregar un tercer gráfico combinado
    // Gráfico combinado de Peso y Masa Muscular (opcional)
    if (pesos.length > 0 && musculos.length > 0) {
        // Puedes agregar un tercer gráfico aquí si lo necesitas
    }

    // Funcionalidad para los filtros
    document.getElementById('time-filter').addEventListener('change', function(e) {
        // Aquí puedes implementar la lógica para filtrar por período
        console.log('Filtrar por período:', e.target.value);
    });

    document.getElementById('sort-filter').addEventListener('change', function(e) {
        // Aquí puedes implementar la lógica para ordenar
        console.log('Ordenar por:', e.target.value);
    });


    // Funcionalidad de paginación (solo si hay muchos datos)
    const table = document.getElementById('progress-table');
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const rowsPerPage = 10;
    let currentPage = 1;

    if (rows.length > rowsPerPage) {
        updatePagination();
    }

    function updatePagination() {
        const totalPages = Math.ceil(rows.length / rowsPerPage);

        // Mostrar solo las filas de la página actual
        rows.forEach((row, index) => {
            const startIndex = (currentPage - 1) * rowsPerPage;
            const endIndex = startIndex + rowsPerPage;

            if (index >= startIndex && index < endIndex) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        // Actualizar info de página
        document.getElementById('page-info').textContent =
            `Página ${currentPage} de ${totalPages}`;

        // Actualizar estado de botones
        document.getElementById('prev-page').disabled = currentPage === 1;
        document.getElementById('next-page').disabled = currentPage === totalPages;
    }

    document.getElementById('prev-page').addEventListener('click', function() {
        if (currentPage > 1) {
            currentPage--;
            updatePagination();
        }
    });

    document.getElementById('next-page').addEventListener('click', function() {
        const totalPages = Math.ceil(rows.length / rowsPerPage);
        if (currentPage < totalPages) {
            currentPage++;
            updatePagination();
        }
    });
</script>
<?php require_once "templates/footer.php"; ?>