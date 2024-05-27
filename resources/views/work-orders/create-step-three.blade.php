@extends('adminlte::page')

@section('title', 'Agregar Servicios y Productos')

@section('content_header')
    <h1>Agregar Servicios y Productos</h1>
@stop

@section('content')
    <form action="{{ route('work-orders.store-step-three') }}" method="POST">
        @csrf
        <input type="hidden" name="vehicle_id" value="{{ $vehicle_id }}">
        <div class="form-group">
            <label for="services">Seleccione Servicios</label>
            <select name="services[]" id="services" class="form-control select2" multiple="multiple" required>
                @foreach($services as $service)
                    <option value="{{ $service->id }}">{{ $service->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="products">Seleccione Productos (Opcional)</label>
            <select name="products[]" id="products" class="form-control select2" multiple="multiple">
                @foreach($products as $product)
                    <option value="{{ $product->id }}">{{ $product->name }}</option>
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
