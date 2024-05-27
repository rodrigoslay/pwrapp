@extends('adminlte::page')

@section('title', 'Editar Cliente')

@section('content_header')
    <h1>Editar Cliente</h1>
@stop

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Editar Cliente</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('clients.update', $client->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="form-group">
                                <label for="name">Nombre</label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ $client->name }}" required>
                            </div>
                            <div class="form-group">
                                <label for="rut">RUT</label>
                                <input type="text" class="form-control" id="rut" name="rut" value="{{ $client->rut }}" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="{{ $client->email }}" required>
                            </div>
                            <div class="form-group">
                                <label for="phone">Teléfono</label>
                                <input type="text" class="form-control" id="phone" name="phone" value="{{ $client->phone }}" required>
                            </div>
                            <div class="form-group">
                                <label for="client_group_id">Grupo de Cliente</label>
                                <select class="form-control" id="client_group_id" name="client_group_id" required>
                                    @foreach ($clientGroups as $group)
                                        <option value="{{ $group->id }}" {{ $group->id == $client->client_group_id ? 'selected' : '' }}>{{ $group->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="status">Estado</label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="1" {{ $client->status ? 'selected' : '' }}>Activo</option>
                                    <option value="0" {{ !$client->status ? 'selected' : '' }}>Inactivo</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </form>
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
@stop
