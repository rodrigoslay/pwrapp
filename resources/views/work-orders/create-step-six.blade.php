@extends('adminlte::page')

@section('title', 'Resumen de la OT')

@section('content_header')
    <h1>Resumen de la OT</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Detalles de la Orden de Trabajo</h3>
                    </div>
                    <div class="card-body">
                        <h4>Vehículo</h4>
                        <p>{{ $vehicle->license_plate }} - {{ $vehicle->model }}</p>
                        <h4>Servicios Seleccionados</h4>
                        <ul>
                            @foreach($services as $service)
                                <li>{{ $service->name }} - Mecánico: {{ $mechanics[$service->id] }}</li>
                            @endforeach
                        </ul>
                        <h4>Productos Seleccionados</h4>
                        <ul>
                            @foreach($products as $product)
                                <li>{{ $product->name }}</li>
                            @endforeach
                        </ul>
                        <h4>Revisiones Extras</h4>
                        <ul>
                            @foreach($extra_reviews as $review)
                                <li>{{ $review }}</li>
                            @endforeach
                        </ul>
                        <form action="{{ route('work-orders.store-step-six') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary">Crear OT</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
