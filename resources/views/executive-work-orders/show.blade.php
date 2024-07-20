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
                            @if ($workOrder->status === 'Agendado' && $workOrder->scheduling)
                                <br>
                                <b>Fecha de agendamiento:</b> {{ \Carbon\Carbon::parse($workOrder->scheduling)->format('d/m/Y H:i:s') }}
                            @endif
                        </div>
                    </div>

                    @if ($workOrder->status === 'Cotización' || $workOrder->status === 'Agendado')
                        <div class="row">
                            <div class="col-12">
                                <button class="btn btn-success" onclick="iniciarOT({{ $workOrder->id }})">Iniciar OT</button>
                            </div>
                        </div>
                    @endif

                    <!-- Servicios -->
                    <div class="row">
                        <div class="col-12 table-responsive">
                            <h4>Servicios</h4>
                            @if (!in_array($workOrder->status, ['Facturado', 'No Realizado','Agendado','Cotización']))
                                <button type="button" class="btn btn-primary float-right mb-2" data-toggle="modal"
                                    data-target="#addServiceModal">
                                    Agregar Servicio
                                </button>
                            @endif
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Servicio</th>
                                        <th>Precio</th>
                                        <th>Técnico Asignado</th>
                                        <th>Estado</th>
                                        <th> @if (!in_array($workOrder->status, ['Facturado', 'No Realizado','Agendado','Cotización'])) Acciones @endif </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($workOrder->services as $service)
                                        <tr>
                                            <td>{{ $service->name }}</td>
                                            <td>{{ $service->price }}</td>
                                            <td>{{ $userNames[$service->pivot->mechanic_id] ?? 'N/A' }}</td>
                                            <td><span class="badge badge-info">{{ $service->pivot->status }}</span></td>
                                            <td>
                                                @if (!in_array($workOrder->status, ['Facturado', 'No Realizado','Agendado','Cotización']))
                                                    <form action="{{ route('work-orders.remove-service', [$workOrder->id, $service->id]) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                                    </form>
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
                            @if (!in_array($workOrder->status, ['Facturado', 'No Realizado','Agendado','Cotización']))
                                <button type="button" class="btn btn-primary float-right mb-2" data-toggle="modal"
                                    data-target="#addProductModal">
                                    Agregar Producto
                                </button>
                            @endif
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Precio</th>
                                        <th>Cantidad</th>
                                        <th>Estado</th>
                                        <th> @if (!in_array($workOrder->status, ['Facturado', 'No Realizado','Agendado','Cotización'])) Acciones @endif</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($workOrder->products as $product)
                                        <tr>
                                            <td>{{ $product->name }}</td>
                                            <td>{{ $product->price }}</td>
                                            <td>{{ $product->pivot->quantity }}</td>
                                            <td>
                                                @if ($product->pivot->status == 'entregado')
                                                    <span class="badge badge-success">Entregado</span>
                                                @else
                                                    <span class="badge badge-danger">Pendiente</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if (!in_array($workOrder->status, ['Facturado', 'No Realizado','Agendado','Cotización']))
                                                    <form action="{{ route('work-orders.remove-product', [$workOrder->id, $product->id]) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                                    </form>
                                                @endif
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
                            <h4>Se realizaron las sgtes. Revisiones:</h4>
                            @if ($hasFaults)
                                <p class="text-danger">Se encontraron fallos en las revisiones. Informar al cliente.</p>
                            @endif
                            @if (!in_array($workOrder->status, ['Facturado', 'No Realizado','Agendado','Cotización']))
                                <button type="button" class="btn btn-primary float-right mb-2" data-toggle="modal"
                                    data-target="#addRevisionModal">
                                    Agregar Revisión
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div id="accordion">
                                @foreach ($revisionsWithFaults as $revision)
                                    <div class="card">
                                        <div class="card-header" id="heading{{ $revision->id }}">
                                            <h5 class="mb-0 d-flex justify-content-between align-items-center">
                                                <button class="btn btn-link" data-toggle="collapse"
                                                    data-target="#collapse{{ $revision->id }}" aria-expanded="true"
                                                    aria-controls="collapse{{ $revision->id }}">
                                                    {{ $revision->name }}
                                                </button>
                                                @if (!in_array($workOrder->status, ['Facturado', 'No Realizado','Agendado','Cotización']))
                                                    <form action="{{ route('work-orders.remove-revision', [$workOrder->id, $revision->id]) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm">Eliminar Revisión</button>
                                                    </form>
                                                @endif
                                            </h5>
                                        </div>
                                        <div id="collapse{{ $revision->id }}" class="collapse"
                                            aria-labelledby="heading{{ $revision->id }}" data-parent="#accordion">
                                            <div class="card-body">
                                                <ul class="list-group">
                                                    @php
                                                        $fallasConEstadoCero = $revision->faults->filter(function($fault) {
                                                            return $fault->status == 0;
                                                        })->count();
                                                    @endphp

                                                    @if ($fallasConEstadoCero == 0)
                                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                                            No se encontraron fallas en la revision
                                                            <span>
                                                                <i class="fas fa-check-circle text-success"></i>
                                                            </span>
                                                        </li>
                                                    @else
                                                        @foreach ($revision->faults as $fault)
                                                            @if ($fault->status == 0)
                                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                                    {{ $fault->fallo }}
                                                                    <span>
                                                                        <i class="fas fa-times-circle text-danger"></i>
                                                                    </span>
                                                                </li>
                                                            @endif
                                                        @endforeach
                                                    @endif
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
                                                @if (in_array($workOrder->status, ['Facturado', 'No Realizado','Agendado','Cotización']))
                                                    @if ($incident->pivot->approved == 1)
                                                        <span class="badge badge-success">Aprobado</span>
                                                    @elseif ($incident->pivot->approved == 0)
                                                        <span class="badge badge-danger">Pendiente</span>
                                                    @elseif ($incident->pivot->approved == 3)
                                                        <span class="badge badge-warning">Rechazado</span>
                                                    @endif
                                                @else
                                                    <select data-incident-id="{{ $incident->id }}"
                                                        class="form-control incident-status">
                                                        <option value="0"
                                                            {{ $incident->pivot->approved == 0 ? 'selected' : '' }}>Pendiente
                                                        </option>
                                                        <option value="1"
                                                            {{ $incident->pivot->approved == 1 ? 'selected' : '' }}>Aprobado
                                                        </option>
                                                        <option value="3"
                                                            {{ $incident->pivot->approved == 3 ? 'selected' : '' }}>Rechazado
                                                        </option>
                                                    </select>
                                                @endif
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

                                $subtotal = $workOrder->services->sum('price') + $workOrder->products->sum(function ($product) {
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
                                {{--
                                <tr>
                                    <th>Impuesto ({{ $workOrder->tax_percentage }}%):</th>
                                    <td>{{ CurrencyHelper::format($tax) }}</td>
                                </tr>
                                 --}}
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
                            @if ($workOrder->status !== 'Facturado' && $workOrder->status !== 'No Realizado')
                                <button onclick="facturarOT()" class="btn btn-warning float-right"
                                    style="margin-right: 10px;">
                                    <i class="fas fa-file-invoice-dollar"></i> Facturar
                                </button>
                                <button onclick="marcarNoRealizado()" class="btn btn-danger float-right"
                                    style="margin-right: 10px;">
                                    <i class="fas fa-times-circle"></i> No Realizado
                                </button>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Modal para agregar servicio -->
    @if (!in_array($workOrder->status, ['Facturado', 'No Realizado','Agendado','Cotización']))
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
    @if (!in_array($workOrder->status, ['Facturado', 'No Realizado','Agendado','Cotización']))
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
    @if (!in_array($workOrder->status, ['Facturado', 'No Realizado','Agendado','Cotización']))
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
@section('footer')

    Realizado por <a href="https://www.slaymultimedios.com/"><strong>Slay Multimedios</strong></a> - Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})<br>
    &copy; 2024 PWRTALLER Versión 1.0. Todos los derechos reservados.
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

        function marcarNoRealizado() {
            $.ajax({
                url: '{{ route('work-orders.no-realizado', $workOrder->id) }}',
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
            if (['Facturado', 'No Realizado','Agendado','Cotización'].includes('{{ $workOrder->status }}')) {
                console.log('La OT está bloqueada y no puede ser actualizada.');
                return;
            }
            $.ajax({
                url: '{{ route('work-orders.update-status', $workOrder->id) }}',
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

        function iniciarOT(workOrderId) {
            Swal.fire({
                title: '¿Está seguro?',
                text: "¡Esta acción cambiará el estado de la OT a 'Iniciado'!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, cambiar estado'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/work-orders/${workOrderId}/start`,
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
                                        location.reload();
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
            });
        }
    </script>
@stop
