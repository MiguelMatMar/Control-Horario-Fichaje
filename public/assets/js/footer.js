let btnLogout = document.getElementById('btnLogout');

if (btnLogout) {
    btnLogout.addEventListener('click', function () {

        Swal.fire({
            title: '¿Cerrar sesión?',
            text: "Tu sesión finalizará.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, salir',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {

                fetch('/routes/web.php?controller=auth&action=logout')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        window.location.href = '/public/index.php';
                    }
                });
            }
        });
    });
}