<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BodyMeasurement;
use Illuminate\Http\Request;

class BodyMeasurementController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            BodyMeasurement::where('user_id', $request->user()->id)
                ->orderByDesc('measured_at')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'measured_at'  => 'required|date',
            'weight_kg'    => 'nullable|numeric|min:0',
            'height_cm'    => 'nullable|numeric|min:0',
            'waist_cm'     => 'nullable|numeric|min:0',
            'hip_cm'       => 'nullable|numeric|min:0',
            'chest_cm'     => 'nullable|numeric|min:0',
            'arm_cm'       => 'nullable|numeric|min:0',
            'leg_cm'       => 'nullable|numeric|min:0',
            'body_fat_pct' => 'nullable|numeric|min:0|max:100',
        ]);

        $measurement = BodyMeasurement::create([...$data, 'user_id' => $request->user()->id]);

        return response()->json($measurement, 201);
    }

    public function destroy(Request $request, BodyMeasurement $bodyMeasurement)
    {
        abort_if($bodyMeasurement->user_id !== $request->user()->id, 403);
        $bodyMeasurement->delete();
        return response()->json(['message' => 'Eliminado.']);
    }
}