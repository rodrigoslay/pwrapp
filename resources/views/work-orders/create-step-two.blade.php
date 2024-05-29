@extends('adminlte::page')

@section('title', 'Seleccionar Vehículo')

@section('content_header')
    <h1>Seleccionar Vehículo</h1>
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
                            <div class="step active" data-target="#step-two">
                                <button type="button" class="step-trigger" role="tab" aria-controls="step-two" id="step-two-trigger" aria-selected="true">
                                    <span class="bs-stepper-circle">2</span>
                                    <span class="bs-stepper-label">Seleccionar Vehículo</span>
                                </button>
                            </div>
                        </div>
                        <div class="bs-stepper-content">
                            @if($vehicles->isEmpty())
                                <div class="alert alert-warning" role="alert">
                                    No se encontraron vehículos con esa patente.
                                </div>
                                <a href="{{ route('work-orders.create-step-one') }}" class="btn btn-secondary">Volver a Buscar</a>
                            @else
                                <form action="{{ route('work-orders.select-vehicle') }}" method="POST">
                                    @csrf
                                    <div id="step-two" class="content active dstepper-block" role="tabpanel" aria-labelledby="step-two-trigger">
                                        <div class="form-group">
                                            <label for="vehicle_id">Seleccione un Vehículo</label>
                                            <select name="vehicle_id" id="vehicle_id" class="form-control" required>
                                                @foreach($vehicles as $vehicle)
                                                    <option value="{{ $vehicle->id }}">{{ $vehicle->license_plate }} - {{ $vehicle->model }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="client_id">Seleccione un Cliente (si es diferente al actual)</label>
                                            <select name="client_id" id="client_id" class="form-control">
                                                @foreach($clients as $client)
                                                    <option value="{{ $client->id }}" {{ $latestClient && $latestClient->id == $client->id ? 'selected' : '' }}>
                                                        {{ $client->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Siguiente</button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    Paso a paso para generar una OT - Recuerda Verificar e ingresar información correcta.
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para agregar un nuevo cliente -->
    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#addClientModal">
        Agregar Nuevo Cliente
    </button>

    <div class="modal fade" id="addClientModal" tabindex="-1" role="dialog" aria-labelledby="addClientModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addClientModalLabel">Agregar Nuevo Cliente</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="addClientForm">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Nombre</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="rut">RUT</label>
                            <input type="text" name="rut" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Teléfono</label>
                            <input type="text" name="phone" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="client_group_id">Grupo de Clientes</label>
                            <select name="client_group_id" class="form-control" required>
                                @foreach($clientGroups as $group)
                                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="status">Estado</label>
                            <select name="status" class="form-control" required>
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cliente</button>
                    </div>
                </form>
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

        $(document).ready(function() {
            $('#addClientForm').on('submit', function(event) {
                event.preventDefault();
                $.ajax({
                    url: "{{ route('clients.store') }}",
                    method: "POST",
                    data: $(this).serialize(),
                    success: function(response) {
                        $('#client_id').append(new Option(response.name, response.id, true, true));
                        $('#addClientModal').modal('hide');
                    },
                    error: function(response) {
                        alert('Error al agregar el cliente.');
                    }
                });
            });
        });
    </script>
@stop
