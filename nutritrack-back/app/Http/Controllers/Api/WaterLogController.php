<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WaterLog;
use Illuminate\Http\Request;

class WaterLogController extends Controller
{
    public function show(Request $request, string $date)
    {
        $log = WaterLog::where('user_id', $request->user()->id)
            ->where('log_date', $date)
            ->first();

        return response()->json($log ?? ['glasses' => 0, 'log_date' => $date]);
    }

    public function upsert(Request $request)
    {
        $data = $request->validate([
            'log_date' => 'required|date',
            'glasses'  => 'required|integer|min:0|max:30',
        ]);

        $log = WaterLog::updateOrCreate(
            ['user_id' => $request->user()->id, 'log_date' => $data['log_date']],
            ['glasses' => $data['glasses']]
        );

        return response()->json($log);
    }
}