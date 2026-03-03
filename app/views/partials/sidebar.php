<aside class="sidebar">
    <nav class="sidebar-nav">
        <ul>
            <li class="nav-item">
                <a href="/public/index.php" class="nav-link active">
                    <i class="fas fa-th-large"></i>
                    <span><?php echo ($_SESSION['role_id'] == rol::admin->value) ? 'Dashboard Admin' : 'Mi Panel'; ?></span>
                </a>
            </li>

            <?php if ($_SESSION['role_id'] == rol::admin->value): ?>
                <li class="nav-section-title">Administración</li>
                
                <li class="nav-item has-submenu">
                    <button class="nav-link submenu-trigger">
                        <i class="fas fa-users-cog"></i>
                        <span>Gestión</span>
                        <i class="fas fa-chevron-right arrow"></i>
                    </button>
                    
                    <ul class="submenu-uno">
                        <li><a href="#" id="menuUsers">Usuarios</a></li>
                        <li><a href="#" id="menuRecords">Registros Globales</a></li>
                        <li><a href="#" id="menuExport">Exportar Datos (CSV)</a></li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-chart-line"></i>
                        <span>Informes Anual</span>
                    </a>
                </li>

            <?php else: ?>
                <li class="nav-section-title">Jornada Laboral</li>

                <li class="nav-item has-submenu">
                    <button class="nav-link submenu-trigger">
                        <i class="fas fa-user-clock"></i>
                        <span>Fichaje</span>
                        <i class="fas fa-chevron-right arrow"></i>
                    </button>
                    
                    <ul class="submenu-uno">
                        <li><a href="#" id="btnEntrada">Fichar Entrada</a></li>
                        <li><a href="#" id="btnSalida">Fichar Salida</a></li>
                    </ul>
                </li>

                <li class="nav-item has-submenu">
                    <button class="nav-link submenu-trigger">
                        <i class="fas fa-coffee"></i>
                        <span>Descansos</span>
                        <i class="fas fa-chevron-right arrow"></i>
                    </button>
                    
                    <ul class="submenu-uno">
                        <li><a href="#" id="btnDescansoInicio">Iniciar Descanso</a></li>
                        <li><a href="#" id="btnDescansoFin">Finalizar Descanso</a></li>
                    </ul>
                </li>
                
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="fas fa-history"></i>
                        <span>Historial Personal</span>
                    </a>
                </li>
            <?php endif; ?>

        </ul>
    </nav>
</aside>