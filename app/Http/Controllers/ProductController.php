<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use RealRashid\SweetAlert\Facades\Alert;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Product::latest()->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $btn = '<a href="'.route('products.edit', $row->id).'" class="edit btn btn-primary btn-sm">Editar</a>';
                    $btn .= '<form action="'.route('products.destroy', $row->id).'" method="POST" style="display:inline-block;">
                                '.csrf_field().'
                                '.method_field("DELETE").'
                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                            </form>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $products = Product::latest()->paginate(10);
        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'sku' => 'required|string|max:255|unique:products',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'inventory' => 'required|integer',
            'status' => 'required|boolean',
        ]);

        Product::create([
            'sku' => $request->sku,
            'name' => $request->name,
            'price' => $request->price,
            'inventory' => $request->inventory,
            'status' => $request->status,
            'created_by' => auth()->user()->id,
        ]);

        Alert::success('Éxito', 'Producto creado con éxito');
        return redirect()->route('products.index');
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'sku' => 'required|string|max:255|unique:products,sku,'.$product->id,
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'inventory' => 'required|integer',
            'status' => 'required|boolean',
        ]);

        $product->update([
            'sku' => $request->sku,
            'name' => $request->name,
            'price' => $request->price,
            'inventory' => $request->inventory,
            'status' => $request->status,
            'updated_by' => auth()->user()->id,
        ]);

        Alert::success('Éxito', 'Producto actualizado con éxito');
        return redirect()->route('products.index');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        Alert::success('Éxito', 'Producto eliminado con éxito');
        return redirect()->route('products.index');
    }
}
