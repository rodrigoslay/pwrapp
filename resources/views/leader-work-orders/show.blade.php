@extends('adminlte::page')

@section('title', 'Detalle de la Orden de Trabajo')

@section('content_header')
    <h1>Vista de OT Líder</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="invoice p-3 mb-3">
                    <div class="row">
                        <div class="col-12 invoice-col">
                            <h4>
                                <i class="fas fa-globe"></i> OT N° #{{ $workOrder->id }}
                                <small class="float-right">Fecha: {{ $workOrder->created_at->format('d/m/Y') }}</small>
                            </h4>
                        </div>
                    </div>

                    <div class="row invoice-info">
                        <div class="col-sm-4 invoice-col">
                            <img src="{{ asset('img/logopowercars_invoice.webp') }}" alt="Logo Powercars" class="img-fluid"
                                style="max-height: 100px;">
                            <p><b>Ejecutivo:</b> {{ $workOrder->createdBy->name ?? 'No asignado' }}</p>
                        </div>
                        <div class="col-sm-4 invoice-col">
                            Cliente
                            <address>
                                <strong>{{ $workOrder->client->name }}</strong><br>
                                Vehículo: {{ $workOrder->vehicle->brand->name }} {{ $workOrder->vehicle->model }}<br>
                                Patente: {{ $workOrder->vehicle->license_plate }}<br>
                            </address>
                        </div>



                        <div class="col-sm-4 invoice-col">
                            <b>OT ID:</b> {{ $workOrder->id }}<br>
                            <b>Fecha de Ingreso:</b> {{ $workOrder->created_at->format('d/m/Y H:i:s') }}<br>
                            <b>Fecha de Salida:</b>
                            {{ $workOrder->status === 'Facturado' || $workOrder->status === 'Cerrado' ? $workOrder->updated_at->format('d/m/Y H:i:s') : 'N/A' }}<br>
                            <b>Estado:</b> <span class="badge"
                                style="font-size: 1.2em; background-color:
                                @if ($workOrder->status === 'Abierto') red
                                @elseif($workOrder->status === 'Facturado' || $workOrder->status === 'Cerrado') green
                                @else yellow @endif">
                                {{ $workOrder->status }}
                            </span>
                        </div>
                    </div>

                    <!-- Servicios -->
                    <div class="row">
                        <div class="col-12 table-responsive">
                            <h4>Servicios</h4>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Servicio</th>
                                        <th>Técnico Asignado</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($workOrder->services as $service)
                                        <tr>
                                            <td>{{ $service->name }}</td>

                                            <td>
                                                @if ($workOrder->status !== 'Facturado')
                                                    <form class="change-mechanic-form" action="{{ route('leader-work-orders.change-mechanic', [$workOrder->id, $service->id]) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <select name="mechanic_id" class="form-control mechanic-select">
                                                            <option value="">Seleccione un técnico</option>
                                                            @foreach ($mechanicsAndLeaders as $mechanic)
                                                                <option value="{{ $mechanic->id }}" {{ $service->pivot->mechanic_id == $mechanic->id ? 'selected' : '' }}>{{ $mechanic->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </form>
                                                @else
                                                    {{ $userNames[$service->pivot->mechanic_id] ?? 'No asignado' }}
                                                @endif
                                            </td>

                                            <td>
                                                @if ($workOrder->status !== 'Facturado')
                                                    <form class="update-status-form" action="{{ route('leader-work-orders.update-status', [$workOrder->id, $service->id]) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <select name="status" class="form-control service-status">
                                                            <option value="pendiente" {{ $service->pivot->status == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                                            <option value="iniciado" {{ $service->pivot->status == 'iniciado' ? 'selected' : '' }}>Iniciado</option>
                                                            <option value="completado" {{ $service->pivot->status == 'completado' ? 'selected' : '' }}>Completado</option>
                                                        </select>
                                                    </form>
                                                @else
                                                    <span class="badge
                                                        @if ($service->pivot->status == 'pendiente') badge-danger
                                                        @elseif($service->pivot->status == 'iniciado') badge-warning
                                                        @else badge-success @endif">
                                                        {{ ucfirst($service->pivot->status) }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Productos -->
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
                                                <span class="badge
                                                    @if ($product->pivot->status == 'pendiente') badge-danger
                                                    @elseif($product->pivot->status == 'parcialmente_entregado') badge-warning
                                                    @else badge-success @endif">
                                                    {{ ucfirst($product->pivot->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Revisiones -->
<div class="row">
    <div class="col-12">
        <h4>Revisiones</h4>
        <div id="accordion">
            @foreach ($revisionsWithFaults as $revision)
                <div class="card">
                    <div class="card-header" id="heading{{ $revision->id }}">
                        <h5 class="mb-0">
                            <button class="btn btn-link" data-toggle="collapse" data-target="#collapse{{ $revision->id }}" aria-expanded="true" aria-controls="collapse{{ $revision->id }}">
                                {{ $revision->name }}
                            </button>
                        </h5>
                    </div>
                    <div id="collapse{{ $revision->id }}" class="collapse" aria-labelledby="heading{{ $revision->id }}" data-parent="#accordion">
                        <div class="card-body">
                            <ul class="list-group">
                                @foreach ($revision->faults as $fault)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        {{ $fault->fallo }}
                                        @if ($workOrder->status !== 'Facturado')
                                            <form class="update-fault-status-form" action="{{ route('leader-work-orders.update-fault-status', [$workOrder->id, $revision->id, $fault->id]) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <select name="status" class="form-control revision-status">
                                                    <option value="1" {{ $fault->status == 1 ? 'selected' : '' }}>Bueno</option>
                                                    <option value="0" {{ $fault->status == 0 ? 'selected' : '' }}>Malo</option>
                                                </select>
                                            </form>
                                        @else
                                            <span class="badge {{ $fault->status == 1 ? 'badge-success' : 'badge-danger' }}">
                                                {{ $fault->status == 1 ? 'Bueno' : 'Malo' }}
                                            </span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>


                    <!-- Incidencias -->
                    <div class="row">
                        <div class="col-12 table-responsive">
                            <h4>Incidencias</h4>
                            @if ($workOrder->status !== 'Facturado')
                                <button type="button" class="btn btn-primary float-right mb-2" data-toggle="modal" data-target="#addIncidentModal">
                                    Agregar Incidencia
                                </button>
                            @endif
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Incidencia</th>
                                        <th>Observación</th>
                                        <th>Reportado Por</th>
                                        <th>Estado</th>
                                        <th>Aprobado Por</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($workOrder->incidents as $incident)
                                        <tr>
                                            <td>{{ $incident->name }}</td>
                                            <td>{{ $incident->pivot->observation }}</td>
                                            <td>{{ App\Models\User::find($incident->pivot->reported_by)->name ?? 'Desconocido' }}</td>
                                            <td>
                                                <span class="badge {{ $incident->pivot->approved == 0 ? 'badge-danger' : 'badge-success' }}">
                                                    {{ $incident->pivot->approved == 0 ? 'Pendiente' : 'Aprobado' }}
                                                </span>
                                            </td>
                                            <td>{{ $incident->pivot->approved ? App\Models\User::find($incident->pivot->approved_by)->name : 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="row no-print">
                        <div class="col-12">
                            <a href="{{ route('leader-work-orders.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Volver a la Lista
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Modal para agregar incidencia -->
    @if ($workOrder->status !== 'Facturado')
        <div class="modal fade" id="addIncidentModal" tabindex="-1" role="dialog" aria-labelledby="addIncidentModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form action="{{ route('leader-work-orders.add-incident', $workOrder->id) }}" method="POST">
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
                                    <option value="">Seleccione una incidencia</option>
                                    @foreach ($incidents as $incident)
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
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@stop
@section('footer')

    Realizado por <a href="https://www.slaymultimedios.com/"><strong>Slay Multimedios</strong></a> - Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})<br>
    &copy; 2024 PWRTALLER Versión 1.0. Todos los derechos reservados.
@stop
@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop
@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(document).ready(function() {
            $('.revision-status, .service-status').change(function() {
                var form = $(this).closest('form');
                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: response.message,
                        }).then((result) => {
                            if (result.isConfirmed) {
                                location.reload();
                            }
                        });
                    },
                    error: function(response) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.responseJSON.message,
                        });
                    }
                });
            });

            $('.mechanic-select').change(function() {
                var form = $(this).closest('form');
                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: response.message,
                        });
                    },
                    error: function(response) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.responseJSON.message,
                        });
                    }
                });
            });
        });
        </script>

@stop
