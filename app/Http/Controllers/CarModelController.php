<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CarModel;
use Illuminate\Support\Facades\DB;

class CarModelController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'brand_id' => 'required|exists:brands,id',
            'model' => 'required|string|max:255',
            'year' => 'required|integer',
        ]);

        CarModel::create($request->all());

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Modelo agregado con éxito.']);
        }

        return redirect()->route('work-orders.create-step-one')->with('success', 'Modelo agregado con éxito.');
    }



    public function getModelsByBrand($brandId)
    {
        $models = CarModel::where('brand_id', $brandId)
                          ->select('id', 'model', 'year')
                          ->get();

        return response()->json(['success' => true, 'models' => $models]);
    }



}
