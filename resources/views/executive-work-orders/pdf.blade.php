<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Orden de Trabajo #{{ $workOrder->id }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            margin: 0 auto;
        }
        .content {
            margin-top: 20px; /* Reducir el margen superior */
            margin-bottom: 50px;
        }
        .invoice-header {
            width: 100%;
            margin-bottom: 10px;
            border-collapse: collapse;
        }
        .invoice-header td {
            padding: 5px;
            border: none; /* Quitar el borde */
        }
        .invoice-header img {
            max-height: 50px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }
        .badge {
            padding: 3px;
            color: #fff;
        }
        .badge-success {
            background-color: green;
        }
        .badge-warning {
            background-color: yellow;
            color: #000;
        }
        .badge-danger {
            background-color: red;
        }
    </style>
</head>
<body>
    <div class="content">
        <table class="invoice-header">
            <tr>
                <td style="width: 30%;">
                    <strong>Cliente:</strong>
                    <address>
                        {{ $workOrder->client->name }}<br>
                        Vehículo: {{ $workOrder->vehicle->brand->name }} {{ $workOrder->vehicle->model }}<br>
                        Patente: {{ $workOrder->vehicle->license_plate }}<br>
                    </address>
                    <strong>Ejecutivo:</strong>
                    <address>{{ $workOrder->createdBy->name }}</address>
                </td>
                
                <td style="width: 30%;">
                    <p><b>OT ID:</b> {{ $workOrder->id }}</p>
                    <p><b>Fecha de Ingreso:</b> {{ $workOrder->created_at->format('d/m/Y H:i:s') }}</p>
                    <p><b>Fecha de Salida:</b> {{ $workOrder->updated_at->format('d/m/Y H:i:s') }}</p>
                    <p><b>Estado:</b> <span class="badge" style="background-color:
                        @if($workOrder->status === 'Abierto') red
                        @elseif($workOrder->status === 'Facturado' || $workOrder->status === 'Cerrado') green
                        @else yellow
                        @endif">
                        {{ $workOrder->status }}
                    </span></p>
                </td>
            </tr>
        </table>

        <h4>Servicios</h4>
        <table>
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
                            <span class="badge badge-{{ $service->pivot->status == 'completado' ? 'success' : ($service->pivot->status == 'iniciado' ? 'warning' : 'danger') }}">
                                {{ ucfirst($service->pivot->status) }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <h4>Productos</h4>
        <table>
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
                            <span class="badge badge-{{ $product->pivot->status == 'entregado' ? 'success' : 'danger' }}">
                                {{ ucfirst($product->pivot->status) }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <h4>Revisiones con Fallos</h4>
        @if($revisionsWithFaults->isEmpty())
            <p>No hay fallos reportados.</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Revisión</th>
                        <th>Fallo</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($revisionsWithFaults as $revision)
                        @foreach ($revision->faults as $fault)
                            <tr>
                                <td>{{ $revision->name }}</td>
                                <td>{{ $fault->fallo }}</td>
                                <td>
                                    <span class="badge badge-{{ $fault->status == 1 ? 'success' : 'danger' }}">
                                        {{ $fault->status == 1 ? 'Bueno' : 'Malo' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        @endif

        <h4>Resumen de Costos</h4>
        @php
            use App\Helpers\CurrencyHelper;

            $subtotal = $workOrder->services->sum('price') + $workOrder->products->sum(function($product) {
                return $product->pivot->quantity * $product->price;
            });
            $discount = $subtotal * ($workOrder->client->clientGroup->discount_percentage / 100);
            $tax = ($subtotal - $discount) * ($workOrder->tax_percentage / 100);
            $total = $subtotal - $discount + $tax;
        @endphp
        <table>
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
    @section('footer')

    Realizado por <a href="https://www.slaymultimedios.com/"><strong>Slay Multimedios</strong></a> - Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})<br>
    &copy; 2024 PWRTALLER Versión 1.0. Todos los derechos reservados.
@stop
</body>
</html>
