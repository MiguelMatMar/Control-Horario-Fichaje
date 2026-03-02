<!-- app/views/dashboard/empleado.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Empleado</title>
    <script src="../assets/js/sweetalert2.all.min.js"></script>
</head>
<body>
    <h2>Dashboard Empleado</h2>
    <a href="../routes/web.php?controller=auth&action=logout">Cerrar sesión</a>

    <h3>Fichajes</h3>
    <button onclick="fichar('entrada')">Entrada</button>
    <button onclick="fichar('salida')">Salida</button>
    <button onclick="fichar('inicio_descanso')">Inicio Descanso</button>
    <button onclick="fichar('fin_descanso')">Fin Descanso</button>

    <ul id="fichajesList"></ul>

    <script>
        async function fichar(tipo) {
            const res = await fetch('../routes/web.php?controller=fichaje&action=registrar', {
                method: 'POST',
                body: new URLSearchParams({tipo})
            });
            const data = await res.json();
            Swal.fire(data.status, data.message, data.status === 'success' ? 'success' : 'error');
            if (data.status === 'success') cargarFichajes();
        }

        async function cargarFichajes() {
            const res = await fetch('../routes/web.php?controller=fichaje&action=listar');
            const data = await res.json();
            const ul = document.getElementById('fichajesList');
            ul.innerHTML = '';
            data.fichajes.forEach(f => {
                const li = document.createElement('li');
                li.textContent = `${f.tipo} → ${f.fecha_hora}`;
                ul.appendChild(li);
            });
        }

        cargarFichajes();
    </script>
</body>
</html>