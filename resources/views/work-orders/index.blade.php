@extends('adminlte::page')

@section('title', 'Órdenes de Trabajo')

@section('content_header')
    <h1>Órdenes de Trabajo</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <h5>Lista de Órdenes de Trabajo</h5>
                </div>
                <a class="float-right btn btn-primary btn-xs m-0" href="{{ route('work-orders.create-step-one') }}"><i class="fas fa-plus"></i> Añadir</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tblData" class="table table-bordered table-striped dataTable dtr-inline">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Número de Factura</th>
                                <th>Subtotal</th>
                                <th>Impuesto</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
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
    $(document).ready(function() {
        $('#tblData').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('work-orders.index') }}",
            columns: [
                { data: 'id', name: 'id' },
                { data: 'invoice_number', name: 'invoice_number' },
                { data: 'subtotal', name: 'subtotal' },
                { data: 'tax', name: 'tax' },
                { data: 'total', name: 'total' },
                { data: 'status', name: 'status' },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ],
            order: [[0, 'desc']]
        });
    });
</script>
@stop
