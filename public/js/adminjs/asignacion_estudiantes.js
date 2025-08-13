document.addEventListener('DOMContentLoaded', function() {
    // =================================================================================
    // 1. DECLARACIÓN DE VARIABLES Y CONSTANTES
    // =================================================================================

    let tablaEstudiantes, tablaResponsables, tablaAsignaciones;
    let selectedStudentIds = new Set();
    let selectedResponsableId = null;

    const loadingOverlay = document.getElementById('loadingOverlay');
    const btnAsignar = document.getElementById('btnAsignar');
    const contadorEstudiantes = document.getElementById('contadorEstudiantes');
    const responsableSeleccionado = document.getElementById('responsableSeleccionado');
    const selectAllCheckbox = document.getElementById('selectAllEstudiantes');
    
    // Obtenemos el token CSRF de la etiqueta meta en el Blade
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // ¡CAMBIO CLAVE! Apuntamos a las nuevas rutas de Laravel
    const API_URLS = {
        obtenerAsignaciones: '/admin/asignaciones/lista',
        obtenerEstudiantesSinAsignar: '/admin/asignaciones/sin-asignar',
        obtenerResponsablesDisponibles: '/admin/asignaciones/responsables',
        asignar: '/admin/asignaciones/asignar',
        eliminar: '/admin/asignaciones/eliminar',
    };

    const LENGUAJE_ESPANOL_DATATABLES = { "sProcessing": "Procesando...", "sLengthMenu": "Mostrar _MENU_ registros", "sZeroRecords": "No se encontraron resultados", "sEmptyTable": "Ningún dato disponible", "sInfo": "Mostrando _START_ al _END_ de _TOTAL_", "sInfoEmpty": "Mostrando 0 al 0 de 0", "sInfoFiltered": "(filtrado de _MAX_ registros)", "sSearch": "Buscar:", "oPaginate": { "sFirst": "Primero", "sLast": "Último", "sNext": "Siguiente", "sPrevious": "Anterior" } };

    // =================================================================================
    // 2. LÓGICA DE LA PÁGINA (LA VERIFICACIÓN DE SESIÓN YA NO ES NECESARIA AQUÍ)
    // =================================================================================

    const showLoading = (show) => loadingOverlay.style.display = show ? 'flex' : 'none';
    const showAlert = (icon, title, text) => Swal.fire({ icon, title, text });

    function initializeTablas() {
        const dataTableConfig = { language: LENGUAJE_ESPANOL_DATATABLES, pageLength: 5, lengthMenu: [5, 10, 25], responsive: true, autoWidth: false };
        tablaEstudiantes = $('#tablaEstudiantes').DataTable({ ...dataTableConfig, ajax: { url: API_URLS.obtenerEstudiantesSinAsignar, dataSrc: 'data' }, columns: [{ data: 'estudiante_id', orderable: false, render: (data) => `<input type="checkbox" class="student-checkbox" value="${data}">` }, { data: 'matricula' }, { data: 'nombre_completo' }] });
        tablaResponsables = $('#tablaResponsables').DataTable({ ...dataTableConfig, ajax: { url: API_URLS.obtenerResponsablesDisponibles, dataSrc: 'data' }, columns: [{ data: 'nombre_completo' }, { data: 'cargo' }] });
        tablaAsignaciones = $('#tablaAsignaciones').DataTable({ ...dataTableConfig, ajax: { url: API_URLS.obtenerAsignaciones, dataSrc: 'data' }, columns: [{ data: 'estudiante' }, { data: 'responsable' }, { data: 'fecha_asignacion', render: data => data ? new Date(data).toLocaleDateString('es-MX') : 'N/A' }, { data: 'asignacion_id', orderable: false, render: data => `<button class="btn btn-sm btn-outline-danger btn-eliminar" data-id="${data}" title="Eliminar"><i class="bi bi-trash"></i></button>` }] });
    }

    function setupEventListeners() {
        selectAllCheckbox.addEventListener('change', function() { $('#tablaEstudiantes').find('.student-checkbox').prop('checked', this.checked).trigger('change'); });
        $('#tablaEstudiantes tbody').on('change', '.student-checkbox', function() { const id = parseInt(this.value, 10); this.checked ? selectedStudentIds.add(id) : selectedStudentIds.delete(id); updateAsignarButtonState(); });
        $('#tablaResponsables tbody').on('click', 'tr', function() { const data = tablaResponsables.row(this).data(); if (!data) return; if ($(this).hasClass('selected')) { $(this).removeClass('selected'); selectedResponsableId = null; responsableSeleccionado.textContent = ''; } else { tablaResponsables.$('tr.selected').removeClass('selected'); $(this).addClass('selected'); selectedResponsableId = data.responsable_id; responsableSeleccionado.textContent = data.nombre_completo; } updateAsignarButtonState(); });
        btnAsignar.addEventListener('click', handleAsignacion);
        $('#tablaAsignaciones').on('click', '.btn-eliminar', function() { handleEliminacion($(this).data('id')); });
    }
    
    function updateAsignarButtonState() {
        btnAsignar.disabled = !(selectedStudentIds.size > 0 && selectedResponsableId !== null);
        contadorEstudiantes.textContent = selectedStudentIds.size;
    }

    async function handleAsignacion() {
        Swal.fire({ title: 'Asignando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        const peticiones = Array.from(selectedStudentIds).map(estudianteId => 
            fetch(API_URLS.asignar, { 
                method: 'POST', 
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken // ¡Importante para la seguridad de Laravel!
                }, 
                body: JSON.stringify({ 
                    estudiante_id: estudianteId, 
                    responsable_id: selectedResponsableId 
                }) 
            }).then(async res => {
                const json = await res.json();
                if (!res.ok) throw new Error(json.message || 'Error en el servidor');
                return json;
            })
        );
        
        try {
            await Promise.all(peticiones);
            Swal.fire('¡Éxito!', `${peticiones.length} asignación(es) creada(s).`, 'success');
            selectedStudentIds.clear(); selectedResponsableId = null; responsableSeleccionado.textContent = ''; 
            tablaResponsables.$('tr.selected').removeClass('selected'); 
            selectAllCheckbox.checked = false; 
            updateAsignarButtonState();
            tablaEstudiantes.ajax.reload(); 
            tablaAsignaciones.ajax.reload();
        } catch (error) {
            Swal.fire('Error', `No se pudo asignar: ${error.message}`, 'error');
        }
    }

    function handleEliminacion(id) {
        Swal.fire({ title: '¿Estás seguro?', text: "No podrás revertir esto.", icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar' })
            .then(async (result) => { 
                if (result.isConfirmed) { 
                    Swal.fire({ title: 'Eliminando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() }); 
                    try { 
                        const response = await fetch(API_URLS.eliminar, { 
                            method: 'POST', 
                            headers: { 
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken // ¡Importante!
                            }, 
                            body: JSON.stringify({ asignacion_id: id }) 
                        }); 
                        const res = await response.json(); 
                        if (!res.success) throw new Error(res.message); 
                        Swal.fire('Eliminada', 'La asignación fue eliminada.', 'success'); 
                        tablaEstudiantes.ajax.reload(); 
                        tablaAsignaciones.ajax.reload(); 
                    } catch (error) { 
                        Swal.fire('Error', `No se pudo eliminar: ${error.message}`, 'error'); 
                    } 
                } 
            });
    }
    
    // =================================================================================
    // 3. FUNCIÓN PRINCIPAL DE INICIALIZACIÓN
    // =================================================================================
    
    function main() {
        showLoading(true);
        try {
            // Ya no es necesario gestionar la sesión aquí
            initializeTablas();
            setupEventListeners();
        } catch (error) {
            console.error("Error en la inicialización:", error);
            showAlert('error', 'Error Crítico', 'No se pudo inicializar la página.');
        } finally {
            showLoading(false);
        }
    }

    // Iniciar la aplicación
    main();
});