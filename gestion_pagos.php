<?php
require_once "autorizacion/auth.php"; // valida login y arranca sesión

// Generar token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Verificar que tenga rol de admin
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: index.php"); // si no lo mandamos al login
    exit();
}

require_once "templates/header.php";
include_once "conexion/bd.php";

// Obtener los pago desde la base de datos
$sql=$conexion->prepare("SELECT usuarios.id as id_socio, CONCAT(usuarios.nombre,' ',usuarios.apellido) as nombre_socio,membresias.nombre as nombre_membresia,monto,fecha_pago,metodo_pago FROM pagos INNER JOIN usuarios ON pagos.id_usuario = usuarios.id INNER JOIN membresias ON pagos.id_membresia = membresias.id ORDER BY fecha_pago DESC");
$sql->execute();
$pagos=$sql->fetchAll(PDO::FETCH_OBJ);

// Total de pagos
$sql=$conexion->prepare("SELECT SUM(monto) as total FROM pagos");
$sql->execute();
$totalPagos=$sql->fetch(PDO::FETCH_OBJ);

// Nueva consulta para obtener la cantidad de pagos por mes
$sqlPagosPorMes = $conexion->prepare("
    SELECT DATE_FORMAT(fecha_pago, '%Y-%m') AS mes, COUNT(*) AS cantidad_pagos
    FROM pagos
    GROUP BY mes
    ORDER BY mes ASC
");
$sqlPagosPorMes->execute();
$pagosPorMes = $sqlPagosPorMes->fetchAll(PDO::FETCH_OBJ);

// Encontrar los pagos del mes actual
$mesActual = date('Y-m');
$pagosMesActual = 0;
foreach ($pagosPorMes as $pago) {
    if ($pago->mes == $mesActual) {
        $pagosMesActual = $pago->cantidad_pagos;
        break;
    }
}
?>
<style>
    /* Estilos específicos para la página de pagos */
    .payments-container {
        background-color: var(--card);
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        padding: 25px;
        margin-bottom: 20px;
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

    .date-filters {
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .date-input {
        padding: 10px 15px;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        font-size: 0.9rem;
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

    /* Badges de pago */
    .payment-badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
        background: #e9ecef;
        color: #495057;
    }

    .payment-cash {
        background: #d4edda;
        color: #155724;
    }

    .payment-card {
        background: #d1ecf1;
        color: #0c5460;
    }

    .payment-transfer {
        background: #e2e3e5;
        color: #383d41;
    }

    /* Total Section */
    .total-section {
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        border-radius: 8px;
        padding: 25px;
        color: white;
        margin-top: 20px;
    }

    .total-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        text-align: center;
    }

    .total-item {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .total-label {
        font-size: 0.9rem;
        opacity: 0.9;
        text-transform: uppercase;
        font-weight: 600;
    }

    .total-value {
        font-size: 2rem;
        font-weight: 700;
        font-family: var(--font-main);
    }

    .total-subtext {
        font-size: 0.8rem;
        opacity: 0.8;
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

    /* Monto */
    .amount {
        font-weight: 600;
        color: var(--dark);
        font-size: 1rem;
    }

    .amount-positive {
        color: var(--success);
    }

    /* Acciones */
    .action-buttons {
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
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

    .btn-receipt {
        background-color: var(--success);
        color: white;
    }

    .btn-receipt:hover {
        background-color: #218838;
    }

    /* Responsive específico para pagos */
    @media (max-width: 768px) {
        .payments-container {
            padding: 20px;
        }

        .filters-section {
            flex-direction: column;
            align-items: stretch;
        }

        .search-box,
        .filter-select,
        .date-input {
            min-width: 100%;
        }

        .date-filters {
            flex-direction: column;
        }

        .total-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 480px) {
        .payments-container {
            padding: 15px;
        }

        .table {
            font-size: 0.8rem;
        }

        .table th,
        .table td {
            padding: 8px 6px;
        }

        .total-value {
            font-size: 1.5rem;
        }
    }
</style>

<!-- Contenido de la página -->
<div class="content">
    <div class="payments-container">
        <!-- Filtros y Búsqueda -->
        <div class="filters-section">
            <div class="search-box">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="search-input" placeholder="Buscar socio..." id="searchInput">
            </div>
            <select class="filter-select" id="statusFilter">
                <option value="">Todos los estados</option>
                <option value="paid">Pagado</option>
                <option value="pending">Pendiente</option>
                <option value="overdue">Vencido</option>
            </select>
            <select class="filter-select" id="methodFilter">
                <option value="">Todos los métodos</option>
                <option value="cash">Efectivo</option>
                <option value="card">Tarjeta</option>
                <option value="transfer">Transferencia</option>
            </select>
            <div class="date-filters">
                <input type="date" class="date-input" id="startDate" value="2024-03-01">
                <span>a</span>
                <input type="date" class="date-input" id="endDate" value="2024-03-31">
            </div>
            <button class="btn btn-secondary" onclick="aplicarFiltros()">
                <i class="fas fa-filter"></i> Filtrar
            </button>
        </div>

        <!-- Tabla de Pagos -->
        <div class="table-container">
            <table class="table" id="paymentsTable">
                <thead>
                    <tr>
                        <th>Socio</th>
                        <th>Membresía</th>
                        <th>Monto</th>
                        <th>Fecha y Hora de Pago</th>
                        <th>Método de Pago</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pagos as $item) { ?>
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
                        <td><?php echo htmlspecialchars($item->nombre_membresia); ?></td>
                        <td class="amount amount-positive">$<?php echo htmlspecialchars($item->monto); ?></td>
                        <td>
                            <?php 
                            // Formatear fecha y hora de forma legible
                            $fecha = new DateTime($item->fecha_pago);
                            $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                            $dia = $fecha->format('d');
                            $mes = $meses[(int)$fecha->format('m') - 1];
                            $anio = $fecha->format('Y');
                            $hora = $fecha->format('H:i');
                            echo htmlspecialchars("$dia $mes $anio, $hora");
                            ?>
                        </td>
                        <td>
                            <?php if ($item->metodo_pago == 'efectivo') : ?>
                            <span class="payment-badge payment-cash">Efectivo</span>
                            <?php elseif ($item->metodo_pago == 'tarjeta') : ?>
                            <span class="payment-badge payment-card">Tarjeta</span>
                            <?php elseif ($item->metodo_pago == 'transferencia') : ?>
                            <span class="payment-badge payment-transfer">Transferencia</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-icon btn-receipt" title="Descargar recibo">
                                    <i class="fas fa-receipt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <!-- Sección de Total -->
        <div class="total-section">
            <div class="total-grid">
                <div class="total-item">
                    <span class="total-label">Total General</span>
                    <span class="total-value" id="totalGeneral">$<?php echo htmlspecialchars($totalPagos->total); ?></span>
                    <span class="total-subtext">Suma total</span>
                </div>
                <div class="total-item">
                    <span class="total-label">Pagos del Mes</span>
                    <span class="total-value" id="totalPagos"><?php echo htmlspecialchars($pagosMesActual); ?></span>
                    <span class="total-subtext">Transacciones</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Inicialización
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle sidebar
        const toggleBtn = document.querySelector('.toggle-sidebar');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                document.querySelector('.sidebar').classList.toggle('active');
                document.querySelector('.main-content').classList.toggle('active');
            });
        }

        // Filtros y búsqueda
        document.getElementById('searchInput').addEventListener('input', filtrarPagos);
        document.getElementById('statusFilter').addEventListener('change', filtrarPagos);
        document.getElementById('methodFilter').addEventListener('change', filtrarPagos);

        // Calcular totales iniciales
        calcularTotales();
    });

    // Filtrar pagos
    function filtrarPagos() {
        const search = document.getElementById('searchInput').value.toLowerCase();
        const statusFilter = document.getElementById('statusFilter').value;
        const methodFilter = document.getElementById('methodFilter').value;
        const rows = document.querySelectorAll('#paymentsTable tbody tr');

        rows.forEach(row => {
            const memberName = row.cells[0].textContent.toLowerCase();
            const status = row.cells[5].textContent.toLowerCase();
            const method = row.cells[4].textContent.toLowerCase();

            const matchesSearch = memberName.includes(search);
            const matchesStatus = !statusFilter || status.includes(statusFilter);
            const matchesMethod = !methodFilter || method.includes(methodFilter);

            row.style.display = matchesSearch && matchesStatus && matchesMethod ? '' : 'none';
        });

        calcularTotales();
    }

    // Aplicar filtros de fecha
    function aplicarFiltros() {
        const startDate = new Date(document.getElementById('startDate').value);
        const endDate = new Date(document.getElementById('endDate').value);
        const rows = document.querySelectorAll('#paymentsTable tbody tr');

        rows.forEach(row => {
            const fechaPagoText = row.cells[3].textContent;
            const fechaPago = parseFecha(fechaPagoText);

            if (fechaPago >= startDate && fechaPago <= endDate) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        calcularTotales();
        alert('Filtros de fecha aplicados correctamente');
    }

    // Parsear fecha "DD MMM YYYY, HH:MM"
    function parseFecha(fechaStr) {
        const meses = {
            'Ene': 0, 'Feb': 1, 'Mar': 2, 'Abr': 3, 'May': 4, 'Jun': 5,
            'Jul': 6, 'Ago': 7, 'Sep': 8, 'Oct': 9, 'Nov': 10, 'Dic': 11
        };
        const partes = fechaStr.split(',')[0].trim().split(' ');
        const dia = parseInt(partes[0]);
        const mes = meses[partes[1]];
        const anio = parseInt(partes[2]);
        return new Date(anio, mes, dia);
    }

    // Calcular totales
    function calcularTotales() {
        let totalRecaudado = 0;
        let totalPendientes = 0;
        let totalPagosCount = 0;
        const rows = document.querySelectorAll('#paymentsTable tbody tr');

        rows.forEach(row => {
            if (row.style.display !== 'none') {
                const montoText = row.cells[2].textContent.replace('$', '').trim();
                const monto = parseFloat(montoText);
                const estado = row.cells[5].textContent.toLowerCase();

                if (estado.includes('pagado')) {
                    totalRecaudado += monto;
                } else {
                    totalPendientes += monto;
                }
                totalPagosCount++;
            }
        });

        const totalGeneral = totalRecaudado + totalPendientes;

        // Actualizar UI
        const totalRecaudadoEl = document.getElementById('totalRecaudado');
        const totalPendientesEl = document.getElementById('totalPendientes');
        const totalGeneralEl = document.getElementById('totalGeneral');
        const totalPagosEl = document.getElementById('totalPagos');

        if (totalRecaudadoEl) totalRecaudadoEl.textContent = `$${totalRecaudado.toFixed(2)}`;
        if (totalPendientesEl) totalPendientesEl.textContent = `$${totalPendientes.toFixed(2)}`;
        if (totalGeneralEl) totalGeneralEl.textContent = `$${totalGeneral.toFixed(2)}`;
        if (totalPagosEl) totalPagosEl.textContent = totalPagosCount;
    }

    // Funciones de botones de acción
    document.querySelectorAll('.btn-receipt').forEach(btn => {
        btn.addEventListener('click', function() {
            const memberName = this.closest('tr').querySelector('.member-details h4').textContent;
            alert(`Generando recibo para: ${memberName}`);
            // Aquí iría la lógica para generar el recibo PDF
        });
    });
</script>

<?php include_once 'templates/footer.php'; ?>
