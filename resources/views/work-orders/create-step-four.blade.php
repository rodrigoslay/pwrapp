@extends('adminlte::page')

@section('title', 'Asignar Mecánicos')

@section('content_header')
    <h1>Asignar Mecánicos</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <h5>Crear OT - Mecánicos</h5>
            </div>
        </div>
        <div class="card-body">
            <div class="bs-stepper">
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
                    <form action="{{ route('work-orders.store-step-four') }}" method="POST">
                        @csrf
                        <div id="step-4" class="content active dstepper-block" role="tabpanel" aria-labelledby="step-4-trigger">
                            <div class="row">
                                <div class="col-md-12">
                                    <h4>Servicios</h4>
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Servicio</th>
                                                <th>Mecánico</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($services as $service)
                                                <tr>
                                                    <td>{{ $service->name }}</td>
                                                    <td>
                                                        <select name="mechanics[{{ $service->id }}]" class="form-control">
                                                            <option value="">Seleccionar Mecánico</option>
                                                            @foreach ($mechanics as $mechanic)
                                                                <option value="{{ $mechanic->id }}">{{ $mechanic->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <h4>Mecánicos y Servicios No Completados</h4>
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Mecánico</th>
                                                <th>Servicios No Completados</th>
                                                <th>Servicios Completados</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($mechanicServiceCounts as $mechanicServiceCount)
                                                <tr>
                                                    <td>{{ $mechanicServiceCount['name'] }}</td>
                                                    <td>{{ $mechanicServiceCount['not_completed_count'] }}</td>
                                                    <td>{{ $mechanicServiceCount['completed_count'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-success float-right">Guardar y Continuar</button>
                                    <a href="{{ route('work-orders.create-step-three') }}" class="btn btn-primary float-right mr-2">Volver</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bs-stepper@1.7.0/dist/css/bs-stepper.min.css">
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/bs-stepper@1.7.0/dist/js/bs-stepper.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            window.stepper = new Stepper(document.querySelector('.bs-stepper'))
        })
    </script>
@stop
