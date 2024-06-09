<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkOrder;
use App\Models\Service;
use App\Models\Product;
use App\Models\Revision;
use App\Models\Incident;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
{
    // Estadísticas de órdenes de trabajo
    $totalOtCreadas = WorkOrder::count();
    $totalOtFacturadas = WorkOrder::where('status', 'Facturado')->count();
    $totalOtEnProceso = WorkOrder::where('status', '!=', 'Facturado')->where('status', '!=', 'Abierto')->count();
    $totalOtSinIniciar = WorkOrder::where('status', 'Abierto')->count();

    // Estadísticas de servicios
    $totalServiciosCompletados = Service::whereHas('workOrders', function($query) {
        $query->where('service_work_order.status', 'completado');
    })->count();
    $totalServiciosSinCompletar = Service::whereHas('workOrders', function($query) {
        $query->where('service_work_order.status', '!=', 'completado');
    })->count();

    // Estadísticas de productos
    $totalProductosEntregados = Product::whereHas('workOrders', function($query) {
        $query->where('product_work_order.status', 'entregado');
    })->count();
    $totalProductosSinEntregar = Product::whereHas('workOrders', function($query) {
        $query->where('product_work_order.status', '!=', 'entregado');
    })->count();

    // Estadísticas de incidencias
    $totalIncidenciasEncontradas = \DB::table('incident_work_order')->count();
    $totalIncidenciasAprobadas = \DB::table('incident_work_order')->where('approved', 1)->count();

    // Top 5 estadísticas
    $topServiciosMasRequeridos = Service::withCount('workOrders')->orderBy('work_orders_count', 'desc')->take(5)->get();
    $topProductosMasComprados = Product::withCount('workOrders')->orderBy('work_orders_count', 'desc')->take(5)->get();
    $topRevisionesMasRequeridas = Revision::withCount('workOrders')->orderBy('work_orders_count', 'desc')->take(5)->get();

    // Top 5 usuarios
    $topUsuariosConMasServiciosCompletados = User::role('Mecánico')->withCount(['workOrders as completed_services_count' => function ($query) {
        $query->where('service_work_order.status', 'completado');
    }])->orderBy('completed_services_count', 'desc')->take(5)->get();

    $topUsuariosConMasOtCreadas = User::withCount('createdWorkOrders')->orderBy('created_work_orders_count', 'desc')->take(5)->get();
    $topUsuariosConMasOtFacturadas = User::withCount(['facturadasWorkOrders as facturadas_count'])->orderBy('facturadas_count', 'desc')->take(5)->get();

    return view('dashboard', compact(
        'totalOtCreadas', 'totalOtFacturadas', 'totalOtEnProceso', 'totalOtSinIniciar',
        'totalServiciosCompletados', 'totalServiciosSinCompletar',
        'totalProductosEntregados', 'totalProductosSinEntregar',
        'totalIncidenciasEncontradas', 'totalIncidenciasAprobadas',
        'topServiciosMasRequeridos', 'topProductosMasComprados', 'topRevisionesMasRequeridas',
        'topUsuariosConMasServiciosCompletados', 'topUsuariosConMasOtCreadas', 'topUsuariosConMasOtFacturadas'
    ));
}

}
