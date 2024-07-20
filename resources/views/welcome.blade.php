<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PWRTALLER - Bienvenido</title>
    <link href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #1a202c;
            color: #f4f6f9;
            margin: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .container {
            text-align: center;
            background: #2d3748;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .logo img {
            width: 150px;
            margin-bottom: 20px;
        }
        h1 {
            font-size: 2.5em;
            margin-bottom: 20px;
            color: #ecc94b;
        }
        p {
            font-size: 1.2em;
            margin-bottom: 30px;
            color: #a0aec0;
        }
        .links a {
            display: inline-block;
            margin: 10px;
            padding: 10px 20px;
            font-size: 1em;
            color: #1a202c;
            background-color: #ecc94b;
            border-radius: 4px;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .links a:hover {
            background-color: #d69e2e;
        }
        .footer {
            margin-top: 30px;
            font-size: 0.9em;
            color: #a0aec0;
        }
        .footer a {
            color: #a0aec0;
            text-decoration: none;
            margin-left: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="https://www.powercars.cl/wp-content/uploads/2024/05/logopowercars.webp" alt="PowerCars Logo">
        </div>
        <h1>Bienvenido a PWRTALLER</h1>
        <p>Plataforma para gestión eficiente de un taller.</p>
        <div class="links">
            @if (Route::has('login'))
                @auth
                    <a href="{{ url('/dashboard') }}">Escritorio</a>
                @else
                    <a href="{{ route('login') }}">Iniciar Sesión</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}">Registro</a>
                    @endif
                @endauth
                <a href="{{ url('/work-order-status') }}">Estado de OT</a>
            @endif
        </div>
    </div>
    <div class="footer">
        Realizado por <a href="https://www.slaymultimedios.com/"><strong>Slay Multimedios</strong></a> - Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})<br>
        &copy; 2024 PWRTALLER Versión 1.0. Todos los derechos reservados.
    </div>
</body>
</html>
