@extends('adminlte::page')

@section('title', 'Servicios')

@section('content_header')
    <h1>Servicios</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h5>Lista de Servicios</h5>
                <a href="{{ route('services.create') }}" class="btn btn-primary btn-sm float-right">Añadir Nuevo</a>
            </div>
            <div class="card-body">
                <table id="servicesTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Precio</th>
                            <th>Descuento Aplicable</th>
                            <th>Estado</th>
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
            $('#servicesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('services.index') }}',
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'description', name: 'description' },
                    { data: 'price', name: 'price' },
                    { data: 'discount_applicable', name: 'discount_applicable' },
                    { data: 'status', name: 'status' },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                ],
                order: [[0, 'desc']]
            });

            $('body').on('click', '.deleteService', function() {
                var id = $(this).data('id');
                var url = '{{ route('services.destroy', ':id') }}'.replace(':id', id);
                if (confirm('¿Está seguro de que desea eliminar este servicio?')) {
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        success: function(response) {
                            $('#servicesTable').DataTable().ajax.reload();
                            alert(response.message);
                        }
                    });
                }
            });
        });
    </script>
@stop

@section('plugins.Datatables', true)

