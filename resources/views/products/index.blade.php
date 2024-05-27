@extends('adminlte::page')

@section('title', 'Productos')

@section('content_header')
    <h1>Productos</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div id="errorBox"></div>
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Lista de Productos</h3>
                        <a class="btn btn-success float-right" href="{{ route('products.create') }}">Crear Producto</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tblData" class="table table-bordered table-striped dataTable dtr-inline">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>SKU</th>
                                        <th>Nombre</th>
                                        <th>Precio</th>
                                        <th>Inventario</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($products as $product)
                                        <tr>
                                            <td>{{ $product->id }}</td>
                                            <td>{{ $product->sku }}</td>
                                            <td>{{ $product->name }}</td>
                                            <td>{{ $product->price }}</td>
                                            <td>{{ $product->inventory }}</td>
                                            <td>{{ $product->status ? 'Activo' : 'Inactivo' }}</td>
                                            <td>
                                                <a href="{{ route('products.edit', $product->id) }}" class="btn btn-primary btn-sm">Editar</a>
                                                <form action="{{ route('products.destroy', $product->id) }}" method="POST" style="display:inline-block;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                        {{ $products->links() }}
                    </div>
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
        $(document).ready(function(){
            $('#tblData').DataTable();
        });
    </script>
@stop
