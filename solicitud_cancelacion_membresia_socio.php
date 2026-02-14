 <?php
    require_once "autorizacion/auth.php"; // valida login y arranca sesión

    // Generar token CSRF si no existe
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    // Verificar que tenga rol de socio
    if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'socio') {
        header("Location: acceso_denegado.php"); // si no lo mandamos al acceso denegado
        exit();
    }
    require_once "templates/header.php";
    include_once "conexion/bd.php";

    $id_socio = $_SESSION['id_usuario']; // id del socio logueado

    if ($id_socio == null || $id_socio == "" || $id_socio == 0 || $id_socio == "0") {
        header("Location: membresia_socio.php"); // si no lo mandamos al login
        exit();
    }

    // Obtener la membresia de este usuario socio
    $sql = $conexion->prepare("SELECT id as id_membresia_usuario FROM membresia_usuario WHERE id_usuario = :id_usuario");
    $sql->bindparam(":id_usuario", $id_socio);
    $sql->execute();
    $membresia = $sql->fetch(PDO::FETCH_OBJ);

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

     /* Alertas */
     .alert {
         padding: 15px 20px;
         border-radius: 8px;
         margin-bottom: 30px;
         display: flex;
         align-items: center;
         font-weight: 600;
     }

     .alert-warning {
         background-color: #fff3cd;
         color: #856404;
         border-left: 4px solid var(--warning);
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

     /* Contenedor del formulario */
     .form-container {
         max-width: 800px;
         margin: 0 auto;
         padding: 0 20px;
     }

     /* Card principal */
     .cancel-card {
         background-color: var(--card);
         border-radius: 12px;
         box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
         overflow: hidden;
         margin-bottom: 30px;
     }

     .card-header {
         background: linear-gradient(135deg, #f8d7da, #f8d7da);
         padding: 25px 30px;
         border-bottom: 1px solid #f1aeb5;
     }

     .card-header h2 {
         font-size: 1.8rem;
         color: #721c24;
         margin-bottom: 10px;
         font-family: var(--font-main);
         display: flex;
         align-items: center;
     }

     .card-header h2 i {
         margin-right: 15px;
         font-size: 2rem;
     }

     .card-header p {
         color: #721c24;
         opacity: 0.9;
         line-height: 1.6;
     }

     .card-body {
         padding: 40px 30px;
     }

     /* Información importante */
     .info-box {
         background-color: #f8f9fa;
         border-radius: 10px;
         padding: 25px;
         margin-bottom: 30px;
         border-left: 4px solid var(--warning);
     }

     .info-box h3 {
         font-size: 1.3rem;
         color: var(--dark);
         margin-bottom: 15px;
         font-family: var(--font-main);
         display: flex;
         align-items: center;
     }

     .info-box h3 i {
         color: var(--warning);
         margin-right: 10px;
     }

     .info-list {
         list-style: none;
     }

     .info-list li {
         padding: 10px 0;
         display: flex;
         align-items: flex-start;
     }

     .info-list li i {
         color: var(--warning);
         margin-right: 10px;
         margin-top: 3px;
     }

     /* Formulario */
     .form-group {
         margin-bottom: 30px;
     }

     .form-group label {
         display: block;
         margin-bottom: 15px;
         font-weight: 600;
         color: var(--dark);
         font-size: 1.1rem;
         font-family: var(--font-main);
     }

     .form-group label span {
         color: var(--danger);
     }

     .textarea-wrapper {
         position: relative;
     }

     textarea.form-control {
         width: 100%;
         min-height: 200px;
         padding: 20px;
         border: 2px solid #e9ecef;
         border-radius: 10px;
         font-size: 1rem;
         font-family: var(--font-secondary);
         line-height: 1.6;
         resize: vertical;
         transition: all 0.3s;
     }

     textarea.form-control:focus {
         border-color: var(--accent);
         outline: none;
         box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
     }

     .char-count {
         position: absolute;
         bottom: 15px;
         right: 20px;
         font-size: 0.85rem;
         color: #6c757d;
         background-color: rgba(255, 255, 255, 0.9);
         padding: 3px 8px;
         border-radius: 4px;
     }

     .form-hint {
         font-size: 0.9rem;
         color: #6c757d;
         margin-top: 10px;
         display: flex;
         align-items: center;
     }

     .form-hint i {
         margin-right: 8px;
         color: var(--accent);
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

     .btn-cancel {
         background-color: #6c757d;
         color: white;
     }

     .btn-cancel:hover {
         background-color: #5a6268;
     }

     .btn-submit {
         background: linear-gradient(135deg, var(--danger), #c0392b);
         color: white;
     }

     .btn-submit:hover {
         background: linear-gradient(135deg, #c0392b, var(--danger));
         transform: translateY(-2px);
         box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
     }

     .btn-submit:disabled {
         opacity: 0.6;
         cursor: not-allowed;
         transform: none !important;
         box-shadow: none !important;
     }

     /* Modal de confirmación */
     .modal-overlay {
         display: none;
         position: fixed;
         top: 0;
         left: 0;
         width: 100%;
         height: 100%;
         background-color: rgba(0, 0, 0, 0.5);
         z-index: 2000;
         justify-content: center;
         align-items: center;
         animation: fadeIn 0.3s ease-out;
     }

     @keyframes fadeIn {
         from {
             opacity: 0;
         }

         to {
             opacity: 1;
         }
     }

     .modal {
         background-color: white;
         border-radius: 12px;
         width: 90%;
         max-width: 500px;
         box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
         animation: slideUp 0.3s ease-out;
     }

     @keyframes slideUp {
         from {
             transform: translateY(30px);
             opacity: 0;
         }

         to {
             transform: translateY(0);
             opacity: 1;
         }
     }

     .modal-header {
         padding: 25px 30px;
         border-bottom: 1px solid #eee;
         display: flex;
         align-items: center;
     }

     .modal-header i {
         font-size: 2rem;
         color: var(--danger);
         margin-right: 15px;
     }

     .modal-header h3 {
         font-size: 1.5rem;
         color: var(--dark);
         font-family: var(--font-main);
     }

     .modal-body {
         padding: 30px;
     }

     .modal-body p {
         color: #6c757d;
         line-height: 1.6;
         margin-bottom: 20px;
     }

     .modal-actions {
         display: flex;
         justify-content: flex-end;
         gap: 15px;
         padding: 20px 30px;
         border-top: 1px solid #eee;
     }

     .modal-btn {
         padding: 12px 25px;
         border-radius: 8px;
         border: none;
         font-weight: 600;
         cursor: pointer;
         transition: all 0.3s;
         min-width: 120px;
     }

     .modal-btn-secondary {
         background-color: #f8f9fa;
         color: #6c757d;
         border: 1px solid #dee2e6;
     }

     .modal-btn-secondary:hover {
         background-color: #e9ecef;
     }

     .modal-btn-primary {
         background: linear-gradient(135deg, var(--danger), #c0392b);
         color: white;
         border: none;
     }

     .modal-btn-primary:hover {
         background: linear-gradient(135deg, #c0392b, var(--danger));
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
         .form-container {
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

         .card-header,
         .card-body {
             padding: 20px;
         }

         .form-actions {
             flex-direction: column;
             align-items: stretch;
         }

         .btn {
             width: 100%;
         }

         .modal-actions {
             flex-direction: column;
         }

         .modal-btn {
             width: 100%;
         }
     }

     @media (max-width: 576px) {
         .page-header h1 {
             font-size: 1.5rem;
         }

         .card-header h2 {
             font-size: 1.5rem;
         }

         .info-box {
             padding: 20px;
         }

         textarea.form-control {
             min-height: 180px;
             padding: 15px;
         }
     }
 </style>
 <div class="page-header">
     <h1>Solicitud de Cancelación</h1>
 </div>

 <!-- Alertas -->
 <div id="alert-message" class="alert">
     <i class="fas fa-info-circle"></i>
     <span id="alert-text"></span>
 </div>

 <div class="form-container">
     <!-- Card principal -->
     <div class="cancel-card">
         <div class="card-header">
             <h2><i class="fas fa-exclamation-triangle"></i> Cancelación de Membresía</h2>
             <p>Estás a punto de solicitar la cancelación de tu membresía. Por favor, lee atentamente la información antes de continuar.</p>
         </div>

         <div class="card-body">
             <!-- Información importante -->
             <div class="info-box">
                 <h3><i class="fas fa-info-circle"></i> Información importante</h3>
                 <ul class="info-list">
                     <li>
                         <i class="fas fa-clock"></i>
                         <div>
                             <strong>Proceso de cancelación:</strong> La cancelación requiere 15 días de anticipación. Tu membresía permanecerá activa hasta el final del período pagado.
                         </div>
                     </li>
                     <li>
                         <i class="fas fa-money-bill-wave"></i>
                         <div>
                             <strong>Reembolsos:</strong> No se realizan reembolsos por períodos no utilizados. La cancelación se aplicará a partir de tu próxima fecha de pago.
                         </div>
                     </li>
                     <li>
                         <i class="fas fa-user-check"></i>
                         <div>
                             <strong>Acceso:</strong> Mantendrás acceso a todas las instalaciones hasta la fecha de vencimiento de tu membresía.
                         </div>
                     </li>
                     <li>
                         <i class="fas fa-comments"></i>
                         <div>
                             <strong>Contacto:</strong> Un representante se comunicará contigo en 48 horas hábiles para confirmar la cancelación.
                         </div>
                     </li>
                 </ul>
             </div>

             <!-- Formulario -->
             <form id="cancel-form" action="controladores/solicitud_cancelacion_membresia_socio.php" method="post">
                 <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                 <input type="hidden" name="id_membresia_usuario" value="<?= $membresia->id_membresia_usuario; ?>">
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
                 <div class="form-group">
                     <label for="motivo">Motivo de la cancelación <span>*</span></label>
                     <div class="textarea-wrapper">
                         <textarea
                             class="form-control"
                             id="motivo"
                             name="motivo"
                             rows="8"
                             placeholder="Por favor, describe detalladamente el motivo de tu solicitud de cancelación. Esta información nos ayuda a mejorar nuestros servicios."
                             required
                             minlength="20"
                             maxlength="1000"></textarea>
                         <div class="char-count" id="char-count">0/1000</div>
                     </div>
                     <div class="form-hint">
                         <i class="fas fa-lightbulb"></i>
                         Sé específico para que podamos entender tu situación y ofrecerte una mejor solución.
                     </div>
                 </div>

                 <div class="form-actions">
                     <a href="membresia_socio.php" class="btn btn-cancel">
                         <i class="fas fa-arrow-left"></i> Volver
                     </a>
                     <button type="submit" class="btn btn-submit" id="submit-btn">
                         <i class="fas fa-paper-plane"></i> Enviar Solicitud
                     </button>
                 </div>
             </form>
         </div>
     </div>
 </div>
 <?php
    require_once "templates/footer.php";
    ?>