// login.js

let form = document.querySelector('.login-form');

form.addEventListener('submit', function(e) {
    e.preventDefault();

    let email = document.querySelector('#email').value.trim();
    let password = document.querySelector('#password').value.trim();

    if (!email || !password) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Todos los campos son obligatorios'
        });
        return;
    }

    let formData = new FormData();
    formData.append('email', email);
    formData.append('password', password);

    fetch('../app/controllers/AuthController.php?action=login', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: '¡Bienvenido!',
                text: data.message,
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                // Redirigir al dashboard
                window.location.href = '../public/index.php';
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudo conectar con el servidor'
        });
        console.error('Error fetch login:', error);
    });
});