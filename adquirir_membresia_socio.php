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
require_once "conexion/bd.php";

$id_socio = $_SESSION['id_usuario']; // id del socio logueado

// Obtener listado de membresias con su duración en días y precio
$sql = $conexion->prepare("SELECT id as id_membresia, nombre, duracion_dias,precio FROM membresias");
$sql->execute();
$membresias = $sql->fetchAll(PDO::FETCH_OBJ);
?>
<style>
    /* Estilos específicos de la página */
    :root {
        --danger: #e74c3c;
    }

    /* Sobrescribir .content para centrar el formulario */
    .content {
        padding: 20px;
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: calc(100vh - 140px);
    }

    /* Contenedor del formulario */
    .form-container {
        width: 100%;
        max-width: 600px;
        margin: 0 auto;
        padding: 0 20px;
    }

    /* Card del formulario */
    .form-card {
        background-color: var(--card);
        border-radius: 12px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        padding: 40px;
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
        margin-bottom: 40px;
    }

    .form-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--success), #27ae60);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        color: white;
        font-size: 2.5rem;
    }

    .form-header h1 {
        font-size: 2rem;
        color: var(--dark);
        margin-bottom: 10px;
        font-family: var(--font-main);
    }

    .form-header p {
        color: #6c757d;
        font-size: 1.1rem;
        line-height: 1.6;
    }

    /* Grupo de formulario */
    .form-group {
        margin-bottom: 30px;
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

    /* Estilos para el input date */
    .date-wrapper {
        position: relative;
    }

    .date-wrapper input {
        width: 100%;
        padding: 15px 20px;
        font-size: 1rem;
        font-family: var(--font-secondary);
        border: 2px solid #e9ecef;
        border-radius: 10px;
        transition: all 0.3s;
    }

    .date-wrapper input:focus {
        border-color: var(--accent);
        outline: none;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    }

    .date-wrapper i {
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
        justify-content: space-between;
        align-items: center;
        padding-top: 30px;
        border-top: 1px solid #eee;
        flex-wrap: wrap;
        gap: 15px;
    }

    .btn {
        padding: 14px 30px;
        border-radius: 8px;
        border: none;
        font-weight: 600;
        font-family: var(--font-secondary);
        cursor: pointer;
        transition: all 0.3s;
        font-size: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 150px;
    }

    .btn i {
        margin-right: 8px;
    }

    .btn-secondary {
        background-color: #6c757d;
        color: white;
    }

    .btn-secondary:hover {
        background-color: #5a6268;
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

    /* Alertas */
    .alert {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 30px;
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

    /* Estilos para inputs de número (monto) */
    .input-wrapper {
        position: relative;
    }

    .input-wrapper input[type="number"] {
        width: 100%;
        padding: 15px 20px 15px 45px;
        font-size: 1rem;
        font-family: var(--font-secondary);
        border: 2px solid #e9ecef;
        border-radius: 10px;
        transition: all 0.3s;
    }

    .input-wrapper input[type="number"]:focus {
        border-color: var(--accent);
        outline: none;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
    }

    .input-wrapper .input-icon {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--success);
        font-size: 1.1rem;
        pointer-events: none;
    }

    /* Nota de advertencia */
    .warning-notice {
        background: linear-gradient(135deg, #fff3cd, #ffeaa7);
        border-left: 5px solid var(--warning);
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 30px;
        box-shadow: 0 4px 15px rgba(243, 156, 18, 0.15);
        animation: pulseWarning 2s ease-in-out infinite;
    }

    @keyframes pulseWarning {

        0%,
        100% {
            box-shadow: 0 4px 15px rgba(243, 156, 18, 0.15);
        }

        50% {
            box-shadow: 0 6px 20px rgba(243, 156, 18, 0.25);
        }
    }

    .warning-notice .warning-header {
        display: flex;
        align-items: center;
        margin-bottom: 12px;
    }

    .warning-notice .warning-icon {
        width: 40px;
        height: 40px;
        background: var(--warning);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.3rem;
        margin-right: 15px;
        animation: shake 3s ease-in-out infinite;
    }

    @keyframes shake {

        0%,
        100% {
            transform: rotate(0deg);
        }

        10%,
        30% {
            transform: rotate(-5deg);
        }

        20%,
        40% {
            transform: rotate(5deg);
        }

        50%,
        90% {
            transform: rotate(0deg);
        }
    }

    .warning-notice h3 {
        margin: 0;
        color: #856404;
        font-size: 1.2rem;
        font-family: var(--font-main);
        font-weight: 700;
    }

    .warning-notice p {
        margin: 0;
        color: #856404;
        font-size: 1rem;
        line-height: 1.6;
        font-weight: 600;
    }

    .warning-notice strong {
        color: #664d03;
        font-weight: 800;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .form-container {
            padding: 0 15px;
        }
    }

    @media (max-width: 768px) {
        .form-card {
            padding: 30px 25px;
        }

        .form-header h1 {
            font-size: 1.8rem;
        }

        .form-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .btn {
            width: 100%;
        }
    }

    @media (max-width: 576px) {
        .form-card {
            padding: 25px 20px;
        }

        .form-icon {
            width: 70px;
            height: 70px;
            font-size: 2rem;
        }

        .form-header h1 {
            font-size: 1.6rem;
        }

        .form-header p {
            font-size: 1rem;
        }

        .form-group label {
            font-size: 1rem;
        }

        .select-wrapper select,
        .date-wrapper input {
            padding: 12px 15px;
            font-size: 0.95rem;
        }
    }
</style>

<div class="form-container">
    <!-- Card del formulario -->
    <div class="form-card">
        <div class="form-header">
            <div class="form-icon">
                <i class="fas fa-user-plus"></i>
            </div>
            <h1>Registrar Nueva Membresía</h1>
            <p>Completa los siguientes campos para registrar una nueva membresía para un socio.</p>
        </div>

        <form id="membership-form" action="controladores/adquirir_membresia_socio.php" method="POST">
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
            <input type="hidden" name="id_socio" value="<?php echo $id_socio; ?>">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <!-- Campo: Tipo de Membresía -->
            <div class="form-group">
                <label for="tipo_membresia">Tipo de Membresía <span>*</span></label>
                <div class="select-wrapper">
                    <select id="tipo_membresia" name="membresia" required>
                        <option value="">-- Seleccione un tipo de membresía --</option>
                        <?php foreach ($membresias as $item): ?>
                            <option value="<?php echo $item->id_membresia; ?>"><?php echo $item->nombre; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>

            <!-- Campo: Fecha de Inicio -->
            <div class="form-group">
                <label for="fecha_inicio">Fecha de Inicio <span>*</span></label>
                <div class="date-wrapper">
                    <input type="date" id="fecha_inicio" name="fecha_inicio" required>
                    <i class="fas fa-calendar-alt"></i>
                </div>
            </div>

            <div class="form-group">
                <label for="fecha_fin">Fecha de Vencimiento <span>*</span></label>
                <div class="date-wrapper">
                    <input type="date" id="fecha_fin" name="fecha_fin" required readonly>
                    <i class="fas fa-calendar-alt"></i>
                </div>
            </div>

            <!-- Campo: Monto a Pagar -->
            <div class="form-group">
                <label for="monto">Monto a Pagar <span>*</span></label>
                <div class="input-wrapper">
                    <i class="fas fa-dollar-sign input-icon"></i>
                    <input type="number" id="monto" name="monto" readonly step="0.01" min="0" placeholder="0.00" required>
                </div>
            </div>

            <!-- Campo: Método de Pago -->
            <div class="form-group">
                <label for="metodo_pago">Método de Pago <span>*</span></label>
                <div class="select-wrapper">
                    <select id="metodo_pago" name="metodo_pago" required>
                        <option value="">-- Seleccione un método de pago --</option>
                        <option value="efectivo">💵 Efectivo</option>
                        <option value="transferencia">🏦 Transferencia Bancaria</option>
                        <option value="tarjeta">💳 Tarjeta de Crédito/Débito</option>
                    </select>
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>

            <!-- Nota de Advertencia -->
            <div class="warning-notice">
                <div class="warning-header">
                    <div class="warning-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h3>⚠️ Importante - Plazo de Pago</h3>
                </div>
                <p>
                    Una vez adquirida la membresía, tiene un plazo de <strong>48 horas</strong> para acercarse al gimnasio y realizar el pago correspondiente.
                    <strong>Caso contrario, se le anulará la membresía automáticamente.</strong>
                </p>
            </div>

            <div class="form-actions">
                <a href="membresia_socio.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary" id="submit-btn">
                    <i class="fas fa-save"></i> Registrar Membresía
                </button>
            </div>
        </form>
    </div>
</div>
</div>
</div>
</div>
</div>

<script>
    // ============================================================================
    // PASO 1: Crear un objeto JavaScript con las duraciones de cada membresía
    // ============================================================================
    // Este código PHP genera un objeto JavaScript dinámicamente desde la base de datos
    // Por ejemplo, si tienes 4 membresías, generará algo como:
    // { "1": 30, "2": 90, "3": 30, "4": 365 }
    // Donde la clave es el ID de la membresía y el valor son los días de duración
    const membresiaDuraciones = <?php
                                // Crear un array asociativo vacío en PHP
                                $duraciones = [];

                                // Recorrer todas las membresías obtenidas de la base de datos
                                foreach ($membresias as $item) {
                                    // Guardar en el array: ID de membresía => duración en días
                                    // Ejemplo: $duraciones[1] = 30 (Mensual dura 30 días)
                                    $duraciones[$item->id_membresia] = $item->duracion_dias;
                                }

                                // Convertir el array PHP a formato JSON para que JavaScript pueda usarlo
                                // json_encode() convierte: ["1" => 30] en {"1": 30}
                                echo json_encode($duraciones);
                                ?>;

    // ============================================================================
    // PASO 1.5: Crear un objeto JavaScript con los precios de cada membresía
    // ============================================================================
    // Similar al objeto de duraciones, este almacena el precio de cada membresía
    // Por ejemplo: { "1": 29.99, "2": 79.99, "3": 49.99, "4": 299.99 }
    const membresiasPrecios = <?php
                                // Crear un array asociativo vacío para los precios
                                $precios = [];

                                // Recorrer todas las membresías obtenidas de la base de datos
                                foreach ($membresias as $item) {
                                    // Guardar en el array: ID de membresía => precio
                                    // Ejemplo: $precios[1] = 29.99 (Mensual cuesta $29.99)
                                    $precios[$item->id_membresia] = $item->precio;
                                }

                                // Convertir el array PHP a formato JSON
                                echo json_encode($precios);
                                ?>;

    // ============================================================================
    // PASO 2: Obtener referencias a los elementos del formulario HTML
    // ============================================================================
    // Guardamos en variables los 4 campos del formulario que vamos a usar:
    const tipoMembresiaSelect = document.getElementById('tipo_membresia'); // Select de tipo de membresía
    const fechaInicioInput = document.getElementById('fecha_inicio'); // Input de fecha de inicio
    const fechaFinInput = document.getElementById('fecha_fin'); // Input de fecha de vencimiento (readonly)
    const montoInput = document.getElementById('monto'); // Input de monto a pagar (readonly)

    // ============================================================================
    // PASO 3: Función principal que calcula la fecha de vencimiento y el monto
    // ============================================================================
    function calcularDatosMembresia() {
        // 3.1 - Obtener los valores actuales de los campos
        const tipoMembresia = tipoMembresiaSelect.value; // ID de la membresía seleccionada (ej: "1", "2", "3", "4")
        const fechaInicio = fechaInicioInput.value; // Fecha seleccionada en formato YYYY-MM-DD (ej: "2025-01-15")

        // 3.2 - Si se seleccionó una membresía, actualizar el MONTO automáticamente
        if (tipoMembresia) {
            // Buscar el precio de la membresía seleccionada
            const precio = membresiasPrecios[tipoMembresia];

            if (precio) {
                // Establecer el precio en el campo de monto
                // parseFloat asegura que sea un número y toFixed(2) lo formatea a 2 decimales
                montoInput.value = parseFloat(precio).toFixed(2);
            } else {
                // Si no encontramos el precio, limpiamos el campo
                montoInput.value = '';
            }
        } else {
            // Si no hay membresía seleccionada, limpiamos el monto
            montoInput.value = '';
        }

        // 3.3 - Verificar que el usuario haya seleccionado AMBOS campos para calcular fecha
        // Si falta alguno, no podemos calcular la fecha de vencimiento
        if (tipoMembresia && fechaInicio) {

            // 3.4 - Buscar cuántos días dura la membresía seleccionada
            // Usamos el ID de la membresía como clave en nuestro objeto
            // Ejemplo: si tipoMembresia = "1" (Mensual), duracionDias = 30
            const duracionDias = membresiaDuraciones[tipoMembresia];

            // 3.5 - Verificar que encontramos la duración (por seguridad)
            if (duracionDias) {

                // 3.6 - Crear un objeto Date de JavaScript con la fecha de inicio
                // Agregamos 'T00:00:00' para evitar problemas de zona horaria
                // Ejemplo: "2025-01-15" se convierte en Date("2025-01-15T00:00:00")
                const fecha = new Date(fechaInicio + 'T00:00:00');

                // 3.7 - SUMAR los días de duración a la fecha de inicio
                // fecha.getDate() obtiene el día actual (ej: 15)
                // Le sumamos duracionDias (ej: 30)
                // fecha.setDate() establece el nuevo día (ej: 45, que JavaScript convierte automáticamente a 14 de febrero)
                // parseInt() asegura que duracionDias sea un número entero
                fecha.setDate(fecha.getDate() + parseInt(duracionDias));

                // 3.8 - Formatear la fecha calculada al formato que necesita el input (YYYY-MM-DD)

                // Obtener el año (ej: 2025)
                const year = fecha.getFullYear();

                // Obtener el mes (0-11, por eso sumamos 1) y agregar un 0 al inicio si es necesario
                // Ejemplo: mes 2 se convierte en "02"
                const month = String(fecha.getMonth() + 1).padStart(2, '0');

                // Obtener el día y agregar un 0 al inicio si es necesario
                // Ejemplo: día 5 se convierte en "05"
                const day = String(fecha.getDate()).padStart(2, '0');

                // Unir todo en formato YYYY-MM-DD
                // Ejemplo: "2025-02-14"
                const fechaFormateada = `${year}-${month}-${day}`;

                // 3.9 - Establecer la fecha calculada en el campo de vencimiento
                // Como el campo tiene readonly, el usuario verá la fecha pero no podrá modificarla
                fechaFinInput.value = fechaFormateada;

            } else {
                // Si no encontramos la duración, limpiamos el campo de vencimiento
                fechaFinInput.value = '';
            }
        } else {
            // Si falta el tipo de membresía o la fecha de inicio, limpiamos el campo de vencimiento
            fechaFinInput.value = '';
        }
    }

    // ============================================================================
    // PASO 4: Configurar los eventos que disparan el cálculo automático
    // ============================================================================

    // Cuando el usuario CAMBIA el tipo de membresía en el select, recalcular fecha y monto
    tipoMembresiaSelect.addEventListener('change', calcularDatosMembresia);

    // Cuando el usuario CAMBIA la fecha de inicio, recalcular fecha de vencimiento
    fechaInicioInput.addEventListener('change', calcularDatosMembresia);

    // ============================================================================
    // PASO 5: Funcionalidad adicional para el sidebar (menú lateral)
    // ============================================================================
    // Toggle sidebar - permite mostrar/ocultar el menú lateral en dispositivos móviles
    document.querySelector('.toggle-sidebar')?.addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('active');
        document.querySelector('.main-content').classList.toggle('active');
    });
</script>
<?php require_once "templates/footer.php"; ?>