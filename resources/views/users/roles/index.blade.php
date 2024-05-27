@extends('adminlte::page')

@section('title', 'Roles | Dashboard')

@section('content_header')
    <h1>Roles</h1>
@stop

@section('content')
   <div class="container-fluid">
       <div id="errorBox"></div>
       <div class="card">
           <div class="card-header">
               <div class="card-title">
                   <h5>Lista</h5>
               </div>
               <a class="float-right btn btn-primary btn-xs m-0" href="{{route('users.roles.create')}}"><i class="fas fa-plus"></i> Agregar</a>
           </div>
           <div class="card-body">
               <!--DataTable-->
               <div class="table-responsive">
                   <table id="tblData" class="table table-bordered table-striped dataTable dtr-inline">
                       <thead>
                           <tr>
                               <th>ID</th>
                               <th>Nombre</th>
                               <th>Usuarios</th>
                               <th>Permisos</th>
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
        headers:{
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    })
    $(document).ready(function(){
        var table = $('#tblData').DataTable({
            reponsive:true, processing:true, serverSide:true, autoWidth:false,
            ajax:"{{route('users.roles.index')}}",
            columns:[
                {data:'id', name:'id'},
                {data:'name', name:'name'},
                {data:'users_count', name:'users_count', className:"text-center"},
                {data:'permissions_count', name:'permissions_count', className:"text-center"},
                {data:'action', name:'action', bSortable:false, className:"text-center"},
            ],
            order:[[0, "desc"]],
            bDestroy:true,
        });
        $('body').on('click', '#btnDel', function(){
            //confirmación
            var id = $(this).data('id');
            if(confirm('¿Eliminar Datos '+id+'?')==true)
            {
                var route = "{{route('users.roles.destroy', ':id')}}";
                route = route.replace(':id', id);
                $.ajax({
                    url:route,
                    type:"delete",
                    success:function(res){
                        console.log(res);
                        $("#tblData").DataTable().ajax.reload();
                    },
                    error:function(res){
                        $('#errorBox').html('<div class="alert alert-danger">'+response.message+'</div>');
                    }
                });
            }else{
                //no hacer nada
            }
        });
    });
</script>
@stop

@section('plugins.Datatables', true)
