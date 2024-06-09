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
use Barryvdh\DomPDF\Facade\Pdf;


if (!function_exists('array_flatten')) {
    function array_flatten($array)
    {
        $return = [];
        array_walk_recursive($array, function($a) use (&$return) { $return[] = $a; });
        return $return;
    }
}

class WorkOrderController extends Controller
{
    public function createStepOne()
    {
        $vehicles = Vehicle::all();
        $clients = Client::all();
        $clientGroups = ClientGroup::all();
        $brands = Brand::all();

        return view('work-orders.create-step-one', compact('vehicles', 'clients', 'clientGroups', 'brands'));
    }

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
    // Validar que al menos un servicio o revisión esté seleccionado
    $request->validate([
        'services' => 'array',
        'revisions' => 'array',
    ]);

    // Obtener las revisiones seleccionadas y sus fallos asociados
    $selectedRevisions = $request->revisions ? Revision::whereIn('id', $request->revisions)->with('faults')->get() : collect();

    // Guardar los servicios y revisiones seleccionados en la sesión
    session(['selected_services' => $request->services, 'selected_revisions' => $selectedRevisions]);

    return redirect()->route('work-orders.create-step-three');
}

    public function createStepThree()
    {
        $products = Product::all();
        return view('work-orders.create-step-three', compact('products'));
    }

    public function storeStepThree(Request $request)
{
    $validatedData = $request->validate([
        'products' => 'array',
        'products.*' => 'exists:products,id',
        'quantities' => 'array',
        'quantities.*' => 'integer|min:1',
    ]);

    $selectedProducts = $validatedData['products'] ?? [];
    $quantities = $validatedData['quantities'] ?? [];

    session([
        'selected_products' => $selectedProducts,
        'products_quantities' => $quantities,
    ]);

    return redirect()->route('work-orders.create-step-four');
}



    public function createStepFour()
{
    $services = session('selected_services') ? Service::whereIn('id', session('selected_services'))->with('mechanics')->get() : [];
    $selectedRevisions = session('selected_revisions') ? collect(session('selected_revisions'))->pluck('id')->toArray() : [];
    $revisions = !empty($selectedRevisions) ? Revision::whereIn('id', $selectedRevisions)->get() : [];
    $mechanics = User::role('Mecánico')->get();
    $mechanicServiceCounts = $this->getMechanicServiceCounts();
    $selectedProductIds = session('selected_products', []);
    $productsQuantities = session('products_quantities', []);

    $products = Product::whereIn('id', $selectedProductIds)->get();

    return view('work-orders.create-step-four', compact('services', 'products', 'revisions', 'productsQuantities', 'mechanics', 'mechanicServiceCounts'));
}



public function storeStepFour(Request $request)
{
    $selectedServices = session('selected_services', []);
    $selectedProducts = session('selected_products', []);
    $selectedRevisions = session('selected_revisions', []);

    if (empty($selectedServices) && empty($selectedProducts) && empty($selectedRevisions)) {
        return redirect()->route('work-orders.create-step-four')->with('error', 'Debe agregar al menos un servicio, producto o revisión para continuar.');
    }

    session(['mechanic_assignments' => $request->mechanics]);
    return redirect()->route('work-orders.create-step-five');
}


public function createStepFive()
{
    $services = session('selected_services') ? Service::whereIn('id', session('selected_services'))->with(['mechanics'])->get() : collect([]);
    $selectedRevisions = session('selected_revisions') ? collect(session('selected_revisions'))->pluck('id')->toArray() : [];
    $revisions = !empty($selectedRevisions) ? Revision::whereIn('id', $selectedRevisions)->with('faults')->get() : collect([]);
    $mechanicAssignments = session('mechanic_assignments', []);
    $productsQuantities = session('products_quantities', []);

    $selectedProducts = session('selected_products', []);
    $products = !empty($selectedProducts) ? Product::whereIn('id', $selectedProducts)->get() : collect([]);
    $client = session('client');
    $vehicle = session('vehicle');

    $mechanicIds = !empty($mechanicAssignments) ? array_values($mechanicAssignments) : [];
    $mechanicNames = !empty($mechanicIds) ? User::whereIn('id', $mechanicIds)->get()->pluck('name', 'id') : collect([]);

    return view('work-orders.create-step-five', compact('services', 'products', 'revisions', 'client', 'vehicle', 'mechanicAssignments', 'productsQuantities', 'mechanicNames'));
}


