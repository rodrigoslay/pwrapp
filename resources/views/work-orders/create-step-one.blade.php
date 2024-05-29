@extends('adminlte::page')

@section('title', 'Buscar Vehículo')

@section('content_header')
    <h1>Buscar Vehículo por Patente</h1>
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
                            <div class="step active" data-target="#step-one">
                                <button type="button" class="step-trigger" role="tab" aria-controls="step-one" id="step-one-trigger" aria-selected="true">
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
                        </div>
                        <div class="bs-stepper-content">
                            <form action="{{ route('work-orders.search-vehicle') }}" method="POST">
                                @csrf
                                <div id="step-one" class="content active dstepper-block" role="tabpanel" aria-labelledby="step-one-trigger">
                                    <div class="form-group">
                                        <label for="license_plate">Patente del Vehículo</label>
                                        <input type="text" name="license_plate" id="license_plate" class="form-control" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Buscar</button>
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
