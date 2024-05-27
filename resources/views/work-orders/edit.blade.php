@extends('adminlte::page')

@section('title', 'Editar Orden de Trabajo')

@section('content_header')
    <h1>Editar Orden de Trabajo</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <form method="POST" action="{{ route('work-orders.update', $workOrder->id) }}">
                    @method('PATCH')
                    @csrf
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Actualizar Orden de Trabajo</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="invoice_number">Número de Factura</label>
                                <input type="text" class="form-control" name="invoice_number" value="{{ old('invoice_number', $workOrder->invoice_number) }}">
                                @if($errors->has('invoice_number'))
                                    <span class="text-danger">{{ $errors->first('invoice_number') }}</span>
                                @endif
                            </div>
                            <div class="form-group">
                                <label for="discount_percentage">Porcentaje de Descuento</label>
                                <input type="number" step="0.01" class="form-control" name="discount_percentage" value="{{ old('discount_percentage', $workOrder->discount_percentage) }}">
                                @if($errors->has('discount_percentage'))
                                    <span class="text-danger">{{ $errors->first('discount_percentage') }}</span>
                                @endif
                            </div>
                            <div class="form-group">
                                <label for="discount_amount">Cantidad de Descuento</label>
                                <input type="number" step="0.01" class="form-control" name="discount_amount" value="{{ old('discount_amount', $workOrder->discount_amount) }}">
                                @if($errors->has('discount_amount'))
                                    <span class="text-danger">{{ $errors->first('discount_amount') }}</span>
                                @endif
                            </div>
                            <div class="form-group">
                                <label for="subtotal">Subtotal</label>
                                <input type="number" step="0.01" class="form-control" name="subtotal" value="{{ old('subtotal', $workOrder->subtotal) }}" required>
                                @if($errors->has('subtotal'))
                                    <span class="text-danger">{{ $errors->first('subtotal') }}</span>
                                @endif
                            </div>
                            <div class="form-group">
                                <label for="tax">Impuesto</label>
                                <input type="number" step="0.01" class="form-control" name="tax" value="{{ old('tax', $workOrder->tax) }}" required>
                                @if($errors->has('tax'))
                                    <span class="text-danger">{{ $errors->first('tax') }}</span>
                                @endif
                            </div>
                            <div class="form-group">
                                <label for="total">Total</label>
                                <input type="number" step="0.01" class="form-control" name="total" value="{{ old('total', $workOrder->total) }}" required>
                                @if($errors->has('total'))
                                    <span class="text-danger">{{ $errors->first('total') }}</span>
                                @endif
                            </div>
                            <div class="form-group">
                                <label for="review">Revisión</label>
                                <select name="review" class="form-control" required>
                                    <option value="0" {{ old('review', $workOrder->review) == 0 ? 'selected' : '' }}>No</option>
                                    <option value="1" {{ old('review', $workOrder->review) == 1 ? 'selected' : '' }}>Sí</option>
                                </select>
                                @if($errors->has('review'))
                                    <span class="text-danger">{{ $errors->first('review') }}</span>
                                @endif
                            </div>
                            <div class="form-group">
                                <label for="executive_id">Ejecutivo</label>
                                <input type="text" class="form-control" name="executive_id" value="{{ old('executive_id', $workOrder->executive_id) }}" required>
                                @if($errors->has('executive_id'))
                                    <span class="text-danger">{{ $errors->first('executive_id') }}</span>
                                @endif
                            </div>
                            <div class="form-group">
                                <label for="mechanic_id">Mecánico</label>
                                <input type="text" class="form-control" name="mechanic_id" value="{{ old('mechanic_id', $workOrder->mechanic_id) }}" required>
                                @if($errors->has('mechanic_id'))
                                    <span class="text-danger">{{ $errors->first('mechanic_id') }}</span>
                                @endif
                            </div>
                            <div class="form-group">
                                <label for="client_id">Cliente</label>
                                <input type="text" class="form-control" name="client_id" value="{{ old('client_id', $workOrder->client_id) }}" required>
                                @if($errors->has('client_id'))
                                    <span class="text-danger">{{ $errors->first('client_id') }}</span>
                                @endif
                            </div>
                            <div class="form-group">
                                <label for="vehicle_id">Vehículo</label>
                                <input type="text" class="form-control" name="vehicle_id" value="{{ old('vehicle_id', $workOrder->vehicle_id) }}" required>
                                @if($errors->has('vehicle_id'))
                                    <span class="text-danger">{{ $errors->first('vehicle_id') }}</span>
                                @endif
                            </div>
                            <div class="form-group">
                                <label for="entry_mileage">Kilometraje de Entrada</label>
                                <input type="number" class="form-control" name="entry_mileage" value="{{ old('entry_mileage', $workOrder->entry_mileage) }}" required>
                                @if($errors->has('entry_mileage'))
                                    <span class="text-danger">{{ $errors->first('entry_mileage') }}</span>
                                @endif
                            </div>
                            <div class="form-group">
                                <label for="exit_mileage">Kilometraje de Salida</label>
                                <input type="number" class="form-control" name="exit_mileage" value="{{ old('exit_mileage', $workOrder->exit_mileage) }}">
                                @if($errors->has('exit_mileage'))
                                    <span class="text-danger">{{ $errors->first('exit_mileage') }}</span>
                                @endif
                            </div>
                            <div class="form-group">
                                <label for="status">Estado</label>
                                <select name="status" class="form-control" required>
                                    <option value="Abierto" {{ old('status', $workOrder->status) == 'Abierto' ? 'selected' : '' }}>Abierto</option>
                                    <option value="Comenzó" {{ old('status', $workOrder->status) == 'Comenzó' ? 'selected' : '' }}>Comenzó</option>
                                    <option value="Incidencias Reportadas" {{ old('status', $workOrder->status) == 'Incidencias Reportadas' ? 'selected' : '' }}>Incidencias Reportadas</option>
                                    <option value="Incidencias Aprobadas" {{ old('status', $workOrder->status) == 'Incidencias Aprobadas' ? 'selected' : '' }}>Incidencias Aprobadas</option>
                                    <option value="Completado" {{ old('status', $workOrder->status) == 'Completado' ? 'selected' : '' }}>Completado</option>
                                    <option value="Facturado" {{ old('status', $workOrder->status) == 'Facturado' ? 'selected' : '' }}>Facturado</option>
                                    <option value="Cerrado" {{ old('status', $workOrder->status) == 'Cerrado' ? 'selected' : '' }}>Cerrado</option>
                                </select>
                                @if($errors->has('status'))
                                    <span class="text-danger">{{ $errors->first('status') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Actualizar</button>
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
