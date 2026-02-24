<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shift_id',
        'date',
        'clock_in',
        'break_start',
        'break_end',
        'clock_out',
        'status',
        'late_minutes',
        'early_out_minutes',
        'is_processed',
        'raw_payload',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'is_processed' => 'boolean',
        'raw_payload' => 'array',
        'late_minutes' => 'integer',
        'early_out_minutes' => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function overtimes(): HasMany
    {
        return $this->hasMany(Overtime::class);
    }

    // ── Computed Attributes ───────────────────────────────────────────────

    /**
     * Total hours actually worked (clock_in to clock_out minus break).
     * Returns float, e.g. 8.25 = 8 hours 15 minutes.
     */
    public function getWorkedHoursAttribute(): ?float
    {
        if (!$this->clock_in || !$this->clock_out) {
            return null;
        }

        $start = Carbon::parse($this->clock_in);
        $end = Carbon::parse($this->clock_out);

        $totalMinutes = $start->diffInMinutes($end);

        // Deduct break if both break timestamps exist
        if ($this->break_start && $this->break_end) {
            $breakStart = Carbon::parse($this->break_start);
            $breakEnd = Carbon::parse($this->break_end);
            $totalMinutes -= $breakStart->diffInMinutes($breakEnd);
        }

        return round($totalMinutes / 60, 2);
    }

    /**
     * Is employee considered present for that date?
     */
    public function getIsPresentAttribute(): bool
    {
        return in_array($this->status, ['present', 'late', 'early_out']);
    }
}
