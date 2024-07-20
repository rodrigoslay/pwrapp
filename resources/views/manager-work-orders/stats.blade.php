@extends('adminlte::page')

@section('title', 'Estadísticas de Órdenes de Trabajo')

@section('content_header')
    <h1>Estadísticas de Órdenes de Trabajo</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <!-- Gráfico Diario -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Órdenes del Día</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="dailyChart"></canvas>
                    </div>
                </div>
            </div>
            <!-- Gráfico Semanal -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Órdenes de la Semana</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="weeklyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <!-- Gráfico Mensual -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Órdenes del Mes</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>
            </div>
            <!-- Gráfico Anual -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Órdenes del Año</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="yearlyChart"></canvas>
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
        const dailyOrders = @json($data['dailyOrders']);
        const weeklyOrders = @json($data['weeklyOrders']);
        const monthlyOrders = @json($data['monthlyOrders']);
        const yearlyOrders = @json($data['yearlyOrders']);

        const createChart = (ctx, data) => {
            return new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['No Realizado', 'En Proceso', 'Facturado'],
                    datasets: [{
                        label: 'Órdenes',
                        data: [data['No Realizado'], data['En Proceso'], data['Facturado']],
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(75, 192, 192, 0.2)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(75, 192, 192, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        createChart(document.getElementById('dailyChart').getContext('2d'), dailyOrders);
        createChart(document.getElementById('weeklyChart').getContext('2d'), weeklyOrders);
        createChart(document.getElementById('monthlyChart').getContext('2d'), monthlyOrders);
        createChart(document.getElementById('yearlyChart').getContext('2d'), yearlyOrders);
    </script>
@stop
