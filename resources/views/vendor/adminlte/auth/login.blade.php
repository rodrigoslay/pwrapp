<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>

    <link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

    <style>
        .login-page {
            background-color: #1a202c;
        }
        .login-box {
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .card {
            border-radius: 8px;
        }
        .card-body {
            background-color: #2d3748;
            color: #f4f6f9;
        }
        .form-control {
            background-color: #1a202c;
            border: 1px solid #4a5568;
            color: #f4f6f9;
        }
        .input-group-text {
            background-color: #1a202c;
            border: 1px solid #4a5568;
            color: #ecc94b;
        }
        .btn-primary {
            background-color: #ecc94b;
            border-color: #ecc94b;
            color: #1a202c;
        }
        .btn-primary:hover {
            background-color: #d69e2e;
            border-color: #d69e2e;
        }
        .invalid-feedback {
            color: #e53e3e;
        }
        .icheck-primary label {
            color: #f4f6f9;
        }
        .icheck-primary input[type="checkbox"]:checked + label::before {
            background-color: #ecc94b;
            border-color: #ecc94b;
        }
    </style>
</head>
<body class="login-page">
    <div class="login-box">
        <div class="login-logo">
            <a href="{{ url('/home') }}">
                <img src="https://www.powercars.cl/wp-content/uploads/2024/05/logopowercars.webp" alt="Admin Logo" height="50">
                <b>PWR</b>APP
            </a>
        </div>
        <div class="card card-outline card-primary">
            <div class="card-header text-center">
                <h3 class="card-title">Autenticarse para iniciar sesión</h3>
            </div>
            <div class="card-body login-card-body">
                <form action="{{ url('/login') }}" method="post">
                    @csrf
                    <div class="input-group mb-3">
                        <input type="email" name="email" class="form-control" value="" placeholder="Email" autofocus>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" name="password" class="form-control" placeholder="Contraseña">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-7">
                            <div class="icheck-primary" title="Mantenerme autenticado indefinidamente o hasta cerrar la sesión manualmente">
                                <input type="checkbox" name="remember" id="remember">
                                <label for="remember">Recordarme</label>
                            </div>
                        </div>
                        <div class="col-5">
                            <button type="submit" class="btn btn-block btn-flat btn-primary">
                                <span class="fas fa-sign-in-alt"></span> Acceder
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-footer">
                <p class="my-0">
                    <a href="{{ url('/password/reset') }}">Olvidé mi contraseña</a>
                </p>
                <p class="my-0">
                    <a href="{{ url('/register') }}">Crear una nueva cuenta</a>
                </p>
            </div>
        </div>
    </div>

    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
    <script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>
</body>
</html>
