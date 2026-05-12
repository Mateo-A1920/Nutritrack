<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserGoal extends Model
{
    protected $fillable = ['user_id','calories_goal','water_glasses_goal','target_weight_kg'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}