@extends('adminlte::page')

@section('title', 'Detalle de la Orden de Trabajo')

@section('content_header')
    <h1>Vista de OT Ejecutiv@</h1>
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
                            Cliente
                            <address>
                                <strong>{{ $workOrder->client->name }}</strong><br>
                                Vehículo: {{ $workOrder->vehicle->brand->name }} {{ $workOrder->vehicle->model }}<br>
                                Patente: {{ $workOrder->vehicle->license_plate }}<br>
                            </address>
                        </div>

                        <div class="col-sm-4 invoice-col">
                            <img src="{{ asset('img/logopowercars.webp') }}" alt="Logo Powercars" class="img-fluid"
                                style="max-height: 100px;">
                            <p><b>Ejecutivo:</b> {{ $workOrder->createdBy->name ?? 'No asignado' }}</p>
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

                    <div class="row">
                        <div class="col-12 table-responsive">
                            <h4>Servicios</h4>
                            @if ($workOrder->status !== 'Facturado')
                                <button type="button" class="btn btn-primary float-right mb-2" data-toggle="modal"
                                    data-target="#addServiceModal">
                                    Agregar Servicio
                                </button>
                            @endif
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Servicio</th>
                                        <th>Mecánico Asignado</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($workOrder->services as $service)
                                        <tr>
                                            <td>{{ $service->name }}</td>
                                            <td>
                                                @if ($service->pivot->mechanic_id)
                                                    {{ App\Models\User::find($service->pivot->mechanic_id)->name ?? 'No asignado' }}
                                                @else
                                                    No asignado
                                                @endif
                                            </td>
                                            <td>
                                                @if ($service->pivot->status == 'completado')
                                                    <span class="badge badge-success">Completado</span>
                                                @elseif($service->pivot->status == 'iniciado')
                                                    <span class="badge badge-warning">Iniciado</span>
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
                        <div class="col-12 table-responsive">
                            <h4>Productos</h4>
                            @if ($workOrder->status !== 'Facturado')
                                <button type="button" class="btn btn-primary float-right mb-2" data-toggle="modal"
                                    data-target="#addProductModal">
                                    Agregar Producto
                                </button>
                            @endif
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
                                            <td>{{ $product->pivot->quantity }}</td> <!-- Mostrar la cantidad correcta -->
                                            <td>
                                                @if ($product->pivot->status == 'entregado')
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
                            <h4>Revisiones</h4>
                            @if ($hasFaults)
                                <p class="text-danger">Se encontraron fallos en las revisiones. Informar al cliente.</p>
                            @endif
                            @if ($workOrder->status !== 'Facturado')
                                <button type="button" class="btn btn-primary float-right mb-2" data-toggle="modal" data-target="#addRevisionModal">
                                    Agregar Revisión
                                </button>
                            @endif
                        </div>
                    </div>
                    <!-- Revisiones -->
                    <div class="row">
                        <div class="col-12">
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
                                                            <span>
                                                                @if ($fault->pivot->status)
                                                                    <i class="fas fa-check-circle text-success"></i>
                                                                @else
                                                                    <i class="fas fa-times-circle text-danger"></i>
                                                                @endif
                                                            </span>
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

                    <div class="row">
                        <h4>Incidencias</h4>
                        <div class="col-12">
                            @if ($hasPendingIncidents)
                                <p class="text-danger">Tienes incidencias que actualizar, comunicarle al cliente.</p>
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
                                        <th>Reportado Por</th>
                                        <th>Estado</th>
                                        <th>Aprobado Por</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($workOrder->incidents as $incident)
                                        <tr>
                                            <td>{{ $incident->name }}</td>
                                            <td>{{ $incident->pivot->observation }}</td>
                                            <td>{{ App\Models\User::find($incident->pivot->reported_by)->name ?? 'Desconocido' }}</td>
                                            <td>
                                                <select data-incident-id="{{ $incident->id }}" class="form-control incident-status">
                                                    <option value="0" {{ $incident->pivot->approved == 0 ? 'selected' : '' }}>Pendiente</option>
                                                    <option value="1" {{ $incident->pivot->approved == 1 ? 'selected' : '' }}>Aprobado</option>
                                                </select>
                                            </td>
                                            <td>{{ $incident->pivot->approved ? App\Models\User::find($incident->pivot->approved_by)->name : 'N/A' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5">No hay incidencias.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <h4>Resumen de Costos</h4>
                            @php
                                use App\Helpers\CurrencyHelper;

                                $subtotal =
                                    $workOrder->services->sum('price') +
                                    $workOrder->products->sum(function ($product) {
                                        return $product->pivot->quantity * $product->price;
                                    });
                                $discount = $subtotal * ($workOrder->client->clientGroup->discount_percentage / 100);
                                $tax = ($subtotal - $discount) * ($workOrder->tax_percentage / 100);
                                $total = $subtotal - $discount + $tax;
                            @endphp
                            <table class="table">
                                <tr>
                                    <th>Subtotal:</th>
                                    <td>{{ CurrencyHelper::format($subtotal) }}</td>
                                </tr>
                                <tr>
                                    <th>Descuento ({{ $workOrder->client->clientGroup->discount_percentage }}%):</th>
                                    <td>{{ CurrencyHelper::format($discount) }}</td>
                                </tr>
                                <tr>
                                    <th>Impuesto ({{ $workOrder->tax_percentage }}%):</th>
                                    <td>{{ CurrencyHelper::format($tax) }}</td>
                                </tr>
                                <tr>
                                    <th>Total:</th>
                                    <td>{{ CurrencyHelper::format($total) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row no-print">
                        <div class="col-12">
                            <a href="{{ route('executive-work-orders.index') }}" class="btn btn-default">
                                <i class="fas fa-arrow-left"></i> Volver a la Lista
                            </a>
                            <a href="{{ route('executive-work-orders.print', $workOrder->id) }}" target="_blank"
                                class="btn btn-success float-right">
                                <i class="fas fa-print"></i> Versión Imprimible
                            </a>
                            @if ($workOrder->status !== 'Facturado')
                                <button onclick="facturarOT()" class="btn btn-warning float-right"
                                    style="margin-right: 10px;">
                                    <i class="fas fa-file-invoice-dollar"></i> Facturar
                                </button>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Modal para agregar servicio -->
    @if ($workOrder->status !== 'Facturado')
        <div class="modal fade" id="addServiceModal" tabindex="-1" role="dialog" aria-labelledby="addServiceModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form action="{{ route('work-orders.add-service', $workOrder->id) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="addServiceModalLabel">Agregar Servicio</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="service_id">Servicio</label>
                                <select name="service_id" class="form-control select2" required>
                                    @foreach ($servicesList as $service)
                                        <option value="{{ $service->id }}">{{ $service->name }} -
                                            {{ CurrencyHelper::format($service->price) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="mechanic_id">Asignar Mecánico</label>
                                <select name="mechanic_id" class="form-control select2" required>
                                    @foreach ($mechanics as $mechanic)
                                        <option value="{{ $mechanic->id }}">{{ $mechanic->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            <button type="submit" class="btn btn-primary">Agregar Servicio</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal para agregar producto -->
    @if ($workOrder->status !== 'Facturado')
        <div class="modal fade" id="addProductModal" tabindex="-1" role="dialog"
            aria-labelledby="addProductModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form action="{{ route('work-orders.add-product', $workOrder->id) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="addProductModalLabel">Agregar Producto</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="product_id">Producto</label>
                                <select name="product_id" class="form-control select2" required>
                                    @foreach ($productsList as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }} -
                                            {{ CurrencyHelper::format($product->price) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="quantity">Cantidad</label>
                                <input type="number" name="quantity" class="form-control" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            <button type="submit" class="btn btn-primary">Agregar Producto</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal para agregar revisión -->
    @if ($workOrder->status !== 'Facturado')
        <div class="modal fade" id="addRevisionModal" tabindex="-1" role="dialog"
            aria-labelledby="addRevisionModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form action="{{ route('work-orders.add-revision', $workOrder->id) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="addRevisionModalLabel">Agregar Revisión</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="revision_id">Revisión</label>
                                <select name="revision_id" class="form-control select2" required>
                                    @foreach ($revisionsList as $revision)
                                        <option value="{{ $revision->id }}">{{ $revision->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            <button type="submit" class="btn btn-primary">Agregar Revisión</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal para mostrar estado incompleto -->
    <div class="modal fade" id="incompleteStatusModal" tabindex="-1" role="dialog"
        aria-labelledby="incompleteStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="incompleteStatusModalLabel">Estado Incompleto</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="incompleteStatusMessage">
                    <!-- Mensaje de estado incompleto se llenará aquí -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container .select2-selection--single {
            height: 38px !important;
            /* Ajusta esta altura según sea necesario */
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px !important;
            /* Ajusta esta altura según sea necesario */
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px !important;
            /* Ajusta esta altura según sea necesario */
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script>
        function facturarOT() {
            $.ajax({
                url: '{{ route('work-orders.facturar', $workOrder->id) }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: response.message,
                        }).then((result) => {
                            if (result.isConfirmed) {
                                updateWorkOrderStatus();
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message,
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON.message,
                    });
                }
            });
        }

        $(document).ready(function() {
            $('.select2').select2({
                width: '100%',
                placeholder: 'Seleccione una opción',
                allowClear: true
            });

            $('select.incident-status').on('change', function() {
                const incidentId = $(this).data('incident-id');
                const status = $(this).val();
                $.ajax({
                    url: `/work-orders/{{ $workOrder->id }}/update-incident-status/${incidentId}`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        status: status
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: response.message,
                        }).then((result) => {
                            if (result.isConfirmed) {
                                updateWorkOrderStatus();
                            }
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON.message,
                        });
                    }
                });
            });
        });
        function updateWorkOrderStatus() {
            $.ajax({
                url: '{{ route("work-orders.update-status", $workOrder->id) }}',
                type: 'PUT',
                data: {
                    _token: '{{ csrf_token() }}',
                },
                success: function(response) {
                    console.log('Estado de la OT actualizado');
                    location.reload(); // Recargar la página después de actualizar el estado de la OT
                },
                error: function(response) {
                    console.error('Error al actualizar el estado de la OT');
                }
            });
        }
    </script>
@stop
