@extends('adminlte::page')

@section('title', 'Asignar Mecánicos')

@section('content_header')
    <h1>Asignar Mecánicos</h1>
@stop

@section('content')
    <form action="{{ route('work-orders.store-step-five') }}" method="POST">
        @csrf
        <h3>Servicios Seleccionados</h3>
        @foreach($services as $service)
            <div class="form-group">
                <label for="mechanic_{{ $service }}">Mecánico para {{ $service }}</label>
                <select name="mechanics[{{ $service }}]" id="mechanic_{{ $service }}" class="form-control select2" required>
                    @foreach($mechanics as $mechanic)
                        <option value="{{ $mechanic->id }}">{{ $mechanic->name }}</option>
                    @endforeach
                </select>
            </div>
        @endforeach
        <button type="submit" class="btn btn-primary">Siguiente</button>
    </form>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>
@stop
