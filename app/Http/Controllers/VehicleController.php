<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Client;
use App\Models\Brand;
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

    public function store(Request $request)
    {
        $request->validate([
            'license_plate' => 'required|string|max:6|unique:vehicles',
            'client_id' => 'required|exists:clients,id',
            'registration_date' => 'required|date',
            'brand_id' => 'required|exists:brands,id',
            'model' => 'required|string',
            'chassis' => 'required|string',
            'color' => 'required|string',
            'kilometers' => 'required|integer',
            'status' => 'required|boolean',
        ]);

        Vehicle::create([
            'license_plate' => $request->license_plate,
            'client_id' => $request->client_id,
            'registration_date' => $request->registration_date,
            'photo' => $request->photo,
            'brand_id' => $request->brand_id,
            'model' => $request->model,
            'chassis' => $request->chassis,
            'color' => $request->color,
            'kilometers' => $request->kilometers,
            'created_by' => auth()->user()->id,
            'status' => $request->status,
        ]);

        Alert::success('Éxito', 'Vehículo creado con éxito');
        return redirect()->route('vehicles.index');
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
}
