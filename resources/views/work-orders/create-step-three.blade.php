@extends('adminlte::page')

@section('title', 'Seleccionar Productos')

@section('content_header')
    <h1>Seleccionar Productos</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <h5>Crear OT - Productos</h5>
            </div>
        </div>
        <div class="card-body">
            <div class="bs-stepper linear">
                <div class="bs-stepper-header" role="tablist">
                    <div class="step" data-target="#step-one">
                        <button type="button" class="step-trigger" role="tab" aria-controls="step-one" id="step-one-trigger" aria-selected="false" disabled>
                            <span class="bs-stepper-circle">1</span>
                            <span class="bs-stepper-label">Paso 1</span>
                        </button>
                    </div>
                    <div class="line"></div>
                    <div class="step" data-target="#step-two">
                        <button type="button" class="step-trigger" role="tab" aria-controls="step-two" id="step-two-trigger" aria-selected="false" disabled>
                            <span class="bs-stepper-circle">2</span>
                            <span class="bs-stepper-label">Paso 2</span>
                        </button>
                    </div>
                    <div class="line"></div>
                    <div class="step active" data-target="#step-three">
                        <button type="button" class="step-trigger" role="tab" aria-controls="step-three" id="step-three-trigger" aria-selected="true">
                            <span class="bs-stepper-circle">3</span>
                            <span class="bs-stepper-label">Paso 3</span>
                        </button>
                    </div>
                    <div class="line"></div>
                    <div class="step" data-target="#step-four">
                        <button type="button" class="step-trigger" role="tab" aria-controls="step-four" id="step-four-trigger" aria-selected="false" disabled>
                            <span class="bs-stepper-circle">4</span>
                            <span class="bs-stepper-label">Paso 4</span>
                        </button>
                    </div>
                    <div class="line"></div>
                    <div class="step" data-target="#step-five">
                        <button type="button" class="step-trigger" role="tab" aria-controls="step-five" id="step-five-trigger" aria-selected="false" disabled>
                            <span class="bs-stepper-circle">5</span>
                            <span class="bs-stepper-label">Paso 5</span>
                        </button>
                    </div>
                </div>
                <div class="bs-stepper-content">
                    <form action="{{ route('work-orders.store-step-three') }}" method="POST">
                        @csrf
                        <div id="step-three" class="content active dstepper-block" role="tabpanel" aria-labelledby="step-three-trigger">
                            <div class="form-group">
                                <label for="products">Productos</label>
                                <div class="table-responsive">
                                    <table id="products-table" class="table table-bordered table-striped dataTable dtr-inline">
                                        <thead>
                                            <tr>
                                                <th>Seleccionar</th>
                                                <th>Nombre</th>
                                                <th>SKU</th>
                                                <th>Precio</th>
                                                <th>Cantidad</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($products as $product)
                                                <tr>
                                                    <td><input type="checkbox" name="products[{{ $product->id }}]" value="{{ $product->id }}"></td>
                                                    <td>{{ $product->name }}</td>
                                                    <td>{{ $product->sku }}</td>
                                                    <td>{{ $product->price }}</td>
                                                    <td><input type="number" name="quantities[{{ $product->id }}]" value="1" min="1"></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Siguiente</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="card-footer">
            Paso a paso para generar una OT - Recuerda Verificar e ingresar información correcta.
        </div>
    </div>
</div>
@stop

@section('css')
    <link rel="stylesheet" href="https://unpkg.com/bs-stepper@1.7.0/dist/css/bs-stepper.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css">
@stop

@section('js')
    <script src="https://unpkg.com/bs-stepper@1.7.0/dist/js/bs-stepper.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var stepperElement = document.querySelector('.bs-stepper');
            if (stepperElement) {
                try {
                    var stepper = new Stepper(stepperElement);
                } catch (error) {
                    console.error('Error initializing Stepper:', error);
                }
            } else {
                console.error('Stepper element not found.');
            }

            var productsTableElement = $('#products-table');
            if (productsTableElement.length) {
                productsTableElement.DataTable({
                    responsive: true,
                    processing: true,
                    serverSide: false,
                    autoWidth: false,
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json'
                    },
                    paging: true,
                    searching: true,
                });
            } else {
                console.error('Products table element not found.');
            }
        });
    </script>
@stop
