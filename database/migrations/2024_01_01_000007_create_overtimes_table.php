<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Overtimes Table
 *
 * Tracks overtime sessions (lembur) linked to a specific attendance record.
 * Two overtime types with different rate logic:
 *
 * TYPE A — Bongkar (Unloading):
 *   Rate    : Rp 10.000/hour
 *   Break   : Optional. If `is_break_taken = true`, deduct 30 minutes.
 *   Meal    : If (net_hours > 2), add Rp 10.000 meal allowance.
 *
 * TYPE B — Non-Bongkar (Regular Overtime):
 *   Rate    : Rp 7.500/hour
 *   Break   : None.
 *   Meal    : None.
 *
 * `earned_amount` and `meal_allowance` are CALCULATED and stored
 * by OvertimeCalculatorService — never set by the client.
 */
return new class extends Migration 
{
    public function up(): void
    {
        Schema::create('overtimes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->foreignId('attendance_id')
                ->constrained('attendances')
                ->onDelete('cascade');

            $table->enum('type', ['bongkar', 'non_bongkar']);

            // Raw time range from supervisor input
            $table->time('start_time');
            $table->time('end_time');

            // Bongkar-specific: Did employee take a break during overtime?
            $table->boolean('is_break_taken')->default(false);

            // --- Calculated by OvertimeCalculatorService ---
            // The gross working hours (decimal), before break deduction
            $table->decimal('gross_hours', 5, 2)->default(0);
            // The net billable hours after break deduction
            $table->decimal('net_hours', 5, 2)->default(0);
            // Meal allowance (Rp 10.000 if type=bongkar AND net_hours > 2)
            $table->decimal('meal_allowance', 10, 2)->default(0);
            // Final earned amount from overtime
            $table->decimal('earned_amount', 10, 2)->default(0);

            $table->text('notes')->nullable();
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('overtimes');
    }
};
