@extends('adminlte::page')

@section('title', 'Agregar Servicios y Productos')

@section('content_header')
    <h1>Agregar Servicios y Productos</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Formulario Paso a Paso</h3>
                </div>
                <div class="card-body">
                    <div class="bs-stepper linear">
                        <div class="bs-stepper-header" role="tablist">
                            <div class="step" data-target="#step-one">
                                <button type="button" class="step-trigger" role="tab" aria-controls="step-one" id="step-one-trigger" aria-selected="false" disabled>
                                    <span class="bs-stepper-circle">1</span>
                                    <span class="bs-stepper-label">Buscar Vehículo</span>
                                </button>
                            </div>
                            <div class="line"></div>
                            <div class="step" data-target="#step-two">
                                <button type="button" class="step-trigger" role="tab" aria-controls="step-two" id="step-two-trigger" aria-selected="false" disabled>
                                    <span class="bs-stepper-circle">2</span>
                                    <span class="bs-stepper-label">Seleccionar Vehículo</span>
                                </button>
                            </div>
                            <div class="line"></div>
                            <div class="step active" data-target="#step-three">
                                <button type="button" class="step-trigger" role="tab" aria-controls="step-three" id="step-three-trigger" aria-selected="true">
                                    <span class="bs-stepper-circle">3</span>
                                    <span class="bs-stepper-label">Agregar Servicios y Productos</span>
                                </button>
                            </div>
                        </div>
                        <div class="bs-stepper-content">
                            <form action="{{ route('work-orders.store-step-three') }}" method="POST">
                                @csrf
                                <input type="hidden" name="vehicle_id" value="{{ $vehicle_id }}">

                                <h3>Servicios Disponibles</h3>
                                <table id="services-table" class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>Seleccionar</th>
                                            <th>Nombre</th>
                                            <th>Descripción</th>
                                            <th>Precio</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($services as $service)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="services[]" value="{{ $service->id }}">
                                                </td>
                                                <td>{{ $service->name }}</td>
                                                <td>{{ $service->description }}</td>
                                                <td>{{ $service->price }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                                <h3>Productos Disponibles</h3>
                                <table id="products-table" class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>Seleccionar</th>
                                            <th>Nombre</th>
                                            <th>Descripción</th>
                                            <th>Precio</th>
                                            <th>Cantidad</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($products as $product)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="products[{{ $product->id }}]" value="{{ $product->id }}">
                                                </td>
                                                <td>{{ $product->name }}</td>
                                                <td>{{ $product->description }}</td>
                                                <td>{{ $product->price }}</td>
                                                <td>
                                                    <input type="number" name="quantities[{{ $product->id }}]" value="1" min="1" class="form-control" style="width: 80px;">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                                <button type="submit" class="btn btn-primary">Siguiente</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    Paso a paso para generar una OT - Recuerda Verificar e ingresar información correcta.
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://unpkg.com/bs-stepper@1.7.0/dist/css/bs-stepper.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script src="https://unpkg.com/bs-stepper@1.7.0/dist/js/bs-stepper.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var stepper = new Stepper(document.querySelector('.bs-stepper'));

            $('#services-table').DataTable({
                responsive: true,
                autoWidth: false,
            });

            $('#products-table').DataTable({
                responsive: true,
                autoWidth: false,
            });
        });
    </script>
@stop
