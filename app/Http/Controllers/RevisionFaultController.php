<?php

namespace App\Http\Controllers;

use App\Models\RevisionFault;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RevisionFaultController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'revision_id' => 'required|exists:revisions,id',
            'fault' => 'required|string|max:255',
        ]);

        try {
            RevisionFault::create($request->all() + ['created_by' => Auth::id()]);

            return response()->json(['success' => true, 'message' => 'Fallo de revisión creado con éxito']);
        } catch (\Exception $e) {
            Log::error('Error al crear el fallo de revisión: ' . $e->getMessage());

            return response()->json(['success' => false, 'message' => 'Error al crear el fallo de revisión'], 500);
        }
    }
}
