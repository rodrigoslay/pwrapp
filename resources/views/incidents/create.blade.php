@extends('adminlte::page')

@section('title', 'Crear Incidente')

@section('content_header')
    <h1>Crear Incidente</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <form method="POST" action="{{ route('incidents.store') }}">
                    @csrf
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Nuevo Incidente</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="name">Nombre</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                            </div>
                            <div class="form-group">
                                <label for="status">Estado</label>
                                <select name="status" class="form-control" required>
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-success">Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
