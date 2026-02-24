<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Shifts Table
 *
 * Stores named shift groups (e.g., "Shift Reguler Toko").
 * The actual day-by-day schedule rules live in `shift_schedules`.
 * This separation allows re-use of one shift group across all branches.
 */
return new class extends Migration 
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Shift Reguler Toko"
            $table->string('code')->unique(); // e.g., "SRT"
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
