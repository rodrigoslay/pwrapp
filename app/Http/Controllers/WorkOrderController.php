<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use App\Models\Vehicle;
use App\Models\Client;
use App\Models\User;
use App\Models\Service;
use App\Models\Product;
use App\Models\ClientGroup;
use App\Models\Incident;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WorkOrderController extends Controller
{
    /**
     * Mostrar la lista de órdenes de trabajo.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = WorkOrder::latest()->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<a href="' . route('work-orders.show', $row->id) . '" class="edit btn btn-primary btn-sm">Ver</a>';
                    $btn .= ' <a href="' . route('work-orders.edit', $row->id) . '" class="edit btn btn-secondary btn-sm">Editar</a>';
                    $btn .= '<form action="' . route('work-orders.destroy', $row->id) . '" method="POST" style="display:inline-block;">
                                ' . csrf_field() . '
                                ' . method_field("DELETE") . '
                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                            </form>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('work-orders.index');
    }

    /**
     * Mostrar el formulario para crear una nueva orden de trabajo.
     */
    public function create()
    {
        return view('work-orders.create');
    }

    /**
     * Almacenar una nueva orden de trabajo en la base de datos.
     */
    public function store(Request $request)
    {
        $request->validate([
            'invoice_number' => 'nullable|string|max:255|unique:work_orders',
            'discount_percentage' => 'nullable|numeric',
            'discount_amount' => 'nullable|numeric',
            'subtotal' => 'required|numeric',
            'tax' => 'required|numeric',
            'total' => 'required|numeric',
            'review' => 'required|boolean',
            'executive_id' => 'required|exists:users,id',
            'client_id' => 'required|exists:clients,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'entry_mileage' => 'required|integer',
            'exit_mileage' => 'nullable|integer',
            'status' => 'required|string|in:Abierto,Comenzó,Incidencias Reportadas,Incidencias Aprobadas,Completado,Facturado,Cerrado',
            'revisiones' => 'required|boolean',
        ]);

        WorkOrder::create($request->all() + ['created_by' => auth()->user()->id]);

        Alert::success('Éxito', 'Orden de Trabajo creada con éxito');
        return redirect()->route('work-orders.index');
    }

    /**
     * Mostrar el formulario para editar una orden de trabajo existente.
     */
    public function edit(WorkOrder $workOrder)
    {
        return view('work-orders.edit', compact('workOrder'));
    }

    /**
     * Actualizar una orden de trabajo en la base de datos.
     */
    public function update(Request $request, WorkOrder $workOrder)
    {
        $request->validate([
            'invoice_number' => 'nullable|string|max:255|unique:work_orders,invoice_number,' . $workOrder->id,
            'discount_percentage' => 'nullable|numeric',
            'discount_amount' => 'nullable|numeric',
            'subtotal' => 'required|numeric',
            'tax' => 'required|numeric',
            'total' => 'required|numeric',
            'review' => 'required|boolean',
            'executive_id' => 'required|exists:users,id',
            'client_id' => 'required|exists:clients,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'entry_mileage' => 'required|integer',
            'exit_mileage' => 'nullable|integer',
            'status' => 'required|string|in:Abierto,Comenzó,Incidencias Reportadas,Incidencias Aprobadas,Completado,Facturado,Cerrado',
            'revisiones' => 'required|boolean',
        ]);

        $workOrder->update($request->all() + ['updated_by' => auth()->user()->id]);

        Alert::success('Éxito', 'Orden de Trabajo actualizada con éxito');
        return redirect()->route('work-orders.index');
    }

    /**
     * Eliminar una orden de trabajo de la base de datos.
     */
    public function destroy(WorkOrder $workOrder)
    {
        $workOrder->delete();
        Alert::success('Éxito', 'Orden de Trabajo eliminada con éxito');
        return redirect()->route('work-orders.index');
    }

    // PASO A PASO DE CREACIÓN DE OT

    /**
     * Paso 1: Mostrar formulario para buscar vehículo por patente.
     */
    public function createStepOne()
    {
        return view('work-orders.create-step-one');
    }

    /**
     * Paso 2: Buscar vehículo por patente.
     */
    public function searchVehicle(Request $request)
    {
        $request->validate(['license_plate' => 'required|string']);

        $vehicles = Vehicle::where('license_plate', $request->license_plate)->get();

        $clients = Client::all();
        $clientGroups = ClientGroup::all();
        $latestClient = null;

        if ($vehicles->isNotEmpty()) {
            $latestClient = $vehicles->first()->clients()->latest()->first();
        }

        return view('work-orders.create-step-two', compact('vehicles', 'clients', 'latestClient', 'clientGroups'));
    }

    /**
     * Paso 3: Seleccionar vehículo o crear nueva asociación.
     */
    public function selectVehicle(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|integer',
            'client_id' => 'nullable|integer',
        ]);

        $vehicle = Vehicle::findOrFail($request->vehicle_id);

        if ($request->client_id) {
            $client = Client::findOrFail($request->client_id);
            $vehicle->clients()->attach($client->id);
            session(['client_id' => $client->id]);
        } else {
            session(['client_id' => $vehicle->client_id]);
        }

        return redirect()->route('work-orders.create-step-three', ['vehicle_id' => $vehicle->id]);
    }

    /**
     * Paso 3: Mostrar formulario para agregar servicios y productos.
     */
    public function createStepThree($vehicle_id)
    {
        $services = Service::where('status', true)->get();
        $products = Product::where('status', true)->get();
        return view('work-orders.create-step-three', compact('vehicle_id', 'services', 'products'));
    }

    /**
     * Paso 3: Almacenar servicios y productos seleccionados.
     */
    public function storeStepThree(Request $request)
    {
        try {
            $request->validate([
                'vehicle_id' => 'required|integer',
                'services' => 'required|array',
                'products' => 'nullable|array',
                'quantities' => 'nullable|array',
            ]);

            $services = Service::whereIn('id', $request->services)->get()->toArray();
            $products = [];
            if ($request->products) {
                $products = Product::whereIn('id', array_keys($request->products))->get()->toArray();
            }
            $quantities = $request->quantities ?? [];

            session([
                'vehicle_id' => $request->vehicle_id,
                'services' => $services,
                'products' => $products,
                'quantities' => $quantities,
            ]);

            return redirect()->route('work-orders.create-step-four');
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return back()->withErrors('Error al procesar la solicitud. Por favor, intente nuevamente.');
        }
    }

    /**
     * Paso 4: Mostrar formulario para confirmar servicios y revisar extras.
     */
    public function createStepFour()
    {
        $services = session('services', []);
        $products = session('products', []);
        $extra_reviews = ['Revisión de frenos', 'Revisión de aceite', 'Revisión de neumáticos']; // Ejemplo de revisiones extras

        if (!is_array($services) || !isset($services[0]) || !is_array($services[0])) {
            $services = [];
        }

        if (!is_array($products) || !isset($products[0]) || !is_array($products[0])) {
            $products = [];
        }

        return view('work-orders.create-step-four', compact('services', 'products', 'extra_reviews'));
    }

    /**
     * Paso 4: Almacenar confirmación de servicios y revisiones extras.
     */
    public function storeStepFour(Request $request)
    {
        $request->validate([
            'extra_reviews' => 'nullable|array',
        ]);

        session(['extra_reviews' => $request->extra_reviews]);

        return redirect()->route('work-orders.create-step-five');
    }

    /**
     * Paso 5: Mostrar formulario para asignar mecánicos.
     */
    public function createStepFive()
    {
        $services = session('services');
        if (is_null($services)) {
            return redirect()->route('work-orders.create-step-three')->withErrors('Por favor, seleccione los servicios primero.');
        }

        $mechanics = User::role('Mecánico')->get(); // Obtener todos los usuarios con el rol 'Mecánico'

        return view('work-orders.create-step-five', compact('services', 'mechanics'));
    }

    /**
     * Paso 5: Almacenar asignaciones de mecánicos.
     */
    public function storeStepFive(Request $request)
    {
        $request->validate([
            'mechanics' => 'required|array',
        ]);

        session(['mechanics' => $request->mechanics]);

        return redirect()->route('work-orders.create-step-six');
    }

    /**
     * Paso 6: Mostrar resumen de la OT.
     */
    public function createStepSix()
    {
        $vehicle = Vehicle::find(session('vehicle_id'));
        $services = session('services', []);
        $products = session('products', []);
        $extra_reviews = session('extra_reviews', []);
        $mechanics = session('mechanics', []);
        $quantities = session('quantities', []);

        $services = array_map(function ($service) {
            return Service::find($service['id']);
        }, $services);

        $products = array_map(function ($product) {
            return Product::find($product['id']);
        }, $products);

        $mechanicNames = [];
        foreach ($mechanics as $serviceId => $mechanicId) {
            $mechanic = User::find($mechanicId);
            if ($mechanic) {
                $mechanicNames[$serviceId] = $mechanic->name;
            }
        }

        return view('work-orders.create-step-six', compact('vehicle', 'services', 'products', 'extra_reviews', 'mechanicNames', 'quantities'));
    }

    /**
     * Paso 6: Almacenar la OT completa.
     */
    public function storeStepSix(Request $request)
    {
        $workOrder = WorkOrder::create([
            'vehicle_id' => session('vehicle_id'),
            'client_id' => session('client_id'),
            'created_by' => auth()->user()->id,
            'executive_id' => auth()->user()->id,
            'status' => 'Abierto',
            'subtotal' => 0,
            'tax' => 0,
            'total' => 0,
            'review' => session('extra_reviews') ? true : false,
            'entry_mileage' => 0,
            'exit_mileage' => 0,
            'revisiones' => session('extra_reviews') ? true : false,
        ]);

        foreach (session('services') as $service) {
            $serviceId = $service['id'];
            $mechanicId = session('mechanics')[$serviceId];

            $workOrder->services()->attach($serviceId, ['mechanic_id' => $mechanicId]);

            DB::table('mechanic_work_order')->insert([
                'work_order_id' => $workOrder->id,
                'service_id' => $serviceId,
                'user_id' => $mechanicId,
            ]);
        }

        foreach (session('products') as $product) {
            $productId = $product['id'];
            $quantity = session('quantities')[$productId] ?? 1;
            $workOrder->products()->attach($productId, ['quantity' => $quantity]);
        }

        Alert::success('Éxito', 'Orden de Trabajo creada con éxito');
        return redirect()->route('executive-work-orders');
    }

    // MOSTRAR DETALLES DE OT

    /**
     * Paso 7: Mostrar detalles de la OT creada.
     */
    public function show(WorkOrder $workOrder)
    {
        $workOrder->load('services', 'products', 'vehicle', 'createdBy', 'mechanics');
        return view('work-orders.show', compact('workOrder'));
    }

    /**
     * Mostrar órdenes de trabajo asignadas a un mecánico.
     */
    public function mechanicWorkOrders(Request $request)
{
    if ($request->ajax()) {
        $data = WorkOrder::with(['client', 'vehicle.brand', 'services', 'products'])
            ->whereHas('services', function ($query) {
                $query->where('mechanic_id', auth()->user()->id);
            })
            ->latest()
            ->get();

        foreach ($data as $workOrder) {
            $workOrder->time = $this->calculateElapsedTime($workOrder);
            $workOrder->action = route('mechanic-work-orders.show', $workOrder->id); // Añadir la URL de la acción
        }

        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('client', function ($row) {
                return $row->client ? $row->client->name : '';
            })
            ->addColumn('vehicle', function ($row) {
                return $row->vehicle ? $row->vehicle->brand->name . ' ' . $row->vehicle->model : '';
            })
            ->addColumn('status', function ($row) {
                return $row->status;
            })
            ->addColumn('time', function ($row) {
                return $row->time;
            })
            ->addColumn('action', function ($row) {
                return $row->action; // Devolver la URL de la acción
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    return view('mechanic-work-orders.index');
}
public function mechanicWorkOrdersList(Request $request)
{
    if ($request->ajax()) {
        $mechanicId = auth()->user()->id;
        $data = WorkOrder::whereHas('services', function ($query) use ($mechanicId) {
            $query->where('mechanic_id', $mechanicId);
        })->with(['client', 'vehicle.brand', 'services', 'products'])
        ->latest()
        ->get();

        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('client', function ($row) {
                return $row->client ? $row->client->name : '';
            })
            ->addColumn('vehicle', function ($row) {
                return $row->vehicle ? $row->vehicle->brand->name . ' ' . $row->vehicle->model : '';
            })
            ->addColumn('service_status', function ($row) {
                $allCompleted = $row->services->every(fn ($service) => $service->pivot->status === 'completado');
                $anyStarted = $row->services->contains(fn ($service) => $service->pivot->status === 'iniciado');

                if ($allCompleted) {
                    return 'Completado';
                } elseif ($anyStarted) {
                    return 'Iniciado';
                } else {
                    return 'Pendiente';
                }
            })
            ->addColumn('product_status', function ($row) {
                $totalProducts = $row->products->sum('pivot.quantity');
                $deliveredProducts = $row->products->where('pivot.status', 'entregado')->sum('pivot.quantity');

                if ($deliveredProducts === $totalProducts) {
                    return 'Entregado';
                } elseif ($deliveredProducts > 0) {
                    return 'Parcialmente Entregado';
                } else {
                    return 'Pendiente';
                }
            })
            ->addColumn('time', function ($row) {
                $completionTime = $row->services->max('pivot.updated_at');
                $timeElapsed = $row->status === 'Facturado'
                    ? $row->created_at->diffForHumans($row->updated_at, true)
                    : $row->created_at->diffForHumans(now(), true);
                return $timeElapsed;
            })
            ->addColumn('status', function ($row) {
                return $row->status;
            })
            ->addColumn('action', function ($row) {
                return route('mechanic-work-orders.show', $row->id);
            })
            ->rawColumns(['service_status', 'product_status', 'action'])
            ->make(true);
    }

    return view('mechanic-work-orders.index');
}





private function calculateElapsedTime($workOrder)
{
    $start = $workOrder->created_at;
    $end = $workOrder->status === 'Facturado' || $workOrder->status === 'Cerrado' ? $workOrder->updated_at : now();
    return $start->diffForHumans($end, \Carbon\CarbonInterface::DIFF_ABSOLUTE, false, 2);
}


    /**
     * Mostrar detalles de una orden de trabajo asignada a un mecánico.
     */
    public function mechanicShowWorkOrder(WorkOrder $workOrder)
    {
        $workOrder->load([
            'vehicle',
            'client',
            'services' => function ($query) {
                $query->with('createdBy');
            },
            'products',
            'incidents' => function ($query) {
                $query->with('reportedBy');
            }
        ]);

        $incidents = Incident::all();

        foreach ($workOrder->services as $service) {
            $service->mechanic_name = User::find($service->pivot->mechanic_id)->name ?? 'Sin asignar';
        }

        foreach ($workOrder->incidents as $incident) {
            $incident->reported_by_name = User::find($incident->pivot->reported_by)->name ?? 'Sin asignar';
        }

        return view('mechanic-work-orders.show', compact('workOrder', 'incidents'));
    }

    /**
     * Actualizar el estado de una orden de trabajo asignada a un mecánico.
     */
    public function updateMechanicWorkOrderStatus(Request $request, WorkOrder $workOrder)
    {
        $request->validate([
            'status' => 'required|string|in:Abierto,Comenzó,Incidencias,Aprobadas,Completado,Facturado,Cerrado',
        ]);

        $workOrder->update([
            'status' => $request->status,
            'updated_by' => auth()->user()->id,
        ]);

        Alert::success('Éxito', 'Estado de la orden de trabajo actualizado con éxito');
        return redirect()->route('mechanic-work-orders.show', $workOrder);
    }

    /**
     * Actualizar el estado de un servicio en una orden de trabajo.
     */
    public function updateServiceStatus(Request $request, WorkOrder $workOrder, $serviceId)
{
    $request->validate([
        'status' => 'required|string|in:pendiente,iniciado,completado',
    ]);

    $mechanicId = auth()->user()->id;
    $service = $workOrder->services()->where('service_id', $serviceId)->wherePivot('mechanic_id', $mechanicId)->first();

    if ($service) {
        $workOrder->services()->updateExistingPivot($serviceId, ['status' => $request->status]);

        // Actualizar el estado de la OT si todos los servicios están completados
        $this->updateWorkOrderStatus($workOrder);

        Alert::success('Éxito', 'Estado del servicio actualizado con éxito');
        return back();
    } else {
        Alert::error('Error', 'No tienes permiso para actualizar este servicio');
        return back();
    }
}

    /**
     * Actualizar el estado general de una orden de trabajo según los servicios.
     */
    public function updateWorkOrderStatus(WorkOrder $workOrder)
    {
        $allServicesCompleted = $workOrder->services()->wherePivot('status', '!=', 'completado')->doesntExist();

        if ($allServicesCompleted) {
            $workOrder->status = 'Completado';
        } else {
            $anyServiceStarted = $workOrder->services()->wherePivot('status', 'iniciado')->exists();
            if ($anyServiceStarted) {
                $workOrder->status = 'Comenzó';
            } else {
                $workOrder->status = 'Abierto';
            }
        }

        $workOrder->save();

        return response()->json(['status' => 'success', 'message' => 'Estado de la Orden de Trabajo actualizado.']);
    }

    // GESTIÓN DE INCIDENCIAS

    /**
     * Agregar una incidencia a una orden de trabajo.
     */
    public function addIncident(Request $request, WorkOrder $workOrder)
    {
        $request->validate([
            'incident_id' => 'required|exists:incidents,id',
            'observation' => 'nullable|string',
        ]);

        $workOrder->incidents()->attach($request->incident_id, [
            'observation' => $request->observation,
            'reported_by' => auth()->user()->id,
        ]);

        $workOrder->update(['status' => 'Incidencias']);

        Alert::success('Éxito', 'Incidencia agregada con éxito');
        return back();
    }

    /**
     * Aprobar una incidencia en una orden de trabajo.
     */
    public function approveIncident(Request $request, WorkOrder $workOrder, $incidentId)
    {
        $workOrder->incidents()->updateExistingPivot($incidentId, [
            'approved' => true,
            'approved_by' => auth()->user()->id,
        ]);

        Alert::success('Éxito', 'Incidencia aprobada con éxito');
        return back();
    }

    /**
     * Actualizar el estado de una incidencia en una orden de trabajo.
     */
    public function updateIncidentStatus(Request $request, WorkOrder $workOrder, $incidentId)
    {
        $request->validate([
            'status' => 'required|boolean',
        ]);

        $workOrder->incidents()->updateExistingPivot($incidentId, [
            'approved' => $request->status,
            'approved_by' => auth()->user()->id,
        ]);

        $totalIncidents = $workOrder->incidents()->count();
        $approvedIncidents = $workOrder->incidents()->wherePivot('approved', true)->count();
        $disapprovedIncidents = $workOrder->incidents()->wherePivot('approved', false)->count();

        if ($approvedIncidents == $totalIncidents) {
            $workOrder->update(['status' => 'Aprobada']);
        } elseif ($approvedIncidents > 0 && $approvedIncidents < $totalIncidents) {
            $workOrder->update(['status' => 'Parcial']);
        } elseif ($disapprovedIncidents == $totalIncidents) {
            $workOrder->update(['status' => 'Desaprobada']);
        }

        Alert::success('Éxito', 'Estado de la incidencia actualizado con éxito');
        return back();
    }

    // GESTIÓN DE PRODUCTOS

    /**
     * Mostrar órdenes de trabajo para la gestión de bodega.
     */
    public function warehouseWorkOrders()
    {
        return view('warehouse-work-orders.index');
    }

    /**
     * Obtener lista de órdenes de trabajo para la gestión de bodega.
     */
    public function warehouseWorkOrdersList(Request $request)
{
    if ($request->ajax()) {
        $data = WorkOrder::with(['client', 'vehicle.brand', 'services', 'products'])
            ->whereHas('products')
            ->latest()
            ->get();

        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('client', function ($row) {
                return $row->client ? $row->client->name : '';
            })
            ->addColumn('vehicle', function ($row) {
                return $row->vehicle ? $row->vehicle->brand->name . ' ' . $row->vehicle->model : '';
            })
            ->addColumn('service_status', function ($row) {
                $allCompleted = $row->services->every(fn ($service) => $service->pivot->status === 'completado');
                $anyStarted = $row->services->contains(fn ($service) => $service->pivot->status === 'iniciado');

                if ($allCompleted) {
                    return 'Completado';
                } elseif ($anyStarted) {
                    return 'Iniciado';
                } else {
                    return 'Pendiente';
                }
            })
            ->addColumn('product_status', function ($row) {
                $totalProducts = $row->products->sum('pivot.quantity');
                $deliveredProducts = $row->products->where('pivot.status', 'entregado')->sum('pivot.quantity');

                if ($deliveredProducts === $totalProducts) {
                    return 'Entregado';
                } elseif ($deliveredProducts > 0) {
                    return 'Parcialmente Entregado';
                } else {
                    return 'Pendiente';
                }
            })
            ->addColumn('time', function ($row) {
                $completionTime = $row->services->max('pivot.updated_at');
                $timeElapsed = $row->status === 'Facturado'
                    ? $row->created_at->diffForHumans($row->updated_at, true)
                    : $row->created_at->diffForHumans(now(), true);
                return $timeElapsed;
            })
            ->addColumn('action', function ($row) {
                return route('warehouse-work-orders.show', $row->id);
            })
            ->rawColumns(['service_status', 'product_status', 'action'])
            ->make(true);
    }

    return view('warehouse-work-orders.index');
}

    /**
     * Mostrar detalles de una orden de trabajo en bodega.
     */
    public function showWarehouseWorkOrder(WorkOrder $workOrder)
{
    $workOrder->load([
        'vehicle',
        'client',
        'services' => function ($query) {
            $query->with('mechanic'); // Relación con el mecánico asignado
        },
        'products',
        'incidents' => function ($query) {
            $query->with('reportedBy'); // Relación con el usuario que reportó la incidencia
        }
    ]);

    // Obtener los nombres de los mecánicos asignados a los servicios
    foreach ($workOrder->services as $service) {
        $service->mechanic_name = User::find($service->pivot->mechanic_id)->name ?? 'Sin asignar';
    }

    // Obtener los nombres de los mecánicos que reportaron las incidencias
    foreach ($workOrder->incidents as $incident) {
        $incident->reported_by_name = User::find($incident->pivot->reported_by)->name ?? 'Sin asignar';
    }

    $incidents = Incident::all(); // Asegurarse de tener todas las incidencias disponibles

    return view('warehouse-work-orders.show', compact('workOrder', 'incidents'));
}




    /**
     * Actualizar el estado de un producto en una orden de trabajo.
     */
    public function updateProductStatus(Request $request, WorkOrder $workOrder, $productId)
{
    $request->validate([
        'status' => 'required|string|in:entregado,pendiente',
    ]);

    $workOrder->products()->updateExistingPivot($productId, ['status' => $request->status]);

    return response()->json(['message' => 'Estado del producto actualizado con éxito.'], 200);
}


    // VISTA DE EJECUTIVOS

    /**
     * Mostrar órdenes de trabajo para ejecutivos.
     */
    public function executiveWorkOrders(Request $request)
{
    if ($request->ajax()) {
        $data = WorkOrder::with(['client', 'vehicle.brand', 'services', 'products'])
            ->where('executive_id', auth()->user()->id)
            ->latest()
            ->get();

        foreach ($data as $workOrder) {
            $workOrder->time = $this->calculateElapsedTime($workOrder);
            $workOrder->action = route('executive-work-orders.show', $workOrder->id); // Añadir la URL de la acción
        }

        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('client', function ($row) {
                return $row->client ? $row->client->name : '';
            })
            ->addColumn('vehicle', function ($row) {
                return $row->vehicle ? $row->vehicle->brand->name . ' ' . $row->vehicle->model : '';
            })
            ->addColumn('service_status', function ($row) {
                return $row->services->every(fn ($service) => $service->pivot->status === 'completado') ? 'Completado' : ($row->services->contains(fn ($service) => $service->pivot->status === 'iniciado') ? 'Iniciado' : 'Pendiente');
            })
            ->addColumn('product_status', function ($row) {
                $totalProducts = $row->products->sum('pivot.quantity');
                $deliveredProducts = $row->products->where('pivot.status', 'entregado')->sum('pivot.quantity');

                return $deliveredProducts === $totalProducts ? 'Entregado' : ($deliveredProducts > 0 ? 'Parcialmente Entregado' : 'Pendiente');
            })
            ->addColumn('time', function ($row) {
                return $row->time;
            })
            ->addColumn('status', function ($row) {
                return $row->status;
            })
            ->addColumn('action', function ($row) {
                return $row->action; // Devolver la URL de la acción
            })
            ->rawColumns(['service_status', 'product_status', 'status', 'action'])
            ->make(true);
    }

    return view('executive-work-orders.index');
}



        /**
     * Mostrar detalles de una orden de trabajo para ejecutivos.
     */
    public function executiveShowWorkOrder(WorkOrder $workOrder)
    {
        $workOrder->load([
            'vehicle',
            'client',
            'services' => function ($query) {
                $query->with('mechanic');
            },
            'products',
            'incidents' => function ($query) {
                $query->with('reportedBy');
            }
        ]);

        $services = Service::all();
        $products = Product::all();
        $mechanics = User::role('Mecánico')->get();
        $incidents = Incident::all();

        foreach ($workOrder->services as $service) {
            $service->mechanic_name = User::find($service->pivot->mechanic_id)->name ?? 'Sin asignar';
        }

        foreach ($workOrder->incidents as $incident) {
            $incident->reported_by_name = User::find($incident->pivot->reported_by)->name ?? 'Sin asignar';
        }

        return view('executive-work-orders.show', compact('workOrder', 'services', 'products', 'mechanics', 'incidents'));
    }

    /**
     * Agregar un servicio a una orden de trabajo.
     */
    public function addService(Request $request, WorkOrder $workOrder)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'mechanic_id' => 'required|exists:users,id',
        ]);

        $service = Service::find($request->service_id);

        $workOrder->services()->attach($service->id, [
            'mechanic_id' => $request->mechanic_id,
            'status' => 'pendiente',
        ]);

        $workOrder->subtotal += $service->price;
        $workOrder->tax = $workOrder->subtotal * ($workOrder->tax_percentage / 100);
        $workOrder->total = $workOrder->subtotal + $workOrder->tax - $workOrder->discount;

        $workOrder->save();

        Alert::success('Éxito', 'Servicio agregado con éxito');
        return back();
    }

    /**
     * Agregar un producto a una orden de trabajo.
     */
    public function addProduct(Request $request, WorkOrder $workOrder)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $productId = $request->input('product_id');
        $quantity = $request->input('quantity');

        $workOrder->products()->attach($productId, ['quantity' => $quantity, 'status' => 'pendiente']);

        Alert::success('Éxito', 'Producto agregado con éxito');
        return back();
    }

    /**
     * Mostrar la versión imprimible de una orden de trabajo.
     */
    public function printWorkOrder(WorkOrder $workOrder)
    {
        $workOrder->load(['client', 'vehicle', 'services', 'products', 'createdBy']);

        foreach ($workOrder->services as $service) {
            $service->mechanic_name = User::find($service->pivot->mechanic_id)->name ?? 'Sin asignar';
        }

        return view('executive-work-orders.print', compact('workOrder'));
    }

       /**
     * Facturar una orden de trabajo.
     */
    public function facturar(Request $request, WorkOrder $workOrder)
    {
        $incompleteServices = $workOrder->services()->wherePivot('status', '!=', 'completado')->get();
        $incompleteProducts = $workOrder->products()->wherePivot('status', '!=', 'entregado')->get();

        if ($incompleteServices->isNotEmpty() || $incompleteProducts->isNotEmpty()) {
            $incompleteDetails = [];
            if ($incompleteServices->isNotEmpty()) {
                $incompleteDetails[] = "En el taller no se han terminado estos servicios: " . $incompleteServices->pluck('name')->implode(', ');
            }
            if ($incompleteProducts->isNotEmpty()) {
                $incompleteDetails[] = "Desde bodega no han entregado los productos: " . $incompleteProducts->pluck('name')->implode(', ');
            }
            $incompleteDetails[] = "Recuerda a tus compañeros cerrar sus procesos para poder facturar la OT.";
            return response()->json(['message' => implode(' ', $incompleteDetails)], 400);
        }

        $workOrder->update(['status' => 'Facturado']);

        return response()->json(['message' => 'La OT ha quedado con estado Facturado. Ya no se pueden hacer cambios.'], 200);
    }

    /**
     * Mostrar la lista de órdenes de trabajo.
     */
    public function list(Request $request)
    {
        if ($request->ajax()) {
            \Log::info('Fetching work orders for DataTable');

            $data = WorkOrder::with(['createdBy', 'client', 'vehicle'])->latest()->get();

            \Log::info('Work orders fetched:', $data->toArray());

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('executive', function ($row) {
                    return $row->createdBy ? $row->createdBy->name : '';
                })
                ->addColumn('client', function ($row) {
                    return $row->client ? $row->client->name : '';
                })
                ->addColumn('vehicle', function ($row) {
                    return $row->vehicle ? $row->vehicle->brand->name . ' ' . $row->vehicle->model : '';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<a href="' . route('work-orders.show', $row->id) . '" class="edit btn btn-info btn-sm"><i class="fas fa-eye"></i></a>';
                    $btn .= ' <a href="' . route('work-orders.edit', $row->id) . '" class="edit btn btn-secondary btn-sm"><i class="fas fa-edit"></i></a>';
                    $btn .= ' <form action="' . route('work-orders.destroy', $row->id) . '" method="POST" style="display:inline-block;">
                            ' . csrf_field() . '
                            ' . method_field("DELETE") . '
                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                        </form>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('work-orders.list');
    }
}
