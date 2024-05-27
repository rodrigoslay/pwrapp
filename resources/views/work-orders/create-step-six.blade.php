@extends('adminlte::page')

@section('title', 'Confirmar Orden de Trabajo')

@section('content_header')
    <h1>Confirmar Orden de Trabajo</h1>
@stop

@section('content')
    <h3>Vehículo</h3>
    <p>Patente: {{ $vehicle->license_plate }}</p>
    <p>Modelo: {{ $vehicle->model }}</p>

    <h3>Servicios Seleccionados</h3>
    <ul>
        @foreach($services as $service)
            <li>{{ $service }} - Mecánico: {{ $mechanics[$service] }}</li>
        @endforeach
    </ul>

    <h3>Productos Seleccionados</h3>
    <ul>
        @foreach($products as $product)
            <li>{{ $product }}</li>
        @endforeach
    </ul>

    <h3>Revisiones Extras</h3>
    <ul>
        @if($extra_reviews)
            @foreach($extra_reviews as $review)
                <li>{{ $review }}</li>
            @endforeach
        @else
            <li>No se seleccionaron revisiones extras</li>
        @endif
    </ul>

    <form action="{{ route('work-orders.store-step-six') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-primary">Crear Orden de Trabajo</button>
    </form>
@stop
