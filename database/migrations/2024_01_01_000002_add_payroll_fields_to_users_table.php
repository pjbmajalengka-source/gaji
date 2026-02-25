<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    /**
     * Run the migrations.
     *
     * Extends Laravel's default `users` table with payroll-specific fields.
     * Run AFTER the default Laravel users migration.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Link to branch
            $table->foreignId('branch_id')
                ->nullable()
                ->after('id')
                ->constrained('branches')
                ->nullOnDelete();

            // Payroll fields
            $table->decimal('daily_base_salary', 12, 2)
                ->default(0)
                ->after('branch_id')
                ->comment('Base daily wage in IDR before allowances/deductions');

            $table->enum('payout_preference', ['daily', 'weekly', 'monthly', 'manual'])
                ->default('manual')
                ->after('daily_base_salary')
                ->comment('How employee prefers to receive their accumulated balance');

            // Optional HR fields (useful to have from the start)
            $table->string('nik', 20)->nullable()->unique()->after('payout_preference')->comment('Nomor Induk Karyawan');
            $table->enum('role', ['superadmin', 'admin', 'hr', 'employee'])->default('employee')->after('nik');
            $table->date('hire_date')->nullable()->after('role');
            $table->boolean('is_active')->default(true)->after('hire_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn([
                'branch_id',
                'daily_base_salary',
                'payout_preference',
                'nik',
                'role',
                'hire_date',
                'is_active',
            ]);
        });
    }
};
