<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ShiftSchedule — One row = rules for one specific day within a shift.
 *
 * day_of_week: 1=Mon, 2=Tue, 3=Wed, 4=Thu, 5=Fri, 6=Sat, 7=Sun
 */
class ShiftSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'shift_id',
        'day_of_week',
        'is_working_day',
        'start_time',
        'end_time',
        'break_start',
        'break_end',
    ];

    protected $casts = [
        'is_working_day' => 'boolean',
        'day_of_week' => 'integer',
    ];

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Net required working minutes (excluding break duration).
     */
    public function getRequiredMinutesAttribute(): int
    {
        if (!$this->start_time || !$this->end_time)
            return 0;

        $start = \Carbon\Carbon::parse($this->start_time);
        $end = \Carbon\Carbon::parse($this->end_time);

        $totalMinutes = $start->diffInMinutes($end);
        $breakMinutes = $this->break_duration_minutes;

        return $totalMinutes - $breakMinutes;
    }

    /**
     * Break duration in minutes (0 if no break defined).
     */
    public function getBreakDurationMinutesAttribute(): int
    {
        if (!$this->break_start || !$this->break_end)
            return 0;

        $breakStart = \Carbon\Carbon::parse($this->break_start);
        $breakEnd = \Carbon\Carbon::parse($this->break_end);

        return $breakStart->diffInMinutes($breakEnd);
    }
}
