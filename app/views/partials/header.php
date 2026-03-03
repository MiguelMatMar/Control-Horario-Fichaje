<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Asegúrate de que el enum esté disponible o importado aquí
if (!enum_exists('rol')) {
    enum rol: int {
        case admin = 1;
        case empleado = 2;
    }
}
?>

<header class="main-header">
    <div class="header-left">
        <button class="menu-toggle" aria-label="Abrir menú">
            <i class="fas fa-bars"></i>
        </button>
        <h1 class="app-logo">Time<span>Control</span></h1>
    </div>

    <div class="header-right">
        <div class="user-info">
            <?php 
                $esAdmin = ($_SESSION['role_id'] ?? 0) == rol::admin->value;
                $claseBadge = $esAdmin ? 'badge-admin' : 'badge-user';
                $textoRol = $esAdmin ? 'Administrador' : 'Empleado';
            ?>
            <span class="user-role <?php echo $claseBadge; ?>">
                <?php echo $textoRol; ?>
            </span> 
            <p class="user-name">
                <?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Usuario'); ?>
            </p>
        </div>
        
        <div class="header-actions">
            <button class="btn-icon" title="Notificaciones">
                <i class="fas fa-bell"></i>
            </button>
            
            <button id="btnLogout" class="btn-logout" title="Cerrar Sesión">
                <i class="fas fa-sign-out-alt"></i>
                <span>Salir</span>
            </button>
        </div>
    </div>
</header>