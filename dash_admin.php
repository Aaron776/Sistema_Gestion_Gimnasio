<?php
require_once "autorizacion/auth.php"; // valida login y arranca sesión

// Verificar que tenga rol de admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: index.php"); // si no lo mandamos al login
    exit();
}
require_once "templates/header.php";
include_once "conexion/bd.php";
?>
<!-- Contenido -->
            <div class="content">
                <div class="page-header">
                    <h1>Dashboard</h1>
                </div>

                <!-- Tarjetas de Métricas -->
                <div class="metrics">
                    <div class="metric-card">
                        <div class="metric-icon bg-secondary">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="metric-info">
                            <h3>1,248</h3>
                            <p>Miembros Activos</p>
                            <div class="metric-trend trend-up">
                                <i class="fas fa-arrow-up"></i> 5.2% este mes
                            </div>
                        </div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-icon bg-success">
                            <i class="fas fa-dumbbell"></i>
                        </div>
                        <div class="metric-info">
                            <h3>342</h3>
                            <p>Entrenamientos Hoy</p>
                            <div class="metric-trend trend-up">
                                <i class="fas fa-arrow-up"></i> 12.7% hoy
                            </div>
                        </div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-icon bg-warning">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="metric-info">
                            <h3>18</h3>
                            <p>Clases Programadas</p>
                            <div class="metric-trend trend-down">
                                <i class="fas fa-arrow-down"></i> 2 hoy
                            </div>
                        </div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-icon bg-info">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="metric-info">
                            <h3>$12,580</h3>
                            <p>Ingresos del Mes</p>
                            <div class="metric-trend trend-up">
                                <i class="fas fa-arrow-up"></i> 8.3% este mes
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráficos y Actividad -->
                <div class="dashboard-content">
                    <div>
                        <div class="chart-container">
                            <h2>Asistencia Mensual</h2>
                            <div class="chart-wrapper">
                                <canvas id="attendanceChart"></canvas>
                            </div>
                        </div>
                        
                        <div class="members-table">
                            <h2>Miembros Recientes</h2>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Email</th>
                                        <th>Membresía</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>María González</td>
                                        <td>maria@email.com</td>
                                        <td>Premium</td>
                                        <td><span class="status-badge status-active">Activo</span></td>
                                        <td>
                                            <button class="btn-sm" style="background: var(--info); color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Carlos Ruiz</td>
                                        <td>carlos@email.com</td>
                                        <td>Básica</td>
                                        <td><span class="status-badge status-active">Activo</span></td>
                                        <td>
                                            <button class="btn-sm" style="background: var(--info); color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Ana López</td>
                                        <td>ana@email.com</td>
                                        <td>Premium</td>
                                        <td><span class="status-badge status-inactive">Inactivo</span></td>
                                        <td>
                                            <button class="btn-sm" style="background: var(--info); color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Roberto Silva</td>
                                        <td>roberto@email.com</td>
                                        <td>VIP</td>
                                        <td><span class="status-badge status-active">Activo</span></td>
                                        <td>
                                            <button class="btn-sm" style="background: var(--info); color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div>
                        <div class="recent-activity">
                            <h2>Actividad Reciente</h2>
                            <ul class="activity-list">
                                <li class="activity-item">
                                    <div class="activity-icon bg-secondary">
                                        <i class="fas fa-user-plus"></i>
                                    </div>
                                    <div class="activity-info">
                                        <h4>Nuevo Miembro</h4>
                                        <p>María González se registró</p>
                                        <div class="activity-time">Hace 2 horas</div>
                                    </div>
                                </li>
                                <li class="activity-item">
                                    <div class="activity-icon bg-success">
                                        <i class="fas fa-dumbbell"></i>
                                    </div>
                                    <div class="activity-info">
                                        <h4>Entrenamiento Completado</h4>
                                        <p>Carlos Ruiz completó su rutina</p>
                                        <div class="activity-time">Hace 3 horas</div>
                                    </div>
                                </li>
                                <li class="activity-item">
                                    <div class="activity-icon bg-warning">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </div>
                                    <div class="activity-info">
                                        <h4>Pago Recibido</h4>
                                        <p>Ana López pagó su membresía</p>
                                        <div class="activity-time">Hace 5 horas</div>
                                    </div>
                                </li>
                                <li class="activity-item">
                                    <div class="activity-icon bg-info">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <div class="activity-info">
                                        <h4>Clase Agendada</h4>
                                        <p>Yoga avanzado - 15 cupos llenos</p>
                                        <div class="activity-time">Hace 1 día</div>
                                    </div>
                                </li>
                                <li class="activity-item">
                                    <div class="activity-icon bg-secondary">
                                        <i class="fas fa-user-plus"></i>
                                    </div>
                                    <div class="activity-info">
                                        <h4>Nuevo Entrenador</h4>
                                        <p>Roberto Silva se unió al equipo</p>
                                        <div class="activity-time">Hace 2 días</div>
                                    </div>
                                </li>
                            </ul>
                        </div>

                        <div class="quick-actions">
                            <h2>Acciones Rápidas</h2>
                            <div class="actions-grid">
                                <a href="#" class="action-btn">
                                    <i class="fas fa-user-plus"></i>
                                    <span>Nuevo Miembro</span>
                                </a>
                                <a href="#" class="action-btn">
                                    <i class="fas fa-calendar-plus"></i>
                                    <span>Agendar Clase</span>
                                </a>
                                <a href="#" class="action-btn">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                    <span>Registrar Pago</span>
                                </a>
                                <a href="#" class="action-btn">
                                    <i class="fas fa-chart-bar"></i>
                                    <span>Generar Reporte</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
<?php
require_once 'templates/footer.php';
?>