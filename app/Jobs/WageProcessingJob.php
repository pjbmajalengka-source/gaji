<?php

namespace App\Jobs;

use App\Models\Attendance;
use App\Models\WalletTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WageProcessingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
    //
    }

    /**
     * Execute the job.
     *
     * Processes all un-processed attendances and converts them to wallet earnings.
     */
    public function handle(): void
    {
        $unprocessedAttendances = Attendance::where('is_processed', false)
            ->with(['user', 'branch'])
            ->get();

        if ($unprocessedAttendances->isEmpty()) {
            return;
        }

        foreach ($unprocessedAttendances as $attendance) {
            $this->processAttendance($attendance);
        }
    }

    /**
     * Process a single attendance record.
     */
    protected function processAttendance(Attendance $attendance): void
    {
        $user = $attendance->user;

        if (!$user || $user->daily_base_salary <= 0) {
            return;
        }

        DB::transaction(function () use ($attendance, $user) {
            $amount = $this->calculateWage($attendance, $user->daily_base_salary);

            if ($amount > 0) {
                WalletTransaction::create([
                    'user_id' => $user->id,
                    'branch_id' => $attendance->branch_id,
                    'type' => 'earning',
                    'amount' => $amount,
                    'description' => "Upah Harian: " . ($attendance->check_in ? $attendance->check_in->format('d/m/Y') : 'Data Absensi'),
                    'reference_type' => Attendance::class ,
                    'reference_id' => $attendance->id,
                ]);
            }

            $attendance->update(['is_processed' => true]);
        });
    }

    /**
     * Wage calculation logic based on attendance status.
     */
    protected function calculateWage(Attendance $attendance, float $baseSalary): float
    {
        return match ($attendance->status) {
                'present', 'late' => $baseSalary,
                'half_day' => $baseSalary / 2,
                'absent' => 0.0,
                default => 0.0,
            };
    }
}
