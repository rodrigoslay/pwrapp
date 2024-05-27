@extends('adminlte::page')

@section('title', 'Configuraciones')

@section('content_header')
    <h1>Configuraciones</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h5>Lista de Configuraciones</h5>
                <a href="{{ route('settings.create') }}" class="btn btn-primary btn-sm float-right">Añadir Nuevo</a>
            </div>
            <div class="card-body">
                <table id="settingsTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Valor</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('#settingsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('settings.index') }}',
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'value', name: 'value' },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                ],
                order: [[0, 'desc']]
            });

            $('body').on('click', '.deleteSetting', function() {
                var id = $(this).data('id');
                var url = '{{ route('settings.destroy', ':id') }}'.replace(':id', id);
                if (confirm('¿Está seguro de que desea eliminar esta configuración?')) {
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        success: function(response) {
                            $('#settingsTable').DataTable().ajax.reload();
                            alert(response.message);
                        }
                    });
                }
            });
        });
    </script>
@stop

@section('plugins.Datatables', true)
