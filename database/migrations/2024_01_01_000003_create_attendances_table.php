<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    /**
     * Run the migrations.
     *
     * Stores RAW attendance logs injected directly by the external
     * Python/Node.js fingerprint microservice. There is NO UI for this table.
     *
     * The microservice identifies each branch via the `source` column
     * (e.g. "fingerspot_mjl", "fingerspot_jtj", "fingerspot_tsm") and
     * maps the device's user ID to our `user_id` foreign key.
     *
     * Wage processing logic will read from this table and produce
     * `wallet_transactions` records of type "earning".
     */
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('branch_id')
                ->constrained('branches');

            // Raw timestamps from fingerprint device (nullable: device may only have check-in)
            $table->dateTime('check_in')->nullable();
            $table->dateTime('check_out')->nullable();

            // Processed status — can be computed by the microservice or a Laravel job
            $table->enum('status', ['present', 'absent', 'late', 'half_day'])
                ->default('present');

            // Wage processing flag — set to true once a wallet_transaction has been created
            $table->boolean('is_processed')->default(false)->index();

            // Audit / traceability
            $table->string('source', 50)
                ->nullable()
                ->comment('Identifier of the microservice / device, e.g. fingerspot_mjl');

            $table->json('raw_payload')
                ->nullable()
                ->comment('The full raw JSON from the fingerprint device for debugging');

            $table->timestamps();

            // Prevent duplicate attendance for same user on the same day
            // (based on check_in date portion — enforced at app level or via unique index)
            $table->index(['user_id', 'check_in']);
            $table->index(['branch_id', 'check_in']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
