<?php
require_once "autorizacion/auth.php"; // valida login y arranca sesión

// Verificar que tenga rol de admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: acceso_denegado.php"); // si no lo mandamos al acceso denegado
    exit();
}
require_once "templates/header.php";
include_once "conexion/bd.php";

// Obteber cantidas de usuarios del tipo socio
$sql = $conexion->prepare("SELECT COUNT(*) as cantidad FROM usuarios WHERE rol = 'socio'");
$sql->execute();
$cantidadSocios = $sql->fetch(PDO::FETCH_OBJ);

// Obtener la cantidad de clases existentes
$sql = $conexion->prepare("SELECT COUNT(*) as cantidad FROM clases");
$sql->execute();
$cantidadClases = $sql->fetch(PDO::FETCH_OBJ);

// Ovtener el total de ingresos del mes actual
$mesActual = date('m');
$anioActual = date('Y');
$sql = $conexion->prepare("SELECT SUM(monto) as total FROM pagos WHERE MONTH(fecha_pago) = :mes AND YEAR(fecha_pago) = :anio");
$sql->bindParam(':mes', $mesActual, PDO::PARAM_INT);
$sql->bindParam(':anio', $anioActual, PDO::PARAM_INT);
$sql->execute();
$totalIngresos = $sql->fetch(PDO::FETCH_OBJ);

// Obtener socios recientes
$sql = $conexion->prepare("SELECT CONCAT(usuarios.nombre,' ',usuarios.apellido) as nombre, usuarios.email as email, membresias.nombre as membresia FROM membresia_usuario INNER JOIN usuarios ON membresia_usuario.id_usuario = usuarios.id INNER JOIN membresias ON membresia_usuario.id_membresia = membresias.id WHERE usuarios.rol = 'socio' ORDER BY usuarios.fecha_registro DESC LIMIT 5");
$sql->execute();
$sociosRecientes = $sql->fetchAll(PDO::FETCH_OBJ);

