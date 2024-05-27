@extends('adminlte::page')

@section('title', 'Vehículos')

@section('content_header')
    <h1>Vehículos</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div id="errorBox"></div>
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <h5>Lista</h5>
                </div>
                <a class="float-right btn btn-primary btn-xs m-0" href="{{route('vehicles.create')}}"><i class="fas fa-plus"></i> Añadir</a>
            </div>
            <div class="card-body">
                <!--DataTable-->
                <div class="table-responsive">
                    <table id="tblData" class="table table-bordered table-striped dataTable dtr-inline">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Placa</th>
                                <th>Cliente</th>
                                <th>Marca</th>
                                <th>Modelo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vehicles as $vehicle)
                                <tr>
                                    <td>{{ $vehicle->id }}</td>
                                    <td>{{ $vehicle->license_plate }}</td>
                                    <td>{{ $vehicle->client->name }}</td>
                                    <td>{{ $vehicle->brand->name }}</td>
                                    <td>{{ $vehicle->model }}</td>
                                    <td>
                                        <a href="{{ route('vehicles.edit', $vehicle->id) }}" class="btn btn-primary btn-sm">Editar</a>
                                        <form action="{{ route('vehicles.destroy', $vehicle->id) }}" method="POST" style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $vehicles->links() }}
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
        $('#tblData').DataTable();
    });
</script>
@stop

@section('plugins.Datatables', true)
