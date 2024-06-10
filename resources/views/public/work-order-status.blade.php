<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status de Órdenes de Trabajo</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.1/dist/css/adminlte.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f6f9;
        }
        .container {
            margin-top: 50px;
        }
        .badge {
            font-size: 1em;
        }
        .status-message {
            font-weight: bold;
        }
        .status-completado {
            background-color: #28a745 !important;
            color: #fff !important;
        }
        .status-incidencias {
            background-color: #dc3545 !important;
            color: #fff !important;
        }
        .status-iniciado {
            background-color: #f8f9fa !important;
            color: #000 !important;
        }
        .status-en-proceso {
            background-color: #ffc107 !important;
            color: #000 !important;
        }
        .status-aprobado {
            background-color: #ffc107 !important;
            color: #000 !important;
        }
        .status-rechazado {
            background-color: #ffc107 !important;
            color: #000 !important;
        }
        .status-parcial {
            background-color: #ffc107 !important;
            color: #000 !important;
        }
    </style>
</head>
<body class="hold-transition layout-top-nav">
    <div class="wrapper">
        <div class="content-wrapper">
            <div class="container">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Status de Vehículos en vivo</h3>
                    </div>
                    <div class="card-body">
                        <table id="work-orders-table" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Patente</th>
                                    <th>Status</th>
                                    <th>Tiempo Transcurrido</th>
                                    <th>Mensaje</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Contenido dinámico -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://js.pusher.com/7.0/pusher.min.js"></script>
    <script>
        $(document).ready(function() {
            var table = $('#work-orders-table').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: '{{ route("api.work-orders") }}',
                    dataSrc: ''
                },
                columns: [
                    { data: 'license_plate' },
                    { data: 'status' },
                    { data: 'time_elapsed' },
                    { data: 'message' }
                ],
                pageLength: 5,
                order: [[ 2, 'desc' ]],
                language: {
                    url: '{{ asset("js/Spanish.json") }}'
                },
                paging: false,
                searching: false,
                info: false,
                createdRow: function(row, data, dataIndex) {
                    if (data.status === 'Completado') {
                        $(row).addClass('status-completado');
                    } else if (data.status === 'Incidencias') {
                        $(row).addClass('status-incidencias');
                    } else if (data.status === 'Iniciado') {
                        $(row).addClass('status-iniciado');
                    } else if (data.status === 'En Proceso') {
                        $(row).addClass('status-en-proceso');
                    } else if (data.status === 'Aprobado') {
                        $(row).addClass('status-aprobado');
                    } else if (data.status === 'Rechazado') {
                        $(row).addClass('status-rechazado');
                    } else if (data.status === 'Parcial') {
                        $(row).addClass('status-parcial');
                    }
                }
            });

            // Inicializar Pusher
            var pusher = new Pusher('{{ env("PUSHER_APP_KEY") }}', {
                cluster: '{{ env("PUSHER_APP_CLUSTER") }}',
                encrypted: true
            });

            // Suscribirse al canal y escuchar eventos
            var channel = pusher.subscribe('work-orders');
            channel.bind('App\\Events\\WorkOrderStatusUpdated', function(data) {
                console.log('Evento recibido:', data);
                // Actualizar la tabla con los nuevos datos
                table.ajax.reload(null, false);
            });
        });
    </script>
</body>
</html>
