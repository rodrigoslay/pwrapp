<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Log;

class IncidentController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Incident::latest()->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $btn = '<a href="'.route('incidents.edit', $row->id).'" class="edit btn btn-primary btn-sm">Editar</a>';
                    $btn .= '<form action="'.route('incidents.destroy', $row->id).'" method="POST" style="display:inline-block;">
                                '.csrf_field().'
                                '.method_field("DELETE").'
                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                            </form>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $incidents = Incident::latest()->paginate(10);
        return view('incidents.index', compact('incidents'));
    }

    public function create()
    {
        return view('incidents.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|boolean',
        ]);

        try {
            Incident::create([
                'name' => $request->name,
                'status' => $request->status,
                'created_by' => auth()->user()->id,
            ]);

            Alert::success('Éxito', 'Incidente creado con éxito');
            return redirect()->route('incidents.index');
        } catch (\Exception $e) {
            Log::error('Error al crear el incidente: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al crear el incidente');
        }
    }

    public function edit(Incident $incident)
    {
        return view('incidents.edit', compact('incident'));
    }

    public function update(Request $request, Incident $incident)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|boolean',
        ]);

        try {
            $incident->update([
                'name' => $request->name,
                'status' => $request->status,
                'updated_by' => auth()->user()->id,
            ]);

            Alert::success('Éxito', 'Incidente actualizado con éxito');
            return redirect()->route('incidents.index');
        } catch (\Exception $e) {
            Log::error('Error al actualizar el incidente: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al actualizar el incidente');
        }
    }

    public function destroy(Incident $incident)
    {
        try {
            $incident->delete();
            Alert::success('Éxito', 'Incidente eliminado con éxito');
            return redirect()->route('incidents.index');
        } catch (\Exception $e) {
            Log::error('Error al eliminar el incidente: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al eliminar el incidente');
        }
    }
}
