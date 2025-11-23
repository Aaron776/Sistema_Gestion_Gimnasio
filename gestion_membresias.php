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

// Obtener membresias de la base de datos
$sql = $conexion->prepare("SELECT id as id_membresia,nombre,descripcion,precio,duracion_dias as duracion FROM membresias");
$sql->execute();
$membresias = $sql->fetchAll(PDO::FETCH_OBJ);
?>

<style>
    /* Estilos específicos para la gestión de membresías */
    .memberships-container {
        padding: 20px;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
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

    .btn-secondary {
        background-color: #6c757d;
        color: white;
    }

    .btn-secondary:hover {
        background-color: #5a6268;
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

    /* Grid de tarjetas */
    .memberships-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 25px;
        margin-top: 20px;
    }

    .membership-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        padding: 25px;
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
        position: relative;
        overflow: hidden;
    }

    .membership-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
    }

    .membership-card.premium {
        border-top: 4px solid var(--warning);
        background: linear-gradient(135deg, #fff9e6 0%, #ffffff 100%);
    }

    .membership-card.standard {
        border-top: 4px solid var(--info);
    }

    .membership-card.basic {
        border-top: 4px solid var(--success);
    }

    .membership-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 15px;
    }

    .membership-title {
        font-size: 1.4rem;
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 5px;
        font-family: var(--font-main);
    }

    .membership-status {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-active {
        background: #d4edda;
        color: #155724;
    }

    .status-inactive {
        background: #f8d7da;
        color: #721c24;
    }

    .membership-price {
        font-size: 2rem;
        font-weight: 700;
        color: var(--secondary);
        margin-bottom: 10px;
        font-family: var(--font-main);
    }

    .membership-duration {
        color: #6c757d;
        font-size: 0.9rem;
        margin-bottom: 15px;
    }

    .membership-description {
        color: #495057;
        line-height: 1.5;
        margin-bottom: 20px;
        min-height: 60px;
    }

    .membership-benefits {
        margin-bottom: 25px;
    }

    .benefits-title {
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 10px;
        font-size: 0.95rem;
    }

    .benefits-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .benefits-list li {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 6px 0;
        color: #495057;
        font-size: 0.9rem;
    }

    .benefits-list li i {
        color: var(--success);
        font-size: 0.8rem;
    }

    .membership-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        padding-top: 15px;
        border-top: 1px solid #e9ecef;
    }

    .btn-sm {
        padding: 8px 16px;
        font-size: 0.85rem;
    }

    .no-memberships {
        grid-column: 1 / -1;
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
    }

    .no-memberships i {
        font-size: 4rem;
        margin-bottom: 20px;
        color: #dee2e6;
    }

    .no-memberships h3 {
        font-size: 1.5rem;
        margin-bottom: 10px;
        color: #495057;
    }

    .no-memberships p {
        font-size: 1rem;
        margin-bottom: 20px;
    }

    .membership-badge {
        position: absolute;
        top: 15px;
        right: -30px;
        background: var(--secondary);
        color: white;
        padding: 5px 30px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        transform: rotate(45deg);
    }

    .price-period {
        font-size: 0.9rem;
        color: #6c757d;
        font-weight: normal;
    }

    /* Modal de confirmación */
    .modal {
        display: none;
        position: fixed;
        z-index: 1050;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
        background-color: white;
        margin: 10% auto;
        padding: 0;
        border-radius: 8px;
        width: 90%;
        max-width: 400px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        animation: modalShow 0.3s;
    }

    @keyframes modalShow {
        from {
            opacity: 0;
            transform: translateY(-50px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .modal-header {
        padding: 15px 20px;
        background: var(--primary);
        color: white;
        border-radius: 8px 8px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-header h3 {
        margin: 0;
        font-family: var(--font-main);
        font-size: 1.2rem;
    }

    .close {
        color: white;
        font-size: 1.5rem;
        font-weight: bold;
        cursor: pointer;
        background: none;
        border: none;
    }

    .close:hover {
        color: #ccc;
    }

    .modal-body {
        padding: 20px;
        text-align: center;
    }

    .modal-icon {
        font-size: 3rem;
        color: var(--secondary);
        margin-bottom: 15px;
    }

    .modal-footer {
        padding: 15px 20px;
        background: #f8f9fa;
        border-radius: 0 0 8px 8px;
        display: flex;
        justify-content: center;
        gap: 10px;
    }

    .btn-cancel {
        background: #6c757d;
        color: white;
    }

    .btn-cancel:hover {
        background: #5a6268;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .memberships-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .page-header {
            flex-direction: column;
            gap: 15px;
            align-items: flex-start;
        }

        .membership-card {
            padding: 20px;
        }

        .membership-actions {
            flex-direction: column;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 480px) {
        .page-header h1 {
            font-size: 1.5rem;
        }

        .memberships-container {
            padding: 10px;
        }

        .membership-title {
            font-size: 1.2rem;
        }

        .membership-price {
            font-size: 1.5rem;
        }
    }
</style>
<div class="memberships-container">
    <div class="page-header">
        <h1>Membresías del Gimnasio</h1>
        <a href="agregar_membresia.php" type="button" class="btn btn-primary" id="btnNuevaMembresia">
            <i class="fas fa-plus"></i> Nueva Membresía
        </a>
    </div>

    <!-- Grid de Membresías -->
    <div class="memberships-grid" id="membershipsGrid">
        <?php if (empty($membresias)): ?>
            <div class="no-memberships">
                <i class="fas fa-id-card"></i>
                <h3>No hay membresías registradas</h3>
                <p>Comienza creando tu primera membresía para ofrecer a los miembros del gimnasio.</p>
                <button class="btn btn-primary">
                    <i class="fas fa-plus"></i> Crear Primera Membresía
                </button>
            </div>
        <?php else: ?>
            <?php foreach ($membresias as $item) { ?>
                <div class="membership-card 
                <?php 
                if($item->nombre == "Mensual"){
                    echo 'basic';
                }elseif($item->nombre == "Trimestral"){
                    echo 'standard';
                }elseif($item->nombre == "Premium"){
                    echo 'premium';
                }elseif($item->nombre == "Anual"){
                    echo 'standard';
                }   
                ?>">
                    <?php if ($item->nombre == "Mensual") { ?>
                        <div class="membership-badge">Popular</div>
                    <?php } elseif ($item->nombre == "Premium") { ?>
                        <div class="membership-badge">Recomendado</div>
                    <?php } ?>

                    <div class="membership-header">
                        <div>
                            <h3 class="membership-title">Membresía <?php echo htmlspecialchars($item->nombre); ?></h3>
                            <span class="membership-status status-active">
                                Activa
                            </span>
                        </div>
                    </div>

                    <div class="membership-price">
                        $<?php echo $item->precio; ?>
                        <span class="price-period">/mes</span>
                    </div>

                    <div class="membership-duration">
                        <i class="fas fa-calendar-alt"></i>
                        Duración: <?php echo $item->duracion; ?> días
                    </div>

                    <div class="membership-description">
                        <?php echo $item->descripcion; ?>.
                    </div>

                    <div class="membership-benefits">
                        <div class="benefits-title">Beneficios incluidos:</div>

                        <?php if ($item->nombre == "Mensual") { ?>
                            <ul class="benefits-list">
                                <li>
                                    <i class="fas fa-check"></i>
                                    Acceso a área de cardio
                                </li>
                                <li>
                                    <i class="fas fa-check"></i>
                                    Acceso a área de pesas
                                </li>
                                <li>
                                    <i class="fas fa-check"></i>
                                    Lockers disponibles
                                </li>
                                <li>
                                    <i class="fas fa-check"></i>
                                    Asesoría básica
                                </li>
                            </ul>
                        <?php } elseif ($item->nombre == "Trimestral") { ?>
                            <ul class="benefits-list">
                                <li>
                                    <i class="fas fa-check"></i>
                                    Todos los beneficios Básicos
                                </li>
                                <li>
                                    <i class="fas fa-check"></i>
                                    Acceso a clases grupales
                                </li>
                                <li>
                                    <i class="fas fa-check"></i>
                                    Horario extendido
                                </li>
                                <li>
                                    <i class="fas fa-check"></i>
                                    Plan nutricional básico
                                </li>
                                <li>
                                    <i class="fas fa-check"></i>
                                    1 sesión de evaluación mensual
                                </li>
                            </ul>
                        <?php } elseif ($item->nombre == "Premium") { ?>
                            <ul class="benefits-list">
                                <li>
                                    <i class="fas fa-check"></i>
                                    Todos los beneficios Estándar
                                </li>
                                <li>
                                    <i class="fas fa-check"></i>
                                    Entrenamiento personalizado
                                </li>
                                <li>
                                    <i class="fas fa-check"></i>
                                    Acceso ilimitado a clases
                                </li>
                                <li>
                                    <i class="fas fa-check"></i>
                                    Plan nutricional personalizado
                                </li>
                                <li>
                                    <i class="fas fa-check"></i>
                                    Acceso a área VIP
                                </li>
                                <li>
                                    <i class="fas fa-check"></i>
                                    Toalla y locker premium
                                </li>
                            </ul>
                        <?php } elseif ($item->nombre == "Anual") { ?>
                            <ul class="benefits-list">
                                <li>
                                    <i class="fas fa-check"></i>
                                    Todos los beneficios Premium
                                </li>
                                <li>
                                    <i class="fas fa-check"></i>
                                    2 meses gratis
                                </li>
                                <li>
                                    <i class="fas fa-check"></i>
                                    Congelación gratuita
                                </li>
                                <li>
                                    <i class="fas fa-check"></i>
                                    Invitados gratuitos
                                </li>
                            </ul>
                        <?php } ?>
                    </div>

                    <div class="membership-actions">
                        <button class="btn btn-edit btn-sm">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <form action="controladores/eliminar_membresia.php" method="POST" style="display:inline;">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="id_membresia" value="<?php echo $item->id_membresia; ?>">
                            <button type="submit" class="btn btn-delete btn-sm" onclick="return confirm('¿Estás seguro de que quieres eliminar esta membresía?');">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </form>
                    </div>
                </div>
            <?php } ?>
        <?php endif; ?>
    </div>
</div>
</div>
</div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle sidebar
        document.querySelector('.toggle-sidebar').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
            document.querySelector('.main-content').classList.toggle('active');
        });

        // Cerrar modal
        document.querySelector('.close').addEventListener('click', closeModal);
    });

    let currentMembershipId = null;

    // Efectos hover mejorados para las tarjetas
    const cards = document.querySelectorAll('.membership-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px) scale(1.02)';
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
</script>
<?php require_once "templates/footer.php"; ?>