<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use App\Models\Vehicle;
use App\Models\Client;
use App\Models\User;
use App\Models\Service;
use App\Models\Product;
use App\Models\ClientGroup;
use App\Models\Mechanic;
use App\Models\Incident;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WorkOrderController extends Controller
{
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

    public function create()
    {
        return view('work-orders.create');
    }

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

    public function edit(WorkOrder $workOrder)
    {
        return view('work-orders.edit', compact('workOrder'));
    }

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

    public function destroy(WorkOrder $workOrder)
    {
        $workOrder->delete();
        Alert::success('Éxito', 'Orden de Trabajo eliminada con éxito');
        return redirect()->route('work-orders.index');
    }

    // Paso 1: Buscar Vehiculo por Patente
    public function createStepOne()
    {
        return view('work-orders.create-step-one');
    }

    // Paso 2: Busque auto por patente
    public function searchVehicle(Request $request)
    {
        $request->validate(['license_plate' => 'required|string']);

        $vehicles = Vehicle::where('license_plate', $request->license_plate)->get();

        $clients = Client::all(); // Obtener todos los clientes
        $clientGroups = ClientGroup::all(); // Obtener todos los grupos de clientes
        $latestClient = null;

        if ($vehicles->isNotEmpty()) {
            $latestClient = $vehicles->first()->clients()->latest()->first(); // Obtener el último cliente asociado al vehículo
        }

        return view('work-orders.create-step-two', compact('vehicles', 'clients', 'latestClient', 'clientGroups'));
    }

    // Paso 3: Seleccione Vehiculo o cree nueva asociacion
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

    // Paso 3: Mostrar formulario para agregar servicios y productos
    public function createStepThree($vehicle_id)
    {
        $services = Service::where('status', true)->get();
        $products = Product::where('status', true)->get();
        return view('work-orders.create-step-three', compact('vehicle_id', 'services', 'products'));
    }

    // Paso 3: Almacenar servicios y productos seleccionados
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




    // Paso 4: Mostrar formulario para confirmar servicios y revisar extras
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

    // Paso 4: Almacenar confirmación de servicios y revisiones extras
    public function storeStepFour(Request $request)
    {
        $request->validate([
            'extra_reviews' => 'nullable|array',
        ]);

        session(['extra_reviews' => $request->extra_reviews]);

        return redirect()->route('work-orders.create-step-five');
    }

    // Paso 5: Mostrar formulario para asignar mecánicos
    public function createStepFive()
    {
        $services = session('services');
        if (is_null($services)) {
            return redirect()->route('work-orders.create-step-three')->withErrors('Por favor, seleccione los servicios primero.');
        }

        $mechanics = User::role('Mecánico')->get(); // Obtener todos los usuarios con el rol 'Mecánico'

        return view('work-orders.create-step-five', compact('services', 'mechanics'));
    }

    // Paso 5: Almacenar asignaciones de mecánicos
    public function storeStepFive(Request $request)
    {
        $request->validate([
            'mechanics' => 'required|array',
        ]);

        session(['mechanics' => $request->mechanics]);

        return redirect()->route('work-orders.create-step-six');
    }

    // Paso 6: Mostrar resumen de la OT
    public function createStepSix()
    {
        $vehicle = Vehicle::find(session('vehicle_id'));
        $services = session('services', []);
        $products = session('products', []);
        $extra_reviews = session('extra_reviews', []);
        $mechanics = session('mechanics', []);

        // Verifica que los servicios y productos se carguen correctamente desde la sesión
        $services = array_map(function ($service) {
            return Service::find($service['id']);
        }, $services);

        $products = array_map(function ($product) {
            return Product::find($product['id']);
        }, $products);

        return view('work-orders.create-step-six', compact('vehicle', 'services', 'products', 'extra_reviews', 'mechanics'));
    }


    // Paso 6: Almacenar la OT completa
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
        return redirect()->route('work-orders.show', $workOrder->id);
    }




    // Paso 7: Mostrar detalles de la OT creada
    public function show(WorkOrder $workOrder)
    {
        $workOrder->load('services', 'products', 'vehicle', 'createdBy', 'mechanics');
        return view('work-orders.show', compact('workOrder'));
    }

    // Para mecánicos: mostrar órdenes de trabajo asignadas
    public function mechanicWorkOrders()
    {
        $mechanicId = auth()->user()->id;
        $workOrders = WorkOrder::whereHas('services', function ($query) use ($mechanicId) {
            $query->where('mechanic_id', $mechanicId);
        })->get();

        return view('mechanic-work-orders.index', compact('workOrders'));
    }

    // Para mecánicos: mostrar detalles de una orden de trabajo
    public function mechanicShowWorkOrder(WorkOrder $workOrder)
    {
        $workOrder->load('vehicle', 'client', 'services', 'products', 'mechanics', 'incidents');

        $incidents = Incident::all();
        return view('mechanic-work-orders.show', compact('workOrder', 'incidents'));
    }

    // Para mecánicos: actualizar el estado de una orden de trabajo
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

    //actualiza estado del servicio
    public function updateWorkOrderStatus(WorkOrder $workOrder)
{
    // Comprobar si todos los servicios están completados
    $allServicesCompleted = $workOrder->services()->wherePivot('status', '!=', 'completado')->doesntExist();

    if ($allServicesCompleted) {
        $workOrder->status = 'Completado';
    } else {
        // Comprobar si al menos un servicio ha sido iniciado
        $anyServiceStarted = $workOrder->services()->wherePivot('status', 'iniciado')->exists();
        if ($anyServiceStarted) {
            $workOrder->status = 'Comenzó';
        } else {
            // Si no hay servicios iniciados, la OT está en estado Abierto
            $workOrder->status = 'Abierto';
        }
    }

    $workOrder->save();

    return response()->json(['status' => 'success', 'message' => 'Estado de la Orden de Trabajo actualizado.']);
}

    // Para mecánicos: actualizar el estado de una orden de trabajo
