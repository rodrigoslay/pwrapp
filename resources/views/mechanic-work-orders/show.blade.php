@extends('adminlte::page')

@section('title', 'Detalle de la Orden de Trabajo')

@section('content_header')
    <h1>Detalle de la Orden de Trabajo</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="invoice p-3 mb-3">
                    <div class="row">
                        <div class="col-12 invoice-col">
                            <h4>
                                <i class="fas fa-globe"></i> Orden de Trabajo #{{ $workOrder->id }}
                                <small class="float-right">Fecha: {{ $workOrder->created_at->format('d/m/Y') }}</small>
                            </h4>
                        </div>
                    </div>

                    <div class="row invoice-info">
                        <div class="col-sm-4 invoice-col">
                            Cliente
                            <address>
                                <strong>{{ $workOrder->client->name }}</strong><br>
                                Vehículo: {{ $workOrder->vehicle->brand->name }} {{ $workOrder->vehicle->model }}<br>
                                Patente: {{ $workOrder->vehicle->license_plate }}<br>
                            </address>
                        </div>

                        <div class="col-sm-4 invoice-col">
                            <img src="{{ asset('img/logopowercars.webp') }}" alt="Logo Powercars" class="img-fluid" style="max-height: 100px;">
                            <p><b>Ejecutivo:</b> {{ $workOrder->createdBy->name }}</p>
                        </div>

                        <div class="col-sm-4 invoice-col">
                            <b>OT ID:</b> {{ $workOrder->id }}<br>
                            <b>Fecha de Ingreso:</b> {{ $workOrder->created_at->format('d/m/Y H:i:s') }}<br>
                            <b>Fecha de Salida:</b> {{ $workOrder->status === 'Facturado' || $workOrder->status === 'Cerrado' ? $workOrder->updated_at->format('d/m/Y H:i:s') : 'N/A' }}<br>
                            <b>Estado:</b> <span class="badge" style="font-size: 1.2em; background-color:
                                @switch($workOrder->status)
                                    @case('Abierto')
                                        red
                                        @break
                                    @case('Completado')
                                        green
                                        @break
                                    @case('Facturado')
                                        black
                                        @break
                                    @case('Comenzó')
                                    @case('Incidencias')
                                    @case('Aprobada')
                                    @case('Parcial')
                                        orange
                                        @break
                                    @case('Desaprobado')
                                        red
                                        @break
                                    @default
                                        yellow
                                @endswitch">
                                {{ $workOrder->status }}
                            </span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 table-responsive">
                            <h4>Servicios</h4>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Servicio</th>
                                        <th>Mecánico Asignado</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($workOrder->services as $service)
                                        <tr>
                                            <td>{{ $service->name }}</td>
                                            <td>{{ $service->mechanic_name }}</td>
                                            <td>
                                                @if($service->pivot->status == 'completado')
                                                    <span class="badge badge-success">Completado</span>
                                                @elseif($service->pivot->status == 'iniciado')
                                                    <span class="badge badge-warning">Iniciado</span>
                                                @else
                                                    <span class="badge badge-danger">Pendiente</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($service->pivot->mechanic_id == auth()->user()->id && $workOrder->status !== 'Facturado')
                                                    <form id="service-status-form-{{ $service->id }}" action="{{ route('work-orders.update-service-status', [$workOrder->id, $service->id]) }}" method="POST">
                                                        @csrf
                                                        <select name="status" class="form-control" onchange="document.getElementById('service-status-form-{{ $service->id }}').submit()">
                                                            <option value="pendiente" {{ $service->pivot->status == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                                            <option value="iniciado" {{ $service->pivot->status == 'iniciado' ? 'selected' : '' }}>Iniciado</option>
                                                            <option value="completado" {{ $service->pivot->status == 'completado' ? 'selected' : '' }}>Completado</option>
                                                        </select>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 table-responsive">
                            <h4>Productos</h4>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($workOrder->products as $product)
                                        <tr>
                                            <td>{{ $product->name }}</td>
                                            <td>{{ $product->pivot->quantity }}</td>
                                            <td>
                                                @if($product->pivot->status == 'entregado')
                                                    <span class="badge badge-success">Entregado</span>
                                                @else
                                                    <span class="badge badge-danger">Pendiente</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <h4>Incidencias</h4>
                            @if($workOrder->status !== 'Facturado')
                                <button type="button" class="btn btn-primary float-right" data-toggle="modal" data-target="#addIncidentModal">
                                    Agregar Incidencia
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Incidencia</th>
                                        <th>Observación</th>
                                        <th>Estado</th>
                                        <th>Mecánico que Reportó</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($workOrder->incidents as $incident)
                                        <tr>
                                            <td>{{ $incident->name }}</td>
                                            <td>{{ $incident->pivot->observation }}</td>
                                            <td>
                                                @if($incident->pivot->approved)
                                                    <span class="badge badge-success">Aprobada</span>
                                                @else
                                                    <span class="badge badge-danger">Pendiente</span>
                                                @endif
                                            </td>
                                            <td>{{ $incident->reported_by_name }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row no-print">
                        <div class="col-12">
                            <a href="{{ route('mechanic-work-orders') }}" class="btn btn-default">
                                <i class="fas fa-arrow-left"></i> Volver a la Lista
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Modal para agregar incidencia -->
    @if($workOrder->status !== 'Facturado')
        <div class="modal fade" id="addIncidentModal" tabindex="-1" role="dialog" aria-labelledby="addIncidentModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form action="{{ route('work-orders.add-incident', $workOrder->id) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="addIncidentModalLabel">Agregar Incidencia</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="incident_id">Incidencia</label>
                                <select name="incident_id" class="form-control" required>
                                    @foreach($incidents as $incident)
                                        <option value="{{ $incident->id }}">{{ $incident->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="observation">Observación</label>
                                <textarea name="observation" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            <button type="submit" class="btn btn-primary">Agregar Incidencia</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.16/dist/sweetalert2.min.css">
    <style>
        .select2-container .select2-selection--single {
            height: 38px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px !important;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.16/dist/sweetalert2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                width: '100%',
                placeholder: 'Seleccione una opción',
                allowClear: true
            });

            $('.product-status-select').on('change', function() {
                var form = $(this).closest('form');
                var status = $(this).val();

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: response.message,
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#3085d6',
                        });
                    },
                    error: function(response) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Hubo un error al actualizar el estado del producto.'
                        });
                    }
                });
            });
        });
    </script>
@stop
