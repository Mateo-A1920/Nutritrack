<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExerciseLog;
use App\Models\WorkoutSession;
use Illuminate\Http\Request;

class ExerciseLogController extends Controller
{
    public function bySession(Request $request, WorkoutSession $workoutSession)
    {
        abort_if($workoutSession->user_id !== $request->user()->id, 403);
        return response()->json(
            $workoutSession->exerciseLogs()->orderBy('set_number')->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'workout_session_id' => 'required|exists:workout_sessions,id',
            'exercise_name'      => 'required|string|max:255',
            'set_number'         => 'required|integer|min:1',
            'reps'               => 'nullable|integer|min:0',
            'weight_kg'          => 'nullable|numeric|min:0',
            'duration_seconds'   => 'nullable|integer|min:0',
            'rounds'             => 'nullable|integer|min:0',
            'notes'              => 'nullable|string',
        ]);

        WorkoutSession::where('id', $data['workout_session_id'])
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $log = ExerciseLog::create($data);
        return response()->json($log, 201);
    }

    public function update(Request $request, ExerciseLog $exerciseLog)
    {
        WorkoutSession::where('id', $exerciseLog->workout_session_id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $data = $request->validate([
            'reps'             => 'nullable|integer|min:0',
            'weight_kg'        => 'nullable|numeric|min:0',
            'duration_seconds' => 'nullable|integer|min:0',
            'rounds'           => 'nullable|integer|min:0',
            'notes'            => 'nullable|string',
        ]);

        $exerciseLog->update($data);
        return response()->json($exerciseLog);
    }

    public function destroy(Request $request, ExerciseLog $exerciseLog)
    {
        WorkoutSession::where('id', $exerciseLog->workout_session_id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $exerciseLog->delete();
        return response()->json(['message' => 'Eliminado.']);
    }

    public function stats(Request $request, string $exerciseName)
    {
        $logs = ExerciseLog::whereHas('session', fn($q) =>
            $q->where('user_id', $request->user()->id)
        )
        ->where('exercise_name', $exerciseName)
        ->orderBy('created_at', 'desc')
        ->limit(50)
        ->get();

        return response()->json($logs);
    }
}