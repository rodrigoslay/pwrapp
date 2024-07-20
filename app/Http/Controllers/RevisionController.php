<?php

namespace App\Http\Controllers;

use App\Models\Revision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RevisionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|boolean'
        ]);

        try {
            Revision::create($request->all() + ['created_by' => Auth::id()]);

            return response()->json(['success' => true, 'message' => 'Revisión creada con éxito']);
        } catch (\Exception $e) {
            Log::error('Error al crear la revisión: ' . $e->getMessage());

            return response()->json(['success' => false, 'message' => 'Error al crear la revisión'], 500);
        }
    }
}
