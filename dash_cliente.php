<?php
require_once "autorizacion/auth.php"; // valida login y arranca sesión

// Verificar que tenga rol de socio
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'socio') {
    header("Location: index.php"); // si no lo mandamos al login
    exit();
}
require_once "templates/header.php";
include_once "conexion/bd.php";

$id_socio = $_SESSION['id_usuario']; // obtener id del socio actual

// Obtener las ultimas tres clases inscritas de este socio
$sql = $conexion->prepare("SELECT clases.nombre as nombre_clase,clases.horario as horario FROM inscripciones INNER JOIN clases ON inscripciones.id_clase = clases.id WHERE inscripciones.id_socio = :id_socio  ORDER BY inscripciones.fecha_inscripcion DESC LIMIT 3");
$sql->bindParam(':id_socio', $id_socio, PDO::PARAM_INT);
$sql->execute();
$clases = $sql->fetchAll(PDO::FETCH_OBJ);

// Obtener las ultimas tres rutinas asignadas a este socio
$sql = $conexion->prepare("SELECT rutinas.descripcion as descripcion, rutinas.fecha_asignacion as fecha_asignacion FROM rutinas INNER JOIN usuarios ON rutinas.id_socio = usuarios.id WHERE usuarios.id = :id_socio  ORDER BY rutinas.fecha_asignacion DESC LIMIT 3");
$sql->bindParam(':id_socio', $id_socio, PDO::PARAM_INT);
$sql->execute();
$rutinas = $sql->fetchAll(PDO::FETCH_OBJ);

// Obtener peso actual
$sql = $conexion->prepare("SELECT peso FROM progreso WHERE id_socio = :id_socio ORDER BY fecha_registro DESC LIMIT 1");
$sql->bindParam(':id_socio', $id_socio, PDO::PARAM_INT);
$sql->execute();
$peso_actual = $sql->fetch(PDO::FETCH_OBJ);


// Obtener masa muscular actual
$sql = $conexion->prepare("SELECT masa_muscular FROM progreso WHERE id_socio = :id_socio ORDER BY fecha_registro DESC LIMIT 1");
$sql->bindParam(':id_socio', $id_socio, PDO::PARAM_INT);
$sql->execute();
$masa_muscular_actual = $sql->fetch(PDO::FETCH_OBJ);

// Obtener porcentaje de grasa corporal actual
$sql = $conexion->prepare("SELECT grasa_corporal FROM progreso WHERE id_socio = :id_socio ORDER BY fecha_registro DESC LIMIT 1");
$sql->bindParam(':id_socio', $id_socio, PDO::PARAM_INT);
$sql->execute();
$grasa_corporal_actual = $sql->fetch(PDO::FETCH_OBJ);

// ------------------------------------------------------------------------------------------------
// [PASO 1] OBTENER HISTORIAL DE PROGRESO
// Obtenemos los últimos 6 registros de progreso para mostrar en la tabla y en el gráfico.
// Ordenamos por fecha descendente (más reciente primero) para la tabla.
// ------------------------------------------------------------------------------------------------
$sql = $conexion->prepare("SELECT * FROM progreso WHERE id_socio = :id_socio ORDER BY fecha_registro DESC LIMIT 6");
$sql->bindParam(':id_socio', $id_socio, PDO::PARAM_INT);
$sql->execute();
$historial_progreso = $sql->fetchAll(PDO::FETCH_OBJ);

// Para el gráfico, necesitamos los datos en orden cronológico (más antiguo primero).
// Así que invertimos el array que usaremos para los datos del gráfico.
$datos_grafico = array_reverse($historial_progreso);

$labels = [];
$peso_data = [];
$grasa_data = [];
$musculo_data = [];

