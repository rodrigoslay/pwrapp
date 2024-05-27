@extends('adminlte::page')

@section('title', 'Editar Solicitud de Almacén | Dashboard')

@section('content_header')
    <h1>Editar Solicitud de Almacén</h1>
@stop

@section('content')
    <div class="container-fluid">
        <form method="POST" action="{{ route('warehouse-requests.update', $warehouseRequest->id) }}">
            @method('PATCH')
            @csrf
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <h5>Editar Solicitud</h5>
                    </div>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="product_id" class="form-label">Producto</label>
                        <select name="product_id" class="form-control">
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ $product->id == $warehouseRequest->product_id ? 'selected' : '' }}>{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="user_id" class="form-label">Usuario</label>
                        <select name="user_id" class="form-control">
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ $user->id == $warehouseRequest->user_id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="work_order_id" class="form-label">Orden de Trabajo</label>
                        <select name="work_order_id" class="form-control">
                            @foreach($workOrders as $workOrder)
                                <option value="{{ $workOrder->id }}" {{ $workOrder->id == $warehouseRequest->work_order_id ? 'selected' : '' }}>{{ $workOrder->id }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="quantity" class="form-label">Cantidad</label>
                        <input type="number" class="form-control" name="quantity" value="{{ old('quantity', $warehouseRequest->quantity) }}">
                    </div>
                    <div class="form-group">
                        <label for="status" class="form-label">Estado</label>
                        <input type="text" class="form-control" name="status" value="{{ old('status', $warehouseRequest->status) }}">
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                </div>
            </div>
        </form>
    </div>
@stop
