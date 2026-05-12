<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyLog extends Model
{
    protected $fillable = ['user_id', 'log_date'];

    protected $casts = ['log_date' => 'date'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function foodEntries(): HasMany
    {
        return $this->hasMany(FoodEntry::class);
    }
}