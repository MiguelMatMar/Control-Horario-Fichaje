<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TimeControl</title>
    
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="css/login.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="login-body">

    <div class="login-container">
        <header class="login-header">
            <div class="logo">
                <i class="fas fa-clock"></i>
                <span>TimeControl</span>
            </div>
            <h1>Bienvenido de nuevo</h1>
            <p>Introduce tus credenciales para acceder</p>
        </header>

        <form action="admin-panel.php" method="POST" class="login-form">
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <div class="input-icon">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" placeholder="ejemplo@empresa.com" required>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <div class="input-icon">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>
            </div>

            <div class="form-options">
                <label class="remember-me">
                    <input type="checkbox"> Recordarme
                </label>
                <a href="#" class="forgot-password">¿Olvidaste tu contraseña?</a>
            </div>

            <button type="submit" class="btn-login">
                Entrar en el panel <i class="fas fa-arrow-right"></i>
            </button>
        </form>

        <footer class="login-footer">
            <p>&copy; 2026 TimeControl System. Todos los derechos reservados.</p>
        </footer>
    </div>

</body>
</html>