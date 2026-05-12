<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserGoal;
use Illuminate\Http\Request;

class UserGoalController extends Controller
{
    public function show(Request $request)
    {
        $goal = UserGoal::firstOrCreate(
            ['user_id' => $request->user()->id],
            ['calories_goal' => 2000, 'water_glasses_goal' => 8]
        );
        return response()->json($goal);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'calories_goal'      => 'sometimes|integer|min:500',
            'water_glasses_goal' => 'sometimes|integer|min:1|max:30',
            'target_weight_kg'   => 'nullable|numeric|min:0',
        ]);

        $goal = UserGoal::updateOrCreate(
            ['user_id' => $request->user()->id],
            $data
        );

        return response()->json($goal);
    }
}