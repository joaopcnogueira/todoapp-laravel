<?php

namespace App\Models;

use App\Enums\Priority;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Todo extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'completed',
        'priority',
        'due_date',
        'completed_at',
        'total_time_seconds',
        'timer_started_at',
    ];

    protected function casts(): array
    {
        return [
            'completed' => 'boolean',
            'priority' => Priority::class,
            'due_date' => 'date',
            'completed_at' => 'datetime',
            'total_time_seconds' => 'integer',
            'timer_started_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function getIsOverdueAttribute(): bool
    {
        return !$this->completed
            && $this->due_date
            && $this->due_date->lt(today());
    }

    public function scopePending($query)
    {
        return $query->where('completed', false);
    }

    public function scopeCompleted($query)
    {
        return $query->where('completed', true);
    }

    public function scopeOverdue($query)
    {
        return $query->where('completed', false)
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->toDateString());
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class)->orderBy('started_at', 'desc');
    }

    public function activeTimeEntry(): HasOne
    {
        return $this->hasOne(TimeEntry::class)->whereNull('ended_at')->latest('started_at');
    }

    public function getIsTimerRunningAttribute(): bool
    {
        return $this->timer_started_at !== null;
    }

    public function getFormattedTotalTimeAttribute(): string
    {
        $seconds = $this->total_time_seconds ?? 0;

        if ($seconds < 3600) {
            $minutes = floor($seconds / 60);
            $secs = $seconds % 60;
            return sprintf('%02d:%02d', $minutes, $secs);
        }

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;
        return sprintf('%d:%02d:%02d', $hours, $minutes, $secs);
    }
}
