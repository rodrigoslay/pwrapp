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
                            Cliente
                            <address>
                                <strong>{{ $client->name ?? 'N/A' }}</strong><br>
                                Vehículo: {{ $vehicle->brand->name ?? 'N/A' }} {{ $vehicle->model ?? 'N/A' }}<br>
                                Patente: {{ $vehicle->license_plate ?? 'N/A' }}<br>
                            </address>
                        </div>
                        <div class="col-sm-4 invoice-col">
                            <img src="{{ asset('img/logopowercars.webp') }}" alt="Logo Powercars" class="img-fluid"
                                style="max-height: 100px;">
                            <p><b>Ejecutivo:</b> {{ auth()->user()->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-sm-4 invoice-col">
                            <b>OT ID:</b> {{ session('work_order_id', 'N/A') }}<br>
                            <b>Fecha de Ingreso:</b> {{ now()->format('d/m/Y H:i:s') }}<br>
                            <b>Estado:</b> <span class="badge badge-warning" style="font-size: 1.2em;">En Proceso</span>
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
                                        <th>Mecánico Asignado</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($services as $service)
                                        <tr>
                                            <td>{{ $service->name }}</td>
                                            <td>
                                                {{ $mechanicNames[$mechanicAssignments[$service->id]] ?? 'N/A' }}
                                            </td>
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
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($products as $product)
                                        <tr>
                                            <td>{{ $product->name ?? 'N/A' }}</td>
                                            <td>{{ $productsQuantities[$product->id] ?? 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Revisiones -->
                    <div class="row">
                        <div class="col-12 table-responsive">
                            <h4>Revisiones</h4>
                            <div id="accordion">
                                @forelse ($revisions as $revision)
                                    <div class="card">
                                        <div class="card-header" id="heading{{ $revision->id }}">
                                            <h5 class="mb-0">
                                                <button class="btn btn-link" data-toggle="collapse"
                                                    data-target="#collapse{{ $revision->id }}" aria-expanded="true"
                                                    aria-controls="collapse{{ $revision->id }}">
                                                    {{ $revision->name ?? 'N/A' }}
                                                </button>
                                            </h5>
                                        </div>
                                        <div id="collapse{{ $revision->id }}" class="collapse"
                                            aria-labelledby="heading{{ $revision->id }}" data-parent="#accordion">
                                            <div class="card-body">
                                                <ul class="list-group">
                                                    <li class="list-group-item">
                                                        <span class="float-right">
                                                            Los Fallos están con estatus buenos hasta que el mecánico los
                                                            revise.
                                                            <span class="badge badge-success"><i
                                                                    class="fas fa-check-circle"></i></span>
                                                        </span>
                                                    </li>

                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <p>No hay revisiones que hacer.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Resumen de Costos -->
                    <div class="row">
                        <div class="col-12 table-responsive">
                            <h4>Resumen de Costos</h4>
                            @php
                                use App\Helpers\CurrencyHelper;

                                $subtotal =
                                    ($services ? $services->sum('price') : 0) +
                                    ($products
                                        ? $products->sum(function ($product) use ($productsQuantities) {
                                            return ($productsQuantities[$product->id] ?? 0) * $product->price;
                                        })
                                        : 0);

                                $discount_percentage = optional($client->clientGroup)->discount_percentage ?? 0;
                                $discount = $subtotal * ($discount_percentage / 100);

                                $tax = ($subtotal - $discount) * 0.19; // Assuming 19% tax rate
                                $total = $subtotal - $discount + $tax;
                            @endphp
                            <table class="table table-striped">
                                <tr>
                                    <th>Subtotal:</th>
                                    <td>{{ CurrencyHelper::format($subtotal) }}</td>
                                </tr>
                                <tr>
                                    <th>Descuento ({{ $client->clientGroup->discount_percentage ?? '0' }}%):</th>
                                    <td>{{ CurrencyHelper::format($discount) }}</td>
                                </tr>
                                <tr>
                                    <th>Impuesto (19%):</th>
                                    <td>{{ CurrencyHelper::format($tax) }}</td>
                                </tr>
                                <tr>
                                    <th>Total:</th>
                                    <td>{{ CurrencyHelper::format($total) }}</td>
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
                                    <input type="hidden" name="services[{{ $loop->index }}][id]"
                                        value="{{ $service->id }}">
                                    <input type="hidden" name="services[{{ $loop->index }}][mechanic_id]"
                                        value="{{ session('mechanic_assignments')[$service->id] ?? 0 }}">
                                @endforeach

                                @foreach ($products as $product)
                                    <input type="hidden" name="products[{{ $loop->index }}][id]"
                                        value="{{ $product->id }}">
                                    <input type="hidden" name="products[{{ $loop->index }}][quantity]"
                                        value="{{ $productsQuantities[$product->id] ?? 0 }}">
                                @endforeach

                                @foreach ($revisions as $revision)
                                    <input type="hidden" name="revisions[{{ $loop->index }}][id]"
                                        value="{{ $revision->id }}">
                                    @foreach ($revision->faults as $fault)
                                        <input type="hidden"
                                            name="revisions[{{ $loop->parent->index }}][faults][{{ $loop->index }}][id]"
                                            value="{{ $fault->id }}">
                                    @endforeach
                                @endforeach

                                <button type="submit" class="btn btn-primary float-right" style="margin-right: 10px;">
                                    <i class="fas fa-check"></i> Crear Orden de Trabajo
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
