<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgressRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'user_id',
        'record_date',
        'planned_progress',
        'actual_progress',
        'variance',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'record_date' => 'date',
        ];
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
