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

        fetch('../../controllers/AuthController.php?action=logout', {
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
// CARGAR USUARIOS EN SELECT
// ===============================

function loadUsers() {

    fetch("/routes/web.php?controller=user&action=list")
        .then(response => response.json())
        .then(data => {

            let select = document.getElementById("filterUser");

            if (!select) return;

            select.innerHTML = '<option value="">Todos los usuarios</option>';

            if (data.status === "success") {

                data.users.forEach(function (user) {

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

    fetch("/routes/web.php?controller=admin&action=stats")
        .then(response => response.json())
        .then(data => {

            if (data.status === "success") {

                document.getElementById("totalUsers").textContent = data.total_users;
                document.getElementById("todayRecords").textContent = data.today_records;
                document.getElementById("extraHours").textContent = data.week_extra_hours + " h";

            }

        })
        .catch(error => console.error("Error cargando stats:", error));
}


// ===============================
// CARGAR TABLA DE FICHAJES
// ===============================

function loadRecords() {

    let form = document.getElementById("filterForm");
    let formData = new FormData(form);

    fetch("/routes/web.php?controller=admin&action=records", {
        method: "POST",
        body: formData
    })
        .then(response => response.json())
        .then(data => {

            let tbody = document.getElementById("recordsTable");
            tbody.innerHTML = "";

            if (data.status === "success") {

                data.records.forEach(function (record) {

                    let row = document.createElement("tr");

                    row.innerHTML = `
                        <td>${record.nombre}</td>
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
    let formData = new FormData(form);

    fetch("/routes/web.php?controller=admin&action=export", {
        method: "POST",
        body: formData
    })
        .then(response => response.blob())
        .then(blob => {

            let url = window.URL.createObjectURL(blob);
            let a = document.createElement("a");
            a.href = url;
            a.download = "exportacion_fichajes.csv";
            document.body.appendChild(a);
            a.click();
            a.remove();

        })
        .catch(error => {
            Swal.fire("Error", "No se pudo exportar el archivo", "error");
            console.error("Error exportando:", error);
        });

}


// ===============================
// GRÁFICO MENSUAL
// ===============================

function loadMonthlyChart() {

    fetch("/routes/web.php?controller=admin&action=monthlySummary")
        .then(response => response.json())
        .then(data => {

            if (data.status !== "success") return;

            let ctx = document.getElementById("monthlyChart").getContext("2d");

            new Chart(ctx, {
                type: "bar",
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: "Horas trabajadas",
                        data: data.values
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true
                        }
                    }
                }
            });

        })
        .catch(error => console.error("Error gráfico mensual:", error));
}