// ===============================
// ADMIN.JS
// ===============================

document.addEventListener("DOMContentLoaded", function () {

    loadUsers();
    loadStats();
    loadRecords();
    loadMonthlyChart();
    logoutBtn();

    let filterForm = document.getElementById("filterForm");
    if (filterForm) {
        filterForm.addEventListener("submit", function (e) {
            e.preventDefault();
            loadRecords();
        });
    }

    let btnExport = document.getElementById("btnExport");
    if (btnExport) {
        btnExport.addEventListener("click", function () {
            exportCSV();
        });
    }

});


// ===============================
// BOTON LOGOUT
// ===============================

function logoutBtn() {
    let logOutBtn = document.getElementById('logoutBtn');
    if (!logOutBtn) return;

    logOutBtn.addEventListener('click', (e) => {
        e.preventDefault();

        fetch('/../app/controllers/AuthController.php?action=logout', {
            method: 'POST',
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Mensaje con SweetAlert
                Swal.fire({
                    icon: 'success',
                    title: 'Sesión cerrada',
                    text: data.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = '/public/index';
                });
            } else {
                Swal.fire('Error', 'No se pudo cerrar sesión', 'error');
            }
        })
        .catch(err => console.error('Error en logout:', err));
    });
}


// ===============================
// CARGAR USUARIOS
// ===============================

function loadUsers() {

    fetch("/app/controllers/UsuarioController.php?action=index")
        .then(response => response.json())
        .then(data => {

            let select = document.getElementById("filterUser");
            if (!select) return;

            select.innerHTML = '<option value="">Todos los usuarios</option>';

            if (data.status === "success") {

                data.usuarios.forEach(function (user) {

                    let option = document.createElement("option");
                    option.value = user.id;
                    option.textContent = user.nombre;
                    select.appendChild(option);

                });
            }

        })
        .catch(error => console.error("Error cargando usuarios:", error));
}


// ===============================
// CARGAR ESTADÍSTICAS
// ===============================

function loadStats() {

    fetch("/app/controllers/AdminController.php?action=resumenSemanal")
        .then(response => response.json())
        .then(data => {

            if (data.status === "success") {
                console.log("Resumen semanal:", data.resumen);
            }

        })
        .catch(error => console.error("Error cargando stats:", error));
}


// ===============================
// CARGAR FICHAJES
// ===============================

function loadRecords() {

    let form = document.getElementById("filterForm");

    fetch("/app/controllers/AdminController.php?action=listarFichajes")
        .then(response => response.json())
        .then(data => {

            let tbody = document.getElementById("recordsTable");
            if (!tbody) return;

            tbody.innerHTML = "";

            if (data.status === "success") {

                data.fichajes.forEach(function (record) {

                    let row = document.createElement("tr");

                    row.innerHTML = `
                        <td>${record.user_id}</td>
                        <td>${record.tipo}</td>
                        <td>${record.fecha_hora}</td>
                    `;

                    tbody.appendChild(row);
                });

            } else {
                Swal.fire("Error", data.message, "error");
            }

        })
        .catch(error => console.error("Error cargando registros:", error));
}


// ===============================
// EXPORTAR CSV
// ===============================

function exportCSV() {

    let form = document.getElementById("filterForm");
    let params = new URLSearchParams(new FormData(form));

    window.location.href = "/routes/web.php?controllerName=admin&action=exportarCSV&" + params.toString();

}


// ===============================
// GRÁFICO MENSUAL
// ===============================

function loadMonthlyChart() {

    fetch("/app/controllers/AdminController.php?action=resumenMensual")
        .then(response => response.json())
        .then(data => {

            if (data.status !== "success") return;

            let ctx = document.getElementById("monthlyChart").getContext("2d");

            new Chart(ctx, {
                type: "bar",
                data: {
                    labels: Object.keys(data.resumen),
                    datasets: [{
                        label: "Horas trabajadas",
                        data: Object.values(data.resumen)
                    }]
                },
                options: {
                    responsive: true
                }
            });

        })
        .catch(error => console.error("Error gráfico mensual:", error));
}