$(document).ready(function() {
    
    let tablaAdmins;
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    const API_URLS = {
        listar: '/admin/gestion-administradores/listar',
        obtener: '/admin/gestion-administradores/', // + ID
        agregar: '/admin/gestion-administradores/agregar',
        editar: '/admin/gestion-administradores/editar/', // + ID
        eliminar: '/admin/gestion-administradores/eliminar',
        cambiarEstado: '/admin/gestion-administradores/cambiar-estado'
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
        
        tablaAdmins = $('#tablaAdministradores').DataTable({
            ajax: { url: API_URLS.listar, dataSrc: 'data' },
            columns: [
                { data: 'admin_id', visible: false }, 
                { data: 'nombre' }, 
                { data: 'apellido_paterno' },
                { data: 'apellido_materno' }, 
                { data: 'correo' },
                { data: 'ultimo_login', render: data => data ? new Date(data).toLocaleString('es-MX') : 'Nunca' },
                { data: 'activo', orderable: false, render: (data, type, row) => `<button class="btn btn-sm btn-cambiar-estado ${data == 1 ? 'btn-success' : 'btn-danger'}" data-id="${row.admin_id}" data-estado-actual="${data}">${data == 1 ? 'Activo' : 'Inactivo'}</button>` },
                { data: null, orderable: false, render: (data, type, row) => `<div class="btn-action-group"><button class="btn btn-outline-primary btn-sm btn-editar" data-id="${row.admin_id}" title="Editar"><i class="bi bi-pencil"></i></button><button class="btn btn-outline-danger btn-sm btn-eliminar" data-id="${row.admin_id}" title="Eliminar"><i class="bi bi-trash"></i></button></div>` }
            ],
            language: LENGUAJE_ESPANOL, responsive: true
        });
    }

    function resetForm() {
        $('#formAdmin')[0].reset();
        $('#admin_id').val('');
        $('#contrasena').prop('required', true);
        $('#passwordHelp').text('La contraseña es requerida para nuevos administradores.');
        $('#formAdmin').removeClass('was-validated');
    }

    function configurarEventListeners() {
        $('#btnAgregarAdmin').click(function() {
            resetForm();
            $('#modalAdminLabel').text('Agregar Administrador');
            $('#modalAdmin').modal('show');
        });

        $('#btnActualizarTabla').on('click', () => {
            showAlert('Actualizando tabla...');
            tablaAdmins.ajax.reload();
        });

        $('#tablaAdministradores').on('click', '.btn-cambiar-estado', function() {
            const boton = $(this);
            const id = boton.data('id');
            const nuevoEstado = boton.data('estado-actual') == 1 ? 0 : 1;
            boton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
            
            $.ajax({
                url: API_URLS.cambiarEstado,
                method: 'POST',
                data: JSON.stringify({ admin_id: id, activo: nuevoEstado, _token: csrfToken }),
                contentType: 'application/json',
                success: (response) => {
                    showAlert(response.message, 'success');
                    tablaAdmins.ajax.reload(null, false);
                },
                error: (err) => {
                    showAlert(err.responseJSON?.message || 'Error de conexión', 'error');
                    boton.prop('disabled', false).text(boton.data('estado-actual') == 1 ? 'Activo' : 'Inactivo');
                }
            });
        });

        $('#tablaAdministradores').on('click', '.btn-editar', function() {
            const id = $(this).data('id');
            $.getJSON(`${API_URLS.obtener}${id}`, function(response) {
                if (response.success && response.data) {
                    const admin = response.data;
                    resetForm();
                    $('#modalAdminLabel').text('Editar Administrador');
                    $('#admin_id').val(admin.admin_id);
                    $('#nombre').val(admin.nombre);
                    $('#apellido_paterno').val(admin.apellido_paterno);
                    $('#apellido_materno').val(admin.apellido_materno);
                    $('#telefono').val(admin.telefono);
                    $('#correo').val(admin.correo);
                    $('#contrasena').prop('required', false);
                    $('#passwordHelp').text('Dejar en blanco para no cambiar la contraseña.');
                    $('#modalAdmin').modal('show');
                } else {
                    showAlert(response.message || "No se encontraron datos.", 'error');
                }
            }).fail(() => showAlert('No se pudo cargar la información.', 'error'));
        });

        $('#tablaAdministradores').on('click', '.btn-eliminar', function() {
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
                        data: JSON.stringify({ admin_id: id, _token: csrfToken }),
                        success: function(response) {
                            if (response.success) {
                                showAlert(response.message, 'success');
                                tablaAdmins.ajax.reload(null, false);
                            } else {
                                showAlert(response.message, 'error');
                            }
                        },
                        error: (err) => showAlert(err.responseJSON?.message || 'Error de conexión', 'error')
                    });
                }
            });
        });

        $('#btnGuardarAdmin').click(function() {
            if (!$('#formAdmin')[0].checkValidity()) {
                $('#formAdmin').addClass('was-validated');
                return;
            }
            const id = $('#admin_id').val();
            const url = id ? `${API_URLS.editar}${id}` : API_URLS.agregar;
            const formData = {
                _token: csrfToken,
                nombre: $('#nombre').val(),
                apellido_paterno: $('#apellido_paterno').val(),
                apellido_materno: $('#apellido_materno').val(),
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
                        tablaAdmins.ajax.reload(null, false);
                        $('#modalAdmin').modal('hide');
                        showAlert(response.message, 'success');
                    } else {
                        showAlert(response.message, 'error');
                    }
                },
                error: (err) => showAlert(err.responseJSON?.message || 'Error al guardar.', 'error')
            });
        });
        
        // Logout
        $('#btn-logout').on('click', (e) => {
            e.preventDefault();
            Swal.fire({
                title: '¿Cerrar sesión?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, salir',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#logout-form').submit();
                }
            });
        });
    }

    inicializarTabla();
    configurarEventListeners();
});