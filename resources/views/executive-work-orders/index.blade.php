@extends('adminlte::page')

@section('title', 'Órdenes de Trabajo del Ejecutivo')

@section('content_header')
    <h1>OT creadas</h1>
@stop

@section('content')
   <div class="container-fluid">
       <div id="errorBox"></div>
       <div class="card">
           <div class="card-header">
               <div class="card-title">
                   <h5>Listado</h5>
               </div>
               <a class="float-right btn btn-primary btn-xl m-0" href="{{ route('work-orders.create-step-one') }}"><i class="fas fa-plus"></i> Agregar</a>
           </div>
           <div class="card-body">
               <!--DataTable-->
               <div class="table-responsive">
                   <table id="tblData" class="table table-bordered table-striped dataTable dtr-inline">
                       <thead>
                           <tr>
                               <th>ID</th>
                               <th>Cliente</th>
                               <th>Vehículo</th>
                               <th>Estado de Servicio</th>
                               <th>Estado de Producto</th>
                               <th>Tiempo</th>
                               <th>Estado OT</th>
                               <th>Acciones</th>
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
        headers:{
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    })
    $(document).ready(function(){
        var table = $('#tblData').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: "{{ route('executive-work-orders.list') }}",
            columns:[
                {data: 'id', name: 'id'},
                {data: 'client', name: 'client'},
                {data: 'vehicle', name: 'vehicle'},
                {data: 'service_status', name: 'service_status', render: function(data, type, row) {
                    if (data === 'Completado') {
                        return '<span class="badge badge-success">Completado</span>';
                    } else if (data === 'Iniciado') {
                        return '<span class="badge badge-warning">Iniciado</span>';
                    } else {
                        return '<span class="badge badge-danger">Pendiente</span>';
                    }
                }},
                {data: 'product_status', name: 'product_status', render: function(data, type, row) {
                    if (data === 'Entregado') {
                        return '<span class="badge badge-success">Entregado</span>';
                    } else if (data === 'Parcialmente Entregado') {
                        return '<span class="badge badge-warning">Parcialmente Entregado</span>';
                    } else {
                        return '<span class="badge badge-danger">Pendiente</span>';
                    }
                }},
                {data: 'time', name: 'time'},
                {data: 'status', name: 'status', render: function(data, type, row) {
                    let badgeClass = '';
                    switch(data) {
                        case 'Completado':
                            badgeClass = 'badge-success';
                            break;
                        case 'Facturado':
                            badgeClass = 'badge-dark';
                            break;
                        case 'Abierto':
                        case 'Desaprobado':
                            badgeClass = 'badge-danger';
                            break;
                        case 'Comenzó':
                        case 'Incidencias':
                        case 'Aprobada':
                        case 'Parcial':
                            badgeClass = 'badge-warning';
                            break;
                        default:
                            badgeClass = 'badge-warning';
                            break;
                    }
                    return `<span class="badge ${badgeClass}">${data}</span>`;
                }},
                {data: 'action', name: 'action', orderable: false, searchable: false, className: "text-center"},
            ],
            order: [[0, "desc"]]
        });
    });
</script>
@stop

@section('plugins.Datatables', true)
