<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * REVISED Attendances Table — 4-Scan System
 *
 * Replaces the previous datetime-based attendance table.
 * Key design decisions:
 * - `date` is a separate DATE column (not part of datetime) — enables
 *   the unique('user_id', 'date') constraint for microservice safety.
 * - 4 separate TIME columns for the 4 fingerprint scans.
 * - `shift_id` FK to know which shift schedule to compare against.
 * - `status` is computed by AttendanceEvaluationService, not the microservice.
 * - `is_processed` guards WageProcessingJob from double-inserting earnings.
 */
return new class extends Migration 
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->foreignId('shift_id')
                ->nullable()
                ->constrained('shifts')
                ->onDelete('set null');

            // --- Core: Separate date from time (pattern from analysis) ---
            $table->date('date');

            // --- 4-Scan Fingerprint Columns ---
            $table->time('clock_in')->nullable(); // Scan 1: Masuk
            $table->time('break_start')->nullable(); // Scan 2: Mulai Istirahat
            $table->time('break_end')->nullable(); // Scan 3: Selesai Istirahat
            $table->time('clock_out')->nullable(); // Scan 4: Pulang

            // --- Evaluated Status (set by AttendanceEvaluationService) ---
            $table->enum('status', [
                'present', // Hadir penuh
                'late', // Telat masuk
                'early_out', // Pulang lebih awal
                'half_day', // Setengah hari
                'absent', // Tidak hadir
            ])->nullable();

            $table->unsignedSmallInteger('late_minutes')->default(0);
            $table->unsignedSmallInteger('early_out_minutes')->default(0);

            // --- Processing Guard ---
            $table->boolean('is_processed')->default(false);

            // Raw JSON from fingerprint device (audit trail)
            $table->json('raw_payload')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            // CRITICAL: Prevent double-injection from fingerprint microservice
            $table->unique(['user_id', 'date'], 'unique_user_date');
            $table->index(['date', 'shift_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
