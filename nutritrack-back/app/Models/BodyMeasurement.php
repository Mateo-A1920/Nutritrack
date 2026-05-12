<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BodyMeasurement extends Model
{
    protected $fillable = [
        'user_id','measured_at','weight_kg','height_cm',
        'waist_cm','hip_cm','chest_cm','arm_cm','leg_cm','body_fat_pct'
    ];
    protected $casts = ['measured_at' => 'date'];

    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d');
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}