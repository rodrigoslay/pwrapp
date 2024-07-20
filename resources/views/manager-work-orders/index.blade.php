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
@section('footer')

    Realizado por <a href="https://www.slaymultimedios.com/"><strong>Slay Multimedios</strong></a> - Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})<br>
    &copy; 2024 PWRTALLER Versión 1.0. Todos los derechos reservados.
@stop
@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
    $.ajaxSetup({
        headers:{
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).ready(function(){
        var table = $('#tblData').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: "{{ route('manager-work-orders.list') }}",
            columns:[
                {data: 'id', name: 'id'},
                {data: 'client', name: 'client'},
                {data: 'vehicle', name: 'vehicle'},
                {data: 'service_status', name: 'service_status', className: "text-center"},
                {data: 'product_status', name: 'product_status', className: "text-center"},
                {data: 'time', name: 'time', className: "text-center"},
                {data: 'status', name: 'status', className: "text-center"},
                {data: 'action', name: 'action', orderable: false, searchable: false, className: "text-center"},
            ],
            order: [[0, "desc"]]
        });
    });
</script>
@stop

@section('plugins.Datatables', true)
