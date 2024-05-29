<!-- resources/views/mechanic-work-orders/show.blade.php -->
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
                        <div class="col-12">
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
                                Estado: {{ $workOrder->status }}<br>
                            </address>
                        </div>

                        <div class="col-sm-4 invoice-col">
                            Mecánico
                            <address>
                                @foreach ($workOrder->services as $service)
                                    <p>{{ $service->name }}:
                                        @if($service->pivot->mechanic)
                                            {{ $service->pivot->mechanic->name }} ({{ $service->pivot->status }})
                                        @else
                                            Sin asignar
                                        @endif
                                    </p>
                                @endforeach
                            </address>
                        </div>

                        <div class="col-sm-4 invoice-col">
                            <b>OT ID:</b> {{ $workOrder->id }}<br>
                            <b>Fecha de Ingreso:</b> {{ $workOrder->created_at->format('d/m/Y H:i:s') }}<br>
                            <b>Fecha de Salida:</b> {{ $workOrder->updated_at->format('d/m/Y H:i:s') }}<br>
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
                                            <td>
                                                @if($service->pivot->mechanic)
                                                    {{ $service->pivot->mechanic->name }}
                                                @else
                                                    Sin asignar
                                                @endif
                                            </td>
                                            <td>{{ $service->pivot->status }}</td>
                                            <td>
                                                @if($service->pivot->mechanic_id == auth()->user()->id)
                                                    <form action="{{ route('work-orders.update-service-status', [$workOrder->id, $service->id]) }}" method="POST">
                                                        @csrf
                                                        <select name="status" class="form-control" onchange="this.form.submit()">
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
                        <div class="col-12">
                            <h4>Incidencias</h4>
                            <button type="button" class="btn btn-primary float-right" data-toggle="modal" data-target="#addIncidentModal">
                                Agregar Incidencia
                            </button>
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
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($workOrder->incidents as $incident)
                                        <tr>
                                            <td>{{ $incident->name }}</td>
                                            <td>{{ $incident->pivot->observation }}</td>
                                            <td>{{ $incident->pivot->approved ? 'Aprobada' : 'Pendiente' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row no-print">
                        <div class="col-12">
                            <a href="{{ route('mechanic-work-orders') }}" class="btn btn-default">Volver a la Lista</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para agregar incidencia -->
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
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('select[name="status"]').on('change', function() {
                var form = $(this).closest('form');
                form.submit();
            });
        });
    </script>
@stop
