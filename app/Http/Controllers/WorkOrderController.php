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
use App\Models\RevisionFault;
use App\Events\WorkOrderStatusUpdated;
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
        array_walk_recursive($array, function ($a) use (&$return) {
            $return[] = $a;
        });
        return $return;
    }
}



class WorkOrderController extends Controller
{
    //ejecutivos
    public function createStepOne()
    {
        $vehicles = Vehicle::all();
        $clients = Client::orderBy('created_at', 'desc')->get();
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
            'phone' => 'required|string|regex:/^56[29][0-9]{8}$/',
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
        $vehicle = Vehicle::with(['brand', 'model'])->find($request->vehicle_id);
        $client = Client::find($request->client_id);


        if ($vehicle && $client) {
            $openWorkOrder = WorkOrder::where('vehicle_id', $vehicle->id)->whereIn('status', ['Iniciado', 'En Proceso', 'Incidencias', 'Agendado', 'Cotización'])->first();
            if ($openWorkOrder) {
                return back()->withErrors(['message' => 'Este vehículo tiene una OT abierta.'])->with('alert', [
                    'type' => 'error',
                    'title' => 'Error',
                    'message' => 'El vehículo tiene una OT abierta.',
                ]);
            }

            session(['vehicle' => $vehicle, 'client' => $client]);
            // Establecer el tipo de orden en la sesión
            $orderType = $request->input('order_type');
            session(['order_type' => $orderType]);
            $scheduling = $request->input('scheduling');
            session(['scheduling' => $scheduling]);

            //dd(session('order_type')); // Debugging
            return redirect()->route('work-orders.create-step-two');
        } else {
            return back()->withErrors(['message' => 'Vehículo o Cliente no encontrado'])->with('alert', [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'Vehículo o Cliente no encontrado',
            ]);
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
    $request->validate([
        'services' => 'array',
        'services.*.id' => 'required|integer|exists:services,id',
        'services.*.quantity' => 'required|integer|min:1',
        'services.*.discount' => 'nullable|numeric|min:0|max:100', // Validar el descuento como porcentaje
        'revisions' => 'array',
    ]);

    $selectedServices = [];
    foreach ($request->services as $service) {
        $selectedServices[$service['id']] = [
            'quantity' => $service['quantity'],
            'discount' => $service['discount'] ?? 0,
        ];
    }

    session(['selected_services' => $selectedServices, 'selected_revisions' => $request->revisions]);

    return redirect()->route('work-orders.create-step-three');
}




    public function createStepThree()
{
    $products = Product::all();
    return view('work-orders.create-step-three', compact('products'));
}

    public function storeStepThree(Request $request)
{
    // Validar las entradas del formulario
    $request->validate([
        'products' => 'required|array',
        'products.*.id' => 'required|integer|exists:products,id',
        'products.*.quantity' => 'required|integer|min:1',
        'products.*.discount' => 'nullable|numeric|min:0|max:100' // Validar el descuento como porcentaje
    ]);

    // Almacenar los productos seleccionados en la sesión
    $selectedProducts = [];
    foreach ($request->products as $product) {
        $selectedProducts[$product['id']] = [
            'quantity' => $product['quantity'],
            'discount' => $product['discount'] ?? 0 // Almacenar el descuento, si está presente
        ];
    }
    session(['selected_products' => $selectedProducts]);

    // Redirigir al siguiente paso
    return redirect()->route('work-orders.create-step-four');
}



public function createStepFour()
{
    $services = session('selected_services') ? Service::whereIn('id', session('selected_services'))->get() : collect([]);
    $selectedRevisions = session('selected_revisions') ? collect(session('selected_revisions'))->pluck('id')->toArray() : [];
    $revisions = !empty($selectedRevisions) ? Revision::whereIn('id', $selectedRevisions)->get() : [];

    $leaders = User::role('Líder')->get(); // Obtener solo usuarios con el rol de líder
    $mechanicServiceCounts = $this->getMechanicServiceCounts();

    $selectedProducts = session('selected_products', []);

    // Extraer solo los IDs de los productos seleccionados
    $selectedProductIds = array_keys($selectedProducts);
    $productsQuantities = $selectedProducts;

    $products = Product::whereIn('id', $selectedProductIds)->get();

    // Obtener las asignaciones de mecánicos de la sesión
    $mechanicAssignments = session('mechanic_assignments', []);

    $client = session('client');
    $vehicle = session('vehicle');

    return view('work-orders.create-step-four', compact(
        'services',
        'products',
        'revisions',
        'productsQuantities',
        'leaders',
        'mechanicServiceCounts',
        'mechanicAssignments',
        'client',
        'vehicle'
    ));
}

public function storeStepFour(Request $request)
{
    $selectedServices = session('selected_services', []);
    $selectedProducts = session('selected_products', []);
    $selectedRevisions = session('selected_revisions', []);

    if (empty($selectedServices) && empty($selectedProducts) && empty($selectedRevisions)) {
        return redirect()->route('work-orders.create-step-four')->with('error', 'Debe agregar al menos un servicio, producto o revisión para continuar.');
    }

    // Asegurarnos de que se está guardando correctamente la asignación de mecánicos/líderes
    $mechanicAssignments = $request->input('mechanics', []);
    session(['mechanic_assignments' => $mechanicAssignments]);

    return redirect()->route('work-orders.create-step-five');
}




public function createStepFive()
{
    $services = session('selected_services') ? Service::whereIn('id', session('selected_services'))->get() : collect([]);
    $selectedRevisions = session('selected_revisions') ? collect(session('selected_revisions'))->pluck('id')->toArray() : [];
    $revisions = !empty($selectedRevisions) ? Revision::whereIn('id', $selectedRevisions)->with('faults')->get() : collect([]);
    $mechanicAssignments = session('mechanic_assignments', []);
    $productsQuantities = session('selected_products', []);

    $selectedProducts = session('selected_products', []);
    $products = !empty($selectedProducts) ? Product::whereIn('id', array_keys($selectedProducts))->get() : collect([]);
    $client = session('client');
    $vehicle = session('vehicle');

    // Asegúrate de que los productos tienen el pivote cargado con 'discount'
    foreach ($products as $product) {
        $product->pivot = (object)[
            'discount' => $selectedProducts[$product->id]['discount'] ?? 0,
            'quantity' => $selectedProducts[$product->id]['quantity'] ?? 1,
        ];
    }

    // Asegúrate de que los servicios tienen el pivote cargado con 'quantity' y 'discount'
    foreach ($services as $service) {
        $service->pivot = (object)[
            'quantity' => $selectedServices[$service->id]['quantity'] ?? 1,
            'discount' => $selectedServices[$service->id]['discount'] ?? 0,
        ];
    }

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
            return redirect()->route('work-orders.create-step-five')->withErrors(['message' => 'Debe seleccionar al menos un servicio, producto o revisión.'])->with('alert', [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'Debe seleccionar al menos un servicio, producto o revisión.',
            ]);
        }

        $services = Service::whereIn('id', $serviceIds)->get();
        $products = Product::whereIn('id', $productIds)->get();
        $quantities = array_column($request->products, 'quantity', 'id');
        $discounts = array_column($request->products, 'discount', 'id');

        $subtotal = $services->sum('price') +
            $products->sum(function ($product) use ($quantities, $discounts) {
                $quantity = $quantities[$product->id] ?? 1;
                $discount = $discounts[$product->id] ?? 0;
                return $product->price * $quantity * (1 - $discount / 100);
            });

        $client = Client::find($request->client_id);
        $discountPercentage = optional($client->clientGroup)->discount_percentage ?? 0;
        $discount = $subtotal * ($discountPercentage / 100);
        $tax = ($subtotal - $discount) * 0; // Assuming 19% tax rate
        $total = $subtotal - $discount + $tax;

        $workOrder = new WorkOrder();
        $workOrder->client_id = $request->client_id;
        $workOrder->vehicle_id = $request->vehicle_id;
        $workOrder->entry_mileage = $request->entry_mileage;

        // Determinar el estado final basado en la selección del ejecutivo
        if (session('order_type') === 'cotizacion') {
            $workOrder->status = 'Cotización';
        } elseif (session('order_type') === 'agendar') {
            $workOrder->status = 'Agendado';
            $workOrder->scheduling = session('scheduling');
        } else {
            $workOrder->status = 'Iniciado';
        }

        $workOrder->subtotal = $subtotal;
        $workOrder->tax = $tax;
        $workOrder->total = $total;
        $workOrder->executive_id = auth()->user()->id;
        $workOrder->created_by = auth()->user()->id;
        $workOrder->save();

        if ($request->has('services')) {
            foreach ($request->services as $serviceData) {
                $workOrder->services()->attach($serviceData['id'], [
                    'mechanic_id' => $serviceData['mechanic_id'],
                    'quantity' => $serviceData['quantity'],
                    'discount' => $serviceData['discount'] ?? 0,
                    'status' => 'pendiente'
                ]);
            }
        }
        
        if ($request->has('products')) {
            foreach ($request->products as $productData) {
                $quantity = $quantities[$productData['id']] ?? 1;
                $discount = $discounts[$productData['id']] ?? 0;
                $workOrder->products()->attach($productData['id'], [
                    'quantity' => $quantity,
                    'discount' => $discount,
                    'status' => 'pendiente'
                ]);
            }
        }

        if ($request->has('revisions')) {
            foreach ($request->revisions as $revisionData) {
                $revisionId = $revisionData['id'] ?? null;
                if ($revisionId) {
                    $revision = Revision::with('faults')->find($revisionId);
                    foreach ($revision->faults as $fault) {
                        $workOrder->revisions()->attach($revisionId, [
                            'fault_id' => $fault->id,
                            'status' => '1'
                        ]);
                    }
                }
            }
        }

        DB::commit();
        // Verificar el estado de la OT y redirigir en consecuencia
        if ($workOrder->status == 'Cotización') {
            return redirect()->route('work-orders.quotations')->with('alert', [
                'type' => 'success',
                'title' => 'Éxito',
                'message' => 'Cotización creada exitosamente',
            ]);
        } elseif ($workOrder->status == 'Agendado') {
            return redirect()->route('work-orders.scheduled')->with('alert', [
                'type' => 'success',
                'title' => 'Éxito',
                'message' => 'OT agendada exitosamente',
            ]);
        } else {
            return redirect()->route('executive-work-orders.index')->with('alert', [
                'type' => 'success',
                'title' => 'Éxito',
                'message' => 'OT creada exitosamente',
            ]);
        }
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->route('work-orders.create-step-five')->withErrors(['message' => $e->getMessage()])->with('alert', [
            'type' => 'error',
            'title' => 'Error',
            'message' => 'No se pudo crear la OT',
        ]);
    }
}

    private function getMechanicServiceCounts()
    {
        return User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['Mecánico', 'Lider']);
        })
        ->withCount([
            'workOrders as not_completed_count' => function ($query) {
                $query->where('service_work_order.status', '!=', 'completado');
            },
            'workOrders as completed_count' => function ($query) {
                $query->where('service_work_order.status', 'completado');
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
            $data = WorkOrder::where('executive_id', auth()->user()->id)
                ->whereNotIn('status', ['Agendado', 'Cotización'])
                ->with(['client', 'vehicle', 'services', 'products'])
                ->get();
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
                    } elseif (in_array('pendiente', $statuses)) {
                        return 'Pendiente';
                    } else {
                        return 'Sin servicios';
                    }
                })
                ->addColumn('product_status', function ($row) {
                    $statuses = $row->products->pluck('pivot.status')->unique()->toArray();
                    if (in_array('entregado', $statuses) && count($statuses) == 1) {
                        return 'Entregado';
                    } elseif (in_array('parcialmente_entregado', $statuses)) {
                        return 'Parcialmente Entregado';
                    } elseif (in_array('pendiente', $statuses)) {
                        return 'Pendiente';
                    } else {
                        return 'Sin Productos';
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

        return view('executive-work-orders.show', compact(
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

    public function executiveQuotations()
    {
        Log::info('Accediendo a la vista de cotizaciones');
        return view('work-orders.quotations');
    }

    public function executiveQuotationsList(Request $request)
    {
        if ($request->ajax()) {
            $data = WorkOrder::where('executive_id', auth()->user()->id)
                ->where('status', 'Cotización')
                ->with(['client', 'vehicle', 'services', 'products'])
                ->get();

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

        return view('work-orders.quotations');
    }


    public function executiveScheduled()
    {
        $workOrders = WorkOrder::where('status', 'Agendado')->with(['client', 'vehicle'])->get();

        // Formatear los datos para el calendario
        $events = $workOrders->map(function ($workOrder) {
            return [
                'title' => 'OT: ' . $workOrder->id . ' - ' . $workOrder->client->name,
                'start' => $workOrder->scheduling,
                'url' => route('executive-work-orders.show', $workOrder->id),
            ];
        });

        return view('work-orders.scheduled', compact('events'));
    }

    public function noRealizado(Request $request, WorkOrder $workOrder)
    {
        try {
            Log::info("Iniciando el proceso para marcar la OT ID: {$workOrder->id} como No Realizado");

            // Verificar si se puede cambiar el estado a No Realizado
            if ($workOrder->status === 'Facturado') {
                Log::warning("No se puede marcar la OT ID: {$workOrder->id} como No Realizado porque ya está facturada.");
                return response()->json(['status' => 'error', 'message' => 'No se puede marcar como No Realizado porque ya está facturada.'], 400);
            }

            $workOrder->status = 'No Realizado';
            $workOrder->save();

            Log::info("Estado de la OT ID: {$workOrder->id} cambiado a No Realizado.");

            // Actualizar estado de la OT
            $this->updateWorkOrderStatus($workOrder);

            event(new WorkOrderStatusUpdated($workOrder));

            return response()->json(['status' => 'success', 'message' => 'Orden de trabajo marcada como No Realizado.']);
        } catch (\Exception $e) {
            Log::error("Error al marcar la OT ID: {$workOrder->id} como No Realizado: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Error al marcar la orden de trabajo como No Realizado.'], 500);
        }
    }

    public function removeService(Request $request, WorkOrder $workOrder, Service $service)
    {
        try {
            $workOrder->services()->detach($service->id);
            return redirect()->route('executive-work-orders.show', $workOrder->id)->with('success', 'Servicio eliminado correctamente.');
        } catch (\Exception $e) {
            return redirect()->route('executive-work-orders.show', $workOrder->id)->with('error', 'Error al eliminar el servicio.');
        }
    }

    public function removeProduct(Request $request, WorkOrder $workOrder, Product $product)
    {
        try {
            $workOrder->products()->detach($product->id);
            return redirect()->route('executive-work-orders.show', $workOrder->id)->with('success', 'Producto eliminado correctamente.');
        } catch (\Exception $e) {
            return redirect()->route('executive-work-orders.show', $workOrder->id)->with('error', 'Error al eliminar el producto.');
        }
    }

    public function removeFault(Request $request, WorkOrder $workOrder, Revision $revision, Fault $fault)
    {
        try {
            $workOrder->revisions()->updateExistingPivot($revision->id, [
                'fault_id' => null,
                'status' => 'pendiente'
            ]);
            return redirect()->route('executive-work-orders.show', $workOrder->id)->with('success', 'Fallo eliminado correctamente.');
        } catch (\Exception $e) {
            return redirect()->route('executive-work-orders.show', $workOrder->id)->with('error', 'Error al eliminar el fallo.');
        }
    }

    public function start($workOrderId, Request $request)
    {
        try {
            $workOrder = WorkOrder::findOrFail($workOrderId);

            if ($workOrder->status === 'Cotización' || $workOrder->status === 'Agendado') {
                $workOrder->status = 'Iniciado';
                $workOrder->save();

                return response()->json(['status' => 'success', 'message' => 'El estado de la OT ha sido cambiado a "Iniciado".']);
            } else {
                return response()->json(['status' => 'error', 'message' => 'No se puede cambiar el estado de la OT.']);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error al cambiar el estado de la OT.']);
        }
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




    public function updateIncidentStatus(Request $request, $workOrderId, $incidentId)
    {
        $workOrder = WorkOrder::findOrFail($workOrderId);
        $incident = $workOrder->incidents()->where('incident_id', $incidentId)->first();
        if ($incident) {
            $incident->pivot->approved = $request->status;
            $incident->pivot->approved_by = auth()->user()->id;
            $incident->pivot->save();
            return response()->json(['status' => 'success', 'message' => 'Estado de incidencia actualizado.']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Incidencia no encontrada.']);
        }
    }

    public function facturar(Request $request, WorkOrder $workOrder)
    {
        try {
            Log::info("Iniciando el proceso de facturación para la OT ID: {$workOrder->id}");

            $allServicesCompleted = $workOrder->services()->wherePivot('status', '!=', 'completado')->doesntExist();
            $allProductsDelivered = $workOrder->products()->wherePivot('status', '!=', 'entregado')->doesntExist();
            $hasPendingIncidents = $workOrder->incidents()->wherePivot('approved', 0)->exists();

            Log::info("Estados antes de facturar: Servicios Completos: {$allServicesCompleted}, Productos Entregados: {$allProductsDelivered}, Incidencias Pendientes: {$hasPendingIncidents}");

            if (!$allServicesCompleted || !$allProductsDelivered || $hasPendingIncidents) {
                Log::warning("No se puede facturar la OT ID: {$workOrder->id} porque hay servicios incompletos, productos no entregados o incidencias pendientes.");
                return response()->json(['status' => 'error', 'message' => 'No se puede facturar porque hay servicios incompletos, productos no entregados o incidencias pendientes.'], 400);
            }

            $workOrder->status = 'Facturado';
            $workOrder->save();

            Log::info("Estado de la OT ID: {$workOrder->id} cambiado a Facturado.");

            // \Mail::to($workOrder->client->email)->send(new \App\Mail\WorkOrderInvoiceMail($workOrder));

            $this->updateWorkOrderStatus($workOrder);

            event(new WorkOrderStatusUpdated($workOrder));

            return response()->json(['status' => 'success', 'message' => 'Orden de trabajo facturada correctamente.']);
        } catch (\Exception $e) {
            Log::error("Error al facturar la OT ID: {$workOrder->id}: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Error al facturar la orden de trabajo.'], 500);
        }
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

        // Emitir el evento para actualizar el estado en tiempo real
        event(new WorkOrderStatusUpdated($workOrder));

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

        // Emitir el evento para actualizar el estado en tiempo real
        event(new WorkOrderStatusUpdated($workOrder));

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

        // Agregar registro para depuración
        Log::info('Agregando revision', ['workOrderId' => $workOrderId, 'revisionId' => $revisionId]);

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
                Log::info('Agregando fallo', ['revisionId' => $revisionId, 'faultId' => $fault->id]);

                $workOrder->revisions()->attach($revisionId, ['fault_id' => $fault->id, 'status' => 1]);
            } else {
                Log::info('La falla ya existe', ['revisionId' => $revisionId, 'faultId' => $fault->id]);
            }
        }

        // Actualizar estado de la OT
        $this->updateWorkOrderStatus($workOrder);

        // Emitir el evento para actualizar el estado en tiempo real
        event(new WorkOrderStatusUpdated($workOrder));

        Log::info('Revisión agregada con éxito');
        Alert::success('Éxito', 'Revisión agregada con éxito');
        return redirect()->route('executive-work-orders.show', $workOrder->id);
    }

    public function getRevisions()
    {
        return Revision::all();
    }

    public function showPrintView($id)
    {
        $workOrder = WorkOrder::with([
            'client',
            'vehicle',
            'services',
            'products',
            'incidents.reportedBy'
        ])->findOrFail($id);


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


        return view('executive-work-orders.print', [
            'workOrder' => $workOrder,
            'revisionsWithFaults' => $revisionsWithFaults,
            'userNames' => $userNames,
            'servicesList' => $servicesList,
            'productsList' => $productsList,
            'revisionsList' => $revisionsList,
            'hasFaults' => $hasFaults,
            'hasPendingIncidents' => $hasPendingIncidents
        ]);
    }

    public function downloadWorkOrderPDF($id)
    {
        set_time_limit(120);

        $workOrder = WorkOrder::with([
            'client',
            'vehicle',
            'services',
            'products',
            'incidents' => function ($query) {
                $query->with('reportedBy', 'approvedBy');
            }
        ])->findOrFail($id);

        $revisionsWithFaults = $this->getRevisionsWithFaults($id);

        $pdf = Pdf::loadView('executive-work-orders.pdf', [
            'workOrder' => $workOrder,
            'revisionsWithFaults' => $revisionsWithFaults
        ]);

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
        $workOrder = WorkOrder::findOrFail($workOrderId);

        // Validar los datos recibidos
        $request->validate([
            'status' => 'required|string|in:pendiente,iniciado,completado',
        ]);

        // Actualizar el estado del servicio
        $workOrder->services()->updateExistingPivot($serviceId, ['status' => $request->status]);

        // Actualizar estado de la OT
        $this->updateWorkOrderStatus($workOrder);

        // Emitir el evento para actualizar el estado en tiempo real
        event(new WorkOrderStatusUpdated($workOrder));

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

        // Emitir el evento para actualizar el estado en tiempo real
        event(new WorkOrderStatusUpdated($workOrder));

        return back()->with('success', 'Incidencia agregada correctamente.');
    }

    public function mechanicWorkOrders()
    {
        return view('mechanic-work-orders.index');
    }

    //lideres
    public function leaderWorkOrders()
    {
        return view('leader-work-orders.index');
    }

    public function leaderWorkOrdersList(Request $request)
    {
        if ($request->ajax()) {
            $data = WorkOrder::whereNotIn('status', ['Agendado', 'Cotización'])
                ->with(['client', 'vehicle', 'services', 'products'])
                ->get();

            return DataTables::of($data)
                ->addIndexColumn()
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
                    } elseif (in_array('pendiente', $statuses)) {
                        return 'Pendiente';
                    } else {
                        return 'Sin servicios';
                    }
                })
                ->addColumn('product_status', function ($row) {
                    $statuses = $row->products->pluck('pivot.status')->unique()->toArray();
                    if (in_array('entregado', $statuses) && count($statuses) == 1) {
                        return 'Entregado';
                    } elseif (in_array('parcialmente_entregado', $statuses)) {
                        return 'Parcialmente Entregado';
                    } elseif (in_array('pendiente', $statuses)) {
                        return 'Pendiente';
                    } else {
                        return 'Sin Productos';
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
                    return '<a href="' . route('leader-work-orders.show', $row->id) . '" class="edit btn btn-primary btn-sm">Ver</a>';
                })
                ->rawColumns(['service_status', 'product_status', 'status', 'action'])
                ->make(true);
        }

        return view('leader-work-orders.index');
    }


    public function leaderShowWorkOrder($id)
    {
        $workOrder = WorkOrder::with([
            'client',
            'vehicle',
            'services',
            'products',
            'incidents.reportedBy'
        ])->findOrFail($id);

        $mechanicsAndLeaders = User::role(['Mecánico', 'Líder'])->get();
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

        // Obtener todas las incidencias disponibles para agregar
        $incidents = Incident::all();

        return view('leader-work-orders.show', compact(
            'workOrder',
            'mechanicsAndLeaders',
            'revisionsWithFaults',
            'userNames',
            'servicesList',
            'productsList',
            'revisionsList',
            'hasFaults',
            'hasPendingIncidents',
            'incidents'
        ));
    }

    public function updateServiceStatusByLeader($workOrderId, $serviceId, Request $request)
    {
        $workOrder = WorkOrder::findOrFail($workOrderId);
        $service = $workOrder->services()->where('service_id', $serviceId)->firstOrFail();

        if ($workOrder->status !== 'Facturado') {
            $service->pivot->status = $request->input('status');
            $service->pivot->save();
            return response()->json(['status' => 'success', 'message' => 'Estado del servicio actualizado.']);
            // Actualizar estado de la OT
            $this->updateWorkOrderStatus($workOrder);

            event(new WorkOrderStatusUpdated($workOrder));
        }

        return response()->json(['status' => 'error', 'message' => 'No se puede actualizar el estado porque la OT está facturada.'], 403);
    }

    public function updateFaultStatusByLeader(Request $request, $workOrderId, $revisionId, $faultId)
    {
        $request->validate([
            'status' => 'required|boolean',
        ]);

        $workOrder = WorkOrder::findOrFail($workOrderId);

        if ($workOrder->status !== 'Facturado') {
            DB::table('revision_work_order')
                ->where('work_order_id', $workOrderId)
                ->where('revision_id', $revisionId)
                ->where('fault_id', $faultId)
                ->update(['status' => $request->input('status')]);

            // Actualizar estado de la OT
            $this->updateWorkOrderStatus($workOrder);

            // Emitir el evento para actualizar el estado en tiempo real
            event(new WorkOrderStatusUpdated($workOrder));

            return response()->json(['status' => 'success', 'message' => 'Estado del fallo actualizado correctamente.']);
        }

        return response()->json(['status' => 'error', 'message' => 'No se puede actualizar el estado porque la OT está facturada.'], 403);
    }




    public function addIncidentByLeader($workOrderId, Request $request)
    {
        $workOrder = WorkOrder::findOrFail($workOrderId);

        if ($workOrder->status !== 'Facturado') {
            $workOrder->incidents()->attach($request->input('incident_id'), [
                'observation' => $request->input('observation'),
                'reported_by' => auth()->id()
            ]);
            // Actualizar estado de la OT
            $this->updateWorkOrderStatus($workOrder);

            // Emitir el evento para actualizar el estado en tiempo real
            event(new WorkOrderStatusUpdated($workOrder));

            return back()->with('success', 'Incidencia agregada correctamente.');
        }

        return response()->json(['status' => 'error', 'message' => 'No se puede agregar incidencias porque la OT está facturada.'], 403);
    }


    public function printWorkOrder($id)
    {
        $workOrder = WorkOrder::with([
            'client',
            'vehicle',
            'services',
            'products',
            'incidents.reportedBy'
        ])->findOrFail($id);

        $mechanics = User::role('Mecánico')->get();
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

        $pdf = PDF::loadView('leader-work-orders.print', compact(
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

        return $pdf->stream('OT_' . $workOrder->id . '.pdf');
    }

    public function changeMechanic(Request $request, $workOrderId, $serviceId)
    {
        $request->validate([
            'mechanic_id' => 'required|exists:users,id',
        ]);

        $workOrder = WorkOrder::findOrFail($workOrderId);
        $workOrder->services()->updateExistingPivot($serviceId, ['mechanic_id' => $request->mechanic_id]);

        // Actualizar estado de la OT
        $this->updateWorkOrderStatus($workOrder);

        // Emitir el evento para actualizar el estado en tiempo real
        event(new WorkOrderStatusUpdated($workOrder));


        return response()->json(['status' => 'success', 'message' => 'Mecánico reasignado con éxito.']);
    }


    public function assignMechanic(Request $request, $workOrderId, $serviceId)
    {
        $request->validate([
            'mechanic_id' => 'required|exists:users,id',
        ]);

        $workOrder = WorkOrder::findOrFail($workOrderId);
        $service = $workOrder->services()->where('service_id', $serviceId)->firstOrFail();

        $service->pivot->mechanic_id = $request->mechanic_id;
        $service->pivot->save();

        // Actualizar estado de la OT
        $this->updateWorkOrderStatus($workOrder);

        // Emitir el evento para actualizar el estado en tiempo real
        event(new WorkOrderStatusUpdated($workOrder));

        return back()->with('success', 'Mecánico asignado correctamente');
    }

    public function removeRevision($workOrderId, $revisionId)
    {
        $workOrder = WorkOrder::findOrFail($workOrderId);
        $workOrder->revisions()->detach($revisionId);

        return redirect()->route('work-orders.show', $workOrderId)
            ->with('success', 'Revisión eliminada correctamente junto con sus fallos.');
    }



    //mecanicos
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
                        return 'Completado';
                    } elseif (in_array('iniciado', $statuses)) {
                        return 'Iniciado';
                    } elseif (in_array('pendiente', $statuses)) {
                        return 'Pendiente';
                    } else {
                        return 'Sin Productos';
                    }
                })
                ->addColumn('product_status', function ($row) {
                    $statuses = $row->products->pluck('pivot.status')->unique()->toArray();
                    if (in_array('entregado', $statuses) && count($statuses) == 1) {
                        return 'Entregado';
                    } elseif (in_array('pendiente', $statuses)) {
                        return 'Pendiente';
                    } else {
                        return 'Sin Productos';
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

        // Actualizar estado de la OT
        $this->updateWorkOrderStatus($workOrder);

        // Emitir el evento para actualizar el estado en tiempo real
        event(new WorkOrderStatusUpdated($workOrder));

        return response()->json(['message' => 'Estado del servicio actualizado correctamente']);
    }

    public function warehouseWorkOrders()
    {
        return view('warehouse-work-orders.index');
    }

    public function warehouseWorkOrdersList(Request $request)
    {
        if ($request->ajax()) {
            $data = WorkOrder::whereNotIn('status', ['Agendado', 'Cotización'])
                ->with(['client', 'vehicle', 'services', 'products'])
                ->get();

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
                    } elseif (in_array('pendiente', $statuses)) {
                        return 'Pendiente';
                    } else {
                        return 'Sin Productos';
                    }
                })
                ->addColumn('product_status', function ($row) {
                    $statuses = $row->products->pluck('pivot.status')->unique()->toArray();
                    if (in_array('entregado', $statuses) && count($statuses) == 1) {
                        return 'Entregado';
                    } elseif (in_array('pendiente', $statuses)) {
                        return 'Pendiente';
                    } else {
                        return 'Sin Productos';
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
            'incidents.reportedBy'
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

        // Emitir el evento para actualizar el estado en tiempo real
        event(new WorkOrderStatusUpdated($workOrder));

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
            // Actualizar estado de la OT
            $this->updateWorkOrderStatus($workOrder);

            // Emitir el evento para actualizar el estado en tiempo real
            event(new WorkOrderStatusUpdated($workOrder));

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
        // Revisar los estados de los servicios, productos e incidencias
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

        // Si la OT ya está facturada o está en estado 'No Realizado', no cambiar el estado
        if (in_array($workOrder->status, ['Facturado', 'No Realizado'])) {
            Log::info("La OT ID: {$workOrder->id} ya está en estado '{$workOrder->status}'.");
            return;
        }

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

        // Emitir el evento para actualizar el estado en tiempo real
        event(new WorkOrderStatusUpdated($workOrder));

        Log::info("Estado de la OT ID: {$workOrder->id} actualizado a {$workOrder->status}.");
    }




    public function updateStatus($workOrderId)
    {
        $workOrder = WorkOrder::findOrFail($workOrderId);
        // Actualizar estado de la OT
        $this->updateWorkOrderStatus($workOrder);

        // Emitir el evento para actualizar el estado en tiempo real
        event(new WorkOrderStatusUpdated($workOrder));

        return response()->json(['message' => 'Estado de la OT actualizado correctamente']);
    }



    public function publicWorkOrderStatus()
    {

        return view('public.work-order-status');
    }

    public function getWorkOrders()
    {
        $workOrders = WorkOrder::whereNotIn('status', ['Agendado', 'Cotización','Facturado','No Realizado'])
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->with(['vehicle'])
            ->get();

        $data = $workOrders->map(function ($workOrder) {
            return [
                'license_plate' => $workOrder->vehicle->license_plate,
                'status' => $workOrder->status,
                'time_elapsed' => $workOrder->created_at->diffForHumans(),
                'message' => $this->getStatusMessage($workOrder->status)
            ];
        });

        return response()->json($data);
    }

    private function getStatusMessage($status)
    {
        switch ($status) {
            case 'Iniciado':
                return 'Se ha informado a los mecánicos que su vehículo está listo para entrar al taller.';
            case 'En Proceso':
                return 'Nuestros mecánicos están trabajando en su vehículo.';
            case 'Incidencias':
                return 'Acérquese a su ejecutivo, su vehículo tiene incidencias.';
            case 'Completado':
                return 'Su vehículo está listo, acérquese a su ejecutivo.';
            case 'Aprobado':
                return 'Se indicó al mecánico que las incidencias están aprobadas.';
            case 'Parcial':
                return 'Se indicó al mecánico que algunas incidencias fueron aprobadas.';
            case 'Rechazado':
                return 'Se indicó al mecánico que las incidencias están rechazadas.';
            default:
                return '';
        }
    }
}