public function storeStepFive(Request $request)
{
    DB::beginTransaction();
    try {
        $serviceIds = array_column($request->services ?? [], 'id');
        $productIds = array_column($request->products ?? [], 'id');
        $revisionIds = array_column($request->revisions ?? [], 'id');

        if (empty($serviceIds) && empty($productIds) && empty($revisionIds)) {
            return redirect()->route('work-orders.create-step-five')->withErrors(['message' => 'Debe seleccionar al menos un servicio, producto o revisión.']);
        }

        $services = Service::whereIn('id', $serviceIds)->get();
        $products = Product::whereIn('id', $productIds)->get();
        $quantities = session('products_quantities', []);

        $subtotal = $services->sum('price') +
            $products->sum(function ($product) use ($quantities) {
                return $product->price * ($quantities[$product->id] ?? 1);
            });

        $client = Client::find($request->client_id);
        $discountPercentage = optional($client->clientGroup)->discount_percentage ?? 0;
        $discount = $subtotal * ($discountPercentage / 100);
        $tax = ($subtotal - $discount) * 0.19;
        $total = $subtotal - $discount + $tax;

        $workOrder = new WorkOrder();
        $workOrder->client_id = $request->client_id;
        $workOrder->vehicle_id = $request->vehicle_id;
        $workOrder->entry_mileage = $request->entry_mileage;
        $workOrder->status = 'Iniciado';
        $workOrder->subtotal = $subtotal;
        $workOrder->tax = $tax;
        $workOrder->total = $total;
        $workOrder->executive_id = auth()->user()->id;
        $workOrder->created_by = auth()->user()->id;
        $workOrder->save();

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

        if ($request->has('products')) {
            foreach ($request->products as $productData) {
                $quantity = $quantities[$productData['id']] ?? 1;
                $workOrder->products()->attach($productData['id'], [
                    'quantity' => $quantity,
                    'status' => 'pendiente'
                ]);
            }
        }

        if ($request->has('revisions')) {
            foreach ($request->revisions as $revision) {
                foreach ($revision['faults'] as $fault) {
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

    public function executiveWorkOrders()
    {
        return view('executive-work-orders.index');
    }

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

    $hasPendingIncidents = $workOrder->incidents()->wherePivot('approved', 0)->exists();
    $hasFaults = $workOrder->revisions()->wherePivot('status', 0)->exists();

    $servicesList = Service::all();
    $mechanics = User::role('Mecánico')->get();
    $productsList = Product::all();
    $revisionsList = Revision::all();

    $revisionsWithFaults = $this->getRevisionsWithFaults($id);

    return view('executive-work-orders.show', compact('workOrder', 'servicesList', 'mechanics', 'productsList', 'revisionsList', 'revisionsWithFaults', 'hasPendingIncidents', 'hasFaults'));
}



private function getRevisionsWithFaults($workOrderId)
{
    return Revision::whereHas('workOrders', function ($query) use ($workOrderId) {
        $query->where('work_order_id', $workOrderId);
    })->with(['faults' => function ($query) use ($workOrderId) {
        $query->wherePivot('work_order_id', $workOrderId);
    }])->get();
}


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

    public function facturar(Request $request, WorkOrder $workOrder)
{
    $allServicesCompleted = $workOrder->services()->wherePivot('status', '!=', 'completado')->doesntExist();
    $allProductsDelivered = $workOrder->products()->wherePivot('status', '!=', 'entregado')->doesntExist();
    $hasPendingIncidents = $workOrder->incidents()->wherePivot('approved', 0)->exists();

    if (!$allServicesCompleted || !$allProductsDelivered || $hasPendingIncidents) {
        return response()->json(['status' => 'error', 'message' => 'No se puede facturar porque hay servicios incompletos, productos no entregados o incidencias pendientes.'], 400);
    }

    $workOrder->status = 'Facturado';
    $workOrder->save();

    // Enviar correo electrónico al cliente
    \Mail::to($workOrder->client->email)->send(new \App\Mail\WorkOrderInvoiceMail($workOrder));

    return response()->json(['status' => 'success', 'message' => 'Orden de trabajo facturada correctamente.']);
}


    public function addService(Request $request, WorkOrder $workOrder)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'mechanic_id' => 'required|exists:users,id',
        ]);

        $workOrder->services()->attach($request->service_id, ['mechanic_id' => $request->mechanic_id, 'status' => 'pendiente']);
            // Actualizar estado de la OT
    $this->updateWorkOrderStatus($workOrder);

        Alert::success('Éxito', 'Servicio agregado con éxito');
        return redirect()->route('executive-work-orders.show', $workOrder->id);
    }

    public function addProduct(Request $request, WorkOrder $workOrder)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $workOrder->products()->attach($request->product_id, ['quantity' => $request->quantity, 'status' => 'pendiente']);
            // Actualizar estado de la OT
    $this->updateWorkOrderStatus($workOrder);

        Alert::success('Éxito', 'Producto agregado con éxito');
        return redirect()->route('executive-work-orders.show', $workOrder->id);
    }



