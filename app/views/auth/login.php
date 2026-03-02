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

    <script>
        const form = document.getElementById('loginForm');
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(form);

            try {
                const response = await fetch('/routes/web.php?controller=auth&action=login', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if(data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Bienvenido!',
                        text: data.message,
                        confirmButtonText: 'Entrar'
                    }).then(() => {
                        // Redirigir al dashboard
                        window.location.href = '/public/index.php';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message
                    });
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo conectar con el servidor'
                });
                console.error(error);
            }
        });
    </script>
</body>
</html>