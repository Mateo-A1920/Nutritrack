<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Routine extends Model
{
    protected $fillable = ['user_id', 'name', 'description', 'day_of_week', 'category'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function exercises(): HasMany { return $this->hasMany(RoutineExercise::class)->orderBy('order'); }
    public function sessions(): HasMany { return $this->hasMany(WorkoutSession::class); }
}