<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Client;
use App\Models\Brand;
use App\Models\CarModel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use RealRashid\SweetAlert\Facades\Alert;


class VehicleController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Vehicle::latest()->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $btn = '<a href="'.route('vehicles.edit', $row->id).'" class="edit btn btn-primary btn-sm">Editar</a>';
                    $btn .= '<form action="'.route('vehicles.destroy', $row->id).'" method="POST" style="display:inline-block;">
                                '.csrf_field().'
                                '.method_field("DELETE").'
                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                            </form>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $vehicles = Vehicle::latest()->paginate(10);
        return view('vehicles.index', compact('vehicles'));
    }

    public function create()
    {
        $clients = Client::all();
        $brands = Brand::all();
        return view('vehicles.create', compact('clients', 'brands'));
    }

    public function storeVehicle(Request $request)
{
    $request->validate([
        'license_plate' => 'required|string|max:255|unique:vehicles',
        'brand_id' => 'required|exists:brands,id',
        'model' => 'required|string|max:255',
        'color' => 'required|string|max:255',
        'chassis' => 'required|string|max:255',
        'kilometers' => 'required|integer',
        'client_id_vehicle' => 'required|exists:clients,id',
        'photo' => 'nullable|image|max:2048|dimensions:max_width=1000,max_height=1000',
    ]);

    if ($request->hasFile('photo')) {
        $photo = $request->file('photo');
        $photoName = 'pwr_' . now()->format('YmdHis') . '_' . $request->client_id_vehicle . '.' . $photo->getClientOriginalExtension();
        $photo->move(public_path('img/vehicles'), $photoName);
    } else {
        $photoName = null;
    }

    Vehicle::create([
        'license_plate' => $request->license_plate,
        'brand_id' => $request->brand_id,
        'model' => $request->model,
        'color' => $request->color,
        'chassis' => $request->chassis,
        'kilometers' => $request->kilometers,
        'client_id' => $request->client_id_vehicle,
        'photo' => $photoName,
        'created_by' => auth()->user()->id,
        'updated_by' => auth()->user()->id,
    ]);

    return redirect()->route('work-orders.create-step-one')->with('success', 'Vehículo agregado con éxito');
}

    public function edit(Vehicle $vehicle)
    {
        $clients = Client::all();
        $brands = Brand::all();
        return view('vehicles.edit', compact('vehicle', 'clients', 'brands'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $request->validate([
            'license_plate' => 'required|string|max:6|unique:vehicles,license_plate,'.$vehicle->id,
            'client_id' => 'required|exists:clients,id',
            'registration_date' => 'required|date',
            'brand_id' => 'required|exists:brands,id',
            'model' => 'required|string',
            'chassis' => 'required|string',
            'color' => 'required|string',
            'kilometers' => 'required|integer',
            'status' => 'required|boolean',
        ]);

        $vehicle->update([
            'license_plate' => $request->license_plate,
            'client_id' => $request->client_id,
            'registration_date' => $request->registration_date,
            'photo' => $request->photo,
            'brand_id' => $request->brand_id,
            'model' => $request->model,
            'chassis' => $request->chassis,
            'color' => $request->color,
            'kilometers' => $request->kilometers,
            'updated_by' => auth()->user()->id,
            'status' => $request->status,
        ]);

        Alert::success('Éxito', 'Vehículo actualizado con éxito');
        return redirect()->route('vehicles.index');
    }

    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();
        Alert::success('Éxito', 'Vehículo eliminado con éxito');
        return redirect()->route('vehicles.index');
    }
    public function store(Request $request)
{
    // Validar los datos de entrada
    $validatedData = Validator::make($request->all(), [
        'license_plate' => 'required|string|max:255|unique:vehicles',
        'brand_id' => 'required|exists:brands,id',
        'model' => 'required|exists:car_models,id',
        'color' => 'nullable|string|max:255',
        'chassis' => 'nullable|string|max:255',
        'kilometers' => 'nullable|integer',
        'registration_date' => 'nullable|date',
        'photo' => 'nullable|image|max:2048',
        'client_id_vehicle' => 'required|exists:clients,id',
    ]);

    // Si la validación falla, devolver una respuesta JSON con los errores
    if ($validatedData->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validatedData->errors()
        ], 422);
    }

    // Establecer valores predeterminados si no se proporcionan
    $color = $request->input('color', '0');
    $chassis = $request->input('chassis', '0');
    $kilometers = $request->input('kilometers', '0');
    $registrationDate = $request->input('registration_date', '0000-00-00');
    $photoName = 'nofoto.png';

    // Manejar la carga de la foto si se proporciona
    if ($request->hasFile('photo')) {
        $photo = $request->file('photo');
        $photoName = 'pwr_' . now()->format('YmdHis') . '_' . $request->client_id_vehicle . '.' . $photo->getClientOriginalExtension();
        $photo->move(public_path('img/vehicles'), $photoName);
    }

    try {
        // Crear el vehículo con los datos proporcionados o los valores predeterminados
        Vehicle::create([
            'license_plate' => $request->license_plate,
            'brand_id' => $request->brand_id,
            'model' => $request->model,
            'color' => $color,
            'chassis' => $chassis,
            'kilometers' => $kilometers,
            'registration_date' => $registrationDate,
            'photo' => $photoName,
            'client_id' => $request->client_id_vehicle,
            'created_by' => auth()->user()->id,
            'updated_by' => auth()->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Vehículo agregado con éxito'
        ]);
    } catch (\Exception $e) {
        // Manejar cualquier excepción y devolver una respuesta JSON con el mensaje de error
        return response()->json([
            'success' => false,
            'message' => 'Error al agregar el vehículo: ' . $e->getMessage()
        ], 500);
    }
}

    public function list()
    {
        $vehicles = Vehicle::select('id', 'license_plate as text')->get();
        return response()->json(['vehicles' => $vehicles]);
    }
    public function show($id)
{
    $vehicle = Vehicle::with('brand')->findOrFail($id);
    return response()->json($vehicle);
}

public function checkLicensePlate(Request $request)
    {
        $exists = Vehicle::where('license_plate', $request->license_plate)->exists();
        return response()->json(['exists' => $exists]);
    }

}
