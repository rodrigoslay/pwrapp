@extends('adminlte::page')

@section('title', 'Editar Producto')

@section('content_header')
    <h1>Editar Producto</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <form method="POST" action="{{ route('products.update', $product->id) }}">
                    @method('PATCH')
                    @csrf
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Editar Producto</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="sku">SKU</label>
                                <input type="text" name="sku" class="form-control" value="{{ $product->sku }}" required>
                            </div>
                            <div class="form-group">
                                <label for="name">Nombre</label>
                                <input type="text" name="name" class="form-control" value="{{ $product->name }}" required>
                            </div>
                            <div class="form-group">
                                <label for="price">Precio</label>
                                <input type="number" step="0.01" name="price" class="form-control" value="{{ $product->price }}" required>
                            </div>
                            <div class="form-group">
                                <label for="inventory">Inventario</label>
                                <input type="number" name="inventory" class="form-control" value="{{ $product->inventory }}" required>
                            </div>
                            <div class="form-group">
                                <label for="status">Estado</label>
                                <select name="status" class="form-control" required>
                                    <option value="1" {{ $product->status ? 'selected' : '' }}>Activo</option>
                                    <option value="0" {{ !$product->status ? 'selected' : '' }}>Inactivo</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-success">Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop


@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
@stop
