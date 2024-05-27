@extends('adminlte::page')

@section('title', 'Crear Configuración')

@section('content_header')
    <h1>Crear Configuración</h1>
@stop

@section('content')
    <div class="container-fluid">
        <form action="{{ route('settings.store') }}" method="POST">
            @csrf
            <div class="card">
                <div class="card-header">
                    <h5>Nueva Configuración</h5>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Nombre</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="value">Valor</label>
                        <input type="text" name="value" class="form-control" value="{{ old('value') }}" required>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </form>
    </div>
@stop



@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
@stop
