@extends('adminlte::page')

@section('title', 'Detalle de la Orden de Trabajo')

@section('content_header')
    <h1>Vista de OT Bodeguero</h1>
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
                            <img src="{{ asset('img/logopowercars_invoice.webp') }}" alt="Logo Powercars" class="img-fluid" style="max-height: 100px;">
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
                                                @if ($workOrder->status !== 'Facturado')
                                                    <form class="update-product-status-form" action="{{ route('warehouse-work-orders.update-product-status', [$workOrder->id, $product->id]) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <select name="status" class="form-control product-status">
                                                            <option value="pendiente" {{ $product->pivot->status == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                                            <option value="entregado" {{ $product->pivot->status == 'entregado' ? 'selected' : '' }}>Entregado</option>
                                                        </select>
                                                    </form>
                                                @else
                                                    <span class="badge badge-{{ $product->pivot->status == 'entregado' ? 'success' : 'danger' }}">
                                                        {{ ucfirst($product->pivot->status) }}
                                                    </span>
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
                            <a href="{{ route('warehouse-work-orders.index') }}" class="btn btn-default">
                                <i class="fas fa-arrow-left"></i> Volver a la Lista
                            </a>
                        </div>
                    </div>

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
@stop
@section('js')
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
    $(document).ready(function() {
        $('.product-status').change(function() {
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
                            updateWorkOrderStatus();
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
    });
    </script>
@stop
