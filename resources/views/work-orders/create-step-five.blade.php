@extends('adminlte::page')

@section('title', 'Resumen de la OT')

@section('content_header')
    <h1>Resumen de la Orden de Trabajo</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="invoice p-3 mb-3">
                    <!-- Información de la OT -->
                    <div class="row">
                        <div class="col-12 invoice-col">
                            <h4>
                                <i class="fas fa-globe"></i> OT N° #{{ session('work_order_id', 'N/A') }}
                                <small class="float-right">Fecha: {{ now()->format('d/m/Y') }}</small>
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
                                <strong>{{ $client->name ?? 'N/A' }}</strong><br>
                                Vehículo: {{ $vehicle->brand->name ?? 'N/A' }} {{ $vehicle->model ?? 'N/A' }}<br>
                                Patente: {{ $vehicle->license_plate ?? 'N/A' }}<br>
                            </address>
                        </div>

                        <div class="col-sm-4 invoice-col">
                            <b>OT ID:</b> {{ session('work_order_id', 'N/A') }}<br>
                            <b>Fecha de Ingreso:</b> {{ now()->format('d/m/Y H:i:s') }}<br>
                            <b>Estado:</b>
                            @if (session('order_type') === 'cotizacion')
                                <span class="badge badge-warning" style="font-size: 1.2em;">Cotización</span>
                            @elseif(session('order_type') === 'agendar')
                                <span class="badge badge-warning" style="font-size: 1.2em;">Agendado</span><br>
                                <b>Fecha de Agendamiento:</b>  {{ \Carbon\Carbon::parse(session('scheduling'))->format('d/m/Y H:i:s') }}
                            @else
                                <span class="badge badge-warning" style="font-size: 1.2em;">Crear OT</span>
                            @endif
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
                                        <th>Usuario Asignado</th>
                                        <th>Precio (con IVA)</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($services as $service)
                                        <tr>
                                            <td>{{ $service->name }}</td>
                                            <td>{{ $mechanicNames[$mechanicAssignments[$service->id]] ?? 'N/A' }}</td>
                                            <td>{{ number_format($service->price, 0, ',', '.') }} CLP</td>
                                            <td>
                                                <span class="badge badge-info">Pendiente</span>
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
                                        <th>Precio Unitario (con IVA)</th>
                                        <th>Descuento (%)</th>
                                        <th>Precio Final</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($products as $product)
                                        @php
                                            $unitPrice = $product->price;
                                            $quantity = $product->pivot->quantity;
                                            $discount = $product->pivot->discount;
                                            $discountedPrice = $unitPrice - ($unitPrice * ($discount / 100));
                                            $finalPrice = $discountedPrice * $quantity;
                                        @endphp
                                        <tr>
                                            <td>{{ $product->name ?? 'N/A' }}</td>
                                            <td>{{ number_format($quantity, 0, ',', '.') }}</td>
                                            <td>{{ number_format($unitPrice, 0, ',', '.') }} CLP</td>
                                            <td>{{ $discount }}%</td>
                                            <td>{{ number_format($finalPrice, 0, ',', '.') }} CLP</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Resumen de Costos -->
                    <div class="row">
                        <div class="col-12 table-responsive">
                            <h4>Resumen de Costos</h4>
                            @php
                                use App\Helpers\CurrencyHelper;

                                $subtotalWithTax =
                                    ($services ? $services->sum('price') : 0) +
                                    ($products
                                        ? $products->sum(function ($product) {
                                            $unitPrice = $product->price;
                                            $quantity = $product->pivot->quantity;
                                            $discount = $product->pivot->discount;
                                            $discountedPrice = $unitPrice - ($unitPrice * ($discount / 100));
                                            return $discountedPrice * $quantity;
                                        })
                                        : 0);

                                $discountPercentage = optional($client->clientGroup)->discount_percentage ?? 0;
                                $discount = $subtotalWithTax * ($discountPercentage / 100);
                                $totalWithoutTax = $subtotalWithTax / 1.19;
                                $totalWithTax = $subtotalWithTax - $discount;
                            @endphp
                            <table class="table table-striped">
                                <tr>
                                    <th>Subtotal (con IVA):</th>
                                    <td>{{ number_format($subtotalWithTax, 0, ',', '.') }} CLP</td>
                                </tr>
                                <tr>
                                    <th>Descuento ({{ $discountPercentage }}%):</th>
                                    <td>{{ number_format($discount, 0, ',', '.') }} CLP</td>
                                </tr>
                                <tr>
                                    <th>Total (sin IVA):</th>
                                    <td>{{ number_format($totalWithoutTax, 0, ',', '.') }} CLP</td>
                                </tr>
                                <tr>
                                    <th>Total (con IVA):</th>
                                    <td>{{ number_format($totalWithTax, 0, ',', '.') }} CLP</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="row no-print my-4">
                        <div class="col-12">
                            <form action="{{ route('work-orders.store-step-five') }}" method="POST">
                                @csrf

                                <input type="hidden" name="client_id" value="{{ $client->id }}">
                                <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">
                                <input type="hidden" name="entry_mileage" value="{{ $entry_mileage ?? 0 }}">

                                @foreach ($services as $service)
                                    <input type="hidden" name="services[{{ $loop->index }}][id]" value="{{ $service->id }}">
                                    <input type="hidden" name="services[{{ $loop->index }}][mechanic_id]" value="{{ session('mechanic_assignments')[$service->id] ?? 0 }}">
                                @endforeach

                                @foreach ($products as $product)
                                    <input type="hidden" name="products[{{ $loop->index }}][id]" value="{{ $product->id }}">
                                    <input type="hidden" name="products[{{ $loop->index }}][quantity]" value="{{ $product->pivot->quantity }}">
                                    <input type="hidden" name="products[{{ $loop->index }}][discount]" value="{{ $product->pivot->discount }}">
                                @endforeach

                                @foreach ($revisions as $revision)
                                    <input type="hidden" name="revisions[{{ $loop->index }}][id]" value="{{ $revision->id }}">
                                    @foreach ($revision->faults as $fault)
                                        <input type="hidden" name="revisions[{{ $loop->index }}][faults][{{ $loop->index }}][id]" value="{{ $fault->id }}">
                                    @endforeach
                                @endforeach

                                @if (session('order_type') === 'agendar')
                                <input type="hidden" name="scheduling" value="{{ session('scheduling') }}">
                                @endif

                                <button type="submit" class="btn btn-primary float-right" style="margin-right: 10px;">
                                    <i class="fas fa-check"></i>
                                    @if (session('order_type') === 'cotizacion')
                                        Crear Cotización
                                    @elseif(session('order_type') === 'agendar')
                                        Crear Agendamiento
                                    @else
                                        Crear Orden de Trabajo
                                    @endif
                                </button>
                                <a href="{{ route('work-orders.create-step-four') }}" class="btn btn-default float-right" style="margin-right: 10px;">
                                    <i class="fas fa-arrow-left"></i> Volver a Asignar Mecánicos
                                </a>
                            </form>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: '{{ session('success') }}',
                    showConfirmButton: true
                }).then(() => {
                    window.location.href =
                        '{{ route('executive-work-orders.show', session('work_order_id')) }}';
                });
            @endif

            @if ($errors->any())
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    html: '<ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>'
                });
            @endif
        });
    </script>
@stop
