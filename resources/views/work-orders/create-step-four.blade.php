@extends('adminlte::page')

@section('title', 'Confirmar Servicios')

@section('content_header')
    <h1>Confirmar Servicios</h1>
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
                            <div class="step" data-target="#step-three">
                                <button type="button" class="step-trigger" role="tab" aria-controls="step-three" id="step-three-trigger" aria-selected="false" disabled>
                                    <span class="bs-stepper-circle">3</span>
                                    <span class="bs-stepper-label">Agregar Servicios y Productos</span>
                                </button>
                            </div>
                            <div class="line"></div>
                            <div class="step active" data-target="#step-four">
                                <button type="button" class="step-trigger" role="tab" aria-controls="step-four" id="step-four-trigger" aria-selected="true">
                                    <span class="bs-stepper-circle">4</span>
                                    <span class="bs-stepper-label">Confirmar Servicios</span>
                                </button>
                            </div>
                        </div>
                        <div class="bs-stepper-content">
                            <form action="{{ route('work-orders.store-step-four') }}" method="POST">
                                @csrf
                                <div id="step-four" class="content active dstepper-block" role="tabpanel" aria-labelledby="step-four-trigger">
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
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://unpkg.com/bs-stepper@1.7.0/dist/css/bs-stepper.min.css">
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script src="https://unpkg.com/bs-stepper@1.7.0/dist/js/bs-stepper.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var stepper = new Stepper(document.querySelector('.bs-stepper'));
        });
    </script>
@stop
