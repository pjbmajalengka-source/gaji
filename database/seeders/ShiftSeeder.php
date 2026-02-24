<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * ShiftSeeder
 *
 * Seeds 1 default shift "Shift Reguler Toko" with:
 * - Mon–Thu: 07:00–16:30, break 12:00–13:00
 * - Fri:     07:00–16:30, break 11:00–13:00 (Friday prayer)
 * - Sat:     07:00–16:30, break 12:00–13:00
 * - Sun:     07:00–12:00, no break (counts as full working day)
 */
class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        $shiftId = DB::table('shifts')->insertGetId([
            'name' => 'Shift Reguler Toko',
            'code' => 'SRT',
            'description' => 'Shift utama untuk karyawan cabang retail.',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // day_of_week: 1=Mon, 2=Tue, 3=Wed, 4=Thu, 5=Fri, 6=Sat, 7=Sun
        $schedules = [
            // Monday — Thursday: normal break 12:00–13:00
            ['day_of_week' => 1, 'break_start' => '12:00', 'break_end' => '13:00'],
            ['day_of_week' => 2, 'break_start' => '12:00', 'break_end' => '13:00'],
            ['day_of_week' => 3, 'break_start' => '12:00', 'break_end' => '13:00'],
            ['day_of_week' => 4, 'break_start' => '12:00', 'break_end' => '13:00'],
            // Friday: Sholat Jumat — break 11:00–13:00
            ['day_of_week' => 5, 'break_start' => '11:00', 'break_end' => '13:00'],
            // Saturday: normal break
            ['day_of_week' => 6, 'break_start' => '12:00', 'break_end' => '13:00'],
            // Sunday: 07:00–12:00, NO break. Counts as 1 full working day.
            ['day_of_week' => 7, 'break_start' => null, 'break_end' => null, 'end_time' => '12:00'],
        ];

        foreach ($schedules as $day) {
            DB::table('shift_schedules')->insert([
                'shift_id' => $shiftId,
                'day_of_week' => $day['day_of_week'],
                'is_working_day' => true,
                'start_time' => '07:00',
                'end_time' => $day['end_time'] ?? '16:30',
                'break_start' => $day['break_start'],
                'break_end' => $day['break_end'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('ShiftSeeder: "Shift Reguler Toko" + 7 schedules seeded.');
    }
}
