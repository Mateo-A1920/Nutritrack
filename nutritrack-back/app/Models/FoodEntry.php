<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoodEntry extends Model
{
    protected $fillable = [
        'daily_log_id', 'meal_type', 'name',
        'calories', 'carbs', 'fats', 'protein', 'sodium', 'sugar',
    ];

    public function dailyLog(): BelongsTo
    {
        return $this->belongsTo(DailyLog::class);
    }
}