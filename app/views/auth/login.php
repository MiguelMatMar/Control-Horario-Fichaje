<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="login-container">
        <h2>Iniciar sesión</h2>
        <form id="loginForm">
            <label>Email:</label>
            <input type="email" name="email" required>
            <label>Contraseña:</label>
            <input type="password" name="password" required>
            <button type="submit">Entrar</button>
        </form>
    </div>
    <script src="../../public/assets/js/login.js"></script>
</body>
</html>