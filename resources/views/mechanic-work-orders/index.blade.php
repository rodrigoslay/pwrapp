@extends('adminlte::page')

@section('title', 'Órdenes de Trabajo Asignadas')

@section('content_header')
    <h1>Órdenes de Trabajo Asignadas</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Listado de Órdenes de Trabajo</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tblData" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Vehículo</th>
                                <th>Cliente</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($workOrders as $workOrder)
                                <tr>
                                    <td>{{ $workOrder->id }}</td>
                                    <td>{{ $workOrder->vehicle->license_plate }} - {{ $workOrder->vehicle->model }}</td>
                                    <td>{{ $workOrder->client->name }}</td>
                                    <td>{{ $workOrder->status }}</td>
                                    <td>
                                        <a href="{{ route('mechanic-work-orders.show', $workOrder->id) }}" class="btn btn-primary btn-sm">Ver Detalles</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('#tblData').DataTable();
        });
    </script>
@stop
