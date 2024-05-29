<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientGroup;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;
use Yajra\DataTables\DataTables;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Client::latest()->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $btn = '<a href="'.route('clients.edit', $row->id).'" class="edit btn btn-primary btn-sm">Editar</a>';
                    $btn .= '<form action="'.route('clients.destroy', $row->id).'" method="POST" style="display:inline-block;">
                                '.csrf_field().'
                                '.method_field("DELETE").'
                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                            </form>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $clients = Client::latest()->paginate(10);
        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        $clientGroups = ClientGroup::all();
        return view('clients.create', compact('clientGroups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'rut' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:255',
            'client_group_id' => 'required|exists:client_groups,id',
            'status' => 'required|boolean',
        ]);

        $client = Client::create($request->all());

        if ($request->ajax()) {
            return response()->json($client);
        }

        Alert::success('Éxito', 'Cliente creado con éxito');
        return redirect()->route('clients.index');
    }


    public function edit(Client $client)
    {
        $clientGroups = ClientGroup::all();
        return view('clients.edit', compact('client', 'clientGroups'));
    }

    public function update(Request $request, Client $client)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'rut' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:255',
            'client_group_id' => 'required|exists:client_groups,id',
            'status' => 'required|boolean',
        ]);

        $client->update([
            'name' => $request->name,
            'rut' => $request->rut,
            'email' => $request->email,
            'phone' => $request->phone,
            'client_group_id' => $request->client_group_id,
            'status' => $request->status,
        ]);

        Alert::success('Éxito', 'Cliente actualizado con éxito');
        return redirect()->route('clients.index');
    }

    public function destroy(Client $client)
    {
        $client->delete();
        Alert::success('Éxito', 'Cliente eliminado con éxito');
        return redirect()->route('clients.index');
    }
}
