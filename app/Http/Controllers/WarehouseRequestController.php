<?php

namespace App\Http\Controllers;

use App\Models\WarehouseRequest;
use App\Models\Product;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use RealRashid\SweetAlert\Facades\Alert;

class WarehouseRequestController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = WarehouseRequest::with(['product', 'user', 'workOrder'])->latest()->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $btn = '<a href="'.route('warehouse-requests.edit', $row->id).'" class="edit btn btn-primary btn-sm">Editar</a>';
                    $btn .= '<form action="'.route('warehouse-requests.destroy', $row->id).'" method="POST" style="display:inline-block;">
                                '.csrf_field().'
                                '.method_field("DELETE").'
                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                            </form>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('warehouse-requests.index');
    }

    public function create()
    {
        $products = Product::all();
        $users = User::all();
        $workOrders = WorkOrder::all();
        return view('warehouse-requests.create', compact('products', 'users', 'workOrders'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'user_id' => 'required|exists:users,id',
            'work_order_id' => 'required|exists:work_orders,id',
            'quantity' => 'required|integer',
            'status' => 'required|string',
        ]);

        WarehouseRequest::create($request->all());

        Alert::success('Éxito', 'Solicitud de almacén creada con éxito');
        return redirect()->route('warehouse-requests.index');
    }

    public function edit(WarehouseRequest $warehouseRequest)
    {
        $products = Product::all();
        $users = User::all();
        $workOrders = WorkOrder::all();
        return view('warehouse-requests.edit', compact('warehouseRequest', 'products', 'users', 'workOrders'));
    }

    public function update(Request $request, WarehouseRequest $warehouseRequest)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'user_id' => 'required|exists:users,id',
            'work_order_id' => 'required|exists:work_orders,id',
            'quantity' => 'required|integer',
            'status' => 'required|string',
        ]);

        $warehouseRequest->update($request->all());

        Alert::success('Éxito', 'Solicitud de almacén actualizada con éxito');
        return redirect()->route('warehouse-requests.index');
    }

    public function destroy(WarehouseRequest $warehouseRequest)
    {
        $warehouseRequest->delete();
        Alert::success('Éxito', 'Solicitud de almacén eliminada con éxito');
        return redirect()->route('warehouse-requests.index');
    }
}
