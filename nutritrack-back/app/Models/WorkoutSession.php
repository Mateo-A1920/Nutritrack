<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkoutSession extends Model
{
    protected $fillable = ['user_id','routine_id','session_date','duration_minutes','notes'];
    protected $casts = ['session_date' => 'date'];

    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d');
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function routine(): BelongsTo { return $this->belongsTo(Routine::class); }
    public function exerciseLogs(): HasMany { return $this->hasMany(ExerciseLog::class); }
}