<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'user_id',
        'project_id',
        'schedule_id',
        'user_name',
        'user_role',
        'category',
        'action_title',
        'target_activity',
        'weather_snapshot',
        'outcome_type',
        'outcome_details',
        'response_time_minutes',
    ];

    protected function casts(): array
    {
        return [
            'weather_snapshot' => 'array',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }
}