public function updateServiceStatus(Request $request, WorkOrder $workOrder, $serviceId)
{
    $request->validate([
        'status' => 'required|string|in:pendiente,iniciado,completado',
    ]);

    $mechanicId = auth()->user()->id;
    $service = $workOrder->services()->where('service_id', $serviceId)->wherePivot('mechanic_id', $mechanicId)->first();

    if ($service) {
        $workOrder->services()->updateExistingPivot($serviceId, ['status' => $request->status]);

        // Cambiar estado de la OT según los servicios
        $this->updateWorkOrderStatus($workOrder);

        Alert::success('Éxito', 'Estado del servicio actualizado con éxito');
        return back();
    } else {
        Alert::error('Error', 'No tienes permiso para actualizar este servicio');
        return back();
    }
}

    // Agregar incidencias
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



    public function approveIncident(Request $request, WorkOrder $workOrder, $incidentId)
{
    $workOrder->incidents()->updateExistingPivot($incidentId, [
        'approved' => true,
        'approved_by' => auth()->user()->id,
    ]);

    Alert::success('Éxito', 'Incidencia aprobada con éxito');
    return back();
}



    //bodeguero
    public function warehouseWorkOrders()
    {
        return view('warehouse-work-orders.index');
    }

    public function warehouseWorkOrdersList(Request $request)
{
    if ($request->ajax()) {
        $data = WorkOrder::with(['client', 'vehicle.brand', 'products'])->whereHas('products')->latest()->get();

        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('vehicle', function ($row) {
                return $row->vehicle ? $row->vehicle->brand->name . ' ' . $row->vehicle->model : '';
            })
            ->addColumn('products', function ($row) {
                return $row->products->sum('pivot.quantity');
            })
            ->addColumn('created_at', function ($row) {
                return $row->created_at->format('Y-m-d H:i:s');
            })
            ->addColumn('status', function ($row) {
                return $row->status;
            })
            ->addColumn('action', function ($row) {
                $totalProducts = $row->products->sum('pivot.quantity');
                $deliveredProducts = $row->products->where('pivot.status', 'entregado')->sum('pivot.quantity');

                if ($deliveredProducts == 0) {
                    $statusIcon = '<i class="fas fa-circle text-danger"></i>';
                } elseif ($deliveredProducts < $totalProducts) {
                    $statusIcon = '<i class="fas fa-circle text-warning"></i>';
                } else {
                    $statusIcon = '<i class="fas fa-circle text-success"></i>';
                }

                return '<a href="' . route('warehouse-work-orders.show', $row->id) . '" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a> ' . $statusIcon;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    return view('warehouse-work-orders.index');
}

//vista ejecutivos
public function executiveWorkOrders(Request $request)
{
    if ($request->ajax()) {
        $data = WorkOrder::with(['client', 'vehicle', 'services', 'products'])
            ->where('executive_id', auth()->user()->id)
            ->latest()
            ->get();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('client', function ($row) {
                return $row->client ? $row->client->name : '';
            })
            ->addColumn('vehicle', function ($row) {
                return $row->vehicle ? $row->vehicle->brand->name . ' ' . $row->vehicle->model : '';
            })
            ->addColumn('service_status', function ($row) {
                $allCompleted = $row->services->every(fn($service) => $service->pivot->status === 'completado');
                $anyStarted = $row->services->contains(fn($service) => $service->pivot->status === 'iniciado');

                if ($allCompleted) {
                    return '<span class="badge badge-success">Completado</span>';
                } elseif ($anyStarted) {
                    return '<span class="badge badge-warning">Iniciado</span>';
                } else {
                    return '<span class="badge badge-danger">Pendiente</span>';
                }
            })
            ->addColumn('product_status', function ($row) {
                $totalProducts = $row->products->sum('pivot.quantity');
                $deliveredProducts = $row->products->where('pivot.status', 'entregado')->sum('pivot.quantity');

                if ($deliveredProducts === $totalProducts) {
                    return '<span class="badge badge-success">Entregado</span>';
                } elseif ($deliveredProducts > 0) {
                    return '<span class="badge badge-warning">Parcialmente Entregado</span>';
                } else {
                    return '<span class="badge badge-danger">Pendiente</span>';
                }
            })
            ->addColumn('time', function ($row) {
                $completionTime = $row->services->max('pivot.updated_at');
                $timeElapsed = now()->diffForHumans($completionTime, true);
                return $timeElapsed;
            })
            ->addColumn('action', function ($row) {
                return '<a href="' . route('executive-work-orders.show', $row->id) . '" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>';
            })
            ->rawColumns(['service_status', 'product_status', 'action'])
            ->make(true);
    }

    return view('executive-work-orders.index');
}

// dettales del ejecutivo
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

    // Actualizar el subtotal, impuesto y total de la OT
    $workOrder->subtotal += $service->price;
    $workOrder->tax = $workOrder->subtotal * ($workOrder->tax_percentage / 100);
    $workOrder->total = $workOrder->subtotal + $workOrder->tax - $workOrder->discount;

    $workOrder->save();

    Alert::success('Éxito', 'Servicio agregado con éxito');
    return back();
}

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

public function printWorkOrder(WorkOrder $workOrder)
{
    $workOrder->load('vehicle', 'client', 'services', 'products', 'mechanics', 'incidents');

    return view('executive-work-orders.print', compact('workOrder'));
}

// Para ejecutivos: mostrar detalles de una orden de trabajo
public function executiveShowWorkOrder(WorkOrder $workOrder)
{
    $workOrder->load([
        'vehicle',
        'client',
        'services' => function($query) {
            $query->with('mechanics');
        },
        'products',
        'incidents.reportedBy'
    ]);

    $services = Service::all();
    $products = Product::all();
    $mechanics = User::role('Mecánico')->get();

    return view('executive-work-orders.show', compact('workOrder', 'services', 'products', 'mechanics'));
}





// Para ejecutivos: aprobar o desaprobar incidencias
public function updateIncidentStatus(Request $request, WorkOrder $workOrder, $incidentId)
{
    $request->validate([
        'status' => 'required|boolean',
    ]);

    $workOrder->incidents()->updateExistingPivot($incidentId, [
        'approved' => $request->status,
        'approved_by' => auth()->user()->id,
    ]);

    // Actualizar el estado de la OT según las incidencias
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





//bodega

    public function showWarehouseWorkOrder(WorkOrder $workOrder)
    {
        $workOrder->load('products', 'services.mechanics');
        return view('warehouse-work-orders.show', compact('workOrder'));
    }

    public function updateProductStatus(Request $request, WorkOrder $workOrder, $productId)
    {
        $request->validate([
            'status' => 'required|string|in:entregado,no-entregado',
        ]);

        $workOrder->products()->updateExistingPivot($productId, ['status' => $request->status]);

        Alert::success('Éxito', 'Estado del producto actualizado con éxito');
        return back();
    }

    //lista de ots


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
