<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoutineExercise extends Model
{
    protected $fillable = ['routine_id','name','sets','reps','weight_kg','rest_seconds','notes','order'];

    public function routine(): BelongsTo { return $this->belongsTo(Routine::class); }
}