@extends('adminlte::page')

@section('title', 'Grupos de Clientes')

@section('content_header')
    <h1>Grupos de Clientes</h1>
@stop

@section('content')
   <div class="container-fluid">
       <div id="errorBox"></div>
       <div class="card">
           <div class="card-header">
               <div class="card-title">
                   <h5>Listado</h5>
               </div>
               <a class="float-right btn btn-primary btn-xs m-0" href="{{ route('client-groups.create') }}"><i class="fas fa-plus"></i> Agregar</a>
           </div>
           <div class="card-body">
               <!--DataTable-->
               <div class="table-responsive">
                   <table id="tblData" class="table table-bordered table-striped dataTable dtr-inline">
                       <thead>
                           <tr>
                               <th>ID</th>
                               <th>Nombre</th>
                               <th>Descuento (%)</th>
                               <th>Estado</th>
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
            ajax: "{{ route('client-groups.index') }}",
            columns:[
                {data: 'id', name: 'id'},
                {data: 'name', name: 'name'},
                {data: 'discount_percentage', name: 'discount_percentage'},
                {data: 'status', name: 'status', render: function(data, type, row) {
                    return data ? 'Activo' : 'Inactivo';
                }},
                {data: 'action', name: 'action', orderable: false, searchable: false, className: "text-center"},
            ],
            order: [[0, "desc"]]
        });
    });
</script>
@stop

@section('plugins.Datatables', true)
