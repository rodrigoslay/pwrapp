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
                                <button type="button" class="step-trigger" role="tab"
                                        aria-controls="step-one"
                                        id="step-one-trigger" aria-selected="false" disabled>
                                    <span class="bs-stepper-circle">1</span>
                                    <span class="bs-stepper-label">Paso 1</span>
                                </button>
                            </div>
                            <div class="line"></div>
                            <div class="step active" data-target="#step-two">
                                <button type="button" class="step-trigger" role="tab"
                                        aria-controls="step-two"
                                        id="step-two-trigger" aria-selected="true">
                                    <span class="bs-stepper-circle">2</span>
                                    <span class="bs-stepper-label">Paso 2</span>
                                </button>
                            </div>
                            <div class="line"></div>
                            <div class="step" data-target="#step-three">
                                <button type="button" class="step-trigger" role="tab"
                                        aria-controls="step-three"
                                        id="step-three-trigger" aria-selected="false" disabled>
                                    <span class="bs-stepper-circle">3</span>
                                    <span class="bs-stepper-label">Paso 3</span>
                                </button>
                            </div>
                            <div class="line"></div>
                            <div class="step" data-target="#step-four">
                                <button type="button" class="step-trigger" role="tab"
                                        aria-controls="step-four"
                                        id="step-four-trigger" aria-selected="false" disabled>
                                    <span class="bs-stepper-circle">4</span>
                                    <span class="bs-stepper-label">Paso 4</span>
                                </button>
                            </div>
                            <div class="line"></div>
                            <div class="step" data-target="#step-five">
                                <button type="button" class="step-trigger" role="tab"
                                        aria-controls="step-five"
                                        id="step-five-trigger" aria-selected="false" disabled>
                                    <span class="bs-stepper-circle">5</span>
                                    <span class="bs-stepper-label">Paso 5</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="bs-stepper-content">
                        <form id="work-order-form" action="{{ route('work-orders.store-step-two') }}" method="POST">
                            @csrf
                            <div id="step-two" class="content active dstepper-block" role="tabpanel"
                                 aria-labelledby="step-two-trigger">
                                <div class="form-group">
                                    <label for="services">Servicios</label>
                                    <div class="table-responsive">
                                        <table id="services-table"
                                               class="table table-bordered table-striped dataTable dtr-inline">
                                            <thead>
                                            <tr>
                                                <th>Seleccionar</th>
                                                <th>SKU</th>
                                                <th>Nombre</th>
                                                <th>Precio</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach ($services as $service)
                                                <tr>
                                                    <td><input type="checkbox" name="services[]"
                                                               value="{{ $service->id }}"></td>
                                                    <td>{{ $service->sku }}</td>
                                                    <td>{{ $service->name }}</td>
                                                    <td>{{ $service->price }}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <hr>
                                <div class="form-group">
                                    <label for="revisions">Revisiones</label>
                                    <div class="table-responsive">
                                        <table id="revisions-table" class="table table-bordered table-striped
                                        dataTable dtr-inline">
                                            <thead>
                                            <tr>
                                                <th>Seleccionar</th>
                                                <th>Nombre</th>
                                                <th>Descripción</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach ($revisions as $revision)
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" name="revisions[]"
                                                               value="{{ $revision->id }}">
                                                    </td>
                                                    <td>{{ $revision->name }}</td>
                                                    <td>{{ $revision->description }}</td>
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
                Paso a paso para generar una OT - Recuerda Verificar e ingresar información
                correcta.
            </div>
        </div>
    </div>
@stop

@section('footer')
    Realizado por <a href="https://www.slaymultimedios.com/"><strong>Slay
        Multimedios</strong></a> - Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{
    PHP_VERSION }})<br>
    &copy; 2024 PWRTALLER Versión 1.0. Todos los derechos reservados.
@stop

@section('css')
    <link rel="stylesheet" href="https://unpkg.com/bs-stepper@1.7.0/dist/css/bs-stepper.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop


@section('js')
<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://unpkg.com/bs-stepper@1.7.0/dist/js/bs-stepper.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    var selectedServices = [];

    $(document).ready(function() {
        var table = $('#services-table').DataTable({
            responsive: true,
            autoWidth: false,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json'
            },
            paging: true,
            searching: true,
        });

        // Manejar la selección de checkboxes
        $('#services-table tbody').on('change', 'input[type="checkbox"]', function() {
            var serviceId = $(this).val();
            if (this.checked) {
                if (!selectedServices.includes(serviceId)) {
                    selectedServices.push(serviceId);
                }
            } else {
                selectedServices = selectedServices.filter(id => id !== serviceId);
            }
        });

        // Manejar la paginación para mantener las selecciones
        table.on('draw', function() {
            table.rows().every(function() {
                var row = this.node();
                var checkbox = $(row).find('input[type="checkbox"]');
                var serviceId = checkbox.val();
                if (selectedServices.includes(serviceId)) {
                    checkbox.prop('checked', true);
                } else {
                    checkbox.prop('checked', false);
                }
            });
        });

        // Enviar todas las selecciones al servidor
        $('#work-order-form').on('submit', function() {
            selectedServices.forEach(function(serviceId) {
                $('<input>').attr({
                    type: 'hidden',
                    name: 'services[]',
                    value: serviceId
                }).appendTo('#work-order-form');
            });
        });
    });
</script>
@stop

