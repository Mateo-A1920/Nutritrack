<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkoutSession;
use Illuminate\Http\Request;

class WorkoutSessionController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            WorkoutSession::with(['routine', 'exerciseLogs'])
                ->where('user_id', $request->user()->id)
                ->orderByDesc('session_date')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'routine_id'       => 'required|exists:routines,id',
            'session_date'     => 'required|date',
            'duration_minutes' => 'nullable|integer|min:1',
            'notes'            => 'nullable|string',
        ]);

        $session = WorkoutSession::create([...$data, 'user_id' => $request->user()->id]);

        return response()->json($session->load('routine'), 201);
    }

    public function show(Request $request, WorkoutSession $workoutSession)
    {
        abort_if($workoutSession->user_id !== $request->user()->id, 403);
        return response()->json($workoutSession->load(['routine', 'exerciseLogs']));
    }

    public function destroy(Request $request, WorkoutSession $workoutSession)
    {
        abort_if($workoutSession->user_id !== $request->user()->id, 403);
        $workoutSession->delete();
        return response()->json(['message' => 'Eliminado.']);
    }
}