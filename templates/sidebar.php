
<div class="header">
                <div class="header-left">
                    <button class="toggle-sidebar">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="header-title">Dashboard PowerFit</div>
                </div>
                <div class="user-menu">
                    <div class="notification-bell">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">3</span>
                    </div>
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($_SESSION['nombre']); ?> <?php echo htmlspecialchars($_SESSION['apellido']); ?></div>
                        <div class="user-role"><?php echo htmlspecialchars(ucfirst($_SESSION['rol'])); ?></div>
                    </div>
                    <div class="user-avatar"><?php echo htmlspecialchars(substr($_SESSION['nombre'], 0, 2)); ?></div>
                </div>
</div>