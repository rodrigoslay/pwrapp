@extends('adminlte::page')

@section('title', 'Lista de Órdenes de Trabajo')

@section('content_header')
    <h1>Lista de Órdenes de Trabajo</h1>
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
                                <th>OT</th>
                                <th>Ejecutivo</th>
                                <th>Cliente</th>
                                <th>Vehículo</th>
                                <th>Ingreso</th>
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
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script>
        $.ajaxSetup({
            headers:{
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        })
        $(document).ready(function() {
            $('#tblData').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                autoWidth: false,
                ajax: "{{ route('work-orders.list') }}",
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'executive', name: 'executive' },
                    { data: 'client', name: 'client' },
                    { data: 'vehicle', name: 'vehicle' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'status', name: 'status' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: "text-center" },
                ],
                order: [[0, 'desc']]
            });
        });
    </script>
@stop

@section('plugins.Datatables', true)
