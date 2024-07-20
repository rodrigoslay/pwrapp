<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Yajra\DataTables\DataTables;
use App\Models\Service;
use App\Models\Product;
use App\Models\Revision;
use App\Models\RevisionFault;


class ManagerWorkOrdersController extends Controller
{
    public function index()
    {
        $workOrders = WorkOrder::with(['client', 'vehicle.brand', 'services', 'products'])->get();

        return view('manager-work-orders.index', compact('workOrders'));
    }

    public function show($id)
    {
        $workOrder = WorkOrder::with([
            'client',
            'vehicle',
            'services',
            'products',
            'incidents.reportedBy'
        ])->findOrFail($id);

        $mechanics = User::role('Líder')->get();
        $revisionsWithFaults = $this->getRevisionsWithFaults($id);

        // Obtener los nombres de los usuarios asignados
        $userIds = $workOrder->services->pluck('pivot.mechanic_id')->unique();
        $userNames = User::whereIn('id', $userIds)->pluck('name', 'id');

        // Obtener la lista de servicios y productos
        $servicesList = Service::all();
        $productsList = Product::all();
        $revisionsList = Revision::all();

        // Determinar si hay fallos pendientes
        $hasFaults = $workOrder->revisions()->wherePivot('status', 0)->exists();

        // Determinar si hay incidencias pendientes
        $hasPendingIncidents = $workOrder->incidents()->wherePivot('approved', 0)->exists();

        return view('manager-work-orders.show', compact(
            'workOrder',
            'mechanics',
            'revisionsWithFaults',
            'userNames',
            'servicesList',
            'productsList',
            'revisionsList',
            'hasFaults',
            'hasPendingIncidents'
        ));


    }
    public function list(Request $request)
    {
        if ($request->ajax()) {
            $data = WorkOrder::with(['client', 'vehicle', 'services', 'products'])->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('client', function ($row) {
                    return $row->client->name;
                })
                ->addColumn('vehicle', function ($row) {
                    return $row->vehicle->brand->name . ' ' . $row->vehicle->model;
                })
                ->addColumn('service_status', function ($row) {
                    $statuses = $row->services->pluck('pivot.status')->unique()->toArray();
                    if (in_array('completado', $statuses) && count($statuses) == 1) {
                        return '<span class="badge badge-success">Completado</span>';
                    } elseif (in_array('iniciado', $statuses)) {
                        return '<span class="badge badge-warning">Iniciado</span>';
                    } else {
                        return '<span class="badge badge-danger">Pendiente</span>';
                    }
                })
                ->addColumn('product_status', function ($row) {
                    $statuses = $row->products->pluck('pivot.status')->unique()->toArray();
                    if (in_array('entregado', $statuses) && count($statuses) == 1) {
                        return '<span class="badge badge-success">Entregado</span>';
                    } elseif (in_array('parcialmente_entregado', $statuses)) {
                        return '<span class="badge badge-warning">Parcialmente Entregado</span>';
                    } else {
                        return '<span class="badge badge-danger">Pendiente</span>';
                    }
                })
                ->addColumn('time', function ($row) {
                    $created_at = Carbon::parse($row->created_at);
                    $end_time = ($row->status == 'Facturado') ? Carbon::parse($row->updated_at) : Carbon::now();
                    $time_diff = $end_time->diff($created_at);

                    if ($time_diff->d > 0) {
                        return $time_diff->format('%d días %H:%I:%S');
                    } else {
                        return $time_diff->format('%H:%I:%S');
                    }
                })
                ->addColumn('status', function ($row) {
                    $badgeClass = '';
                    switch ($row->status) {
                        case 'Completado':
                            $badgeClass = 'badge-success';
                            break;
                        case 'Facturado':
                        case 'No Realizado':
                            $badgeClass = 'badge-dark';
                            break;
                        case 'Iniciado':
                        case 'Rechazado':
                            $badgeClass = 'badge-danger';
                            break;
                        case 'Inicio':
                        case 'Incidencias':
                        case 'Aprobado':
                        case 'Parcial':
                            $badgeClass = 'badge-warning';
                            break;
                            case 'Cotización':
                                case 'Agendado':
                                $badgeClass = 'badge-info';
                                break;
                        default:
                            $badgeClass = 'badge-warning';
                            break;
                    }
                    return '<span class="badge ' . $badgeClass . '">' . $row->status . '</span>';
                })
                ->addColumn('action', function ($row) {
                    return '<a href="' . route('manager-work-orders.show', $row->id) . '" class="edit btn btn-primary btn-sm">Ver</a>';
                })
                ->rawColumns(['service_status', 'product_status', 'status', 'action'])
                ->make(true);
        }

        return view('manager-work-orders.index');
    }

