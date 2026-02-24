<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Shift Schedules Table — The Custom Working Hours Engine
 *
 * Stores day-specific schedule rules per shift.
 * This is the key table for dynamic business rules:
 *
 * day_of_week: 1=Monday, 2=Tuesday, ..., 6=Saturday, 7=Sunday
 *
 * Example rows for "Shift Reguler Toko" (shift_id=1):
 *   day 1-4 (Mon-Thu): start=07:00, end=16:30, break=12:00-13:00
 *   day 5   (Fri):     start=07:00, end=16:30, break=11:00-13:00
 *   day 6   (Sat):     start=07:00, end=16:30, break=12:00-13:00
 *   day 7   (Sun):     start=07:00, end=12:00, break=null (full day pay)
 *
 * Note: `is_working_day=false` means it's a non-working day
 * (no penalty for absence, no earning injected).
 */
return new class extends Migration 
{
    public function up(): void
    {
        Schema::create('shift_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_id')
                ->constrained('shifts')
                ->onDelete('cascade');

            $table->unsignedTinyInteger('day_of_week'); // 1=Mon ... 7=Sun
            $table->boolean('is_working_day')->default(true);

            // Required working hours
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            // Break window (nullable — Sunday has no break)
            $table->time('break_start')->nullable();
            $table->time('break_end')->nullable();

            $table->timestamps();

            // One rule per day per shift
            $table->unique(['shift_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_schedules');
    }
};
