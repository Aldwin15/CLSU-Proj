<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeatherThreshold extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'master_activity_id',
        'max_rain_probability',
        'max_rain_volume_mm',
        'max_wind_speed_kmh',
        'max_temperature_c',
        'worker_safety_trigger',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function masterActivity(): BelongsTo
    {
        return $this->belongsTo(MasterActivity::class);
    }
}
