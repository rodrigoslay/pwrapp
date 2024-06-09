@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $totalOtCreadas }}</h3>
                        <p>Órdenes Creadas</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $totalOtFacturadas }}</h3>
                        <p>Órdenes Facturadas</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $totalOtEnProceso }}</h3>
                        <p>Órdenes en Proceso</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-spinner"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $totalOtSinIniciar }}</h3>
                        <p>Órdenes sin Iniciar</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Top 5 Servicios más Requeridos</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="topServicesChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Top 5 Productos más Comprados</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="topProductsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Top 5 Revisiones más Requeridas</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="topRevisionsChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Top 5 Mecanicos con más Servicios Completados</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="topUsersCompletedServicesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Top 5 Ejecutivos con más OT Creadas</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="topUsersOtCreatedChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Top 5 Ejecutivos con más OT Facturadas</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="topUsersOtInvoicedChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Incidencias Encontradas y Aprobadas</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="incidentsChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Productos Entregados vs Pendientes</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="productsStatusChart"></canvas>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(document).ready(function() {
            var topServicesChartCtx = document.getElementById('topServicesChart').getContext('2d');
            var topServicesChart = new Chart(topServicesChartCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($topServiciosMasRequeridos->pluck('name')) !!},
                    datasets: [{
                        label: 'Cantidad de Servicios',
                        data: {!! json_encode($topServiciosMasRequeridos->pluck('work_orders_count')) !!},
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
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

            var topProductsChartCtx = document.getElementById('topProductsChart').getContext('2d');
            var topProductsChart = new Chart(topProductsChartCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($topProductosMasComprados->pluck('name')) !!},
                    datasets: [{
                        label: 'Cantidad de Productos',
                        data: {!! json_encode($topProductosMasComprados->pluck('work_orders_count')) !!},
                        backgroundColor: 'rgba(255, 206, 86, 0.2)',
                        borderColor: 'rgba(255, 206, 86, 1)',
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

            var topRevisionsChartCtx = document.getElementById('topRevisionsChart').getContext('2d');
            var topRevisionsChart = new Chart(topRevisionsChartCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($topRevisionesMasRequeridas->pluck('name')) !!},
                    datasets: [{
                        label: 'Cantidad de Revisiones',
                        data: {!! json_encode($topRevisionesMasRequeridas->pluck('work_orders_count')) !!},
                        backgroundColor: 'rgba(153, 102, 255, 0.2)',
                        borderColor: 'rgba(153, 102, 255, 1)',
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

            var topUsersCompletedServicesChartCtx = document.getElementById('topUsersCompletedServicesChart').getContext('2d');
            var topUsersCompletedServicesChart = new Chart(topUsersCompletedServicesChartCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($topUsuariosConMasServiciosCompletados->pluck('name')) !!},
                    datasets: [{
                        label: 'Servicios Completados',
                        data: {!! json_encode($topUsuariosConMasServiciosCompletados->pluck('completed_services_count')) !!},
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
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

            var topUsersOtCreatedChartCtx = document.getElementById('topUsersOtCreatedChart').getContext('2d');
            var topUsersOtCreatedChart = new Chart(topUsersOtCreatedChartCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($topUsuariosConMasOtCreadas->pluck('name')) !!},
                    datasets: [{
                        label: 'OT Creadas',
                        data: {!! json_encode($topUsuariosConMasOtCreadas->pluck('created_work_orders_count')) !!},
                        backgroundColor: 'rgba(255, 159, 64, 0.2)',
                        borderColor: 'rgba(255, 159, 64, 1)',
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

            var topUsersOtInvoicedChartCtx = document.getElementById('topUsersOtInvoicedChart').getContext('2d');
            var topUsersOtInvoicedChart = new Chart(topUsersOtInvoicedChartCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($topUsuariosConMasOtFacturadas->pluck('name')) !!},
                    datasets: [{
                        label: 'OT Facturadas',
                        data: {!! json_encode($topUsuariosConMasOtFacturadas->pluck('facturadas_count')) !!},
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
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

            var incidentsChartCtx = document.getElementById('incidentsChart').getContext('2d');
            var incidentsChart = new Chart(incidentsChartCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Encontradas', 'Aprobadas'],
                    datasets: [{
                        label: 'Cantidad de Incidencias',
                        data: [{{ $totalIncidenciasEncontradas }}, {{ $totalIncidenciasAprobadas }}],
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(54, 162, 235, 0.2)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)'
                        ],
                        borderWidth: 1
                    }]
                }
            });

            var productsStatusChartCtx = document.getElementById('productsStatusChart').getContext('2d');
            var productsStatusChart = new Chart(productsStatusChartCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Entregados', 'Pendientes'],
                    datasets: [{
                        label: 'Estado de Productos',
                        data: [{{ $totalProductosEntregados }}, {{ $totalProductosSinEntregar }}],
                        backgroundColor: [
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(255, 159, 64, 0.2)'
                        ],
                        borderColor: [
                            'rgba(75, 192, 192, 1)',
                            'rgba(255, 159, 64, 1)'
                        ],
                        borderWidth: 1
                    }]
                }
            });
        });
    </script>
@stop
