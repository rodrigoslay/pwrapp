@extends('adminlte::page')

@section('title', 'Órdenes de Trabajo Agendadas')

@section('content_header')
    <h1>Órdenes de Trabajo Agendadas</h1>
@stop

@section('content')
   <div class="container-fluid">
    @if(session('alert'))
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                icon: '{{ session('alert.type') }}',
                title: '{{ session('alert.title') }}',
                text: '{{ session('alert.message') }}',
            });
        });
    </script>
@endif

<div id="errorBox"></div>
       <div class="card">
           <div class="card-header">
               <div class="card-title">
                   <h5>Calendario de OT Agendadas</h5>
               </div>
           </div>
           <div class="card-body">
               <div id="calendar"></div>
           </div>
       </div>
   </div>
@stop
@section('footer')

    Realizado por <a href="https://www.slaymultimedios.com/"><strong>Slay Multimedios</strong></a> - Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})<br>
    &copy; 2024 PWRTALLER Versión 1.0. Todos los derechos reservados.
@stop
@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css">
    <style>
        /* Estilos adicionales para mejorar la experiencia móvil */
        @media (max-width: 768px) {
            .main-header .navbar {
                display: flex;
                justify-content: space-between;
            }

            .sidebar-mini.sidebar-collapse .main-sidebar {
                display: block;
            }

            .sidebar-mini.sidebar-collapse .sidebar-menu {
                display: none;
            }

            .sidebar-mini.sidebar-collapse .sidebar-menu.open {
                display: block;
            }
        }

        #calendar {
            max-width: 100%;
            margin: 20px auto;
            padding: 10px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            background-color: #fff;
        }

        .fc-toolbar {
            background-color: #007bff;
            color: #fff;
            border-radius: 10px 10px 0 0;
            padding: 10px;
        }

        .fc-button {
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            color: #fff;
        }

        .fc-button:hover {
            background-color: #0056b3;
        }

        .fc-today {
            background-color: #e9ecef !important;
        }

        .fc-event {
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            color: #fff;
        }

        .fc-event:hover {
            background-color: #0056b3;
        }

        .fc-day-grid-event .fc-content {
            padding: 5px;
        }

        .fc-title {
            font-weight: bold;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/locale/es.js"></script>
    <script>
        $(document).ready(function() {
            $('#calendar').fullCalendar({
                locale: 'es', // Configurar el idioma a español
                events: @json($events),
                editable: false,
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'month,agendaWeek,agendaDay'
                },
                buttonText: {
                    today: 'Hoy',
                    month: 'Mes',
                    week: 'Semana',
                    day: 'Día'
                },
                eventClick: function(event) {
                    if (event.url) {
                        window.location.href = event.url;
                        return false;
                    }
                }
            });

            // Mejora de la usabilidad del menú en móviles
            const toggleSidebar = () => {
                const body = $('body');
                const sidebarMenu = $('.sidebar-menu');
                body.toggleClass('sidebar-collapse');
                if (body.hasClass('sidebar-collapse')) {
                    sidebarMenu.addClass('open');
                } else {
                    sidebarMenu.removeClass('open');
                }
            };

            $('.sidebar-toggle').on('click', function(e) {
                e.preventDefault();
                toggleSidebar();
            });

            $(window).on('resize', function() {
                if ($(window).width() > 768) {
                    $('body').removeClass('sidebar-collapse');
                    $('.sidebar-menu').removeClass('open');
                }
            });
        });
    </script>
@stop
