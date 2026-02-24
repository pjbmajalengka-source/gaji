<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'description', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * The daily schedule rules for this shift.
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(ShiftSchedule::class);
    }

    /**
     * Get the schedule rule for a given day of week.
     * Day: 1=Mon, 2=Tue, ..., 6=Sat, 7=Sun
     */
    public function scheduleForDay(int $dayOfWeek): ?ShiftSchedule
    {
        return $this->schedules->firstWhere('day_of_week', $dayOfWeek);
    }

    /**
     * Get users assigned to this shift.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
