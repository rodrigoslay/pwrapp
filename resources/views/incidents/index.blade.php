@extends('adminlte::page')

@section('title', 'Incidentes')

@section('content_header')
    <h1>Incidentes</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div id="errorBox"></div>
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Lista de Incidentes</h3>
                        <a class="btn btn-success float-right" href="{{ route('incidents.create') }}">Crear Incidente</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tblData" class="table table-bordered table-striped dataTable dtr-inline">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($incidents as $incident)
                                        <tr>
                                            <td>{{ $incident->id }}</td>
                                            <td>{{ $incident->name }}</td>
                                            <td>{{ $incident->status ? 'Activo' : 'Inactivo' }}</td>
                                            <td>
                                                <a href="{{ route('incidents.edit', $incident->id) }}" class="btn btn-primary btn-sm">Editar</a>
                                                <form action="{{ route('incidents.destroy', $incident->id) }}" method="POST" style="display:inline-block;">
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
                        {{ $incidents->links() }}
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
