<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    /**
     * Run the migrations.
     *
     * THE CORE LEDGER TABLE — every financial movement is recorded here.
     *
     * Transaction types:
     *  - earning  : Daily wage credit (generated from a processed attendance record)
     *  - cashbon  : Advance/loan request by employee (debit from wallet balance)
     *  - payout   : Final salary disbursement (debit from wallet balance)
     *
     * Balance formula per employee:
     *   current_balance = SUM(amount WHERE type='earning')
     *                   - SUM(amount WHERE type IN ('cashbon','payout'))
     *
     * amount is always POSITIVE. The `type` column determines the direction.
     */
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('branch_id')
                ->constrained('branches');

            $table->enum('type', ['earning', 'cashbon', 'payout']);

            // Always positive; direction implied by `type`
            $table->decimal('amount', 12, 2);

            $table->string('description')->nullable()
                ->comment('Human-readable note, e.g. "Hadir 2024-01-15", "Cashbon request #12"');

            // Flexible polymorphic reference — store attendance_id, cashbon_id, etc.
            $table->string('reference_type')->nullable()
                ->comment('Model class name, e.g. App\Models\Attendance');
            $table->unsignedBigInteger('reference_id')->nullable()
                ->comment('PK of the referenced record');

            // Who approved/created this transaction (admin or system)
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            // Indexes for common queries
            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'created_at']);
            $table->index(['branch_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
