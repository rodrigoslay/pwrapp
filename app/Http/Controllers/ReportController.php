<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use RealRashid\SweetAlert\Facades\Alert;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Report::latest()->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $btn = '<a href="'.route('reports.edit', $row->id).'" class="edit btn btn-primary btn-sm">Editar</a>';
                    $btn .= '<form action="'.route('reports.destroy', $row->id).'" method="POST" style="display:inline-block;">
                                '.csrf_field().'
                                '.method_field("DELETE").'
                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                            </form>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $reports = Report::latest()->paginate(10);
        return view('reports.index', compact('reports'));
    }

    public function create()
    {
        return view('reports.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        Report::create([
            'title' => $request->title,
            'content' => $request->content,
        ]);

        Alert::success('Éxito', 'Reporte creado con éxito');
        return redirect()->route('reports.index');
    }

    public function edit(Report $report)
    {
        return view('reports.edit', compact('report'));
    }

    public function update(Request $request, Report $report)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $report->update([
            'title' => $request->title,
            'content' => $request->content,
        ]);

        Alert::success('Éxito', 'Reporte actualizado con éxito');
        return redirect()->route('reports.index');
    }

    public function destroy(Report $report)
    {
        $report->delete();
        Alert::success('Éxito', 'Reporte eliminado con éxito');
        return redirect()->route('reports.index');
    }
}
