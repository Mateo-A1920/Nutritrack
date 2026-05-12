<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyLog;
use App\Models\FoodEntry;
use Illuminate\Http\Request;

class FoodEntryController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'daily_log_id' => 'required|exists:daily_logs,id',
            'meal_type'    => 'required|in:desayuno,almuerzo,cena,aperitivo',
            'name'         => 'required|string|max:255',
            'calories'     => 'required|numeric|min:0',
            'carbs'        => 'required|numeric|min:0',
            'fats'         => 'required|numeric|min:0',
            'protein'      => 'required|numeric|min:0',
            'sodium'       => 'required|numeric|min:0',
            'sugar'        => 'required|numeric|min:0',
        ]);

        // Verificar que el log pertenece al usuario
        $log = DailyLog::where('id', $data['daily_log_id'])
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $entry = FoodEntry::create($data);

        return response()->json($entry, 201);
    }

    public function destroy(Request $request, FoodEntry $foodEntry)
    {
        $log = DailyLog::where('id', $foodEntry->daily_log_id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $foodEntry->delete();

        return response()->json(['message' => 'Eliminado.']);
    }

    public function update(Request $request, FoodEntry $foodEntry)
    {
        DailyLog::where('id', $foodEntry->daily_log_id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $data = $request->validate([
            'name'      => 'sometimes|string|max:255',
            'meal_type' => 'sometimes|in:desayuno,almuerzo,cena,aperitivo',
            'calories'  => 'sometimes|numeric|min:0',
            'carbs'     => 'sometimes|numeric|min:0',
            'fats'      => 'sometimes|numeric|min:0',
            'protein'   => 'sometimes|numeric|min:0',
            'sodium'    => 'sometimes|numeric|min:0',
            'sugar'     => 'sometimes|numeric|min:0',
        ]);

        $foodEntry->update($data);

        return response()->json($foodEntry);
    }
}