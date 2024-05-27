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
                        <option value="{{ $client->id }}" {{ $latestClient && $client->id == $latestClient->id ? 'selected' : '' }}>
                            {{ $client->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#addClientModal">
                Agregar Nuevo Cliente
            </button>
            <button type="submit" class="btn btn-primary">Siguiente</button>
        </form>
    @endif

    <!-- Modal para agregar nuevo cliente -->
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
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="client_name">Nombre</label>
                            <input type="text" class="form-control" id="client_name" name="client_name" required>
                        </div>
                        <div class="form-group">
                            <label for="client_rut">RUT</label>
                            <input type="text" class="form-control" id="client_rut" name="client_rut" required>
                        </div>
                        <div class="form-group">
                            <label for="client_email">Email</label>
                            <input type="email" class="form-control" id="client_email" name="client_email" required>
                        </div>
                        <div class="form-group">
                            <label for="client_phone">Teléfono</label>
                            <input type="text" class="form-control" id="client_phone" name="client_phone" required>
                        </div>
                        <div class="form-group">
                            <label for="client_group_id">Grupo de Cliente</label>
                            <select name="client_group_id" id="client_group_id" class="form-control" required>
                                @foreach($clientGroups as $group)
                                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                                @endforeach
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

@section('js')
    <script>
        $('#addClientForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: '{{ route("clients.store") }}',
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    // Agregar nuevo cliente al select
                    $('#client_id').append(new Option(response.name, response.id, true, true)).trigger('change');
                    $('#addClientModal').modal('hide');
                },
                error: function(response) {
                    console.log(response);
                }
            });
        });
    </script>
@stop
