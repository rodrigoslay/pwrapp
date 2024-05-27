@extends('adminlte::page')

@section('title', 'Crear Servicio')

@section('content_header')
    <h1>Crear Servicio</h1>
@stop

@section('content')
    <div class="container-fluid">
        <form action="{{ route('services.store') }}" method="POST">
            @csrf
            <div class="card">
                <div class="card-header">
                    <h5>Nuevo Servicio</h5>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Nombre</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Descripción</label>
                        <textarea name="description" class="form-control" required>{{ old('description') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="price">Precio</label>
                        <input type="number" name="price" class="form-control" value="{{ old('price') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="discount_applicable">Descuento Aplicable</label>
                        <select name="discount_applicable" class="form-control" required>
                            <option value="1" {{ old('discount_applicable') == '1' ? 'selected' : '' }}>Sí</option>
                            <option value="0" {{ old('discount_applicable') == '0' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="status">Estado</label>
                        <select name="status" class="form-control" required>
                            <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Activo</option>
                            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactivo</option>
                        </select>
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
