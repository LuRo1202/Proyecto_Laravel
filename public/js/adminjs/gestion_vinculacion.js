$(document).ready(function() {
    
    let tablaVinculacion;
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    const API_URLS = {
        listar: '/admin/gestion-vinculacion/listar',
        obtener: '/admin/gestion-vinculacion/', // + ID
        agregar: '/admin/gestion-vinculacion/agregar',
        editar: '/admin/gestion-vinculacion/editar/', // + ID
        eliminar: '/admin/gestion-vinculacion/eliminar',
        cambiarEstado: '/admin/gestion-vinculacion/cambiar-estado'
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

    function initDataTable() {
        const LENGUAJE_ESPANOL = { "search": "Buscar:", "lengthMenu": "Mostrar _MENU_ registros", "info": "Mostrando _START_ a _END_ de _TOTAL_", "infoEmpty": "Mostrando 0 a 0 de 0", "zeroRecords": "No se encontraron resultados", "paginate": { "first": "Primero", "last": "Último", "next": "Siguiente", "previous": "Anterior" } };
        
        tablaVinculacion = $('#tablaVinculacion').DataTable({
            ajax: { url: API_URLS.listar, dataSrc: 'data' },
            columns: [
                { data: 'nombre' },
                { data: 'apellido_paterno' },
                { data: 'apellido_materno' },
                { data: 'correo' },
                { data: 'telefono' },
                { data: 'activo', render: (data) => data == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Inactivo</span>' },
                {
                    data: null,
                    orderable: false,
                    render: function(data, type, row) {
                        return `
                            <div class="btn-group">
                                <button class="btn btn-sm ${row.activo == 1 ? 'btn-warning' : 'btn-success'} btn-estado" data-id="${row.vinculacion_id}" data-estado="${row.activo}" title="${row.activo == 1 ? 'Desactivar' : 'Activar'}">
                                    <i class="bi ${row.activo == 1 ? 'bi-toggle-on' : 'bi-toggle-off'}"></i>
                                </button>
                                <button class="btn btn-sm btn-primary btn-editar" data-id="${row.vinculacion_id}"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-sm btn-danger btn-eliminar" data-id="${row.vinculacion_id}"><i class="bi bi-trash"></i></button>
                            </div>
                        `;
                    }
                }
            ],
            language: LENGUAJE_ESPANOL,
            responsive: true
        });
    }

    function resetForm() {
        $('#formVinculacion')[0].reset();
        $('#vinculacion_id').val('');
        $('#contrasena').prop('required', true);
        $('#passwordHelp').text('Contraseña requerida para nuevos usuarios.');
        $('#formVinculacion').removeClass('was-validated');
    }

    function configurarEventListeners() {
        $('#btnAgregarVinculacion').click(function() {
            resetForm();
            $('#modalVinculacionLabel').text('Agregar Personal de Vinculación');
            $('#modalVinculacion').modal('show');
        });

        $('#btn-refresh').on('click', function() {
            tablaVinculacion.ajax.reload(null, false);
            showAlert('Tabla actualizada');
        });

        $('#tablaVinculacion').on('click', '.btn-editar', function() {
            const id = $(this).data('id');
            $.getJSON(`${API_URLS.obtener}${id}`, function(response) {
                if(response.success) {
                    const data = response.data;
                    resetForm();
                    $('#vinculacion_id').val(data.vinculacion_id);
                    $('#nombre').val(data.nombre);
                    $('#apellido_paterno').val(data.apellido_paterno);
                    $('#apellido_materno').val(data.apellido_materno || '');
                    $('#telefono').val(data.telefono || '');
                    $('#correo').val(data.correo);
                    $('#contrasena').prop('required', false);
                    $('#passwordHelp').text('Dejar en blanco para no cambiar la contraseña');
                    $('#modalVinculacionLabel').text('Editar Personal de Vinculación');
                    $('#modalVinculacion').modal('show');
                } else {
                    showAlert(response.message || 'No se pudo cargar la información', 'error');
                }
            });
        });

        $('#tablaVinculacion').on('click', '.btn-estado', function() {
            const id = $(this).data('id');
            const estadoActual = $(this).data('estado');
            const nuevoEstado = estadoActual == 1 ? 0 : 1;
            
            Swal.fire({
                title: '¿Cambiar estado?',
                icon: 'question', showCancelButton: true,
                confirmButtonText: 'Sí, cambiar', cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: API_URLS.cambiarEstado,
                        method: 'POST',
                        data: JSON.stringify({ vinculacion_id: id, activo: nuevoEstado, _token: csrfToken }),
                        contentType: 'application/json',
                        success: (response) => {
                            showAlert(response.message, 'success');
                            tablaVinculacion.ajax.reload(null, false);
                        },
                        error: (err) => showAlert(err.responseJSON?.message || 'Error', 'error')
                    });
                }
            });
        });

        $('#tablaVinculacion').on('click', '.btn-eliminar', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: '¿Eliminar registro?',
                text: "Esta acción no se puede deshacer.",
                icon: 'warning', showCancelButton: true,
                confirmButtonColor: '#d33', confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: API_URLS.eliminar,
                        method: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({ vinculacion_id: id, _token: csrfToken }),
                        success: function(response) {
                            showAlert(response.message, 'success');
                            tablaVinculacion.ajax.reload(null, false);
                        },
                        error: (err) => showAlert(err.responseJSON?.message || 'Error', 'error')
                    });
                }
            });
        });

        $('#btnGuardarVinculacion').click(function() {
            if (!$('#formVinculacion')[0].checkValidity()) {
                $('#formVinculacion')[0].reportValidity();
                return;
            }
            const id = $('#vinculacion_id').val();
            const url = id ? `${API_URLS.editar}${id}` : API_URLS.agregar;
            const formData = {
                _token: csrfToken,
                nombre: $('#nombre').val(),
                apellido_paterno: $('#apellido_paterno').val(),
                apellido_materno: $('#apellido_materno').val(),
                telefono: $('#telefono').val(),
                correo: $('#correo').val(),
                contrasena: $('#contrasena').val()
            };
            
            $.ajax({
                url: url,
                method: 'POST',
                data: JSON.stringify(formData),
                contentType: 'application/json',
                success: function(response) {
                    if(response.success) {
                        showAlert(response.message, 'success');
                        tablaVinculacion.ajax.reload(null, false);
                        $('#modalVinculacion').modal('hide');
                    } else {
                        showAlert(response.message, 'error');
                    }
                },
                error: (err) => showAlert(err.responseJSON?.message || 'Error en el servidor', 'error')
            });
        });

        // Logout
        $('#btn-logout').on('click', (e) => {
            e.preventDefault();
            Swal.fire({
                title: '¿Cerrar sesión?', icon: 'question', showCancelButton: true,
                confirmButtonText: 'Sí, salir', cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) $('#logout-form').submit();
            });
        });
    }

    initDataTable();
    configurarEventListeners();
});