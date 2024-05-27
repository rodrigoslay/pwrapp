@extends('adminlte::page')

@section('title', 'Clientes')

@section('content_header')
    <h1>Clientes</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div id="errorBox"></div>
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Lista de Clientes</h3>
                        <a class="btn btn-success float-right" href="{{ route('clients.create') }}">Crear Cliente</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tblData" class="table table-bordered table-striped dataTable dtr-inline">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>RUT</th>
                                        <th>Email</th>
                                        <th>Teléfono</th>
                                        <th>Grupo de Clientes</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($clients as $client)
                                        <tr>
                                            <td>{{ $client->id }}</td>
                                            <td>{{ $client->name }}</td>
                                            <td>{{ $client->rut }}</td>
                                            <td>{{ $client->email }}</td>
                                            <td>{{ $client->phone }}</td>
                                            <td>{{ $client->clientGroup->name }}</td>
                                            <td>
                                                <a href="{{ route('clients.edit', $client->id) }}" class="btn btn-primary btn-sm">Editar</a>
                                                <form action="{{ route('clients.destroy', $client->id) }}" method="POST" style="display:inline-block;">
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
                    </div>
                    <div class="card-footer">
                        {{ $clients->links() }}
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
        $(document).ready(function(){
            $('#tblData').DataTable();
        });
    </script>
@stop
