<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientGroup;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
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
        $validatedData = Validator::make($request->all(), $this->rules(), $this->messages())->validate();

        try {
            $client = Client::create($validatedData);
            return response()->json([
                'success' => true,
                'message' => 'Cliente creado con éxito',
                'client' => $client,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al agregar el cliente: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al agregar el cliente: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function edit(Client $client)
    {
        $clientGroups = ClientGroup::all();
        return view('clients.edit', compact('client', 'clientGroups'));
    }

    public function update(Request $request, Client $client)
    {
        $validatedData = $request->validate($this->rules($client->id), $this->messages());

        try {
            $client->update($validatedData);
            Alert::success('Éxito', 'Cliente actualizado con éxito');
            return redirect()->route('clients.index');
        } catch (\Exception $e) {
            Log::error('Error al actualizar el cliente: ' . $e->getMessage());
            return redirect()->route('clients.index')->with('error', 'Error al actualizar el cliente: ' . $e->getMessage());
        }
    }

    public function destroy(Client $client)
    {
        try {
            $client->delete();
            Alert::success('Éxito', 'Cliente eliminado con éxito');
            return redirect()->route('clients.index');
        } catch (\Exception $e) {
            Log::error('Error al eliminar el cliente: ' . $e->getMessage());
            return redirect()->route('clients.index')->with('error', 'Error al eliminar el cliente: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $client = Client::with('clientGroup')->findOrFail($id);
        return response()->json($client);
    }

    public function list()
    {
        $clients = Client::all();
        return response()->json(['clients' => $clients]);
    }

    public function checkRUT(Request $request)
    {
        $exists = Client::where('rut', $request->rut)->exists();
        return response()->json(['exists' => $exists]);
    }

    private function rules($clientId = null)
    {
        return [
            'name' => 'required|string|max:255',
            'rut' => 'required|string|max:255|unique:clients,rut' . ($clientId ? ',' . $clientId : ''),
            'email' => 'required|email|max:255|unique:clients,email' . ($clientId ? ',' . $clientId : ''),
            'phone' => 'required|string|max:15',
            'client_group_id' => 'required|exists:client_groups,id',
            'status' => 'required|boolean',
        ];
    }

    private function messages()
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'rut.required' => 'El RUT es obligatorio.',
            'rut.unique' => 'El RUT ya está registrado.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El formato del correo electrónico es inválido.',
            'email.unique' => 'El correo electrónico ya está registrado.',
            'phone.required' => 'El teléfono es obligatorio.',
            'client_group_id.required' => 'El grupo de cliente es obligatorio.',
            'client_group_id.exists' => 'El grupo de cliente seleccionado es inválido.',
            'status.required' => 'El estado es obligatorio.',
            'status.boolean' => 'El estado debe ser verdadero o falso.',
        ];
    }
}