public function addRevision(Request $request, $workOrderId)
{
    $request->validate([
        'revision_id' => 'required|exists:revisions,id',
    ]);

    $workOrder = WorkOrder::findOrFail($workOrderId);
    $revisionId = $request->input('revision_id');

    // Obtener los fallos asociados a la revisión
    $faults = Revision::find($revisionId)->faults;

    // Insertar los fallos en la tabla pivot
    foreach ($faults as $fault) {
        $existingEntry = DB::table('revision_work_order')
            ->where('revision_id', $revisionId)
            ->where('work_order_id', $workOrderId)
            ->where('fault_id', $fault->id)
            ->first();

        if (!$existingEntry) {
            $workOrder->revisions()->attach($revisionId, ['fault_id' => $fault->id, 'status' => 1]);
                // Actualizar estado de la OT
    $this->updateWorkOrderStatus($workOrder);
        }
    }

    Alert::success('Éxito', 'Revisión agregada con éxito');
    return redirect()->route('executive-work-orders.show', $workOrder->id);
}


    public function getRevisions()
    {
        return Revision::all();
    }

    public function printWorkOrder($id)
{
    set_time_limit(120);

    Log::info('Inicio de generación de PDF para OT ID: ' . $id);

    $workOrder = WorkOrder::with([
        'client',
        'vehicle.brand',
        'services.mechanics',
        'products',
        'revisions.faults' => function ($query) use ($id) {
            $query->withPivot('status')->wherePivot('work_order_id', $id);
        },
        'incidents.reportedBy'
    ])->findOrFail($id);

    Log::info('Datos cargados para OT ID: ' . $id);

    $revisionsWithFaults = $this->getRevisionsWithFaults($id);

    Log::info('Revisiones con fallos obtenidas para OT ID: ' . $id);

    $pdf = Pdf::loadView('executive-work-orders.print', [
        'workOrder' => $workOrder,
        'revisionsWithFaults' => $revisionsWithFaults
    ]);

    Log::info('PDF generado para OT ID: ' . $id);

    return $pdf->download('OrdenDeTrabajo_' . $workOrder->id . '.pdf');
}





    public function listClients()
    {
        $clients = Client::select('id', 'name as text')->get();
        return response()->json($clients);
    }

    public function listVehicles()
    {
        $vehicles = Vehicle::select('id', 'license_plate as text')->get();
        return response()->json($vehicles);
    }

    public function updateServiceStatus(Request $request, $workOrderId, $serviceId)
{
    $request->validate([
        'status' => 'required|string|in:pendiente,iniciado,completado',
    ]);

    DB::table('service_work_order')
        ->where('work_order_id', $workOrderId)
        ->where('service_id', $serviceId)
        ->update(['status' => $request->status]);

    $workOrder = WorkOrder::findOrFail($workOrderId);
    $this->updateWorkOrderStatus($workOrder);

    return response()->json(['success' => true, 'message' => 'Estado del servicio actualizado con éxito.']);
}

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

            // Actualizar estado de la OT
    $this->updateWorkOrderStatus($workOrder);

        return back()->with('success', 'Incidencia agregada correctamente.');
    }

    public function mechanicWorkOrders()
    {
        return view('mechanic-work-orders.index');
    }

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

        $incidents = Incident::all();

        $revisionsWithFaults = $this->getRevisionsWithFaults($id);

        return view('mechanic-work-orders.show', compact('workOrder', 'incidents', 'revisionsWithFaults'));
    }

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

    $this->updateWorkOrderStatus($workOrder);

    return response()->json(['message' => 'Estado del servicio actualizado correctamente']);
}

    public function warehouseWorkOrders()
    {
        return view('warehouse-work-orders.index');
    }

    public function warehouseWorkOrdersList(Request $request)
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
                    return '<a href="' . route('warehouse-work-orders.show', $row->id) . '" class="edit btn btn-primary btn-sm">Ver</a>';
                })
                ->rawColumns(['service_status', 'product_status', 'status', 'action'])
                ->make(true);
        }

        return view('warehouse-work-orders.index');
    }

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
        'incidents.reportedBy',
        'incidents.approvedBy'
    ])->findOrFail($id);

    $revisionsWithFaults = $this->getRevisionsWithFaults($id);

    return view('warehouse-work-orders.show', compact('workOrder', 'revisionsWithFaults'));
}


    public function updateProductStatus(Request $request, WorkOrder $workOrder, Product $product)
    {
        $request->validate([
            'status' => 'required|string|in:pendiente,entregado',
        ]);

        $workOrder->products()->updateExistingPivot($product->id, ['status' => $request->status]);
            // Actualizar estado de la OT
    $this->updateWorkOrderStatus($workOrder);

        return response()->json(['success' => true]);
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

        $workOrder = WorkOrder::findOrFail($workOrderId);
        $this->updateWorkOrderStatus($workOrder);

        return response()->json(['success' => true, 'message' => 'Estado de la revisión actualizado correctamente.']);
    }

    return response()->json(['error' => true, 'message' => 'No se pudo actualizar el estado de la revisión'], 400);
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

        $incidents = Incident::all();

        return view('mechanic-work-orders.show', compact('workOrder', 'incidents'));
    }
    private function updateWorkOrderStatus(WorkOrder $workOrder)
    {
        $allServicesPending = $workOrder->services()->wherePivot('status', '!=', 'pendiente')->doesntExist();
        $allProductsPending = $workOrder->products()->wherePivot('status', '!=', 'pendiente')->doesntExist();
        $allServicesCompleted = $workOrder->services()->wherePivot('status', '!=', 'completado')->doesntExist();
        $allProductsDelivered = $workOrder->products()->wherePivot('status', '!=', 'entregado')->doesntExist();
        $hasPendingIncidents = $workOrder->incidents()->wherePivot('approved', 0)->exists();
        $hasFaults = $workOrder->revisions()->wherePivot('status', 0)->exists();

        // Contar incidencias aprobadas y rechazadas
        $approvedIncidentsCount = $workOrder->incidents()->wherePivot('approved', 1)->count();
        $rejectedIncidentsCount = $workOrder->incidents()->wherePivot('approved', -1)->count();
        $totalIncidentsCount = $workOrder->incidents()->count();

        if (!$hasPendingIncidents && $allServicesPending && $allProductsPending) {
            $workOrder->status = 'Iniciado';
        } elseif ($hasPendingIncidents) {
            $workOrder->status = 'Incidencias';
        } elseif ($allServicesCompleted && $allProductsDelivered && !$hasPendingIncidents) {
            $workOrder->status = 'Completado';
        } elseif ($approvedIncidentsCount == $totalIncidentsCount && $totalIncidentsCount > 0) {
            $workOrder->status = 'Aprobado';
        } elseif ($approvedIncidentsCount > 0 && $rejectedIncidentsCount > 0) {
            $workOrder->status = 'Parcial';
        } elseif ($rejectedIncidentsCount == $totalIncidentsCount && $totalIncidentsCount > 0) {
            $workOrder->status = 'Rechazado';
        } else {
            $workOrder->status = 'En Proceso';
        }

        $workOrder->save();
    }

public function updateStatus($workOrderId)
{
    $workOrder = WorkOrder::findOrFail($workOrderId);
    $this->updateWorkOrderStatus($workOrder);

    return response()->json(['message' => 'Estado de la OT actualizado correctamente']);
}


}
