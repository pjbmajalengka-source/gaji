<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Overtime;
use Carbon\Carbon;

/**
 * OvertimeCalculatorService
 *
 * Responsible for computing all financial values for an overtime session.
 *
 * === BUSINESS RULES ===
 *
 * Type A — Bongkar (Unloading Work):
 *   - Rate            : Rp 10.000 / hour
 *   - Break deduction : If `is_break_taken = true`, deduct 30 minutes.
 *   - Meal allowance  : If net_hours > 2 after break → add Rp 10.000 flat.
 *
 * Type B — Non-Bongkar (General Overtime):
 *   - Rate            : Rp 7.500 / hour
 *   - Break deduction : None.
 *   - Meal allowance  : None.
 *
 * Usage:
 *   $result = app(OvertimeCalculatorService::class)->calculate($overtime);
 *   $overtime->update($result);
 */
class OvertimeCalculatorService
{
    // ── Constants (easy to update if rates change) ─────────────────────────
    private const RATE_BONGKAR = 10000; // Rp/hour
    private const RATE_NON_BONGKAR = 7500; // Rp/hour
    private const BREAK_DEDUCT_MIN = 30; // Minutes deducted if break taken
    private const MEAL_THRESHOLD_H = 2; // Hours threshold for meal allowance
    private const MEAL_ALLOWANCE = 10000; // Rp flat meal allowance

    /**
     * Calculate overtime financials for a given Overtime model.
     *
     * @param  Overtime  $overtime  (must have start_time, end_time, type, is_break_taken)
     * @return array  ['gross_hours', 'net_hours', 'meal_allowance', 'earned_amount']
     */
    public function calculate(Overtime $overtime): array
    {
        $start = Carbon::parse($overtime->start_time);
        $end = Carbon::parse($overtime->end_time);

        // Handle overnight overtime (past midnight)
        if ($end->lessThan($start)) {
            $end->addDay();
        }

        $grossMinutes = $start->diffInMinutes($end);
        $grossHours = $grossMinutes / 60;

        // ── Apply break deduction (Bongkar only) ──────────────────────────
        $netMinutes = $grossMinutes;
        if ($overtime->type === 'bongkar' && $overtime->is_break_taken) {
            $netMinutes = max(0, $grossMinutes - self::BREAK_DEDUCT_MIN);
        }
        $netHours = $netMinutes / 60;

        // ── Calculate rate and meal allowance ─────────────────────────────
        [$rate, $mealAllowance] = $this->getRateAndMeal($overtime->type, $netHours);

        $earnedAmount = ($netHours * $rate) + $mealAllowance;

        return [
            'gross_hours' => round($grossHours, 2),
            'net_hours' => round($netHours, 2),
            'meal_allowance' => $mealAllowance,
            'earned_amount' => round($earnedAmount, 2),
        ];
    }

    /**
     * Create and persist an overtime record with all calculated values.
     *
     * @param  Attendance  $attendance
     * @param  array       $input  {type, start_time, end_time, is_break_taken, notes}
     * @param  int|null    $createdBy  user_id of the supervisor
     * @return Overtime
     */
    public function store(Attendance $attendance, array $input, ?int $createdBy = null): Overtime
    {
        $overtime = new Overtime(array_merge($input, [
            'user_id' => $attendance->user_id,
            'attendance_id' => $attendance->id,
            'created_by' => $createdBy,
        ]));

        $calculated = $this->calculate($overtime);

        $overtime->fill($calculated);
        $overtime->save();

        return $overtime;
    }

    // ── Private Helpers ────────────────────────────────────────────────────

    /**
     * Returns [rate_per_hour, meal_allowance] based on type and net hours.
     */
    private function getRateAndMeal(string $type, float $netHours): array
    {
        if ($type === 'bongkar') {
            $meal = ($netHours > self::MEAL_THRESHOLD_H) ?self::MEAL_ALLOWANCE : 0;
            return [self::RATE_BONGKAR, $meal];
        }

        // non_bongkar — flat rate, no meal
        return [self::RATE_NON_BONGKAR, 0];
    }
}
