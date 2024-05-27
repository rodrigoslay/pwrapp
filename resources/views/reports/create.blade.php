@extends('adminlte::page')

@section('title', 'Crear Reporte')

@section('content_header')
    <h1>Crear Reporte</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <form method="POST" action="{{ route('reports.store') }}">
                    @csrf
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Nuevo Reporte</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="title">Título</label>
                                <input type="text" name="title" class="form-control" value="{{ old('title') }}">
                            </div>
                            <div class="form-group">
                                <label for="content">Contenido</label>
                                <textarea name="content" class="form-control">{{ old('content') }}</textarea>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-success">Guardar</button>
                            </div>
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
