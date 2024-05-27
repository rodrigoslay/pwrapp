@extends('adminlte::page')

@section('title', 'Confirmar Servicios y Revisar Extras')

@section('content_header')
    <h1>Confirmar Servicios y Revisar Extras</h1>
@stop

@section('content')
    <form action="{{ route('work-orders.store-step-four') }}" method="POST">
        @csrf
        <h3>Servicios Seleccionados</h3>
        <ul>
            @foreach($services as $service)
                <li>{{ $service }}</li>
            @endforeach
        </ul>
        <h3>Productos Seleccionados</h3>
        <ul>
            @foreach($products as $product)
                <li>{{ $product }}</li>
            @endforeach
        </ul>
        <div class="form-group">
            <label for="extra_reviews">Seleccione Revisiones Extras (Opcional)</label>
            <select name="extra_reviews[]" id="extra_reviews" class="form-control select2" multiple="multiple">
                @foreach($extra_reviews as $review)
                    <option value="{{ $review }}">{{ $review }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Siguiente</button>
    </form>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>
@stop
