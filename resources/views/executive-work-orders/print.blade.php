@extends('adminlte::page')

@section('title', 'Imprimir Orden de Trabajo')

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
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($workOrder->services as $service)
                                        <tr>
                                            <td>{{ $service->name }} - ${{ $service->price }}</td>
                                            <td>
                                                @if($service->pivot->mechanic)
                                                    {{ $service->pivot->mechanic->name }}
                                                @else
                                                    Sin asignar
                                                @endif
                                            </td>
                                            <td>{{ $service->pivot->status }}</td>
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
                                    @foreach ($workOrder->products as $product)
                                        <tr>
                                            <td>{{ $product->name }}</td>
                                            <td>{{ $product->pivot->quantity }}</td>
                                            <td>${{ $product->price }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <h4>Resumen de Costos</h4>
                            <table class="table">
                                <tr>
                                    <th>Subtotal:</th>
                                    <td>${{ number_format($workOrder->subtotal, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Impuesto ({{ $workOrder->tax_percentage }}%):</th>
                                    <td>${{ number_format($workOrder->tax, 2) }}</td>
                                </tr>
                                @if($workOrder->discount > 0)
                                    <tr>
                                        <th>Descuento ({{ $workOrder->discount_percentage }}%):</th>
                                        <td>${{ number_format($workOrder->discount, 2) }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <th>Total:</th>
                                    <td>${{ number_format($workOrder->total, 2) }}</td>
                                </tr>
                            </table>
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
