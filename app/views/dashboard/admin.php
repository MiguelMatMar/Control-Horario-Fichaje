<!-- app/views/dashboard/admin.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <script src="../assets/js/sweetalert2.all.min.js"></script>
</head>
<body>
    <h2>Dashboard Admin</h2>
    <a href="../routes/web.php?controller=auth&action=logout">Cerrar sesión</a>

    <h3>Usuarios</h3>
    <table border="1" id="usuariosTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Rol</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <script>
        async function cargarUsuarios() {
            const res = await fetch('../routes/web.php?controller=user&action=index');
            const data = await res.json();
            const tbody = document.querySelector('#usuariosTable tbody');
            tbody.innerHTML = '';

            data.usuarios.forEach(u => {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td>${u.id}</td><td>${u.nombre}</td><td>${u.email}</td><td>${u.role_id}</td>`;
                tbody.appendChild(tr);
            });
        }

        cargarUsuarios();
    </script>
</body>
</html>