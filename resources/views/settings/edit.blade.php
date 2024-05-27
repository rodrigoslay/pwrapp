@extends('adminlte::page')

@section('title', 'Editar Configuración')

@section('content_header')
    <h1>Editar Configuración</h1>
@stop

@section('content')
    <div class="container-fluid">
        <form action="{{ route('settings.update', $setting->id) }}" method="POST">
            @csrf
            @method('PATCH')
            <div class="card">
                <div class="card-header">
                    <h5>Actualizar Configuración</h5>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Nombre</label>
                        <input type="text" name="name" class="form-control" value="{{ $setting->name }}" required>
                    </div>
                    <div class="form-group">
                        <label for="value">Valor</label>
                        <input type="text" name="value" class="form-control" value="{{ $setting->value }}" required>
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
