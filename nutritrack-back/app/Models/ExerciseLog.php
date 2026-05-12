<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExerciseLog extends Model
{
    protected $fillable = [
        'workout_session_id','exercise_name','set_number',
        'reps','weight_kg','duration_seconds','rounds','notes'
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(WorkoutSession::class, 'workout_session_id');
    }
}