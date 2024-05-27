@extends('adminlte::page')

@section('title', 'Editar Incidente')

@section('content_header')
    <h1>Editar Incidente</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <form method="POST" action="{{ route('incidents.update', $incident->id) }}">
                    @method('PATCH')
                    @csrf
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Editar Incidente</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="name">Nombre</label>
                                <input type="text" name="name" class="form-control" value="{{ $incident->name }}" required>
                            </div>
                            <div class="form-group">
                                <label for="status">Estado</label>
                                <select name="status" class="form-control" required>
                                    <option value="1" {{ $incident->status ? 'selected' : '' }}>Activo</option>
                                    <option value="0" {{ !$incident->status ? 'selected' : '' }}>Inactivo</option>
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


@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
@stop
