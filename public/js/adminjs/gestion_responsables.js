$(document).ready(function() {

    let tablaResponsables;
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    // URLs de la API de Laravel
    const API_URLS = {
        listar: '/admin/responsables/listar',
        obtener: '/admin/responsables/', // + ID
        agregar: '/admin/responsables/agregar',
        editar: '/admin/responsables/editar/', // + ID
        eliminar: '/admin/responsables/eliminar',
        cambiarEstado: '/admin/responsables/cambiar-estado'
    };

    function showAlert(message, type = 'success') {
        const icon = type === 'danger' ? 'error' : type;
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
        const LENGUAJE_ESPANOL = { "search": "Buscar:", "lengthMenu": "Mostrar _MENU_ registros", "info": "Mostrando _START_ a _END_ de _TOTAL_", "infoEmpty": "Mostrando 0 de 0", "zeroRecords": "No se encontraron resultados", "paginate": { "first": "Primero", "last": "Último", "next": "Siguiente", "previous": "Anterior" } };
        
        tablaResponsables = $('#tablaResponsables').DataTable({
            ajax: { url: API_URLS.listar, dataSrc: 'data' },
            columns: [
                { data: 'responsable_id', visible: false }, 
                { data: 'nombre' }, 
                { data: 'apellido_paterno' },
                { data: 'apellido_materno' }, 
                { data: 'cargo' }, 
                { data: 'departamento' }, 
                { data: 'correo' },
                { data: 'activo', orderable: false, render: (data, type, row) => `<button class="btn btn-sm btn-cambiar-estado ${data == 1 ? 'btn-success' : 'btn-danger'}" data-id="${row.responsable_id}" data-estado-actual="${data}">${data == 1 ? 'Activo' : 'Inactivo'}</button>` },
                { data: null, orderable: false, render: (data, type, row) => `<div class="btn-action-group"><button class="btn btn-outline-primary btn-sm btn-editar" data-id="${row.responsable_id}" title="Editar"><i class="bi bi-pencil"></i></button><button class="btn btn-outline-danger btn-sm btn-eliminar" data-id="${row.responsable_id}" title="Eliminar"><i class="bi bi-trash"></i></button></div>` }
            ],
            language: LENGUAJE_ESPANOL, responsive: true
        });
    }

    function resetForm() {
        $('#formResponsable')[0].reset();
        $('#responsable_id').val('');
        $('#contrasena').prop('required', true).attr('placeholder', 'Contraseña requerida');
        $('#passwordHelp').text('Requerida para nuevos responsables.');
        $('#formResponsable').removeClass('was-validated');
    }

    function configurarEventListeners() {
        $('#btnAgregarResponsable').click(function() {
            resetForm();
            $('#modalResponsableLabel').text('Agregar Responsable');
            $('#modalResponsable').modal('show');
        });
        
        $('#btnActualizarTabla').on('click', () => {
            showAlert('Actualizando tabla...');
            tablaResponsables.ajax.reload();
        });

        $('#tablaResponsables').on('click', '.btn-cambiar-estado', function() {
            const boton = $(this);
            const id = boton.data('id');
            const nuevoEstado = boton.data('estado-actual') == 1 ? 0 : 1;
            boton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
            
            $.ajax({
                url: API_URLS.cambiarEstado,
                method: 'POST',
                data: JSON.stringify({ responsable_id: id, activo: nuevoEstado, _token: csrfToken }),
                contentType: 'application/json',
                success: (response) => {
                    showAlert(response.message, 'success');
                    tablaResponsables.ajax.reload(null, false);
                },
                error: (err) => {
                    showAlert(err.responseJSON?.message || 'Error de conexión', 'error');
                    boton.prop('disabled', false).text(boton.data('estado-actual') == 1 ? 'Activo' : 'Inactivo');
                }
            });
        });

        $('#tablaResponsables').on('click', '.btn-editar', function() {
            const id = $(this).data('id');
            $.getJSON(`${API_URLS.obtener}${id}`, function(response) {
                if (response.success) {
                    const resp = response.data;
                    resetForm();
                    $('#modalResponsableLabel').text('Editar Responsable');
                    $('#responsable_id').val(resp.responsable_id);
                    $('#nombre').val(resp.nombre); 
                    $('#apellido_paterno').val(resp.apellido_paterno);
                    $('#apellido_materno').val(resp.apellido_materno); 
                    $('#cargo').val(resp.cargo);
                    $('#departamento').val(resp.departamento); 
                    $('#telefono').val(resp.telefono);
                    $('#correo').val(resp.correo);
                    $('#contrasena').prop('required', false).attr('placeholder', 'Dejar en blanco para no cambiar');
                    $('#passwordHelp').text('Dejar en blanco para no cambiar la contraseña.');
                    $('#modalResponsable').modal('show');
                } else {
                    showAlert(response.message, 'error');
                }
            }).fail(() => showAlert('No se pudo cargar la información.', 'error'));
        });

        $('#tablaResponsables').on('click', '.btn-eliminar', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: '¿Estás seguro de eliminar?',
                text: "Esta acción no se puede revertir.",
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
                        data: JSON.stringify({ responsable_id: id, _token: csrfToken }),
                        success: function(response) {
                            if (response.success) {
                                showAlert(response.message, 'success');
                                tablaResponsables.ajax.reload(null, false);
                            } else {
                                showAlert(response.message, 'error');
                            }
                        },
                        error: () => showAlert('Error de conexión al eliminar.', 'error')
                    });
                }
            });
        });

        $('#btnGuardarResponsable').click(function() {
            if (!$('#formResponsable')[0].checkValidity()) {
                $('#formResponsable').addClass('was-validated');
                return;
            }
            const id = $('#responsable_id').val();
            const url = id ? `${API_URLS.editar}${id}` : API_URLS.agregar;
            const formData = {
                _token: csrfToken,
                nombre: $('#nombre').val(),
                apellido_paterno: $('#apellido_paterno').val(),
                apellido_materno: $('#apellido_materno').val(),
                cargo: $('#cargo').val(),
                departamento: $('#departamento').val(),
                telefono: $('#telefono').val(),
                correo: $('#correo').val(),
                contrasena: $('#contrasena').val(),
            };
            
            $.ajax({
                url: url,
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(formData),
                success: function(response) {
                    if (response.success) {
                        tablaResponsables.ajax.reload(null, false);
                        $('#modalResponsable').modal('hide');
                        showAlert(response.message, 'success');
                    } else {
                        showAlert(response.message, 'error');
                    }
                },
                error: (err) => showAlert(err.responseJSON?.message || 'Error al guardar.', 'error')
            });
        });
    }

    // --- Inicialización ---
    inicializarTabla();
    configurarEventListeners();
});