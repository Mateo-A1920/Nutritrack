<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyLog;
use Illuminate\Http\Request;

class DailyLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = DailyLog::where('user_id', $request->user()->id)
            ->orderByDesc('log_date')
            ->get();

        return response()->json($logs);
    }

    public function show(Request $request, string $date)
    {
        $log = DailyLog::with('foodEntries')
            ->where('user_id', $request->user()->id)
            ->where('log_date', $date)
            ->first();

        if (!$log) {
            return response()->json(['log' => null, 'entries' => []]);
        }

        return response()->json([
            'log'     => $log,
            'entries' => $log->foodEntries,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(['log_date' => 'required|date']);

        $log = DailyLog::firstOrCreate([
            'user_id'  => $request->user()->id,
            'log_date' => $request->log_date,
        ]);

        return response()->json($log, 201);
    }
}