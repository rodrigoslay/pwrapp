<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use RealRashid\SweetAlert\Facades\Alert;

class SettingController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Setting::latest()->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $btn = '<a href="'.route('settings.edit', $row->id).'" class="edit btn btn-primary btn-sm">Editar</a>';
                    $btn .= '<form action="'.route('settings.destroy', $row->id).'" method="POST" style="display:inline-block;">
                                '.csrf_field().'
                                '.method_field("DELETE").'
                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                            </form>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('settings.index');
    }

    public function create()
    {
        return view('settings.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'value' => 'required|string|max:255',
        ]);

        Setting::create([
            'name' => $request->name,
            'value' => $request->value,
        ]);

        Alert::success('Éxito', 'Configuración creada con éxito');
        return redirect()->route('settings.index');
    }

    public function edit(Setting $setting)
    {
        return view('settings.edit', compact('setting'));
    }

    public function update(Request $request, Setting $setting)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'value' => 'required|string|max:255',
        ]);

        $setting->update([
            'name' => $request->name,
            'value' => $request->value,
        ]);

        Alert::success('Éxito', 'Configuración actualizada con éxito');
        return redirect()->route('settings.index');
    }

    public function destroy(Setting $setting)
    {
        $setting->delete();
        Alert::success('Éxito', 'Configuración eliminada con éxito');
        return redirect()->route('settings.index');
    }
}
