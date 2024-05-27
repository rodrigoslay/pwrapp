<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use RealRashid\SweetAlert\Facades\Alert;

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
        return view('brands.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|boolean',
        ]);

        Brand::create([
            'name' => $request->name,
            'status' => $request->status,
            'created_by' => auth()->user()->id,
        ]);

        Alert::success('Éxito', 'Marca creada con éxito');
        return redirect()->route('brands.index');
    }

    public function edit(Brand $brand)
    {
        return view('brands.edit', compact('brand'));
    }

    public function update(Request $request, Brand $brand)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|boolean',
        ]);

        $brand->update([
            'name' => $request->name,
            'status' => $request->status,
            'updated_by' => auth()->user()->id,
        ]);

        Alert::success('Éxito', 'Marca actualizada con éxito');
        return redirect()->route('brands.index');
    }

    public function destroy(Brand $brand)
    {
        $brand->delete();
        Alert::success('Éxito', 'Marca eliminada con éxito');
        return redirect()->route('brands.index');
    }
}
