@extends('adminlte::page')

@section('title', 'Crear Grupo de Clientes')

@section('content_header')
    <h1>Crear Grupo de Clientes</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Nuevo Grupo de Clientes</h3>
                    </div>
                    <form action="{{ route('client-groups.store') }}" method="POST">
                        @csrf
                        <div class="card-body">
                            <div class="form-group">
                                <label for="name">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" placeholder="Ingrese el nombre del grupo de clientes" value="{{ old('name') }}">
                                @if ($errors->has('name'))
                                    <span class="text-danger">{{ $errors->first('name') }}</span>
                                @endif
                            </div>
                            <div class="form-group">
                                <label for="discount_percentage">Descuento (%) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="discount_percentage" placeholder="Ingrese el porcentaje de descuento" value="{{ old('discount_percentage') }}">
                                @if ($errors->has('discount_percentage'))
                                    <span class="text-danger">{{ $errors->first('discount_percentage') }}</span>
                                @endif
                            </div>
                            <div class="form-group">
                                <label for="status">Estado <span class="text-danger">*</span></label>
                                <select class="form-control" name="status">
                                    <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Activo</option>
                                    <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactivo</option>
                                </select>
                                @if ($errors->has('status'))
                                    <span class="text-danger">{{ $errors->first('status') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Guardar</button>
                            <a href="{{ route('client-groups.index') }}" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
@stop
