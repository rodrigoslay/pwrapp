<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CarModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CarModelController extends Controller
{
    public function store(Request $request)
    {
        $request->validate($this->rules(), $this->messages());

        try {
            CarModel::create($request->all());

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Modelo agregado con éxito.']);
            }

            return redirect()->route('work-orders.create-step-one')->with('success', 'Modelo agregado con éxito.');
        } catch (\Exception $e) {
            Log::error('Error al agregar el modelo: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Error al agregar el modelo: ' . $e->getMessage()], 500);
            }

            return redirect()->route('work-orders.create-step-one')->with('error', 'Error al agregar el modelo: ' . $e->getMessage());
        }
    }

    public function getModelsByBrand($brandId)
    {
        try {
            $models = CarModel::where('brand_id', $brandId)
                              ->select('id', 'model', 'year')
                              ->get();

            return response()->json(['success' => true, 'models' => $models]);
        } catch (\Exception $e) {
            Log::error('Error al obtener los modelos: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener los modelos: ' . $e->getMessage()], 500);
        }
    }

    private function rules()
    {
        return [
            'brand_id' => 'required|exists:brands,id',
            'model' => 'required|string|max:255',
            'year' => 'required|integer',
        ];
    }

    private function messages()
    {
        return [
            'brand_id.required' => 'El campo marca es obligatorio.',
            'brand_id.exists' => 'La marca seleccionada no existe.',
            'model.required' => 'El campo modelo es obligatorio.',
            'model.max' => 'El campo modelo no debe exceder los 255 caracteres.',
            'year.required' => 'El campo año es obligatorio.',
            'year.integer' => 'El campo año debe ser un número entero.',
        ];
    }
}
