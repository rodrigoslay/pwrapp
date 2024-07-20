@extends('adminlte::page')

@section('title', 'Imprimir Orden de Trabajo')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="invoice p-3 mb-3">
                    <div class="col-12">
                        <h4>
                            <i class="fas fa-globe"></i> Orden de Trabajo #{{ $workOrder->id }}
                            <small class="float-right">Fecha: {{ $workOrder->created_at->format('d/m/Y') }}</small>
                        </h4>
                    </div>

                    <div class="row invoice-info">
                        <div class="col-sm-4 invoice-col text-center">
                            <img src="{{ asset('img/logopowercars_invoice.webp') }}" alt="Logo Powercars" class="img-fluid"
                                style="max-height: 100px;">
                            <p><b>Ejecutivo:</b> {{ $workOrder->createdBy->name }}</p>
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
                            <b>Fecha de Salida:</b> {{ $workOrder->updated_at->format('d/m/Y H:i:s') }}<br>
                            <b>Estado:</b> <span class="badge"
                                style="font-size: 1.2em; background-color:
                                @if ($workOrder->status === 'Abierto') red
                                @elseif($workOrder->status === 'Facturado' || $workOrder->status === 'Cerrado') green
                                @else yellow @endif">
                                {{ $workOrder->status }}
                            </span>
                        </div>
                    </div>

                    {{-- Aquí los servicios --}}
                    <!-- Servicios -->
                    <div class="row">
                        <div class="col-12 table-responsive">
                            <h4>Servicios</h4>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Servicio</th>
                                        <th>Precio</th>
                                        <th>Técnico Asignado</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($workOrder->services as $service)
                                        <tr>
                                            <td>{{ $service->name }}</td>
                                            <td>$ {{ $service->price }}</td>
                                            <td>{{ $userNames[$service->pivot->mechanic_id] ?? 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Aquí los productos --}}
                    <div class="row">
                        <div class="col-12 table-responsive">
                            <h4>Productos</h4>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Precio Uni.</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($workOrder->products as $product)
                                        <tr>
                                            <td>{{ $product->name }}</td>
                                            <td>{{ $product->pivot->quantity }}</td>
                                            <td>
                                                $ {{ $product->price }}
                                            </td>
                                            <td>
                                                $ {{ $product->pivot->quantity * $product->price }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Aquí las revisiones con fallos --}}
                    <div class="row">
                        <div class="col-12">
                            <h4>Se realizaron las sgtes. Revisiones:</h4>
                            @if ($revisionsWithFaults->isEmpty())
                                <p>No hay fallos reportados.</p>
                            @else
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Revisión</th>
                                        <th>Diagnostico</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($revisionsWithFaults as $revision)
                                        @php
                                            $fallasConEstadoCero = $revision->faults->filter(function($fault) {
                                                return $fault->status == 0;
                                            })->count();
                                        @endphp

                                        @if ($fallasConEstadoCero == 0)
                                            <tr>
                                                <td>{{ $revision->name }}</td>
                                                <td colspan="2">
                                                    No se encontraron fallas en la revisión
                                                    <span>
                                                        <i class="fas fa-check-circle text-success"></i>
                                                    </span>
                                                </td>
                                            </tr>
                                        @else
                                            @foreach ($revision->faults as $fault)
                                                @if ($fault->status == 0)
                                                    <tr>
                                                        <td>{{ $revision->name }}</td>
                                                        <td>
                                                            @php
                                                            $fallo = preg_replace('/Problema detectado:/', '<strong>Problema detectado:</strong>', $fault->fallo);
                                                            @endphp
                                                            {!! $fallo !!}
                                                            <br>
                                                            @php
                                                            $recomendacion = preg_replace('/Recomendación:/', '<strong>Recomendación:</strong>', $fault->recomendacion);
                                                            @endphp
                                                            {!! $recomendacion !!}
                                                        </td>
                                                        <td>
                                                            <span class="badge badge-danger">Malo</span>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                            @endif
                        </div>
                    </div>

                    {{-- Aquí el resumen de costos --}}
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
                               {{-- <tr>
                                    <th>Impuesto ({{ $workOrder->tax_percentage }}%):</th>
                                    <td>{{ CurrencyHelper::format($tax) }}</td>
                                </tr> --}}
                                <tr>
                                    <th>Total:</th>
                                    <td>{{ CurrencyHelper::format($total) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    {{-- Aquí los botones --}}
                    <div class="row no-print">
                        <div class="col-12">
                            <button onclick="window.print();" class="btn btn-primary">
                                <i class="fas fa-print"></i> Imprimir
                            </button>
                            <a href="{{ route('executive-work-orders.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Volver a la Lista
                            </a>
                            <a href="{{ route('executive-work-orders.download-pdf', $workOrder->id) }}"
                                class="btn btn-success">
                                <i class="fas fa-download"></i> Descargar PDF
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@stop
@section('footer')

    Realizado por <a href="https://www.slaymultimedios.com/"><strong>Slay Multimedios</strong></a> - Laravel
    v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})<br>
    &copy; 2024 PWRTALLER Versión 1.0. Todos los derechos reservados.
@stop
@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <style>
        @media print {
            .no-print {
                display: none;
            }

            .main-header,
            .main-sidebar,
            .main-footer {
                display: none;
            }
        }
    </style>
@stop
