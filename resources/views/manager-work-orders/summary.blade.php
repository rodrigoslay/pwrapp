@extends('adminlte::page')

@section('title', 'Resumen de Montos')

@section('content_header')
    <h1>Resumen de Montos</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <!-- Resumen Diario -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Resumen del Día</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="dailySummaryChart"></canvas>
                        <p>Total Productos: ${{ number_format($data['dailySummary']['productTotal'], 2) }}</p>
                        <p>Total Servicios: ${{ number_format($data['dailySummary']['serviceTotal'], 2) }}</p>
                        <p>Total Suma de Productos y Servicios: ${{ number_format($data['dailySummary']['productTotal'] + $data['dailySummary']['serviceTotal'], 2) }}</p>
                    </div>
                </div>
            </div>
            <!-- Resumen Semanal -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Resumen de la Semana</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="weeklySummaryChart"></canvas>
                        <p>Total Productos: ${{ number_format($data['weeklySummary']['productTotal'], 2) }}</p>
                        <p>Total Servicios: ${{ number_format($data['weeklySummary']['serviceTotal'], 2) }}</p>
                        <p>Total Suma de Productos y Servicios: ${{ number_format($data['weeklySummary']['productTotal'] + $data['weeklySummary']['serviceTotal'], 2) }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <!-- Resumen Mensual -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Resumen del Mes</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="monthlySummaryChart"></canvas>
                        <p>Total Productos: ${{ number_format($data['monthlySummary']['productTotal'], 2) }}</p>
                        <p>Total Servicios: ${{ number_format($data['monthlySummary']['serviceTotal'], 2) }}</p>
                        <p>Total Suma de Productos y Servicios: ${{ number_format($data['monthlySummary']['productTotal'] + $data['monthlySummary']['serviceTotal'], 2) }}</p>
                    </div>
                </div>
            </div>
            <!-- Resumen Anual -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Resumen del Año</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="yearlySummaryChart"></canvas>
                        <p>Total Productos: ${{ number_format($data['yearlySummary']['productTotal'], 2) }}</p>
                        <p>Total Servicios: ${{ number_format($data['yearlySummary']['serviceTotal'], 2) }}</p>
                        <p>Total Suma de Productos y Servicios: ${{ number_format($data['yearlySummary']['productTotal'] + $data['yearlySummary']['serviceTotal'], 2) }}</p>
                    </div>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const dailySummary = @json($data['dailySummary']);
        const weeklySummary = @json($data['weeklySummary']);
        const monthlySummary = @json($data['monthlySummary']);
        const yearlySummary = @json($data['yearlySummary']);

        const createSummaryChart = (ctx, summary) => {
            return new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Productos', 'Servicios'],
                    datasets: [{
                        label: ['Productos', 'Servicios'],
                        data: [summary.productTotal, summary.serviceTotal],
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.2)', // Color para productos
                            'rgba(255, 159, 64, 0.2)'  // Color para servicios
                        ],
                        borderColor: [
                            'rgba(54, 162, 235, 1)', // Color para productos
                            'rgba(255, 159, 64, 1)'  // Color para servicios
                        ],
                        borderWidth: 1
                    }]
                }
            });
        }

        createSummaryChart(document.getElementById('dailySummaryChart').getContext('2d'), dailySummary);
        createSummaryChart(document.getElementById('weeklySummaryChart').getContext('2d'), weeklySummary);
        createSummaryChart(document.getElementById('monthlySummaryChart').getContext('2d'), monthlySummary);
        createSummaryChart(document.getElementById('yearlySummaryChart').getContext('2d'), yearlySummary);


    </script>
@stop
