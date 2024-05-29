@extends('adminlte::page')

@section('title', 'Resumen de la OT')

@section('content_header')
    <h1>Resumen de la OT</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="invoice p-3 mb-3">
                    <div class="row">
                        <div class="col-12 invoice-col">
                            <h4>
                                <i class="fas fa-globe"></i> Resumen de la OT
                                <small class="float-right">Fecha: {{ now()->format('d/m/Y') }}</small>
                            </h4>
                        </div>
                    </div>

                    <div class="row invoice-info">
                        <div class="col-sm-4 invoice-col">
                            Cliente
                            <address>
                                <strong>{{ $vehicle->client->name }}</strong><br>
                                Vehículo: {{ $vehicle->brand->name }} {{ $vehicle->model }}<br>
                                Patente: {{ $vehicle->license_plate }}<br>
                            </address>
                        </div>

                        <div class="col-sm-4 invoice-col">
                            <img src="{{ asset('img/logopowercars.webp') }}" alt="Logo Powercars" class="img-fluid" style="max-height: 100px;">
                        </div>

                        <div class="col-sm-4 invoice-col">
                            <b>Fecha de Ingreso:</b> {{ now()->format('d/m/Y H:i:s') }}<br>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 table-responsive">
                            <h4>Servicios</h4>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Servicio</th>
                                        <th>Precio</th>
                                        <th>Mecánico Asignado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($services as $service)
                                        <tr>
                                            <td>{{ $service->name }}</td>
                                            <td>${{ $service->price }}</td>
                                            <td>{{ $mechanicNames[$service->id] ?? 'Sin asignar' }}</td>
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
                                        <th>Precio</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($products as $product)
                                        <tr>
                                            <td>{{ $product->name }}</td>
                                            <td>{{ $quantities[$product->id] ?? 1 }}</td>
                                            <td>${{ $product->price }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <h4>Revisiones Extras</h4>
                            <ul>
                                @foreach ($extra_reviews as $review)
                                    <li>{{ $review }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <h4>Resumen de Costos</h4>
                            @php
                                $subtotal = array_sum(array_column($services, 'price')) + array_sum(array_map(function($product) use ($quantities) {
                                    return $product->price * ($quantities[$product->id] ?? 1);
                                }, $products));
                                $discount = $subtotal * ($vehicle->client->clientGroup->discount_percentage / 100);
                                $tax = ($subtotal - $discount) * (config('app.tax_percentage') / 100);
                                $total = $subtotal - $discount + $tax;
                            @endphp
                            <table class="table">
                                <tr>
                                    <th>Subtotal:</th>
                                    <td>${{ number_format($subtotal, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Descuento ({{ $vehicle->client->clientGroup->discount_percentage }}%):</th>
                                    <td>${{ number_format($discount, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Impuesto ({{ config('app.tax_percentage') }}%):</th>
                                    <td>${{ number_format($tax, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Total:</th>
                                    <td>${{ number_format($total, 2) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row no-print">
                        <div class="col-12">
                            <form action="{{ route('work-orders.store-step-six') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary float-right">Crear OT</button>
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
    <script>
        $(document).ready(function() {
            // SweetAlert for success messages
            @if(session('success'))
                Swal.fire({
                    title: 'Éxito',
                    text: '{{ session('success') }}',
                    icon: 'success',
                    confirmButtonText: 'Aceptar'
                });
            @endif
        });
    </script>
@stop