    private function getRevisionsWithFaults($workOrderId)
    {
        // Obtenemos todas las revisiones para una orden de trabajo específica
        $revisions = Revision::whereHas('workOrders', function ($query) use ($workOrderId) {
            $query->where('work_order_id', $workOrderId);
        })->get();

        // Iteramos sobre las revisiones para obtener los fallos y sus estados
        foreach ($revisions as $revision) {
            $revision->faults = RevisionFault::select('revision_faults.*', 'revision_work_order.status')
                ->join('revision_work_order', function ($join) use ($workOrderId, $revision) {
                    $join->on('revision_faults.id', '=', 'revision_work_order.fault_id')
                        ->where('revision_work_order.work_order_id', $workOrderId)
                        ->where('revision_work_order.revision_id', $revision->id);
                })
                ->get();
        }

        return $revisions;
    }


    public function stats()
    {
        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $monthStart = Carbon::now()->startOfMonth();
        $yearStart = Carbon::now()->startOfYear();

        $statuses = ['No Realizado', 'En Proceso', 'Facturado'];

        $data = [
            'dailyOrders' => $this->getOrderStats($today, $statuses),
            'weeklyOrders' => $this->getOrderStats($weekStart, $statuses),
            'monthlyOrders' => $this->getOrderStats($monthStart, $statuses),
            'yearlyOrders' => $this->getOrderStats($yearStart, $statuses),
        ];

        return view('manager-work-orders.stats', compact('data'));
    }

    private function getOrderStats($startDate, $statuses)
    {
        $orders = WorkOrder::where('created_at', '>=', $startDate)->get();
        $stats = [];

        foreach ($statuses as $status) {
            $stats[$status] = $orders->where('status', $status)->count();
        }

        return $stats;
    }

    public function summary()
    {
        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $monthStart = Carbon::now()->startOfMonth();
        $yearStart = Carbon::now()->startOfYear();

        $data = [
            'dailySummary' => $this->getSalesSummary($today),
            'weeklySummary' => $this->getSalesSummary($weekStart),
            'monthlySummary' => $this->getSalesSummary($monthStart),
            'yearlySummary' => $this->getSalesSummary($yearStart),
        ];

        return view('manager-work-orders.summary', compact('data'));
    }

    private function getSalesSummary($startDate)
    {
        $workOrders = WorkOrder::where('created_at', '>=', $startDate)->where('status', 'Facturado')->get();

        $productTotal = 0;
        $serviceTotal = 0;

        foreach ($workOrders as $workOrder) {
            foreach ($workOrder->products as $product) {
                $productTotal += $product->pivot->quantity * $product->price;
            }
            foreach ($workOrder->services as $service) {
                $serviceTotal += $service->price; // Asegúrate de que 'service->price' es el campo correcto
            }
        }

        return [
            'productTotal' => $productTotal,
            'serviceTotal' => $serviceTotal,
        ];
    }

    public function closeWorkOrder($id)
    {
        $workOrder = WorkOrder::findOrFail($id);
        $workOrder->status = 'Cerrado';
        $workOrder->save();

        return redirect()->route('manager-work-orders.show', $id)->with('status', 'La OT ha sido cerrada');
    }
}
