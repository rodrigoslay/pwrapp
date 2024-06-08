<?php

namespace App\Http\Controllers;

use App\Helpers\CurrencyHelper;
use Illuminate\Http\Request;
use App\Models\WorkOrder;
use App\Models\Vehicle;
use App\Models\Client;
use App\Models\Service;
use App\Models\Product;
use App\Models\Revision;
use App\Models\Incident;
use App\Models\User;
use App\Models\RevisionWorkOrder;
use App\Models\CarModel;
use App\Models\Brand;
use App\Models\BrandWorkOrder;
use App\Models\ClientGroup;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Carbon;
use Yajra\DataTables\Facades\DataTables;


class WorkOrderController extends Controller
{
    // Paso 1: Seleccionar Vehículo y Cliente
    public function createStepOne()
    {
        $vehicles = Vehicle::all();
        $clients = Client::all();
        $clientGroups = ClientGroup::all();
        $brands = Brand::all();

        return view('work-orders.create-step-one', compact('vehicles', 'clients', 'clientGroups', 'brands'));
    }

    // Agregar cliente
    public function storeClient(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'rut' => 'required|string|max:255|unique:clients',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:15',
            'client_group_id' => 'required|exists:client_groups,id',
            'status' => 'required|boolean',
        ]);

        try {
            $client = Client::create($validatedData);
            return response()->json(['success' => true, 'client' => $client]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // Agregar vehículo
    public function storeVehicle(Request $request)
    {
        $validatedData = $request->validate([
            'license_plate' => 'required|string|max:255|unique:vehicles',
            'brand_id' => 'required|exists:brands,id',
            'model' => 'required|string|max:255',
            'color' => 'required|string|max:255',
            'chassis' => 'required|string|max:255',
            'kilometers' => 'required|integer',
            'registration_date' => 'nullable|date',
            'photo' => 'nullable|image|max:2048',
            'client_id_vehicle' => 'required|exists:clients,id',
        ]);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('vehicles', 'public');
            $validatedData['photo'] = $path;
        }

        try {
            $vehicle = Vehicle::create($validatedData);
            return response()->json(['success' => true, 'vehicle' => $vehicle]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // Agregar marca
    public function storeBrand(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:brands',
        ]);

        try {
            $brand = Brand::create($validatedData);
            return response()->json(['success' => true, 'brand' => $brand]);
        } catch (\Exception $e) {
            Log::error('Error al agregar la marca: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al agregar la marca: ' . $e->getMessage()]);
        }
    }

    // Agregar modelo de vehículo
    public function storeCarModel(Request $request)
    {
        $validatedData = $request->validate([
            'brand_id' => 'required|exists:brands,id',
            'model' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:' . date('Y'),
        ]);

        try {
            $carModel = CarModel::create($validatedData);
            return response()->json(['success' => true, 'carModel' => $carModel]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // Método para obtener los modelos de una marca específica
    public function getModelsByBrand($brandId)
    {
        $models = CarModel::where('brand_id', $brandId)->get();
        return response()->json($models);
    }

    public function searchVehicle(Request $request)
    {
        $vehicle = Vehicle::find($request->vehicle_id);
        $client = Client::find($request->client_id);

        if ($vehicle && $client) {
            session(['vehicle' => $vehicle, 'client' => $client]);
            return redirect()->route('work-orders.create-step-two');
        } else {
            return back()->withErrors(['message' => 'Vehículo o Cliente no encontrado']);
        }
    }

    public function createStepTwo()
    {
        $services = Service::all();
        $revisions = Revision::all();

        return view('work-orders.create-step-two', compact('services', 'revisions'));
    }

    public function storeStepTwo(Request $request)
{
    // Guardar las revisiones y sus fallos en la sesión
    $selectedRevisions = Revision::whereIn('id', $request->revisions)->with('faults')->get();
    session(['selected_services' => $request->services, 'selected_revisions' => $selectedRevisions]);

    return redirect()->route('work-orders.create-step-three');
}

    public function createStepThree()
    {
        $products = Product::all();
        return view('work-orders.create-step-three', compact('products'));
    }

    // Guarda los productos seleccionados y sus cantidades y pasa al siguiente paso
    public function storeStepThree(Request $request)
{
    // Filtrar solo los productos seleccionados
    $selectedProducts = array_filter($request->products, function ($value, $key) use ($request) {
        return $request->has('products.' . $key);
    }, ARRAY_FILTER_USE_BOTH);

    // Almacenar productos y cantidades en la sesión
    session([
        'selected_products' => $selectedProducts,
        'products_quantities' => $request->quantities,
    ]);

    return redirect()->route('work-orders.create-step-four');
}
    public function createStepFour()
    {

        $services = session('selected_services') ? Service::whereIn('id', session('selected_services'))->with('mechanics')->get() : [];
        $revisions = session('selected_revisions') ? Revision::whereIn('id', session('selected_revisions'))->get() : [];
        $mechanics = User::role('Mecánico')->get();
        $mechanicServiceCounts = $this->getMechanicServiceCounts();
        $selectedProductIds = session('selected_products', []);
        $productsQuantities = session('products_quantities', []);

        $products = Product::whereIn('id', $selectedProductIds)->get();

        return view('work-orders.create-step-four', compact('services', 'products', 'revisions', 'productsQuantities', 'mechanics', 'mechanicServiceCounts'));
    }

   // Guarda la selección de mecánicos y pasa al siguiente paso
   public function storeStepFour(Request $request)
   {
       // Guarda las asignaciones de mecánicos en la sesión
       session(['mechanic_assignments' => $request->mechanics]);
       return redirect()->route('work-orders.create-step-five');
   }
    // Método que prepara la vista para el paso cinco de creación de la OT
 // Prepara la vista del paso cinco
 public function createStepFive()
{
    $services = session('selected_services') ? Service::whereIn('id', session('selected_services'))->with(['mechanics'])->get() : [];
    $revisions = session('selected_revisions', []); // Recuperar las revisiones de la sesión

    $mechanicAssignments = session('mechanic_assignments', []);
    $productsQuantities = session('products_quantities', []);

    $products = session('selected_products') ? Product::whereIn('id', session('selected_products'))->get() : [];
    $client = session('client');
    $vehicle = session('vehicle');

    $mechanicIds = array_values($mechanicAssignments);
    $mechanicNames = User::whereIn('id', $mechanicIds)->get()->pluck('name', 'id');

    return view('work-orders.create-step-five', compact('services', 'products', 'revisions', 'client', 'vehicle', 'mechanicAssignments', 'productsQuantities', 'mechanicNames'));
}



public function storeStepFive(Request $request)
{
    DB::beginTransaction();
    try {
        // Calcular el subtotal
        $services = Service::whereIn('id', collect($request->services)->pluck('id'))->get();
        $products = Product::whereIn('id', collect($request->products)->pluck('id'))->get();

        $subtotal = $services->sum('price') +
            $products->sum(function ($product) use ($request) {
                return $product->price * collect($request->products)->firstWhere('id', $product->id)['quantity'];
            });

        // Calcular el descuento
        $client = Client::find($request->client_id);
        $discountPercentage = optional($client->clientGroup)->discount_percentage ?? 0;
        $discount = $subtotal * ($discountPercentage / 100);

        // Calcular el impuesto
        $tax = ($subtotal - $discount) * 0.19; // Assuming 19% tax rate

        // Calcular el total
        $total = $subtotal - $discount + $tax;

        // Crear la orden de trabajo
        $workOrder = new WorkOrder();
        $workOrder->client_id = $request->client_id;
        $workOrder->vehicle_id = $request->vehicle_id;
        $workOrder->entry_mileage = $request->entry_mileage;
        $workOrder->status = 'Abierto';
        $workOrder->subtotal = $subtotal;
        $workOrder->tax = $tax;
        $workOrder->total = $total;
        $workOrder->executive_id = auth()->user()->id; // Añadir el ID del usuario autenticado como el ejecutivo
        $workOrder->created_by = auth()->user()->id; // Añadir el ID del usuario autenticado como el ejecutivo
        $workOrder->save();

        // Agregar servicios
        if ($request->has('services')) {
            foreach ($request->services as $serviceData) {
                $mechanic_id = $serviceData['mechanic_id'] ?? null;
                if ($mechanic_id) {
                    $workOrder->services()->attach($serviceData['id'], [
                        'mechanic_id' => $mechanic_id,
                        'status' => 'pendiente'
                    ]);
                }
            }
        }

        // Agregar productos con sus cantidades
        if ($request->has('products')) {
            foreach ($request->products as $productData) {
                $quantity = $request->quantities[$productData['id']] ?? 1; // Recuperar la cantidad de la sesión
                $workOrder->products()->attach($productData['id'], [
                    'quantity' => $quantity,
                    'status' => 'pendiente'
                ]);
            }
        }

        // Agregar revisiones y sus fallos asociados
        if ($request->has('revisions')) {
            foreach ($request->revisions as $revision) {
                foreach ($revision['faults'] as $fault) {
                    // Verificar si la entrada ya existe antes de insertar
                    $existingEntry = DB::table('revision_work_order')
                        ->where('work_order_id', $workOrder->id)
                        ->where('revision_id', $revision['id'])
                        ->where('fault_id', $fault['id'])
                        ->first();

                    if (!$existingEntry) {
                        $workOrder->revisions()->attach($revision['id'], [
                            'fault_id' => $fault['id'],
                            'status' => 1
                        ]);
                    }
                }
            }
        }

        DB::commit();

        // Redirigir a la vista de detalles del ejecutivo con el ID de la OT creada
        return redirect()->route('executive-work-orders.show', $workOrder->id)->with('success', 'Orden de trabajo creada exitosamente');
    } catch (\Exception $e) {
        DB::rollback();
        return redirect()->route('work-orders.create-step-five')->withErrors(['message' => 'Error al crear la orden de trabajo: ' . $e->getMessage()]);
    }
}


    private function getMechanicServiceCounts()
{
    return User::role('Mecánico')
        ->withCount([
            'workOrders as not_completed_count' => function ($query) {
                $query->where('work_orders.status', '!=', 'completado');
            },
            'workOrders as completed_count' => function ($query) {
                $query->where('work_orders.status', 'completado');
            }
        ])->get()->map(function ($mechanic) {
            return [
                'name' => $mechanic->name,
                'not_completed_count' => $mechanic->not_completed_count,
                'completed_count' => $mechanic->completed_count,
            ];
        });
}

// Otros métodos como index, show, etc.
// Mostrar órdenes de trabajo para ejecutivos
public function executiveWorkOrders()
{
    return view('executive-work-orders.index');
}

// Listar las órdenes de trabajo creadas por el ejecutivo para la DataTable
public function executiveWorkOrdersList(Request $request)
{
    if ($request->ajax()) {
        $data = WorkOrder::where('executive_id', auth()->user()->id)->with(['client', 'vehicle', 'services', 'products'])->get();

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
                    return 'Completado';
                } elseif (in_array('iniciado', $statuses)) {
                    return 'Iniciado';
                } else {
                    return 'Pendiente';
                }
            })
            ->addColumn('product_status', function ($row) {
                $statuses = $row->products->pluck('pivot.status')->unique()->toArray();
                if (in_array('entregado', $statuses) && count($statuses) == 1) {
                    return 'Entregado';
                } elseif (in_array('parcialmente_entregado', $statuses)) {
                    return 'Parcialmente Entregado';
                } else {
                    return 'Pendiente';
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
                return $row->status;
            })
            ->addColumn('action', function ($row) {
                $btn = '<a href="' . route('executive-work-orders.show', $row->id) . '" class="edit btn btn-primary btn-sm">Ver</a>';
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    return view('executive-work-orders.index');
}


// Mostrar una orden de trabajo específica para ejecutivos
public function executiveShowWorkOrder($id)
    {
        $workOrder = WorkOrder::with([
            'client',
            'vehicle',
            'services',
            'products',
            'revisions.faults' => function ($query) use ($id) {
                $query->withPivot('status')->wherePivot('work_order_id', $id);
            },
            'incidents' => function ($query) {
                $query->with('reportedBy', 'approvedBy');
            }
        ])->findOrFail($id);

        $servicesList = Service::all();
        $mechanics = User::role('Mecánico')->get();
        $productsList = Product::all();
        $revisionsList = Revision::all();

        $revisionsWithFaults = $this->getRevisionsWithFaults($id);

        return view('executive-work-orders.show', compact('workOrder', 'servicesList', 'mechanics', 'productsList', 'revisionsList', 'revisionsWithFaults'));
    }


    private function getRevisionsWithFaults($workOrderId)
    {
        return Revision::whereHas('workOrders', function ($query) use ($workOrderId) {
            $query->where('work_order_id', $workOrderId);
        })->with(['faults' => function ($query) use ($workOrderId) {
            $query->wherePivot('work_order_id', $workOrderId);
        }])->get();
    }


// Actualizar el estado de una incidencia en la orden de trabajo para ejecutivos
public function updateIncidentStatus(Request $request, $workOrderId, $incidentId)
{
    $request->validate([
        'status' => 'required|in:0,1',
    ]);

    $workOrder = WorkOrder::findOrFail($workOrderId);
    $incident = $workOrder->incidents()->where('incident_id', $incidentId)->firstOrFail();

    $incident->pivot->approved = $request->status;
    $incident->pivot->approved_by = auth()->user()->id;
    $incident->pivot->save();

    return response()->json(['message' => 'Estado de la incidencia actualizado correctamente']);
}
// Facturar una orden de trabajo
public function facturar(Request $request, WorkOrder $workOrder)
{
    // Validar que todos los servicios estén completados
    $incompleteServices = $workOrder->services()->wherePivot('status', '!=', 'completado')->count();
    if ($incompleteServices > 0) {
        return response()->json(['status' => 'error', 'message' => 'No se puede facturar porque hay servicios incompletos.'], 400);
    }

    // Validar que todos los productos estén entregados
    $undeliveredProducts = $workOrder->products()->wherePivot('status', '!=', 'entregado')->count();
    if ($undeliveredProducts > 0) {
        return response()->json(['status' => 'error', 'message' => 'No se puede facturar porque hay productos no entregados.'], 400);
    }

    // Lógica para facturar
    $workOrder->status = 'Facturado';
    $workOrder->save();

    return response()->json(['status' => 'success', 'message' => 'Orden de trabajo facturada correctamente.']);
}

// Añadir un servicio a la orden de trabajo
public function addService(Request $request, WorkOrder $workOrder)
{
    $request->validate([
        'service_id' => 'required|exists:services,id',
        'mechanic_id' => 'required|exists:users,id',
    ]);

    $workOrder->services()->attach($request->service_id, ['mechanic_id' => $request->mechanic_id, 'status' => 'pendiente']);

    Alert::success('Éxito', 'Servicio agregado con éxito');
    return redirect()->route('executive-work-orders.show', $workOrder->id);
}

// Añadir un producto a la orden de trabajo
public function addProduct(Request $request, WorkOrder $workOrder)
{
    $request->validate([
        'product_id' => 'required|exists:products,id',
        'quantity' => 'required|integer|min:1',
    ]);

    $workOrder->products()->attach($request->product_id, ['quantity' => $request->quantity, 'status' => 'pendiente']);

    Alert::success('Éxito', 'Producto agregado con éxito');
    return redirect()->route('executive-work-orders.show', $workOrder->id);
}

// Añadir una revisión a la orden de trabajo
public function addRevision(Request $request, WorkOrder $workOrder)
{
    $request->validate([
        'revision_id' => 'required|exists:revisions,id',
    ]);

    $workOrder->revisions()->attach($request->revision_id, ['status' => 1]);

    Alert::success('Éxito', 'Revisión agregada con éxito');
    return redirect()->route('executive-work-orders.show', $workOrder->id);
}
// Obtener la lista de revisiones
 // Obtener la lista de revisiones
 public function getRevisions()
 {
     return Revision::all();
 }

 // Imprimir la orden de trabajo
 public function printWorkOrder(WorkOrder $workOrder)
 {
     // Aquí va la lógica para imprimir la orden de trabajo
     $pdf = PDF::loadView('work-orders.print', compact('workOrder'));
     return $pdf->download('OrdenDeTrabajo_' . $workOrder->id . '.pdf');
 }

 // Listar clientes para Select2
 public function listClients()
 {
     $clients = Client::select('id', 'name as text')->get();
     return response()->json($clients);
 }

 // Listar vehículos para Select2
 public function listVehicles()
 {
     $vehicles = Vehicle::select('id', 'license_plate as text')->get();
     return response()->json($vehicles);
 }
// Actualizar el estado de un servicio en la orden de trabajo
public function updateServiceStatus(Request $request, $workOrderId, $serviceId)
{
    $request->validate([
        'status' => 'required|string|in:pendiente,iniciado,completado',
    ]);

    DB::table('service_work_order')
        ->where('work_order_id', $workOrderId)
        ->where('service_id', $serviceId)
        ->update(['status' => $request->status]);

    return back()->with('success', 'Estado del servicio actualizado con éxito.');
}
// Añadir una incidencia a la orden de trabajo
public function addIncident(Request $request, $workOrderId)
{
    $request->validate([
        'incident_id' => 'required|exists:incidents,id',
        'observation' => 'required|string|max:255',
    ]);

    $workOrder = WorkOrder::findOrFail($workOrderId);
    $workOrder->incidents()->attach($request->incident_id, [
        'observation' => $request->observation,
        'reported_by' => auth()->user()->id,
        'approved' => 0,
    ]);

    return back()->with('success', 'Incidencia agregada correctamente.');
}
// Mostrar órdenes de trabajo para mecánicos
public function mechanicWorkOrders()
{
    return view('mechanic-work-orders.index');
}

// Listar órdenes de trabajo para mecánicos
public function mechanicWorkOrdersList(Request $request)
{
    if ($request->ajax()) {
        $data = WorkOrder::whereHas('services', function ($query) {
            $query->where('mechanic_id', auth()->user()->id);
        })->with(['client', 'vehicle', 'services', 'products'])->get();

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
                        $badgeClass = 'badge-dark';
                        break;
                    case 'Abierto':
                    case 'Desaprobado':
                        $badgeClass = 'badge-danger';
                        break;
                    case 'Comenzó':
                    case 'Incidencias':
                    case 'Aprobada':
                    case 'Parcial':
                        $badgeClass = 'badge-warning';
                        break;
                    default:
                        $badgeClass = 'badge-warning';
                        break;
                }
                return '<span class="badge ' . $badgeClass . '">' . $row->status . '</span>';
            })
            ->addColumn('action', function ($row) {
                return '<a href="' . route('mechanic-work-orders.show', $row->id) . '" class="edit btn btn-primary btn-sm">Ver</a>';
            })
            ->rawColumns(['service_status', 'product_status', 'status', 'action'])
            ->make(true);
    }

    return view('mechanic-work-orders.index');
}

// Mostrar una orden de trabajo específica para mecánicos
public function mechanicShowWorkOrder($id)
{
    $workOrder = WorkOrder::with([
        'client',
        'vehicle',
        'services',
        'products',
        'revisions.faults' => function ($query) use ($id) {
            $query->withPivot('status')->wherePivot('work_order_id', $id);
        },
        'incidents.reportedBy'
    ])->findOrFail($id);

    $incidents = Incident::all(); // Asegúrate de tener la lista de incidentes para el modal

    $revisionsWithFaults = $this->getRevisionsWithFaults($id);

    return view('mechanic-work-orders.show', compact('workOrder', 'incidents', 'revisionsWithFaults'));
}




// Actualizar el estado de una orden de trabajo para mecánicos
public function updateMechanicWorkOrderStatus(Request $request, $workOrderId, $serviceId)
{
    $request->validate([
        'status' => 'required|in:pendiente,iniciado,completado',
    ]);

    $workOrder = WorkOrder::findOrFail($workOrderId);
    $service = $workOrder->services()->where('service_id', $serviceId)->firstOrFail();

    if ($service->pivot->mechanic_id != auth()->user()->id) {
        return response()->json(['message' => 'No tienes permiso para actualizar este servicio'], 403);
    }

    $service->pivot->status = $request->status;
    $service->pivot->save();

    return response()->json(['message' => 'Estado del servicio actualizado correctamente']);
}



// Mostrar órdenes de trabajo para bodeguero
public function warehouseWorkOrders()
    {
        return view('warehouse-work-orders.index');
    }

// Listar órdenes de trabajo para bodeguero
public function warehouseWorkOrdersList(Request $request)
{
    if ($request->ajax()) {
        $data = WorkOrder::whereHas('products')->with(['client', 'vehicle', 'services', 'products'])->get();

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
                        $badgeClass = 'badge-dark';
                        break;
                    case 'Abierto':
                    case 'Desaprobado':
                        $badgeClass = 'badge-danger';
                        break;
                    case 'Comenzó':
                    case 'Incidencias':
                    case 'Aprobada':
                    case 'Parcial':
                        $badgeClass = 'badge-warning';
                        break;
                    default:
                        $badgeClass = 'badge-warning';
                        break;
                }
                return '<span class="badge ' . $badgeClass . '">' . $row->status . '</span>';
            })
            ->addColumn('action', function ($row) {
                return '<a href="' . route('warehouse-work-orders.show', $row->id) . '" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> Ver</a>';
            })
            ->rawColumns(['service_status', 'product_status', 'status', 'action'])
            ->make(true);
    }

    return view('warehouse-work-orders.index');
}

