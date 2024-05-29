@extends('adminlte::page')

@section('title', 'Agregar Servicios y Productos')

@section('content_header')
    <h1>Agregar Servicios y Productos</h1>
@stop

@section('content')
    <form action="{{ route('work-orders.store-step-three') }}" method="POST">
        @csrf
        <input type="hidden" name="vehicle_id" value="{{ $vehicle_id }}">

        <h3>Servicios Disponibles</h3>
        @foreach($services as $service)
            <div class="form-group">
                <input type="checkbox" name="services[]" value="{{ $service->id }}">
                <label for="service_{{ $service->id }}">{{ $service->name }}</label>
            </div>
        @endforeach

        <h3>Productos Disponibles</h3>
        @foreach($products as $product)
            <div class="form-group">
                <input type="checkbox" name="products[{{ $product->id }}]" value="{{ $product->id }}">
                <label for="product_{{ $product->id }}">{{ $product->name }}</label>
                <input type="number" name="quantities[{{ $product->id }}]" value="1" min="1" class="form-control" style="width: 80px; display: inline-block;">
            </div>
        @endforeach

        <button type="submit" class="btn btn-primary">Siguiente</button>
    </form>
@stop
