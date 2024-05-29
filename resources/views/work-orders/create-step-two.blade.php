@extends('adminlte::page')

@section('title', 'Seleccionar Vehículo')

@section('content_header')
    <h1>Seleccionar Vehículo</h1>
@stop

@section('content')
    @if($vehicles->isEmpty())
        <p>No se encontraron vehículos con esa patente.</p>
    @else
        <form action="{{ route('work-orders.select-vehicle') }}" method="POST">
            @csrf
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
        </form>
    @endif

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
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
<script>
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