// Mostrar una orden de trabajo específica para bodeguero
public function showWarehouseWorkOrder($id)
    {
        $workOrder = WorkOrder::with([
            'client',
            'vehicle',
            'services',
            'products',
            'revisions.faults' => function ($query) use ($id) {
                $query->withPivot('status')->wherePivot('work_order_id', $id);
            },
            'incidents' => function ($query) {
                $query->with('reportedBy', 'approvedBy');
            }
        ])->findOrFail($id);

        $productsList = Product::all();

        return view('warehouse-work-orders.show', compact('workOrder', 'productsList'));
    }

// Actualizar el estado de un producto en la orden de trabajo para bodeguero
// Actualizar el estado de un producto en la orden de trabajo para bodeguero
public function updateProductStatus(Request $request, $workOrderId, $productId)
{
    $request->validate([
        'status' => 'required|string|in:pendiente,entregado',
    ]);

    $workOrder = WorkOrder::findOrFail($workOrderId);
    $product = $workOrder->products()->where('product_id', $productId)->firstOrFail();

    $product->pivot->status = $request->status;
    $product->pivot->save();

    return response()->json(['message' => 'Estado del producto actualizado correctamente']);
}
public function updateFaultStatus(Request $request, $workOrderId, $revisionId, $faultId)
{
    $revisionWorkOrder = DB::table('revision_work_order')
                            ->where('work_order_id', $workOrderId)
                            ->where('revision_id', $revisionId)
                            ->where('fault_id', $faultId)
                            ->first();

    if ($revisionWorkOrder) {
        DB::table('revision_work_order')
            ->where('work_order_id', $workOrderId)
            ->where('revision_id', $revisionId)
            ->where('fault_id', $faultId)
            ->update(['status' => $request->input('status')]);

        return response()->json(['message' => 'Estado de la revisión actualizado correctamente']);
    }

    return response()->json(['message' => 'No se pudo actualizar el estado de la revisión'], 400);
}

public function show($id)
{
    $workOrder = WorkOrder::with([
        'client',
        'vehicle',
        'services',
        'products',
        'revisions.faults' => function ($query) use ($id) {
            $query->withPivot('status')->wherePivot('work_order_id', $id);
        },
        'incidents'
    ])->findOrFail($id);

    $incidents = Incident::all(); // Asegúrate de tener la lista de incidentes para el modal

    return view('mechanic-work-orders.show', compact('workOrder', 'incidents'));
}

}
