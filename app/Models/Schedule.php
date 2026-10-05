<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'master_activity_id',
        'predecessor_id',
        'name',
        'duration_days',
        'baseline_start_date',
        'baseline_end_date',
        'current_start_date',
        'current_end_date',
        'feasibility',
        'has_weather_alert',
        'weather_rule_text',
        'status',
        'continued_same_day',
        'resume_time',
        'mitigation_notes',
    ];

    protected function casts(): array
    {
        return [
            'baseline_start_date' => 'date',
            'baseline_end_date' => 'date',
            'current_start_date' => 'date',
            'current_end_date' => 'date',
            'has_weather_alert' => 'boolean',
            'continued_same_day' => 'boolean',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function masterActivity(): BelongsTo
    {
        return $this->belongsTo(MasterActivity::class);
    }

    public function predecessor(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'predecessor_id');
    }

    public function successors(): HasMany
    {
        return $this->hasMany(Schedule::class, 'predecessor_id');
    }

    public function progressRecords(): HasMany
    {
        return $this->hasMany(ProgressRecord::class);
    }

    public function weatherThreshold(): HasOne
    {
        return $this->hasOne(WeatherThreshold::class);
    }
}