// CONSULTA PARA EL PRIMER GRÁFICO: Distribución de Membresías
$sql = $conexion->prepare("
    SELECT 
        m.nombre as membresia,
        COUNT(mu.id_usuario) as cantidad
    FROM membresia_usuario mu
    INNER JOIN membresias m ON mu.id_membresia = m.id
    GROUP BY m.nombre
    ORDER BY cantidad DESC
");
$sql->execute();
$distribucionMembresias = $sql->fetchAll(PDO::FETCH_OBJ);

// CONSULTA PARA EL SEGUNDO GRÁFICO: Ingresos por Mes (últimos 6 meses)
$sql = $conexion->prepare("
    SELECT 
        MONTHNAME(fecha_pago) as mes,
        SUM(monto) as total
    FROM pagos 
    WHERE fecha_pago >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY MONTH(fecha_pago), MONTHNAME(fecha_pago)
    ORDER BY MONTH(fecha_pago) ASC
");
$sql->execute();
$ingresosPorMes = $sql->fetchAll(PDO::FETCH_OBJ);

// Preparar datos para Chart.js
$labelsMembresias = [];
$dataMembresias = [];
$colorsMembresias = [
    'rgba(255, 99, 132, 0.7)',
    'rgba(54, 162, 235, 0.7)',
    'rgba(255, 206, 86, 0.7)',
    'rgba(75, 192, 192, 0.7)',
    'rgba(153, 102, 255, 0.7)',
    'rgba(255, 159, 64, 0.7)'
];

foreach ($distribucionMembresias as $item) {
    $labelsMembresias[] = htmlspecialchars($item->membresia);
    $dataMembresias[] = $item->cantidad;
}

$labelsMeses = [];
$dataIngresos = [];
foreach ($ingresosPorMes as $item) {
    $labelsMeses[] = htmlspecialchars($item->mes);
    $dataIngresos[] = $item->total;
}

// Si no hay datos de ingresos, mostrar datos de ejemplo
if (empty($dataIngresos)) {
    $labelsMeses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio'];
    $dataIngresos = [1500, 1800, 2200, 1900, 2400, 2100];
}
?>
<!-- Incluir Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
                <h3><?php echo $cantidadSocios->cantidad; ?></h3>
                <p>Socios Activos</p>
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-icon bg-success">
                <i class="fas fa-dumbbell"></i>
            </div>
            <div class="metric-info">
                <h3>342</h3>
                <p>Entrenamientos Hoy</p>
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-icon bg-warning">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="metric-info">
                <h3><?php echo htmlspecialchars($cantidadClases->cantidad); ?></h3>
                <p>Clases Existentes</p>
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-icon bg-info">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="metric-info">
                <h3>$<?php echo htmlspecialchars($totalIngresos->total ?? 0); ?></h3>
                <p>Ingresos del Mes</p>
            </div>
        </div>
    </div>

    <!-- Gráficos y Actividad -->
    <div class="dashboard-content">
        <div>
            <div class="chart-container">
                <h2>Distribución de Membresías</h2>
                <div class="chart-wrapper">
                    <canvas id="membershipChart"></canvas>
                </div>
            </div>

            <!-- Tabla de Socios Recientes -->
            <div class="recent-members">
                <div class="section-header">
                    <h3>Socios Recientes</h3>
                    <a href="gestion_socios.php" class="btn btn-sm btn-primary">Ver Todos</a>
                </div>
                <div style="overflow-x: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Plan</th>
                                <th>Fecha Registro</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sociosRecientes as $socio): // Assuming $ultimos_socios should be $sociosRecientes based on context 
                            ?>
                                <tr>
                                    <td>
                                        <div class="member-info">
                                            <!-- <img src="assets/img/avatar.png" alt="Avatar" class="member-avatar"> -->
                                            <div>
                                                <span class="member-name"><?php echo htmlspecialchars($socio->nombre); ?></span>
                                                <span class="member-email"><?php echo htmlspecialchars($socio->email); ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge badge-plan"><?php echo htmlspecialchars(ucfirst($socio->membresia)); ?></span></td>
                                    <td><?php echo date('d/m/Y', strtotime($socio->fecha_registro)); ?></td>
                                    <td>
                                        <?php // Assuming a 'status' or 'estado' property might be added to $socio later, for now, using a placeholder or adapting 
                                        ?>
                                        <?php if (isset($socio->estado) && $socio->estado == 'activo'): ?>
                                            <span class="status-indicator status-active">Activo</span>
                                        <?php else: ?>
                                            <span class="status-indicator status-inactive">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div>
            <div class="chart-container">
                <h2>Ingresos (Últimos 6 Meses)</h2>
                <div class="chart-wrapper">
                    <canvas id="revenueChart"></canvas>
                </div>
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

<script>
    // Gráfico de Distribución de Membresías (Gráfico de Dona)
    const membershipCtx = document.getElementById('membershipChart').getContext('2d');
    const membershipChart = new Chart(membershipCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($labelsMembresias); ?>,
            datasets: [{
                data: <?php echo json_encode($dataMembresias); ?>,
                backgroundColor: <?php echo json_encode(array_slice($colorsMembresias, 0, count($labelsMembresias))); ?>,
                borderColor: '#ffffff',
                borderWidth: 2,
                hoverOffset: 15
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += context.raw + ' socios';
                            return label;
                        }
                    }
                }
            }
        }
    });

    // Gráfico de Ingresos (Gráfico de Línea)
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($labelsMeses); ?>,
            datasets: [{
                label: 'Ingresos ($)',
                data: <?php echo json_encode($dataIngresos); ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false
                    },
                    ticks: {
                        callback: function(value) {
                            return '$' + value;
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
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
                            return 'Ingresos: $' + context.raw;
                        }
                    }
                }
            }
        }
    });
</script>

<style>
    .chart-container {
        background: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .chart-wrapper {
        position: relative;
        height: 300px;
        width: 100%;
    }

    .chart-container h2 {
        margin-bottom: 20px;
        color: #333;
        font-size: 18px;
    }

    .members-table {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .members-table h2 {
        margin-bottom: 20px;
        color: #333;
        font-size: 18px;
    }
</style>
<?php
require_once 'templates/footer.php';
?>