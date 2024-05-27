<?php

namespace App\Http\Controllers;

use App\Models\ClientGroup;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use RealRashid\SweetAlert\Facades\Alert;

class ClientGroupController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = ClientGroup::latest()->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $btn = '<a href="'.route('client-groups.edit', $row->id).'" class="edit btn btn-primary btn-sm">Editar</a>';
                    $btn .= '<form action="'.route('client-groups.destroy', $row->id).'" method="POST" style="display:inline-block;">
                                '.csrf_field().'
                                '.method_field("DELETE").'
                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                            </form>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $clientGroups = ClientGroup::latest()->paginate(10);
        return view('client-groups.index', compact('clientGroups'));
    }

    public function create()
    {
        return view('client-groups.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'discount_percentage' => 'required|numeric',
            'status' => 'required|boolean',
        ]);

        ClientGroup::create([
            'name' => $request->name,
            'discount_percentage' => $request->discount_percentage,
            'status' => $request->status,
        ]);

        Alert::success('Éxito', 'Grupo de Clientes creado con éxito');
        return redirect()->route('client-groups.index');
    }

    public function edit(ClientGroup $clientGroup)
    {
        return view('client-groups.edit', compact('clientGroup'));
    }

    public function update(Request $request, ClientGroup $clientGroup)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'discount_percentage' => 'required|numeric',
            'status' => 'required|boolean',
        ]);

        $clientGroup->update([
            'name' => $request->name,
            'discount_percentage' => $request->discount_percentage,
            'status' => $request->status,
        ]);

        Alert::success('Éxito', 'Grupo de Clientes actualizado con éxito');
        return redirect()->route('client-groups.index');
    }

    public function destroy(ClientGroup $clientGroup)
    {
        $clientGroup->delete();
        Alert::success('Éxito', 'Grupo de Clientes eliminado con éxito');
        return redirect()->route('client-groups.index');
    }
}
