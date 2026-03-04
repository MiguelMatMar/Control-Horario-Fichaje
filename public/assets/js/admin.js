// ===============================
// ADMIN.JS
// ===============================

document.addEventListener("DOMContentLoaded", function () {

    loadUsers();
    loadStats();
    loadMonthlyChart();
    logoutBtn();
    totalUsuarios();
    todayRecords();
    extraHours();
    loadRecords(1);

    setInterval(() => { // Actualizar cada minuto
        loadRecords();
        totalUsuarios();
        todayRecords();
        extraHours();
    }, 60000);

    let filterForm = document.getElementById("filterForm");
    if (filterForm) {
        filterForm.addEventListener("submit", function (e) {
            e.preventDefault();
            loadRecords(1); // reinicia página al filtrar
        });
    }

    let btnExport = document.getElementById("btnExport");
    if (btnExport) {
        btnExport.addEventListener("click", function () {
            exportCSV();
        });
    }

    // Botones paginación
    let prevBtn = document.getElementById("prevPage");
    let nextBtn = document.getElementById("nextPage");

    if (prevBtn) prevBtn.addEventListener("click", () => {
        if (currentPage > 1) loadRecords(currentPage - 1);
    });
    if (nextBtn) nextBtn.addEventListener("click", () => {
        loadRecords(currentPage + 1);
    });

});

// ===============================
// VARIABLES PAGINACIÓN
// ===============================
let currentPage = 1;
let recordsPerPage = 10;

// ===============================
// Cargar horas extras de esta semana
// ===============================
function extraHours() {
    let today = new Date().toISOString().split('T')[0];

    fetch(`/app/controllers/AdminController.php?action=resumenSemanal&fecha=${today}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                let totalExtra = 0;
                let jornada = 8; // horas normales por día
                let diasSemana = 5; // lunes a viernes

                for (let userData of Object.values(data.resumen)) {
                    if (Array.isArray(userData)) {
                        let sumaHoras = userData.reduce((a, b) => a + b, 0);
                        totalExtra += Math.max(0, sumaHoras - jornada * diasSemana);
                    } else if (typeof userData === "number") {
                        totalExtra += Math.max(0, userData - jornada * diasSemana);
                    }
                }

                let elem = document.getElementById("extraHours");
                if (elem) elem.textContent = totalExtra.toFixed(2);
            }
        })
        .catch(error => console.error("Error extraHours:", error));
}

// ===============================
// Fichajes de hoy
// ===============================
function todayRecords() {

    fetch("/app/controllers/AdminController.php?action=fichajeHoy")
        .then(response => response.json())
        .then(data => {

            let elem = document.getElementById("todayRecords");
            if (!elem) return;

            // data ya es un entero
            elem.textContent = data;

        })
        .catch(error => console.error("Error todayRecords:", error));
}

// ===============================
// Total de usuarios
// ===============================
function totalUsuarios() {
    fetch("/app/controllers/AdminController.php?action=listarUsuarios") 
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                let totalUsersElem = document.getElementById("totalUsers");
                if (totalUsersElem) totalUsersElem.textContent = data.total;
            }
        })
        .catch(error => console.error("Error totalUsuarios:", error));
}

// ===============================
// BOTON LOGOUT
// ===============================
function logoutBtn() {
    let logOutBtn = document.getElementById('btnLogout');
    if (!logOutBtn) return;

    logOutBtn.addEventListener('click', (e) => {
        e.preventDefault();

        fetch('/../app/controllers/AuthController.php?action=logout', { method: 'POST' })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sesión cerrada',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => window.location.href = '/public/index.php');
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
        })
        .catch(error => console.error("Error cargando stats:", error));
}

// ===============================
// CARGAR FICHAJES CON PAGINACIÓN
// ===============================
function loadRecords(page = 1) {
    currentPage = page;
    let form = document.getElementById("filterForm");
    let params = new URLSearchParams(new FormData(form));
    params.append('page', page);
    params.append('limit', recordsPerPage);

    fetch("/app/controllers/AdminController.php?action=listarFichajes&" + params.toString())
        .then(response => response.json())
        .then(data => {
            let tbody = document.getElementById("recordsTable");
            if (!tbody) return;

            tbody.innerHTML = "";

            if (data.status === "success") {
                data.fichajes.forEach(function (record) {
                    let row = document.createElement("tr");
                    row.innerHTML = `
                        <td>${record.usuario}</td>
                        <td>${record.tipo}</td>
                        <td>${record.fecha_hora}</td>
                    `;
                    tbody.appendChild(row);
                });

                // Actualizar paginación
                let currentPageElem = document.getElementById("currentPage");
                if (currentPageElem) currentPageElem.textContent = currentPage;

                let prevBtn = document.getElementById("prevPage");
                let nextBtn = document.getElementById("nextPage");

                if (prevBtn) prevBtn.disabled = currentPage <= 1;
                if (nextBtn) nextBtn.disabled = !data.hasMore;

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

    window.location.href = "/app/controllers/AdminController.php?action=exportarCSV&" + params.toString();
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
                options: { responsive: true }
            });
        })
        .catch(error => console.error("Error gráfico mensual:", error));
}