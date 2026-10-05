<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'category',
        'default_duration_days',
        'max_rain_probability',
        'max_rain_volume_mm',
        'max_wind_speed_kmh',
        'max_temperature_c',
        'safety_trigger',
        'predecessor_hint',
        'description',
    ];

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }
}
