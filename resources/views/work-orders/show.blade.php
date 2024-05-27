@extends('adminlte::page')

@section('title', 'Detalles de la Orden de Trabajo')

@section('content_header')
    <h1>Detalles de la Orden de Trabajo</h1>
@stop

@section('content')
    <h3>Vehículo</h3>
    <p>Patente: {{ $workOrder->vehicle->license_plate }}</p>
    <p>Modelo: {{ $workOrder->vehicle->model }}</p>

    <h3>Servicios</h3>
    <ul>
        @foreach($workOrder->services as $service)
            <li>{{ $service->name }} - Mecánico: {{ $service->pivot->mechanic_id }}</li>
        @endforeach
    </ul>

    <h3>Productos</h3>
    <ul>
        @foreach($workOrder->products as $product)
            <li>{{ $product->name }}</li>
        @endforeach
    </ul>

    <h3>Revisiones Extras</h3>
    <ul>
        @if($workOrder->extra_reviews)
            @foreach($workOrder->extra_reviews as $review)
                <li>{{ $review }}</li>
            @endforeach
        @else
            <li>No se seleccionaron revisiones extras</li>
        @endif
    </ul>
@stop
