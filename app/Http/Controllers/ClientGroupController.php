<?php

namespace App\Http\Controllers;

use App\Models\ClientGroup;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Log;

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
        $validatedData = $request->validate($this->rules(), $this->messages());

        try {
            ClientGroup::create($validatedData);
            Alert::success('Éxito', 'Grupo de Clientes creado con éxito');
            return redirect()->route('client-groups.index');
        } catch (\Exception $e) {
            Log::error('Error al crear el grupo de clientes: ' . $e->getMessage());
            return redirect()->route('client-groups.index')->with('error', 'Error al crear el grupo de clientes: ' . $e->getMessage());
        }
    }

    public function edit(ClientGroup $clientGroup)
    {
        return view('client-groups.edit', compact('clientGroup'));
    }

    public function update(Request $request, ClientGroup $clientGroup)
    {
        $validatedData = $request->validate($this->rules(), $this->messages());

        try {
            $clientGroup->update($validatedData);
            Alert::success('Éxito', 'Grupo de Clientes actualizado con éxito');
            return redirect()->route('client-groups.index');
        } catch (\Exception $e) {
            Log::error('Error al actualizar el grupo de clientes: ' . $e->getMessage());
            return redirect()->route('client-groups.index')->with('error', 'Error al actualizar el grupo de clientes: ' . $e->getMessage());
        }
    }

    public function destroy(ClientGroup $clientGroup)
    {
        try {
            $clientGroup->delete();
            Alert::success('Éxito', 'Grupo de Clientes eliminado con éxito');
            return redirect()->route('client-groups.index');
        } catch (\Exception $e) {
            Log::error('Error al eliminar el grupo de clientes: ' . $e->getMessage());
            return redirect()->route('client-groups.index')->with('error', 'Error al eliminar el grupo de clientes: ' . $e->getMessage());
        }
    }

    private function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'discount_percentage' => 'required|numeric',
            'status' => 'required|boolean',
        ];
    }

    private function messages()
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no debe exceder los 255 caracteres.',
            'discount_percentage.required' => 'El porcentaje de descuento es obligatorio.',
            'discount_percentage.numeric' => 'El porcentaje de descuento debe ser un número.',
            'status.required' => 'El estado es obligatorio.',
            'status.boolean' => 'El estado debe ser verdadero o falso.',
        ];
    }
}
