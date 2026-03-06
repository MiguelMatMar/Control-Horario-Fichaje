let currentPage = 1;

function escapeHTML(str) {
    if (!str) return "";
    let p = document.createElement('p');
    p.textContent = str;
    return p.innerHTML;
}

function loadUsers(page = 1) {
    currentPage = page;
    let nombre = encodeURIComponent(document.getElementById("filterNombre")?.value || "");
    let email = encodeURIComponent(document.getElementById("filterEmail")?.value || "");
    let rol = encodeURIComponent(document.getElementById("filterRol")?.value || "");

    fetch(`/app/controllers/UsuarioController.php?action=listUsers&page=${page}&nombre=${nombre}&email=${email}&rol=${rol}`)
        .then(res => res.text()) 
        .then(text => {
            try {
                let data = JSON.parse(text);
                if (data.status === "success") {
                    renderUsers(data.users); 
                    renderPagination(data.totalPages, page);
                } else {
                    console.error("Error del servidor:", data.message);
                }
            } catch (e) {
                console.error("El servidor no devolvió un JSON válido:", text);
            }
        })
        .catch(err => console.error("Error de conexión:", err));
}

function renderUsers(users) {
    let tbody = document.getElementById("usersTableBody");
    if (!tbody) return;

    if (users.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center">No hay registros</td></tr>';
        return;
    }

    tbody.innerHTML = users.map(user => {
        let nombreEsc = escapeHTML(user.nombre);
        let emailEsc = escapeHTML(user.email);
        
        // Botón de estado según el activo
        let estadoBtn = user.activo == 1
            ? `<button onclick="toggleUsuario(${user.id}, 1)" class="btn btn-danger">Desactivar</button>`
            : `<button onclick="toggleUsuario(${user.id}, 0)" class="btn btn-success">Activar</button>`;

        return `
            <tr>
                <td>${user.id}</td>
                <td>${nombreEsc}</td>
                <td>${emailEsc}</td>
                <td>${escapeHTML(user.rol)}</td>
                <td>${user.activo == 1 ? "Activo" : "Inactivo"}</td>
                <td>
                    <button class="btn btn-primary" onclick="editarUsuario(${user.id}, '${nombreEsc}', '${emailEsc}')">Editar</button>
                    ${estadoBtn}
                </td>
            </tr>
        `;
    }).join("");
}

function renderPagination(totalPages, current) {
    let container = document.getElementById("pagination");
    if (!container) return;

    container.innerHTML = "";
    for (let i = 1; i <= totalPages; i++) {
        let btn = document.createElement("button");
        btn.innerText = i;
        btn.className = (i === current) ? "nextPage" : "prevPage"; 
        btn.onclick = () => loadUsers(i);
        container.appendChild(btn);
    }
}

function editarUsuario(id, nombre, email) {
    Swal.fire({
        title: "Editar usuario",
        html: `
            <input id="editNombre" class="swal2-input" value="${escapeHTML(nombre)}">
            <input id="editEmail" class="swal2-input" value="${escapeHTML(email)}">
            <input id="editPassword" type="password" class="swal2-input" placeholder="Nueva contraseña">
        `,
        showCancelButton: true,
        confirmButtonText: "Guardar"
    }).then(result => {
        if (!result.isConfirmed) return;

        // Llama a la acción 'editarUsuario' añadida al controlador
        fetch("/app/controllers/UsuarioController.php?action=editarUsuario", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                id: id,
                nombre: document.getElementById("editNombre").value,
                email: document.getElementById("editEmail").value,
                password: document.getElementById("editPassword").value
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === "success") {
                Swal.fire("Éxito", data.message, "success");
                loadUsers(currentPage);
            } else {
                Swal.fire("Error", data.message, "error");
            }
        });
    });
}

function toggleUsuario(id, activoActual) {
    let accion = activoActual == 1 ? "desactivar" : "activar";

    Swal.fire({
        title: `¿Confirmar ${accion}?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí"
    }).then(result => {
        if (!result.isConfirmed) return;

        // Llama a la acción 'toggleUsuario' añadida al controlador
        fetch("/app/controllers/UsuarioController.php?action=toggleUsuario", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id: id })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === "success") {
                Swal.fire("Correcto", data.message, "success");
                loadUsers(currentPage);
            } else {
                Swal.fire("Error", data.message, "error");
            }
        });
    });
}

function logoutBtn() {
    let logOutBtn = document.getElementById("btnLogout");
    if (!logOutBtn) return;

    logOutBtn.addEventListener("click", (e) => {
        e.preventDefault();
        fetch("/app/controllers/AuthController.php?action=logout", { method: "POST" })
            .then(res => res.json())
            .then(() => { window.location.href = "/public/index.php"; });
    });
}

document.addEventListener("DOMContentLoaded", () => {
    loadUsers(1);
    document.getElementById("btnFiltrar")?.addEventListener("click", () => loadUsers(1));
    logoutBtn();
});