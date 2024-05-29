@extends('adminlte::page')

@section('title', 'Asignar Mecánicos')

@section('content_header')
    <h1>Asignar Mecánicos</h1>
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
                            <div class="step" data-target="#step-four">
                                <button type="button" class="step-trigger" role="tab" aria-controls="step-four" id="step-four-trigger" aria-selected="false" disabled>
                                    <span class="bs-stepper-circle">4</span>
                                    <span class="bs-stepper-label">Confirmar Servicios</span>
                                </button>
                            </div>
                            <div class="line"></div>
                            <div class="step active" data-target="#step-five">
                                <button type="button" class="step-trigger" role="tab" aria-controls="step-five" id="step-five-trigger" aria-selected="true">
                                    <span class="bs-stepper-circle">5</span>
                                    <span class="bs-stepper-label">Asignar Mecánicos</span>
                                </button>
                            </div>
                        </div>
                        <div class="bs-stepper-content">
                            <form action="{{ route('work-orders.store-step-five') }}" method="POST">
                                @csrf
                                <div id="step-five" class="content active dstepper-block" role="tabpanel" aria-labelledby="step-five-trigger">
                                    <h3>Servicios Seleccionados</h3>
                                    @foreach($services as $service)
                                        <div class="form-group">
                                            <label for="mechanic_{{ $service['id'] }}">Mecánico para {{ $service['name'] }}</label>
                                            <select name="mechanics[{{ $service['id'] }}]" id="mechanic_{{ $service['id'] }}" class="form-control select2" required>
                                                @foreach($mechanics as $mechanic)
                                                    <option value="{{ $mechanic->id }}">{{ $mechanic->name }}</option>
                                                @endforeach
                                            </select>
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script src="https://unpkg.com/bs-stepper@1.7.0/dist/js/bs-stepper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var stepper = new Stepper(document.querySelector('.bs-stepper'));

            $('.select2').select2();
        });
    </script>
@stop
