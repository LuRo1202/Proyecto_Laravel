<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Iniciar Sesión - Servicio Social</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" />
    
    <style>
        :root {
            --primary-dark: #8E7CC3;
            --primary-medium: #AEA0D8;
            --primary-light: #D4C9EC;
            --text-dark: #343a40;
            --text-light: #FFFFFF;
            --pastel-background: #F8F5FB;
        }
        body {
            background-color: var(--pastel-background);
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            color: var(--text-dark);
        }
        .card {
            box-shadow: 0 6px 12px rgba(0,0,0,.1);
            border-radius: 12px;
            overflow: hidden;
            border: none;
            background-color: var(--text-light);
        }
        .card-body h3 {
            color: var(--primary-dark);
            font-weight: 700;
        }
        .form-control {
            border-radius: 8px;
            padding: .75rem 1rem;
        }
        .form-control:focus {
            border-color: var(--primary-medium);
            box-shadow: 0 0 0 .25rem rgba(174,160,216,.25);
        }
        .btn-primary {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            font-weight: 600;
            padding: .75rem 1rem;
            border-radius: 8px;
            transition: all .3s ease;
        }
        .btn-primary:hover {
            background-color: var(--primary-medium);
            border-color: var(--primary-medium);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,.15);
        }
        .text-decoration-none {
            color: var(--primary-dark);
            font-weight: 500;
        }
        #alertContainer {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            width: 90%;
            max-width: 380px;
        }
        .link-disabled {
            color: #6c757d !important;
            pointer-events: none;
            cursor: not-allowed;
            opacity: 0.65;
            text-decoration: none;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center vh-100">
    <div id="alertContainer">
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>
    
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <img src="{{ asset('imagenes/data.png') }}" alt="Logo" class="img-fluid" style="max-height: 80px;">
                            <h3 class="mt-3">Servicio Social</h3>
                        </div>
                        <form id="formLogin" method="POST" action="{{ url('/login') }}">
                            @csrf <div class="mb-3">
                                <label for="correo" class="form-label">Correo Electrónico</label>
                                <input type="email" class="form-control" id="correo" name="correo" value="{{ old('correo') }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="contrasena" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="contrasena" name="contrasena" required>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                                </button>
                            </div>
                        </form>
                        <div class="mt-3 text-center">
                            <a id="link-registro" class="text-decoration-none link-disabled">¿No tienes cuenta? Regístrate</a>
                            <br>
                            <a href="{{ url('recuperar_contrasena.html') }}" class="text-decoration-none">¿Olvidaste tu contraseña?</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/index.js') }}"></script>
</body>
</html>