@extends('adminlte::page')

@section('title', 'Buscar Vehículo')

@section('content_header')
    <h1>Buscar Vehículo por Patente</h1>
@stop

@section('content')
    <form action="{{ route('work-orders.search-vehicle') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="license_plate">Patente del Vehículo</label>
            <input type="text" name="license_plate" id="license_plate" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Buscar</button>
    </form>
@stop
