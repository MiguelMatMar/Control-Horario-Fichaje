
        let form = document.getElementById('loginForm');
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            let formData = new FormData(form);

            try {
                let response = await fetch('/routes/web.php?controller=auth&action=login', {
                    method: 'POST',
                    body: formData
                });

                let data = await response.json();

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
