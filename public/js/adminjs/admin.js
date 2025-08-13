document.addEventListener('DOMContentLoaded', function() {
    // Variable global para el gráfico
    let carreraChart = null; 

    // URL de la API de Laravel
    const API_URL = '/admin/dashboard-data'; // ¡Cambio clave!

    // Elementos del DOM
    const loadingOverlay = document.querySelector('.loading-overlay');
    const btnRefresh = document.getElementById('btn-refresh');
    const btnLogout = document.getElementById('btn-logout');

    // --- Inicialización ---
    // Ya no necesitamos verificar la sesión aquí, Laravel lo hace antes de cargar la página.
    loadDashboardData(false); // Carga los datos al entrar

    // --- Event Listeners ---
    btnRefresh.addEventListener('click', () => loadDashboardData(true)); 
    
    // Evento para el botón de logout
    if (btnLogout) {
        btnLogout.addEventListener('click', (e) => {
            e.preventDefault();
            confirmLogout();
        });
    }

    // --- Funciones Principales ---

    async function loadDashboardData(showUpdateAlert = true) { 
        if (showUpdateAlert) {
            showUpdatingAlert(); 
        } else {
            showLoading(true);
        }

        try {
            // Usamos fetch para llamar a nuestra nueva ruta de la API
            const response = await fetch(API_URL);

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || `Error HTTP: ${response.status}`);
            }

            const result = await response.json();

            if (!result.success) {
                throw new Error(result.message || 'Error en los datos recibidos del servidor');
            }

            updateSummaryCards(result.data);
            updateStudentsTable(result.data.estudiantesHorasPendientes);
            createCarreraChart(result.data.estudiantesPorCarrera);
            
            if (showUpdateAlert) {
                Swal.close(); 
                Swal.fire({
                    icon: 'success',
                    title: '¡Actualizado!',
                    text: 'Los datos se han cargado correctamente.',
                    showConfirmButton: false,
                    timer: 1500 
                });
            }

        } catch (error) {
            console.error('Error al cargar datos del dashboard:', error);
            if (showUpdateAlert) Swal.close(); 
            showError(error.message || 'Error al cargar los datos. Verifique la conexión o el servidor.');
        } finally {
            showLoading(false); 
        }
    }

    function updateSummaryCards(data) {
        document.getElementById('total-estudiantes').textContent = data.totalEstudiantes || 0;
        document.getElementById('total-responsables').textContent = data.totalResponsables || 0;
        document.getElementById('horas-pendientes').textContent = parseFloat(data.horasPendientes || 0).toFixed(2);
        document.getElementById('total-asignaciones').textContent = data.totalAsignaciones || 0;
    }

    function updateStudentsTable(students) {
        const tbody = document.getElementById('estudiantes-table-body');
        tbody.innerHTML = ''; 

        if (!students || students.length === 0) {
            tbody.innerHTML = '<tr><td colspan="3" class="text-center py-4 text-muted">No hay estudiantes con horas pendientes.</td></tr>';
            return;
        }

        students.forEach(student => {
            const completed = parseFloat(student.horas_completadas) || 0;
            const horasRequeridas = parseInt(student.horas_requeridas) || 480; 
            const remaining = Math.max(0, horasRequeridas - completed).toFixed(2); 
            const percentage = horasRequeridas > 0 ? ((completed / horasRequeridas) * 100).toFixed(1) : 0; 

            const fullName = [student.nombre, student.apellido_paterno, student.apellido_materno].filter(Boolean).join(' ');

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${fullName || 'N/A'}</td>
                <td>${completed.toFixed(2)} / ${horasRequeridas} (${remaining} restantes)</td>
                <td>
                    <div class="progress progress-thin">
                        <div class="progress-bar" role="progressbar" style="width: ${percentage}%" 
                             aria-valuenow="${percentage}" aria-valuemin="0" aria-valuemax="100">
                             ${percentage}%
                        </div>
                    </div>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    function createCarreraChart(carrerasData) {
        const ctx = document.getElementById('carreraChart');
        if (!ctx) return;

        if (carreraChart) {
            carreraChart.destroy();
        }

        if (!carrerasData || carrerasData.length === 0) {
            ctx.closest('.card-body').innerHTML = `
                <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted">
                    <i class="bi bi-pie-chart-fill fs-1 mb-3"></i>
                    <p class="h6">No hay datos de carreras disponibles.</p>
                </div>`;
            return;
        }

        const labels = carrerasData.map(item => item.carrera);
        const data = carrerasData.map(item => item.cantidad);
        const baseColors = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796'];

        carreraChart = new Chart(ctx.getContext('2d'), {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Estudiantes',
                    data: data,
                    backgroundColor: baseColors,
                    borderColor: '#fff', 
                    borderWidth: 1,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right' }
                }
            }
        });
    }

    // --- UI Helpers ---

    function showLoading(show) {
        if(loadingOverlay) loadingOverlay.style.display = show ? 'flex' : 'none';
    }

    function showUpdatingAlert(title = 'Cargando datos...') {
        Swal.fire({
            title: title,
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });
    }

    function showError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message,
            confirmButtonColor: '#dc3545' 
        });
    }
    
    function confirmLogout() {
        Swal.fire({
            title: '¿Cerrar sesión?',
            text: "¿Estás seguro de que deseas salir?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, cerrar sesión',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Si confirma, envía el formulario de logout que está en el Blade
                document.getElementById('logout-form').submit();
            }
        });
    }
});