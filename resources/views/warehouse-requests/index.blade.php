@extends('adminlte::page')

@section('title', 'Solicitudes de Almacén | Dashboard')

@section('content_header')
    <h1>Solicitudes de Almacén</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div id="errorBox"></div>
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <h5>Lista</h5>
                </div>
                <a class="float-right btn btn-primary btn-xs m-0" href="{{ route('warehouse-requests.create') }}">
                    <i class="fas fa-plus"></i> Añadir
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tblData" class="table table-bordered table-striped dataTable dtr-inline">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Producto</th>
                                <th>Usuario</th>
                                <th>Orden de Trabajo</th>
                                <th>Cantidad</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).ready(function() {
        var table = $('#tblData').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: "{{ route('warehouse-requests.index') }}",
            columns: [
                {data: 'id', name: 'id'},
                {data: 'product.name', name: 'product.name'},
                {data: 'user.name', name: 'user.name'},
                {data: 'work_order.id', name: 'work_order.id'},
                {data: 'quantity', name: 'quantity'},
                {data: 'status', name: 'status'},
                {data: 'action', name: 'action', orderable: false, searchable: false, className: "text-center"},
            ],
            order: [[0, "desc"]]
        });

        $('body').on('click', '#btnDel', function() {
            var id = $(this).data('id');
            if(confirm('¿Eliminar solicitud ' + id + '?')) {
                var route = "{{ route('warehouse-requests.destroy', ':id') }}";
                route = route.replace(':id', id);
                $.ajax({
                    url: route,
                    type: "DELETE",
                    success: function(res) {
                        table.ajax.reload();
                    },
                    error: function(res) {
                        $('#errorBox').html('<div class="alert alert-danger">' + res.responseJSON.message + '</div>');
                    }
                });
            }
        });
    });
</script>
@stop
