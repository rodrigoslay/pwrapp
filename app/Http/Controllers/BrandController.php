<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\CarModel;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Log;


class BrandController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Brand::latest()->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $btn = '<a href="'.route('brands.edit', $row->id).'" class="edit btn btn-primary btn-sm">Editar</a>';
                    $btn .= '<form action="'.route('brands.destroy', $row->id).'" method="POST" style="display:inline-block;">
                                '.csrf_field().'
                                '.method_field("DELETE").'
                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                            </form>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $brands = Brand::latest()->paginate(10);
        return view('brands.index', compact('brands'));
    }

    public function create()
    {
        $brands = Brand::orderBy('name', 'asc')->get();
        return view('brands.create', compact('brands'));
    }

    public function store(Request $request)
{
    $messages = [
        'name.required' => 'El nombre de la marca es obligatorio.',
        'name.max' => 'El nombre de la marca no debe exceder los 255 caracteres.',
        'status.required' => 'El estado de la marca es obligatorio.',
        'status.boolean' => 'El estado de la marca debe ser verdadero o falso.',
    ];

    $validatedData = $request->validate([
        'name' => 'required|string|max:255',
        'status' => 'required|boolean',
    ], $messages);

    try {
        $brand = Brand::create([
            'name' => $validatedData['name'],
            'status' => $validatedData['status'],
            'created_by' => auth()->user()->id,
            'updated_by' => auth()->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Marca creada con éxito',
            'brand' => $brand,
        ]);

    } catch (\Exception $e) {
        Log::error('Error al crear la marca: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error al crear la marca: ' . $e->getMessage(),
        ], 500);
    }
}





    public function edit(Brand $brand)
    {
        return view('brands.edit', compact('brand'));
    }

    public function update(Request $request, Brand $brand)
{
    $messages = [
        'name.required' => 'El nombre de la marca es obligatorio.',
        'name.max' => 'El nombre de la marca no debe exceder los 255 caracteres.',
        'status.required' => 'El estado de la marca es obligatorio.',
        'status.boolean' => 'El estado de la marca debe ser verdadero o falso.',
    ];

    $validatedData = $request->validate([
        'name' => 'required|string|max:255',
        'status' => 'required|boolean',
    ], $messages);

    try {
        $brand->update([
            'name' => $validatedData['name'],
            'status' => $validatedData['status'],
            'updated_by' => auth()->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Marca actualizada con éxito',
        ]);

    } catch (\Exception $e) {
        Log::error('Error al actualizar la marca: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error al actualizar la marca: ' . $e->getMessage(),
        ], 500);
    }
}

    public function destroy(Brand $brand)
    {
        $brand->delete();
        Alert::success('Éxito', 'Marca eliminada con éxito');
        return redirect()->route('brands.index');
    }
    public function list()
{
    $brands = Brand::orderBy('name', 'asc')->get();
    return response()->json(['brands' => $brands]);
}

    public function getModels($brandId)
{
    try {
        $models = CarModel::where('brand_id', $brandId)->get();
        return response()->json(['success' => true, 'models' => $models]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Error al obtener los modelos: ' . $e->getMessage()], 500);
    }
}

}
