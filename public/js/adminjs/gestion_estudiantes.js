$(document).ready(function() {
    
    let tablaEstudiantes;
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    const API_URLS = {
        listar: '/admin/estudiantes/listar',
        obtener: '/admin/estudiantes/',
        agregar: '/admin/estudiantes/agregar',
        editar: '/admin/estudiantes/editar/',
        eliminar: '/admin/estudiantes/eliminar',
        registros: '/admin/estudiantes/',
        cambiarEstado: '/admin/estudiantes/cambiar-estado'
    };

    // --- Función de Alertas (Corregida) ---
    function showAlert(message, type = 'success') {
        const icon = type === 'danger' ? 'error' : type; // Convierte 'danger' a 'error' para SweetAlert2
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: icon,
            title: message,
            showConfirmButton: false,
            timer: 3500,
            timerProgressBar: true
        });
    }
    
    function inicializarTabla() {
        const LENGUAJE_ESPANOL = { "search": "Buscar:", "lengthMenu": "Mostrar _MENU_ registros", "info": "Mostrando _START_ a _END_ de _TOTAL_ registros", "infoEmpty": "Mostrando 0 a 0 de 0", "zeroRecords": "No se encontraron resultados", "paginate": { "first": "Primero", "last": "Último", "next": "Siguiente", "previous": "Anterior" } };
        
        tablaEstudiantes = $('#tablaEstudiantes').DataTable({
            ajax: { url: API_URLS.listar, dataSrc: 'data', error: () => showAlert("Error al cargar la lista de estudiantes.", "error") },
            columns: [
                { data: 'estudiante_id', visible: false }, 
                { data: 'matricula' }, 
                { data: 'nombre' },
                { data: 'apellido_paterno' }, 
                { data: 'apellido_materno' }, 
                { data: 'carrera' },
                { data: 'cuatrimestre', className: 'text-center', render: data => data ? `${data}°` : '' },
                { data: null, className: 'text-center', render: data => `<span class="badge bg-primary">${parseFloat(data.horas_completadas || 0).toFixed(2)} / ${data.horas_requeridas || 480}</span>` },
                { data: 'activo', className: 'text-center', render: (data, type, row) => `<button class="btn btn-sm btn-cambiar-estado ${data == 1 ? 'btn-success' : 'btn-danger'}" data-id="${row.estudiante_id}" data-estado-actual="${data}">${data == 1 ? 'Activo' : 'Inactivo'}</button>` },
                { data: null, orderable: false, render: (data, type, row) => `<div class="btn-action-group"><button class="btn btn-outline-primary btn-sm btn-editar" data-id="${row.estudiante_id}" title="Editar"><i class="bi bi-pencil"></i></button><button class="btn btn-outline-info btn-sm btn-registros" data-id="${row.estudiante_id}" title="Ver Registros"><i class="bi bi-clock-history"></i></button><button class="btn btn-outline-danger btn-sm btn-eliminar" data-id="${row.estudiante_id}" title="Eliminar"><i class="bi bi-trash"></i></button></div>` }
            ],
            language: LENGUAJE_ESPANOL, responsive: true, autoWidth: false
        });
    }

    function resetForm() {
        $('#formEstudiante')[0].reset();
        $('#estudiante_id').val('');
        $('#contrasena').prop('required', true).attr('placeholder', 'Contraseña requerida');
        $('#passwordHelp').text('Requerida para nuevos estudiantes.');
        $('#formEstudiante').removeClass('was-validated');
    }
    
    function getBadgeClass(estado) {
        return { 'aprobado': 'bg-success', 'rechazado': 'bg-danger', 'pendiente': 'bg-warning text-dark' }[estado] || 'bg-secondary';
    }

    function configurarEventListeners() {
        $('#btnAgregarEstudiante').click(() => {
            resetForm();
            $('#modalEstudianteLabel').text('Agregar Estudiante');
            $('#modalEstudiante').modal('show');
        });
        
        $('#btnActualizarTabla').on('click', () => {
            showAlert('Actualizando tabla...');
            tablaEstudiantes.ajax.reload();
        });

        $('#tablaEstudiantes').on('click', '.btn-cambiar-estado', function() {
            const boton = $(this);
            const id = boton.data('id');
            const nuevoEstado = boton.data('estado-actual') == 1 ? 0 : 1;
            boton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
            
            $.ajax({
                url: API_URLS.cambiarEstado,
                method: 'POST',
                data: JSON.stringify({ estudiante_id: id, activo: nuevoEstado, _token: csrfToken }),
                contentType: 'application/json',
                success: (response) => {
                    tablaEstudiantes.ajax.reload(null, false);
                    showAlert(response.message, 'success');
                },
                error: (err) => {
                    showAlert(err.responseJSON?.message || 'Error de conexión', 'error');
                    boton.prop('disabled', false).text(boton.data('estado-actual') == 1 ? 'Activo' : 'Inactivo');
                }
            });
        });

        $('#tablaEstudiantes').on('click', '.btn-editar', function() {
            const id = $(this).data('id');
            $.getJSON(`${API_URLS.obtener}${id}`, function(response) {
                if (response.success) {
                    const est = response.data;
                    resetForm();
                    $('#modalEstudianteLabel').text('Editar Estudiante');
                    
                    $('#estudiante_id').val(est.estudiante_id);
                    $('#matricula').val(est.matricula);
                    $('#nombre').val(est.nombre);
                    $('#apellido_paterno').val(est.apellido_paterno);
                    $('#apellido_materno').val(est.apellido_materno);
                    $('#carrera').val(est.carrera);
                    $('#cuatrimestre').val(est.cuatrimestre);
                    $('#telefono').val(est.telefono);
                    $('#correo').val(est.correo);
                    $('#activo').prop('checked', est.activo == 1);
                    
                    $('#contrasena').prop('required', false).attr('placeholder', 'Dejar en blanco para no cambiar');
                    $('#passwordHelp').text('Dejar en blanco para no cambiar.');
                    
                    $('#modalEstudiante').modal('show');
                } else {
                    showAlert(response.message, 'error');
                }
            }).fail(() => showAlert('No se pudo cargar la información del estudiante.', 'error'));
        });

        $('#tablaEstudiantes').on('click', '.btn-eliminar', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción eliminará al estudiante y todos sus registros. ¡No se puede revertir!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Sí, ¡eliminar!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: API_URLS.eliminar,
                        method: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({ estudiante_id: id, _token: csrfToken }),
                        success: function(response) {
                            if (response.success) {
                                showAlert(response.message, 'success');
                                tablaEstudiantes.ajax.reload(null, false);
                            } else {
                                showAlert(response.message, 'error');
                            }
                        },
                        error: () => showAlert('Error de conexión al eliminar.', 'error')
                    });
                }
            });
        });

        $('#tablaEstudiantes').on('click', '.btn-registros', function() {
            const id = $(this).data('id');
            $.getJSON(`${API_URLS.registros}${id}/registros`, function(response) {
                const tbody = $('#tablaRegistros tbody').empty();
                if (response.success && response.data.length > 0) {
                    response.data.forEach(r => {
                        tbody.append(`<tr><td>${r.fecha||'N/A'}</td><td>${r.hora_entrada||'N/A'}</td><td>${r.hora_salida||'N/A'}</td><td>${parseFloat(r.horas_acumuladas||0).toFixed(2)}</td><td><span class="badge ${getBadgeClass(r.estado)}">${r.estado||'pendiente'}</span></td><td>${r.responsable||'N/A'}</td></tr>`);
                    });
                } else {
                    tbody.append('<tr><td colspan="6" class="text-center">Este estudiante no tiene registros.</td></tr>');
                }
                $('#modalRegistros').modal('show');
            }).fail(() => showAlert('No se pudieron cargar los registros.', 'error'));
        });

        // GUARDAR (AGREGAR O EDITAR)
        $('#btnGuardarEstudiante').click(function() {
            if (!$('#formEstudiante')[0].checkValidity()) {
                $('#formEstudiante').addClass('was-validated');
                return;
            }
            const id = $('#estudiante_id').val();
            const url = id ? `${API_URLS.editar}${id}` : API_URLS.agregar;
            
            // --- ¡CORRECCIÓN CLAVE AQUÍ! ---
            // Nos aseguramos de enviar todos los campos que el controlador espera
            const formData = {
                _token: csrfToken,
                estudiante_id: id,
                matricula: $('#matricula').val(),
                nombre: $('#nombre').val(),
                apellido_paterno: $('#apellido_paterno').val(),
                apellido_materno: $('#apellido_materno').val(),
                carrera: $('#carrera').val(),
                cuatrimestre: $('#cuatrimestre').val(),
                telefono: $('#telefono').val(),
                correo: $('#correo').val(),
                contrasena: $('#contrasena').val(),
                activo: $('#activo').is(':checked')
            };

            $.ajax({
                url: url,
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(formData),
                success: function(response) {
                    if (response.success) {
                        tablaEstudiantes.ajax.reload(null, false);
                        $('#modalEstudiante').modal('hide');
                        showAlert(response.message, 'success');
                    } else {
                        showAlert(response.message, 'error');
                    }
                },
                error: (err) => showAlert(err.responseJSON?.message || 'Error al guardar.', 'error')
            });
        });
    }

    inicializarTabla();
    configurarEventListeners();
});