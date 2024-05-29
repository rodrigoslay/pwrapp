@extends('adminlte::page')

@section('title', 'Detalle de Orden de Trabajo')

@section('content_header')
    <h1>Detalle de Orden de Trabajo</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3>OT #{{ $workOrder->id }}</h3>
            </div>
            <div class="card-body">
                <h4>Cliente: {{ $workOrder->client->name }}</h4>
                <h4>Vehículo: {{ $workOrder->vehicle->brand->name }} {{ $workOrder->vehicle->model }}</h4>

                <h4>Productos</h4>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($workOrder->products as $product)
                            <tr>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->pivot->quantity }}</td>
                                <td>
                                    <form class="update-product-status-form">
                                        @csrf
                                        <input type="hidden" name="work_order_id" value="{{ $workOrder->id }}">
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <select name="status" class="form-control product-status-select" data-product-id="{{ $product->id }}" data-work-order-id="{{ $workOrder->id }}">
                                            <option value="pendiente" {{ $product->pivot->status == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                            <option value="entregado" {{ $product->pivot->status == 'entregado' ? 'selected' : '' }}>Entregado</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.1.0/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.16/dist/sweetalert2.min.css">
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.16/dist/sweetalert2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.product-status-select').on('change', function() {
                var form = $(this).closest('form');
                var status = $(this).val();

                $.ajax({
                    url: `/warehouse-work-orders/${form.find('input[name="work_order_id"]').val()}/update-product-status/${form.find('input[name="product_id"]').val()}`,
                    type: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: 'Estado del producto actualizado con éxito.'
                        });
                    },
                    error: function(response) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Hubo un error al actualizar el estado del producto.'
                        });
                    }
                });
            });
        });
    </script>
@stop
