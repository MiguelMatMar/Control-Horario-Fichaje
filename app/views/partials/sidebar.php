<aside class="sidebar">
    <nav class="sidebar-nav">
        <ul>
            <li class="nav-item">
                <a href="/public/index.php" class="nav-link active">
                    <i class="fas fa-th-large"></i>
                    <span><?php echo ($_SESSION['role_id'] == rol::admin->value) ? 'Dashboard Admin' : 'Mi Panel'; ?></span>
                </a>
            </li>

  

        </ul>
    </nav>
</aside>