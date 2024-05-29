@extends('adminlte::page')

@section('title', 'Confirmar Servicios')

@section('content_header')
    <h1>Confirmar Servicios</h1>
@stop

@section('content')
    <form action="{{ route('work-orders.store-step-four') }}" method="POST">
        @csrf
        <h3>Servicios Seleccionados</h3>
        @foreach($services as $service)
            <div class="form-group">
                <label for="service_{{ $service['id'] }}">{{ $service['name'] }}</label>
                <input type="text" class="form-control" value="{{ $service['description'] ?? 'Sin descripción' }}" disabled>
            </div>
        @endforeach

        <h3>Productos Seleccionados</h3>
        @foreach($products as $product)
            <div class="form-group">
                <label for="product_{{ $product['id'] }}">{{ $product['name'] }}</label>
                <input type="text" class="form-control" value="{{ $product['description'] ?? 'Sin descripción' }}" disabled>
            </div>
        @endforeach

        <h3>Revisiones Extras</h3>
        @foreach($extra_reviews as $review)
            <div class="form-check">
                <input type="checkbox" name="extra_reviews[]" class="form-check-input" id="extra_review_{{ $review }}" value="{{ $review }}">
                <label class="form-check-label" for="extra_review_{{ $review }}">{{ $review }}</label>
            </div>
        @endforeach

        <button type="submit" class="btn btn-primary">Siguiente</button>
    </form>
@stop
