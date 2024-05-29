@extends('adminlte::page')

@section('title', 'Órdenes de Trabajo del Ejecutivo')

@section('content_header')
    <h1>Órdenes de Trabajo del Ejecutivo</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <h5>Lista de Órdenes de Trabajo</h5>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tblData" class="table table-bordered table-striped dataTable dtr-inline">
                        <thead>
                            <tr>
                                <th>OT</th>
                                <th>Cliente</th>
                                <th>Servicios</th>
                                <th>Productos</th>
                                <th>Tiempo</th>
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
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#tblData').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('executive-work-orders') }}",
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'client', name: 'client' },
                    { data: 'service_status', name: 'service_status' },
                    { data: 'product_status', name: 'product_status' },
                    { data: 'time', name: 'time' },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                ],
                order: [[0, 'desc']]
            });
        });
    </script>
@stop
