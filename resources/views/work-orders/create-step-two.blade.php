@extends('adminlte::page')

@section('title', 'Seleccionar Servicios y Revisiones')

@section('content_header')
    <h1>Seleccionar Servicios y Revisiones</h1>
@stop

@section('content')

<div class="container-fluid">
        <div class="col-md-12">
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Crear OT - Servicios y Revisiones</h3>
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
                            <div class="step active" data-target="#step-two">
                                <button type="button" class="step-trigger" role="tab" aria-controls="step-two" id="step-two-trigger" aria-selected="true">
                                    <span class="bs-stepper-circle">2</span>
                                    <span class="bs-stepper-label">Paso 2</span>
                                </button>
                            </div>
                            <div class="line"></div>
                            <div class="step" data-target="#step-three">
                                <button type="button" class="step-trigger" role="tab" aria-controls="step-three" id="step-three-trigger" aria-selected="false" disabled>
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
                            <form action="{{ route('work-orders.store-step-two') }}" method="POST">
                                @csrf
                                <div id="step-two" class="content active dstepper-block" role="tabpanel" aria-labelledby="step-two-trigger">
                                    <div class="form-group">
                                        <label for="services">Servicios</label>
                                        <table id="services-table" class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Seleccionar</th>
                                                    <th>Nombre</th>
                                                    <th>Precio</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($services as $service)
                                                    <tr>
                                                        <td><input type="checkbox" name="services[]" value="{{ $service->id }}"></td>
                                                        <td>{{ $service->name }}</td>
                                                        <td>{{ $service->price }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <hr>
                                    <div class="form-group">
                                        <label for="revisions">Revisiones</label>
                                        <table id="revisions-table" class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Seleccionar</th>
                                                    <th>Nombre</th>
                                                    <th>Descripción</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($revisions as $revision)
                                                    <tr>
                                                        <td><input type="checkbox" name="revisions[]" value="{{ $revision->id }}"></td>
                                                        <td>{{ $revision->name }}</td>
                                                        <td>{{ $revision->description }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
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
    </div>

@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bs-stepper/dist/css/bs-stepper.min.css">
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/bs-stepper/dist/js/bs-stepper.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var stepperElement = document.querySelector('.bs-stepper');
            if (stepperElement) {
                var stepper = new Stepper(stepperElement);
            }

            $('#services-table, #revisions-table').DataTable({
                responsive: true,
                autoWidth: false,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json'
                }
            });
        });
    </script>
@stop
