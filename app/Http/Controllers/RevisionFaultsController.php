<?php

namespace App\Http\Controllers;

use App\Models\RevisionFault;
use Illuminate\Http\Request;

class RevisionFaultController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'revision_id' => 'required|exists:revisions,id',
            'fault' => 'required|string|max:255',
        ]);

        RevisionFault::create($request->all());

        return response()->json(['success' => true]);
    }
}
