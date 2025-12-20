<?php
require_once "autorizacion/auth.php"; // valida login y arranca sesión

// Verificar que tenga rol de socio
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'socio') {
    header("Location: index.php"); // si no lo mandamos al login
    exit();
}
require_once "templates/header.php";
include_once "conexion/bd.php";
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

    .metric-card {
        background-color: var(--card);
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s;
    }

    .metric-card:hover {
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

    .metric-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        color: white;
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

    .metric-trend {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.95rem;
        font-weight: 600;
    }

    .trend-up {
        color: var(--success);
    }

    .trend-down {
        color: var(--danger);
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

    .chart-wrapper {
        height: 250px;
        position: relative;
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

    .actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
    }

    .action-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 20px 15px;
        background: #f8f9fa;
        border-radius: 10px;
        text-decoration: none;
        color: var(--dark);
        transition: all 0.3s;
        text-align: center;
    }

    .action-btn:hover {
        background: var(--accent);
        color: white;
        transform: translateY(-3px);
    }

    .action-btn i {
        font-size: 1.8rem;
        margin-bottom: 10px;
    }

    .action-btn span {
        font-size: 0.9rem;
        font-weight: 600;
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

        .actions-grid {
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

        .actions-grid {
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
            <h1>¡Bienvenido de nuevo, <?php echo htmlspecialchars($_SESSION['nombre'].' '.$_SESSION['apellido']); ?>!</h1>
            <p>Tu dedicación está dando resultados. Sigue así para alcanzar tus metas de fitness.</p>
        </div>
    </div>

    <!-- Métricas principales -->
    <div class="main-metrics">
        <div class="metric-card">
            <div class="metric-header">
                <h3 class="metric-title">Peso Actual</h3>
                <div class="metric-icon weight">
                    <i class="fas fa-weight"></i>
                </div>
            </div>
            <div class="metric-value weight-value">78.5 kg</div>
            <div class="metric-trend">
                <i class="fas fa-arrow-down trend-down"></i>
                <span class="trend-down">-1.5 kg este mes</span>
            </div>
            <p style="color: #6c757d; margin-top: 10px; font-size: 0.9rem;">Meta: 75 kg</p>
        </div>

        <div class="metric-card">
            <div class="metric-header">
                <h3 class="metric-title">Grasa Corporal</h3>
                <div class="metric-icon fat">
                    <i class="fas fa-percentage"></i>
                </div>
            </div>
            <div class="metric-value fat-value">22.0 %</div>
            <div class="metric-trend">
                <i class="fas fa-arrow-down trend-down"></i>
                <span class="trend-down">-2.0% este mes</span>
            </div>
            <p style="color: #6c757d; margin-top: 10px; font-size: 0.9rem;">Meta: 18%</p>
        </div>

        <div class="metric-card">
            <div class="metric-header">
                <h3 class="metric-title">Masa Muscular</h3>
                <div class="metric-icon muscle">
                    <i class="fas fa-dumbbell"></i>
                </div>
            </div>
            <div class="metric-value muscle-value">35.0 kg</div>
            <div class="metric-trend">
                <i class="fas fa-arrow-up trend-up"></i>
                <span class="trend-up">+1.5 kg este mes</span>
            </div>
            <p style="color: #6c757d; margin-top: 10px; font-size: 0.9rem;">Meta: 38 kg</p>
        </div>

        <div class="metric-card">
            <div class="metric-header">
                <h3 class="metric-title">Próxima Clase</h3>
                <div class="metric-icon calendar">
                    <i class="fas fa-calendar-alt"></i>
                </div>
            </div>
            <div class="metric-value" style="color: var(--accent);">10:00 AM</div>
            <div class="metric-trend">
                <i class="fas fa-clock"></i>
                <span>CrossFit en 2 horas</span>
            </div>
            <p style="color: #6c757d; margin-top: 10px; font-size: 0.9rem;">Con el entrenador Carlos</p>
        </div>
    </div>

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
                <li class="class-item">
                    <div class="class-icon crossfit">
                        <i class="fas fa-fire"></i>
                    </div>
                    <div class="class-info">
                        <div class="class-name">CrossFit Intermedio</div>
                        <div class="class-time">Hoy • 10:00 - 11:30 AM</div>
                    </div>
                    <span class="class-status status-confirmed">Confirmada</span>
                </li>
                <li class="class-item">
                    <div class="class-icon yoga">
                        <i class="fas fa-spa"></i>
                    </div>
                    <div class="class-info">
                        <div class="class-name">Yoga Matutino</div>
                        <div class="class-time">Mañana • 08:00 - 09:00 AM</div>
                    </div>
                    <span class="class-status status-confirmed">Confirmada</span>
                </li>
                <li class="class-item">
                    <div class="class-icon spinning">
                        <i class="fas fa-bicycle"></i>
                    </div>
                    <div class="class-info">
                        <div class="class-name">Spinning Avanzado</div>
                        <div class="class-time">Jueves • 16:00 - 17:00 PM</div>
                    </div>
                    <span class="class-status status-pending">Por confirmar</span>
                </li>
            </ul>
        </div>

        <!-- Rutina Actual -->
        <div class="routine-card">
            <div class="routine-header">
                <h3><i class="fas fa-dumbbell"></i> Rutina de Hoy</h3>
            </div>
            <div class="routine-details">
                <div class="routine-meta">
                    <div class="meta-item">
                        <i class="fas fa-dumbbell"></i>
                        <div>
                            <div class="meta-label">Tipo</div>
                            <div class="meta-value">Fuerza Superior</div>
                        </div>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-clock"></i>
                        <div>
                            <div class="meta-label">Duración</div>
                            <div class="meta-value">60 minutos</div>
                        </div>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-bullseye"></i>
                        <div>
                            <div class="meta-label">Objetivo</div>
                            <div class="meta-value">Hipertrofia</div>
                        </div>
                    </div>
                </div>
                <ul class="exercises-list">
                    <li class="exercise-item">
                        <div class="exercise-icon">
                            <i class="fas fa-weight"></i>
                        </div>
                        <div class="exercise-info">
                            <div class="exercise-name">Press de Banca</div>
                            <div class="exercise-sets">4 series × 8-10 repeticiones</div>
                        </div>
                    </li>
                    <li class="exercise-item">
                        <div class="exercise-icon">
                            <i class="fas fa-dumbbell"></i>
                        </div>
                        <div class="exercise-info">
                            <div class="exercise-name">Remo con Barra</div>
                            <div class="exercise-sets">3 series × 10-12 repeticiones</div>
                        </div>
                    </li>
                    <li class="exercise-item">
                        <div class="exercise-icon">
                            <i class="fas fa-weight-hanging"></i>
                        </div>
                        <div class="exercise-info">
                            <div class="exercise-name">Press Militar</div>
                            <div class="exercise-sets">3 series × 8-10 repeticiones</div>
                        </div>
                    </li>
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
                    <tr>
                        <td>15 Mar 2024</td>
                        <td>78.5 kg <div class="trend-indicator"><i class="fas fa-arrow-down trend-down"></i></div>
                        </td>
                        <td>22.0% <div class="trend-indicator"><i class="fas fa-arrow-down trend-down"></i></div>
                        </td>
                        <td>35.0 kg <div class="trend-indicator"><i class="fas fa-arrow-up trend-up"></i></div>
                        </td>
                    </tr>
                    <tr>
                        <td>08 Mar 2024</td>
                        <td>79.0 kg <div class="trend-indicator"><i class="fas fa-arrow-down trend-down"></i></div>
                        </td>
                        <td>22.2% <div class="trend-indicator"><i class="fas fa-arrow-down trend-down"></i></div>
                        </td>
                        <td>34.8 kg <div class="trend-indicator"><i class="fas fa-arrow-up trend-up"></i></div>
                        </td>
                    </tr>
                    <tr>
                        <td>01 Mar 2024</td>
                        <td>79.5 kg</td>
                        <td>22.5%</td>
                        <td>34.5 kg</td>
                    </tr>
                    <tr>
                        <td>23 Feb 2024</td>
                        <td>80.0 kg</td>
                        <td>23.0%</td>
                        <td>34.0 kg</td>
                    </tr>
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
    // Datos para gráficos
    const evolutionData = {
        labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
        peso: [84.0, 82.0, 80.0, 79.5, 78.5, 78.5],
        grasa: [26.0, 24.5, 23.0, 22.5, 22.0, 22.0],
        musculo: [31.0, 32.0, 33.5, 34.0, 35.0, 35.0]
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