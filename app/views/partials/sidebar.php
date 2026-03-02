<aside class="sidebar">

    <nav>
        <ul>

            <?php if ($_SESSION['role_id'] == 1): ?>

                <li>
                    <a href="/public/index.php">
                        🏠 Dashboard Admin
                    </a>
                </li>

                <li>
                    <a href="#" id="menuUsers">
                        👥 Gestión de usuarios
                    </a>
                </li>

                <li>
                    <a href="#" id="menuRecords">
                        📋 Registros globales
                    </a>
                </li>

                <li>
                    <a href="#" id="menuExport">
                        📤 Exportar datos
                    </a>
                </li>

            <?php else: ?>

                <li>
                    <a href="/public/index.php">
                        🏠 Mi Panel
                    </a>
                </li>

                <li>
                    <a href="#" id="btnEntrada">
                        🟢 Fichar entrada
                    </a>
                </li>

                <li>
                    <a href="#" id="btnSalida">
                        🔴 Fichar salida
                    </a>
                </li>

                <li>
                    <a href="#" id="btnDescansoInicio">
                        ☕ Iniciar descanso
                    </a>
                </li>

                <li>
                    <a href="#" id="btnDescansoFin">
                        ▶ Finalizar descanso
                    </a>
                </li>

            <?php endif; ?>

        </ul>
    </nav>

</aside>