<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Service::latest()->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $btn = '<a href="'.route('services.edit', $row->id).'" class="edit btn btn-primary btn-sm">Editar</a>';
                    $btn .= '<form action="'.route('services.destroy', $row->id).'" method="POST" style="display:inline-block;">
                                '.csrf_field().'
                                '.method_field("DELETE").'
                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                            </form>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('services.index');
    }

    public function create()
    {
        return view('services.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'price_half' => 'required|numeric',
            'price_high' => 'required|numeric',
            'discount_applicable' => 'required|boolean',
            'status' => 'required|boolean',
        ]);

        try {
            Service::create($request->all() + ['created_by' => Auth::id()]);

            return response()->json(['success' => true, 'message' => 'Servicio creado con éxito']);
        } catch (\Exception $e) {
            Log::error('Error al crear el servicio: ' . $e->getMessage());

            return response()->json(['success' => false, 'message' => 'Error al crear el servicio'], 500);
        }
    }

    public function edit(Service $service)
    {
        return view('services.edit', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        $request->validate([
            'sku' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'price_half' => 'required|numeric',
            'price_high' => 'required|numeric',
            'discount_applicable' => 'required|boolean',
            'status' => 'required|boolean',
        ]);

        try {
            $service->update([
                'sku' => $request->sku,
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'discount_applicable' => $request->discount_applicable,
                'status' => $request->status,
                'updated_by' => auth()->user()->id,
            ]);

            Alert::success('Éxito', 'Servicio actualizado con éxito');
            return redirect()->route('services.index');
        } catch (\Exception $e) {
            Log::error('Error al actualizar el servicio: ' . $e->getMessage());

            return response()->json(['success' => false, 'message' => 'Error al actualizar el servicio'], 500);
        }
    }

    public function destroy(Service $service)
    {
        try {
            $service->delete();
            Alert::success('Éxito', 'Servicio eliminado con éxito');
            return redirect()->route('services.index');
        } catch (\Exception $e) {
            Log::error('Error al eliminar el servicio: ' . $e->getMessage());
            Alert::error('Error', 'No se pudo eliminar el servicio');
            return redirect()->route('services.index');
        }
    }
}
