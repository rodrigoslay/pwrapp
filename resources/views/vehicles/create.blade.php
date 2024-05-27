@extends('adminlte::page')

@section('title', 'Crear Vehículo')

@section('content_header')
    <h1>Crear Vehículo</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <form method="POST" action="{{ route('vehicles.store') }}">
                    @csrf
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Nuevo Vehículo</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="license_plate">Placa</label>
                                <input type="text" name="license_plate" class="form-control" value="{{ old('license_plate') }}">
                            </div>
                            <div class="form-group">
                                <label for="client_id">Cliente</label>
                                <select name="client_id" class="form-control">
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="registration_date">Fecha de Registro</label>
                                <input type="date" name="registration_date" class="form-control" value="{{ old('registration_date') }}">
                            </div>
                            <div class="form-group">
                                <label for="mileage">Kilometraje</label>
                                <input type="number" name="mileage" class="form-control" value="{{ old('mileage') }}">
                            </div>
                            <div class="form-group">
                                <label for="brand_id">Marca</label>
                                <select name="brand_id" class="form-control">
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="model">Modelo</label>
                                <input type="text" name="model" class="form-control" value="{{ old('model') }}">
                            </div>
                            <div class="form-group">
                                <label for="chassis">Chasis</label>
                                <input type="text" name="chassis" class="form-control" value="{{ old('chassis') }}">
                            </div>
                            <div class="form-group">
                                <label for="color">Color</label>
                                <input type="text" name="color" class="form-control" value="{{ old('color') }}">
                            </div>
                            <div class="form-group">
                                <label for="kilometers">Kilómetros</label>
                                <input type="number" name="kilometers" class="form-control" value="{{ old('kilometers') }}">
                            </div>
                            <div class="form-group">
                                <label for="photo">Foto</label>
                                <input type="file" name="photo" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="status">Estado</label>
                                <select name="status" class="form-control">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
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
