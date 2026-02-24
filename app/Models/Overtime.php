<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Overtime extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'type',
        'start_time',
        'end_time',
        'is_break_taken',
        'gross_hours',
        'net_hours',
        'meal_allowance',
        'earned_amount',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'is_break_taken' => 'boolean',
        'gross_hours' => 'decimal:2',
        'net_hours' => 'decimal:2',
        'meal_allowance' => 'decimal:2',
        'earned_amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class , 'created_by');
    }
}
