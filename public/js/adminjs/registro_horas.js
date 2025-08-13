$(document).ready(function() {
    let tablaEstudiantes, tablaDetalle;
    const modalGestion = new bootstrap.Modal(document.getElementById('modalGestionEstudiante'));
    const modalAgregarEditar = new bootstrap.Modal(document.getElementById('modalAgregarEditarRegistro'));
    let currentStudentId = null;
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    const API_URLS = {
        listarProgreso: '/admin/registro-horas/listar-progreso',
        detalleEstudiante: '/admin/registro-horas/detalle-estudiante/', // + ID
        listarResponsables: '/admin/registro-horas/listar-responsables',
        obtenerRegistro: '/admin/registro-horas/obtener-registro/', // + ID
        agregarRegistro: '/admin/registro-horas/agregar-registro',
        editarRegistro: '/admin/registro-horas/editar-registro/', // + ID
        eliminarRegistro: '/admin/registro-horas/eliminar-registro',
        liberarServicio: '/admin/registro-horas/liberar-servicio'
    };
    
    function showAlert(message, type = 'success') {
        const icon = type === 'danger' ? 'error' : type;
        Swal.fire({ toast: true, position: 'top-end', icon: icon, title: message, showConfirmButton: false, timer: 3500, timerProgressBar: true });
    }

    const LENGUAJE_ESPANOL_DATATABLES = { "search": "Buscar:", "lengthMenu": "Mostrar _MENU_ registros", "info": "Mostrando _START_ a _END_ de _TOTAL_", "infoEmpty": "Mostrando 0 de 0", "zeroRecords": "No se encontraron resultados", "paginate": { "first": "Primero", "last": "Último", "next": "Siguiente", "previous": "Anterior" } };

    function inicializarTablas() {
        tablaEstudiantes = $('#tablaEstudiantesHoras').DataTable({
            ajax: { url: API_URLS.listarProgreso, dataSrc: 'data' },
            columns: [
                { data: 'matricula' }, { data: 'nombre' }, { data: 'apellido_paterno' }, { data: 'apellido_materno' }, { data: 'carrera' },
                { data: null, render: data => {
                    const horas = parseFloat(data.horas_completadas || 0), req = parseFloat(data.horas_requeridas || 480);
                    const pct = req > 0 ? Math.min((horas / req) * 100, 100).toFixed(1) : 0;
                    return `${horas.toFixed(2)} / ${req} hrs<div class="progress mt-1"><div class="progress-bar" style="width: ${pct}%">${pct}%</div></div>`;
                }},
                { data: null, render: data => (parseFloat(data.horas_completadas || 0) >= parseFloat(data.horas_requeridas || 480)) ? '<span class="badge bg-success">Liberado</span>' : '<span class="badge bg-info text-dark">En Proceso</span>' },
                { data: null, orderable: false, render: data => `<button class="btn btn-primary btn-sm btn-gestionar" data-id="${data.estudiante_id}"><i class="bi bi-clock"></i> Gestionar</button>` }
            ],
            language: LENGUAJE_ESPANOL_DATATABLES, responsive: true, autoWidth: false
        });

        tablaDetalle = $('#tablaDetalleHoras').DataTable({
            data: [],
            columns: [
                { data: 'fecha', render: data => data ? new Date(data + 'T00:00:00').toLocaleDateString('es-MX', { year: 'numeric', month: 'short', day: 'numeric' }) : 'N/A' },
                { data: 'horas_acumuladas', render: data => data ? `${parseFloat(data).toFixed(2)} hrs` : 'N/A' },
                { data: 'estado', render: data => `<span class="badge ${ ({ 'aprobado': 'bg-success', 'rechazado': 'bg-danger', 'pendiente': 'bg-warning text-dark' })[data] || 'bg-secondary'}">${data}</span>` },
                { data: 'responsable_nombre', defaultContent: 'N/A' },
                { data: null, orderable: false, render: data => `<div class="btn-group btn-group-sm"><button class="btn btn-info btn-editar-registro" data-id="${data.registro_id}"><i class="bi bi-pencil"></i></button><button class="btn btn-danger btn-eliminar-registro" data-id="${data.registro_id}"><i class="bi bi-trash"></i></button></div>` }
            ],
            language: LENGUAJE_ESPANOL_DATATABLES, order: [[0, 'desc']], searching: false, lengthChange: false, pageLength: 5, responsive: true
        });
    }

    function abrirModalGestion(studentId) {
        currentStudentId = studentId;
        $.getJSON(`${API_URLS.detalleEstudiante}${studentId}`, function(response) {
            if (!response.success) return showAlert(response.message || 'Error al cargar detalles.', 'danger');
            const { estudiante, registros } = response.data;
            $('#nombreEstudianteModal').text(`Gestión: ${estudiante.nombre} ${estudiante.apellido_paterno}`);
            const horas = parseFloat(estudiante.horas_completadas || 0), req = parseFloat(estudiante.horas_requeridas || 480);
            const pct = req > 0 ? Math.min((horas / req) * 100, 100).toFixed(1) : 0;
            $('#progresoContainer').html(`<strong>Progreso:</strong><div class="progress mt-1"><div class="progress-bar fs-6" style="width: ${pct}%">${pct}%</div></div>`);
            $('#btnLiberarServicio').prop('disabled', horas >= req);
            tablaDetalle.clear().rows.add(registros || []).draw();
            modalGestion.show();
        });
    }

    function cargarResponsables() {
        return $.getJSON(API_URLS.listarResponsables, function(response) {
            const select = $('#responsable_id_form').empty().append('<option value="" disabled selected>Seleccione...</option>');
            if (response.success) response.data.forEach(r => select.append(`<option value="${r.responsable_id}">${r.nombre_completo}</option>`));
        });
    }

    function configurarEventListeners() {
        $('#tablaEstudiantesHoras tbody').on('click', '.btn-gestionar', function() { abrirModalGestion($(this).data('id')); });
        
        $('#btnActualizarTabla').on('click', () => {
            showAlert('Actualizando...');
            tablaEstudiantes.ajax.reload();
        });

        $('#modalGestionEstudiante').on('click', '#btnAgregarRegistro, .btn-editar-registro', function() {
            const registroId = $(this).data('id') || '';
            $('#formRegistroHoras')[0].reset();
            $('#registro_id').val(registroId);
            $('#estudiante_id_form').val(currentStudentId);
            
            cargarResponsables().then(() => {
                if (registroId) {
                    $('#modalAgregarEditarLabel').text('Editar Registro');
                    $.getJSON(`${API_URLS.obtenerRegistro}${registroId}`, (response) => {
                        if (response.success) {
                            const reg = response.data;
                            $('#responsable_id_form').val(reg.responsable_id);
                            $('#fecha_form').val(reg.fecha);
                            $('#hora_entrada_form').val(reg.hora_entrada);
                            $('#hora_salida_form').val(reg.hora_salida);
                            $('#horas_acumuladas_form').val(reg.horas_acumuladas);
                            $('#estado_form').val(reg.estado);
                            $('#observaciones_form').val(reg.observaciones);
                            modalAgregarEditar.show();
                        }
                    });
                } else {
                    $('#modalAgregarEditarLabel').text('Agregar Nuevo Registro');
                    $('#fecha_form').val(new Date().toISOString().slice(0, 10));
                    modalAgregarEditar.show();
                }
            });
        });

        $('#hora_entrada_form, #hora_salida_form').on('change', function() {
            const entrada = $('#hora_entrada_form').val(), salida = $('#hora_salida_form').val();
            if (entrada && salida) {
                const diffMs = new Date(`1970-01-01T${salida}`) - new Date(`1970-01-01T${entrada}`);
                $('#horas_acumuladas_form').val(diffMs > 0 ? (diffMs / 3600000).toFixed(2) : '0.00');
            }
        });

        $('#btnGuardarRegistroHoras').on('click', function() {
            if (!$('#formRegistroHoras')[0].checkValidity()) {
                $('#formRegistroHoras')[0].reportValidity();
                return;
            }
            const id = $('#registro_id').val();
            const url = id ? `${API_URLS.editarRegistro}${id}` : API_URLS.agregarRegistro;
            const data = {
                _token: csrfToken,
                estudiante_id: $('#estudiante_id_form').val(),
                responsable_id: $('#responsable_id_form').val(),
                fecha: $('#fecha_form').val(),
                hora_entrada: $('#hora_entrada_form').val(),
                hora_salida: $('#hora_salida_form').val(),
                horas_acumuladas: $('#horas_acumuladas_form').val(),
                estado: $('#estado_form').val(),
                observaciones: $('#observaciones_form').val()
            };

            $.post(url, data, response => {
                if (response.success) {
                    showAlert(response.message, 'success');
                    modalAgregarEditar.hide();
                    tablaEstudiantes.ajax.reload(null, false);
                    abrirModalGestion(currentStudentId);
                }
            }, 'json').fail(err => showAlert(err.responseJSON?.message || 'Error', 'danger'));
        });

        $('#tablaDetalleHoras tbody').on('click', '.btn-eliminar-registro', function() {
            const registroId = $(this).data('id');
            Swal.fire({ title: '¿Estás seguro?', text: "No podrás revertir esto.", icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, ¡eliminar!'})
            .then(result => {
                if (result.isConfirmed) {
                    $.post(API_URLS.eliminarRegistro, { registro_id: registroId, _token: csrfToken }, response => {
                        showAlert(response.message, 'success');
                        tablaEstudiantes.ajax.reload(null, false);
                        abrirModalGestion(currentStudentId);
                    }, 'json').fail(err => showAlert(err.responseJSON?.message || 'Error', 'danger'));
                }
            });
        });

        $('#btnLiberarServicio').on('click', function() {
            Swal.fire({ title: '¿Liberar Servicio Social?', text: "Las horas completadas se igualarán a las requeridas.", icon: 'question', showCancelButton: true, confirmButtonText: 'Sí, liberar' })
            .then(result => {
                if (result.isConfirmed) {
                    $.post(API_URLS.liberarServicio, { estudiante_id: currentStudentId, _token: csrfToken }, response => {
                        showAlert(response.message, 'success');
                        modalGestion.hide();
                        tablaEstudiantes.ajax.reload(null, false);
                    }, 'json').fail(err => showAlert(err.responseJSON?.message || 'Error', 'danger'));
                }
            });
        });

        // Logout
        $('#btn-logout').on('click', (e) => {
            e.preventDefault();
            Swal.fire({ title: '¿Cerrar sesión?', icon: 'question', showCancelButton: true, confirmButtonText: 'Sí, salir', cancelButtonText: 'Cancelar' })
            .then((result) => {
                if (result.isConfirmed) $('#logout-form').submit();
            });
        });
    }

    inicializarTablas();
    configurarEventListeners();
});