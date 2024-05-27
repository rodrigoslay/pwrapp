<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use App\Models\Vehicle;
use App\Models\Client;
use App\Models\User;
use App\Models\Service;
use App\Models\Product;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use RealRashid\SweetAlert\Facades\Alert;

class WorkOrderController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = WorkOrder::latest()->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<a href="' . route('work-orders.edit', $row->id) . '" class="edit btn btn-primary btn-sm">Editar</a>';
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
            'mechanic_id' => 'required|exists:users,id',
            'client_id' => 'required|exists:clients,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'entry_mileage' => 'required|integer',
            'exit_mileage' => 'nullable|integer',
            'status' => 'required|string|in:Abierto,Comenzó,Incidencias Reportadas,Incidencias Aprobadas,Completado,Facturado,Cerrado',
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
            'mechanic_id' => 'required|exists:users,id',
            'client_id' => 'required|exists:clients,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'entry_mileage' => 'required|integer',
            'exit_mileage' => 'nullable|integer',
            'status' => 'required|string|in:Abierto,Comenzó,Incidencias Reportadas,Incidencias Aprobadas,Completado,Facturado,Cerrado',
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

    //FORMSPET
    // Step One: Show form to search vehicle
    public function createStepOne()
    {
        return view('work-orders.create-step-one');
    }

    // Step Two: Search vehicle by license plate
    public function searchVehicle(Request $request)
    {
        $request->validate(['license_plate' => 'required|string']);

        $vehicles = Vehicle::where('license_plate', $request->license_plate)->get();

        $clients = Client::all(); // Obtener todos los clientes
        $latestClient = null;

        if ($vehicles->isNotEmpty()) {
            $latestClient = $vehicles->first()->clients()->latest()->first(); // Obtener el último cliente asociado al vehículo
        }

        return view('work-orders.create-step-two', compact('vehicles', 'clients', 'latestClient'));
    }

    // Step Three: Select vehicle or add new association
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
        $request->validate([
            'vehicle_id' => 'required|integer',
            'services' => 'required|array',
            'products' => 'nullable|array',
        ]);

        session([
            'vehicle_id' => $request->vehicle_id,
            'services' => $request->services,
            'products' => $request->products,
        ]);

        return redirect()->route('work-orders.create-step-four');
    }
    // Paso 4: Mostrar formulario para confirmar servicios y revisar extras
    public function createStepFour()
    {
        $services = session('services');
        $products = session('products');
        $extra_reviews = ['Revisión de frenos', 'Revisión de aceite', 'Revisión de neumáticos']; // Ejemplo de revisiones extras

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
        $mechanics = User::role('Mecánico')->get();

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
        $services = session('services');
        $products = session('products');
        $extra_reviews = session('extra_reviews');
        $mechanics = session('mechanics');

        return view('work-orders.create-step-six', compact('vehicle', 'services', 'products', 'extra_reviews', 'mechanics'));
    }

    // Paso 6: Almacenar la OT completa
    public function storeStepSix(Request $request)
    {
        $workOrder = WorkOrder::create([
            'vehicle_id' => session('vehicle_id'),
            'created_by' => auth()->user()->id,
            'status' => 'Abierto',
            // Agregar otros campos necesarios
        ]);

        foreach (session('services') as $service) {
            // Asignar servicios a la OT
            $workOrder->services()->attach($service, ['mechanic_id' => session('mechanics')[$service]]);
        }

        foreach (session('products') as $product) {
            // Asignar productos a la OT
            $workOrder->products()->attach($product);
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
}