foreach ($datos_grafico as $registro) {
    // Formateamos la fecha a algo más corto, ej: '15 Mar'
    $date = new DateTime($registro->fecha_registro);
    // Array de meses en español para formateo personalizado si se desea, o usar format simple
    $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    $mes_index = (int)$date->format('n') - 1;
    $fecha_formateada = $date->format('d') . ' ' . $meses[$mes_index];

    $labels[] = $fecha_formateada;
    $peso_data[] = $registro->peso;
    $grasa_data[] = $registro->grasa_corporal;
    $musculo_data[] = $registro->masa_muscular;
}
?>
<style>
    :root {
        --muscle: #3498db;
        --fat: #e74c3c;
        --weight: #2c3e50;
    }

    /* Welcome Banner */
    .welcome-banner {
        background: linear-gradient(135deg, var(--primary), var(--accent));
        color: white;
        border-radius: 12px;
        padding: 30px;
        margin-bottom: 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
    }

    .welcome-text h1 {
        font-size: 2.2rem;
        margin-bottom: 10px;
        font-family: var(--font-main);
    }

    .welcome-text p {
        font-size: 1.1rem;
        opacity: 0.9;
        max-width: 600px;
    }

    .welcome-stats {
        display: flex;
        gap: 25px;
        flex-wrap: wrap;
    }

    .stat-item {
        text-align: center;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .stat-label {
        font-size: 0.9rem;
        opacity: 0.8;
    }

    /* Métricas principales */
    .main-metrics {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 25px;
        margin-bottom: 30px;
    }

    /* .metric-card hereda estilos de header.php, solo sobrescribimos lo necesario */
    .main-metrics .metric-card {
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .main-metrics .metric-card:hover {
        transform: translateY(-5px);
    }

    .metric-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .metric-title {
        font-size: 1.3rem;
        color: var(--dark);
        font-family: var(--font-main);
    }

    /* .metric-icon hereda estilos de header.php, solo sobrescribimos lo necesario */
    .main-metrics .metric-icon {
        border-radius: 12px;
        font-size: 1.8rem;
    }

    .metric-icon.weight {
        background-color: var(--weight);
    }

    .metric-icon.fat {
        background-color: var(--fat);
    }

    .metric-icon.muscle {
        background-color: var(--muscle);
    }

    .metric-icon.calendar {
        background-color: var(--success);
    }

    .metric-value {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 10px;
        font-family: var(--font-main);
    }

    .weight-value {
        color: var(--weight);
    }

    .fat-value {
        color: var(--fat);
    }

    .muscle-value {
        color: var(--muscle);
    }

    /* .metric-trend, .trend-up, .trend-down heredan de header.php */
    /* Solo agregamos estilos específicos si es necesario */
    .metric-trend {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Dashboard Grid */
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
        gap: 25px;
        margin-bottom: 30px;
    }

    @media (max-width: 1100px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Gráficos */
    .chart-card {
        background-color: var(--card);
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .chart-header h3 {
        font-size: 1.3rem;
        color: var(--dark);
        font-family: var(--font-main);
    }

    .chart-header h3 i {
        color: var(--accent);
        margin-right: 10px;
    }

    /* .chart-wrapper hereda de header.php, solo ajustamos altura específica */
    .chart-card .chart-wrapper {
        height: 250px;
    }

    /* Próximas Clases */
    .classes-card {
        background-color: var(--card);
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .classes-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .classes-header h3 {
        font-size: 1.3rem;
        color: var(--dark);
        font-family: var(--font-main);
    }

    .classes-header h3 i {
        color: var(--warning);
        margin-right: 10px;
    }

    .view-all {
        color: var(--accent);
        text-decoration: none;
        font-weight: 600;
        font-size: 0.95rem;
    }

    .classes-list {
        list-style: none;
    }

    .class-item {
        display: flex;
        align-items: center;
        padding: 15px;
        border-bottom: 1px solid #eee;
        transition: background-color 0.3s;
    }

    .class-item:hover {
        background-color: #f8f9fa;
    }

    .class-item:last-child {
        border-bottom: none;
    }

    .class-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        font-size: 1.3rem;
        color: white;
    }

    .class-icon.yoga {
        background-color: #9b59b6;
    }

    .class-icon.crossfit {
        background-color: var(--secondary);
    }

    .class-icon.spinning {
        background-color: var(--accent);
    }

    .class-info {
        flex: 1;
    }

    .class-name {
        font-weight: 600;
        margin-bottom: 5px;
        color: var(--dark);
    }

    .class-time {
        font-size: 0.9rem;
        color: #6c757d;
    }

    .class-status {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .status-confirmed {
        background-color: #d4edda;
        color: #155724;
    }

    .status-pending {
        background-color: #fff3cd;
        color: #856404;
    }

    /* Rutina Actual */
    .routine-card {
        background-color: var(--card);
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .routine-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .routine-header h3 {
        font-size: 1.3rem;
        color: var(--dark);
        font-family: var(--font-main);
    }

    .routine-header h3 i {
        color: var(--success);
        margin-right: 10px;
    }

    .routine-details {
        margin-bottom: 25px;
    }

    .routine-meta {
        display: flex;
        gap: 30px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .meta-item i {
        color: var(--accent);
        font-size: 1.2rem;
    }

    .meta-label {
        font-size: 0.9rem;
        color: #6c757d;
    }

    .meta-value {
        font-weight: 600;
        color: var(--dark);
    }

    .exercises-list {
        list-style: none;
    }

    .exercise-item {
        display: flex;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #eee;
    }

    .exercise-item:last-child {
        border-bottom: none;
    }

    .exercise-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        background-color: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        color: var(--accent);
        font-size: 1.1rem;
    }

    .exercise-info {
        flex: 1;
    }

    .exercise-name {
        font-weight: 600;
        margin-bottom: 3px;
        color: var(--dark);
    }

    .exercise-sets {
        font-size: 0.85rem;
        color: #6c757d;
    }

    /* Progreso Reciente */
    .recent-progress {
        background-color: var(--card);
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .progress-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .progress-header h3 {
        font-size: 1.3rem;
        color: var(--dark);
        font-family: var(--font-main);
    }

    .progress-header h3 i {
        color: var(--info);
        margin-right: 10px;
    }

    .progress-table {
        width: 100%;
        border-collapse: collapse;
    }

    .progress-table th,
    .progress-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }

    .progress-table th {
        font-weight: 600;
        color: var(--dark);
        font-family: var(--font-main);
    }

    .progress-table tr:hover {
        background-color: #f8f9fa;
    }

    .progress-table tr:last-child td {
        border-bottom: none;
    }

    .trend-indicator {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    /* Acciones Rápidas */
    .quick-actions {
        background-color: var(--card);
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .actions-header h3 {
        font-size: 1.3rem;
        color: var(--dark);
        margin-bottom: 20px;
        font-family: var(--font-main);
    }

    .actions-header h3 i {
        color: var(--primary);
        margin-right: 10px;
    }

    /* .actions-grid y .action-btn heredan de header.php, solo ajustamos lo específico */
    .quick-actions .actions-grid {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    }

    .quick-actions .action-btn {
        padding: 20px 15px;
        border-radius: 10px;
    }

    .quick-actions .action-btn:hover {
        background: var(--accent);
    }

    .quick-actions .action-btn i {
        font-size: 1.8rem;
    }

    /* Logros */
    .achievements {
        background-color: var(--card);
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .achievements-header h3 {
        font-size: 1.3rem;
        color: var(--dark);
        margin-bottom: 20px;
        font-family: var(--font-main);
    }

    .achievements-header h3 i {
        color: var(--warning);
        margin-right: 10px;
    }

    .badges-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
    }

    .badge-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        width: 100px;
    }

    .badge-icon {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--warning), #f1c40f);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        color: white;
        margin-bottom: 10px;
    }

    .badge-name {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--dark);
    }

    .badge-date {
        font-size: 0.75rem;
        color: #6c757d;
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 992px) {
        .welcome-banner {
            flex-direction: column;
            text-align: center;
        }

        .welcome-stats {
            justify-content: center;
        }

        .main-metrics {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .main-metrics {
            grid-template-columns: 1fr;
        }

        .quick-actions .actions-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 576px) {
        .welcome-text h1 {
            font-size: 1.8rem;
        }

        .metric-value {
            font-size: 2rem;
        }

        .welcome-stats {
            flex-direction: column;
            gap: 15px;
        }

        .routine-meta {
            flex-direction: column;
            gap: 15px;
        }

        .quick-actions .actions-grid {
            grid-template-columns: 1fr;
        }

        .badges-grid {
            justify-content: center;
        }
    }
</style>

<div class="content">
    <!-- Banner de bienvenida -->
    <div class="welcome-banner">
        <div class="welcome-text">
            <h1>¡Bienvenido de nuevo, <?php echo htmlspecialchars($_SESSION['nombre'] . ' ' . $_SESSION['apellido']); ?>!</h1>
            <p>Tu dedicación está dando resultados. Sigue así para alcanzar tus metas de fitness.</p>
        </div>
    </div>

    <!-- Métricas principales -->
    <?php if ($peso_actual && $masa_muscular_actual && $grasa_corporal_actual): ?>
        <div class="main-metrics">
            <div class="metric-card">
                <div class="metric-header">
                    <h3 class="metric-title">Peso Actual</h3>
                    <div class="metric-icon weight">
                        <i class="fas fa-weight"></i>
                    </div>
                </div>
                <div class="metric-value weight-value"><?php echo htmlspecialchars($peso_actual->peso); ?> kg</div>
            </div>

            <div class="metric-card">
                <div class="metric-header">
                    <h3 class="metric-title">Grasa Corporal</h3>
                    <div class="metric-icon fat">
                        <i class="fas fa-percentage"></i>
                    </div>
                </div>
                <div class="metric-value fat-value"><?php echo htmlspecialchars($grasa_corporal_actual->grasa_corporal); ?> %</div>
            </div>

            <div class="metric-card">
                <div class="metric-header">
                    <h3 class="metric-title">Masa Muscular</h3>
                    <div class="metric-icon muscle">
                        <i class="fas fa-dumbbell"></i>
                    </div>
                </div>
                <div class="metric-value muscle-value"><?php echo htmlspecialchars($masa_muscular_actual->masa_muscular); ?> kg</div>
            </div>
        </div>
    <?php else: ?>
        <div class="main-metrics">
            <div class="metric-card">
                <div class="metric-header">
                    <h3 class="metric-title">Peso Actual</h3>
                    <div class="metric-icon weight">
                        <i class="fas fa-weight"></i>
                    </div>
                </div>
                <div class="metric-value weight-value">No hay datos</div>
            </div>
            <div class="metric-card">
                <div class="metric-header">
                    <h3 class="metric-title">Grasa Corporal</h3>
                    <div class="metric-icon fat">
                        <i class="fas fa-percentage"></i>
                    </div>
                </div>
                <div class="metric-value fat-value">No hay datos</div>
            </div>
            <div class="metric-card">
                <div class="metric-header">
                    <h3 class="metric-title">Masa Muscular</h3>
                    <div class="metric-icon muscle">
                        <i class="fas fa-dumbbell"></i>
                    </div>
                </div>
                <div class="metric-value muscle-value">No hay datos</div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Dashboard Grid -->
    <div class="dashboard-grid">
        <!-- Gráfico de evolución -->
        <div class="chart-card">
            <div class="chart-header">
                <h3><i class="fas fa-chart-line"></i> Mi Evolución</h3>
            </div>
            <div class="chart-wrapper">
                <canvas id="evolutionChart"></canvas>
            </div>
        </div>

        <!-- Próximas Clases -->
        <div class="classes-card">
            <div class="classes-header">
                <h3><i class="fas fa-calendar-alt"></i> Próximas Clases</h3>
                <a href="mis-clases.html" class="view-all">Ver todas</a>
            </div>
            <ul class="classes-list">
                <?php foreach ($clases as $item) { ?>
                    <li class="class-item">
                        <div class="class-info">
                            <div class="class-name"><?php echo htmlspecialchars($item->nombre_clase); ?></div>
                            <div class="class-time"><?php echo htmlspecialchars($item->horario); ?></div>
                        </div>
                        <span class="class-status status-confirmed">Confirmada</span>
                    </li>
                <?php } ?>
            </ul>
        </div>

        <!-- Rutina Actual -->
        <div class="routine-card">
            <div class="routine-header">
                <h3><i class="fas fa-dumbbell"></i> Rutina de Hoy</h3>
            </div>
            <div class="routine-details">
                <ul class="exercises-list">
                    <?php foreach ($rutinas as $item) { ?>
                        <li class="exercise-item">
                            <div class="exercise-icon">
                                <i class="fas fa-dumbbell"></i>
                            </div>
                            <div class="exercise-info">
                                <div class="exercise-name"><?php echo htmlspecialchars($item->descripcion); ?></div>
                                <div class="exercise-sets"><?php echo htmlspecialchars($item->fecha_asignacion); ?></div>
                            </div>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>

        <!-- Progreso Reciente -->
        <div class="recent-progress">
            <div class="progress-header">
                <h3><i class="fas fa-history"></i> Progreso Reciente</h3>
                <a href="mi-progreso.html" class="view-all">Ver histórico</a>
            </div>
            <table class="progress-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Peso</th>
                        <th>Grasa</th>
                        <th>Músculo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // [PASO 2] GENERAR TABLA DE PROGRESO
                    // Iteramos sobre el historial obtenido.
                    // $historial_progreso ya está ordenado del más reciente al más antiguo.
                    foreach ($historial_progreso as $index => $registro):
                        // Lógica para comparar con el registro anterior (que en este array ordenado DESC es el siguiente elemento)
                        // para determinar si hubo subida o bajada.
                        $prev_registro = isset($historial_progreso[$index + 1]) ? $historial_progreso[$index + 1] : null;

                        // Iconos de tendencia
                        $trend_down = '<div class="trend-indicator"><i class="fas fa-arrow-down trend-down"></i></div>';
                        $trend_up = '<div class="trend-indicator"><i class="fas fa-arrow-up trend-up"></i></div>';

                        // Determinar tendencia (Peso: bajar es bueno/verde? Depende. Asumiremos visualización simple: Arriba=Rojo/Verde según contexto?)
                        // Para simplificar: Arriba=Flecha Arriba, Abajo=Flecha Abajo. Colores: Up=Success(verde)? No siempre.
                        // Usaremos la convención visual: 
                        // Peso: si baja -> flecha abajo verde (trend-down es rojo en CSS, ajustaremos si es necesario o usaremos clases standard).
                        // Vamos a usar colores neutros o clases existentes. En la plantilla original:
                        // "trend-down" tiene color danger(rojo), "trend-up" tiene color success(verde).
                        // Generalmente bajar peso y grasa es "bueno" -> verde, subir músculo es "bueno" -> verde.
                        // Pero la clase "trend-down" es roja. Vamos a mantener la coherencia visual de las flechas simplemente indicando dirección.

                        // PESO
                        $peso_trend = '';
                        if ($prev_registro) {
                            if ($registro->peso < $prev_registro->peso) $peso_trend = '<div class="trend-indicator"><i class="fas fa-arrow-down" style="color: var(--success);"></i></div>';
                            elseif ($registro->peso > $prev_registro->peso) $peso_trend = '<div class="trend-indicator"><i class="fas fa-arrow-up" style="color: var(--danger);"></i></div>';
                        }

                        // GRASA
                        $grasa_trend = '';
                        if ($prev_registro) {
                            if ($registro->grasa_corporal < $prev_registro->grasa_corporal) $grasa_trend = '<div class="trend-indicator"><i class="fas fa-arrow-down" style="color: var(--success);"></i></div>';
                            elseif ($registro->grasa_corporal > $prev_registro->grasa_corporal) $grasa_trend = '<div class="trend-indicator"><i class="fas fa-arrow-up" style="color: var(--danger);"></i></div>';
                        }

                        // MUSCULO
                        $musculo_trend = '';
                        if ($prev_registro) {
                            if ($registro->masa_muscular > $prev_registro->masa_muscular) $musculo_trend = '<div class="trend-indicator"><i class="fas fa-arrow-up" style="color: var(--success);"></i></div>';
                            elseif ($registro->masa_muscular < $prev_registro->masa_muscular) $musculo_trend = '<div class="trend-indicator"><i class="fas fa-arrow-down" style="color: var(--danger);"></i></div>';
                        }

                        $dateObj = new DateTime($registro->fecha_registro);
                        $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                        $mes_index = (int)$dateObj->format('n') - 1;
                        $fecha_espanol = $dateObj->format('d') . ' ' . $meses[$mes_index] . ' ' . $dateObj->format('Y');
                    ?>
                        <tr>
                            <td><?php echo $fecha_espanol; ?></td>
                            <td><?php echo htmlspecialchars($registro->peso); ?> kg <?php echo $peso_trend; ?></td>
                            <td><?php echo htmlspecialchars($registro->grasa_corporal); ?>% <?php echo $grasa_trend; ?></td>
                            <td><?php echo htmlspecialchars($registro->masa_muscular); ?> kg <?php echo $musculo_trend; ?></td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($historial_progreso)): ?>
                        <tr>
                            <td colspan="4" style="text-align:center;">No hay registros de progreso recientes.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Acciones Rápidas y Logros -->
    <div class="dashboard-grid">
        <!-- Acciones Rápidas -->
        <div class="quick-actions">
            <div class="actions-header">
                <h3><i class="fas fa-bolt"></i> Acciones Rápidas</h3>
            </div>
            <div class="actions-grid">
                <a href="registrar-progreso.html" class="action-btn">
                    <i class="fas fa-plus"></i>
                    <span>Registrar Progreso</span>
                </a>
                <a href="reservar-clase.html" class="action-btn">
                    <i class="fas fa-calendar-plus"></i>
                    <span>Reservar Clase</span>
                </a>
                <a href="mi-rutina.html" class="action-btn">
                    <i class="fas fa-dumbbell"></i>
                    <span>Ver Rutina</span>
                </a>
                <a href="pagos.html" class="action-btn">
                    <i class="fas fa-credit-card"></i>
                    <span>Pagos</span>
                </a>
                <a href="contactar-entrenador.html" class="action-btn">
                    <i class="fas fa-comment-dots"></i>
                    <span>Contactar Entrenador</span>
                </a>
                <a href="mi-perfil.html" class="action-btn">
                    <i class="fas fa-user-edit"></i>
                    <span>Editar Perfil</span>
                </a>
            </div>
        </div>

        <!-- Logros -->
        <div class="achievements">
            <div class="achievements-header">
                <h3><i class="fas fa-trophy"></i> Mis Logros</h3>
            </div>
            <div class="badges-grid">
                <div class="badge-item">
                    <div class="badge-icon">
                        <i class="fas fa-fire"></i>
                    </div>
                    <div class="badge-name">30 Días</div>
                    <div class="badge-date">Consecutivos</div>
                </div>
                <div class="badge-item">
                    <div class="badge-icon">
                        <i class="fas fa-weight"></i>
                    </div>
                    <div class="badge-name">-5 kg</div>
                    <div class="badge-date">Peso perdido</div>
                </div>
                <div class="badge-item">
                    <div class="badge-icon">
                        <i class="fas fa-dumbbell"></i>
                    </div>
                    <div class="badge-name">+3 kg</div>
                    <div class="badge-date">Músculo ganado</div>
                </div>
                <div class="badge-item">
                    <div class="badge-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="badge-name">Asistente</div>
                    <div class="badge-date">50+ clases</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // [PASO 3] CONFIGURACIÓN DEL GRÁFICO
    // Inyectamos los datos preparados en PHP directamente en el objeto de configuración JS.
    const evolutionData = {
        labels: <?php echo json_encode($labels); ?>,
        peso: <?php echo json_encode($peso_data); ?>,
        grasa: <?php echo json_encode($grasa_data); ?>,
        musculo: <?php echo json_encode($musculo_data); ?>
    };

    // Inicializar gráfico de evolución
    const evolutionCtx = document.getElementById('evolutionChart').getContext('2d');
    const evolutionChart = new Chart(evolutionCtx, {
        type: 'line',
        data: {
            labels: evolutionData.labels,
            datasets: [{
                    label: 'Peso (kg)',
                    data: evolutionData.peso,
                    borderColor: 'var(--weight)',
                    backgroundColor: 'rgba(44, 62, 80, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Grasa (%)',
                    data: evolutionData.grasa,
                    borderColor: 'var(--fat)',
                    backgroundColor: 'rgba(231, 76, 60, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Músculo (kg)',
                    data: evolutionData.musculo,
                    borderColor: 'var(--muscle)',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: false,
                    title: {
                        display: true,
                        text: 'Valores'
                    }
                }
            }
        }
    });

    // Animación para las tarjetas de métricas
    document.querySelectorAll('.metric-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px)';
            this.style.boxShadow = '0 8px 25px rgba(0, 0, 0, 0.15)';
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 4px 15px rgba(0, 0, 0, 0.1)';
        });
    });

    // Actualizar contador de días seguidos
    function actualizarContadorDias() {
        const hoy = new Date();
        const fechaInicio = new Date('2024-02-01');
        const diffTime = hoy - fechaInicio;
        const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));

        if (diffDays > 0) {
            document.querySelector('.welcome-stats .stat-value').textContent = diffDays;
        }
    }

    // Inicialización
    document.addEventListener('DOMContentLoaded', function() {
        actualizarContadorDias();
    });

    // Agregar animación de pulso
    const style = document.createElement('style');
    style.textContent = `
            @keyframes pulse {
                0% { transform: scale(1); }
                50% { transform: scale(1.2); }
                100% { transform: scale(1); }
            }
        `;
    document.head.appendChild(style);
</script>
<?php require_once "templates/footer.php"; ?>