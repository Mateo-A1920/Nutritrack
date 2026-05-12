<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Routine;
use Illuminate\Http\Request;

class RoutineController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            Routine::with('exercises')
                ->where('user_id', $request->user()->id)
                ->orderBy('created_at', 'desc')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'day_of_week' => 'nullable|in:lunes,martes,miercoles,jueves,viernes,sabado,domingo',
            'category'    => 'nullable|in:pesas,box,cardio,otro',
            'exercises'   => 'nullable|array',
            'exercises.*.name'         => 'required|string',
            'exercises.*.sets'         => 'required|integer|min:1',
            'exercises.*.reps'         => 'required|integer|min:1',
            'exercises.*.weight_kg'    => 'nullable|numeric|min:0',
            'exercises.*.rest_seconds' => 'nullable|integer|min:0',
            'exercises.*.notes'        => 'nullable|string',
        ]);

        $routine = Routine::create([
            'user_id'     => $request->user()->id,
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'day_of_week' => $data['day_of_week'] ?? null,
            'category'    => $data['category'] ?? 'otro',
        ]);

        foreach ($data['exercises'] ?? [] as $i => $ex) {
            $routine->exercises()->create([...$ex, 'order' => $i]);
        }

        return response()->json($routine->load('exercises'), 201);
    }

    public function update(Request $request, Routine $routine)
    {
        abort_if($routine->user_id !== $request->user()->id, 403);

        $data = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'day_of_week' => 'nullable|in:lunes,martes,miercoles,jueves,viernes,sabado,domingo',
            'category'    => 'nullable|in:pesas,box,cardio,otro',
            'exercises'   => 'nullable|array',
            'exercises.*.id'           => 'nullable|exists:routine_exercises,id',
            'exercises.*.name'         => 'required|string',
            'exercises.*.sets'         => 'required|integer|min:1',
            'exercises.*.reps'         => 'required|integer|min:1',
            'exercises.*.weight_kg'    => 'nullable|numeric|min:0',
            'exercises.*.rest_seconds' => 'nullable|integer|min:0',
            'exercises.*.notes'        => 'nullable|string',
        ]);

        $routine->update([
            'name'        => $data['name']        ?? $routine->name,
            'description' => $data['description'] ?? $routine->description,
            'day_of_week' => $data['day_of_week'] ?? $routine->day_of_week,
            'category'    => $data['category']    ?? $routine->category,
        ]);

        if (isset($data['exercises'])) {
            $routine->exercises()->delete();
            foreach ($data['exercises'] as $i => $ex) {
                $routine->exercises()->create([...$ex, 'order' => $i]);
            }
        }

        return response()->json($routine->load('exercises'));
    }

    public function destroy(Request $request, Routine $routine)
    {
        abort_if($routine->user_id !== $request->user()->id, 403);
        $routine->delete();
        return response()->json(['message' => 'Eliminado.']);
    }
}