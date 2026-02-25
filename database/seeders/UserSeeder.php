<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * UserSeeder
 *
 * Seeds realistic dummy employees across all 3 branches for testing:
 *   - 1 Super Admin (no branch)
 *   - 1 Admin per branch (MJL, JTJ, TSM)
 *   - 3 Employees per branch
 *
 * All passwords default to: "password"
 * All employees are on "Shift Reguler Toko" (shift_id=1).
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Get branch IDs
        $branches = DB::table('branches')->pluck('id', 'code');
        $shiftId = DB::table('shifts')->where('code', 'SRT')->value('id');

        $now = now();
        $users = [];

        // ── Super Admin (no branch) ──────────────────────────────────────
        $users[] = [
            'name' => 'Super Admin',
            'email' => 'admin@payrollpjbm.test',
            'password' => Hash::make('password'),
            'branch_id' => null,
            'daily_base_salary' => 0,
            'payout_preference' => 'monthly',
            'nik' => 'SA001',
            'role' => 'superadmin',
            'hire_date' => '2024-01-01',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        // ── Branch-specific admins & employees ───────────────────────────
        $branchData = [
            'MJL' => [
                'admin' => ['name' => 'Ahmad Fauzan', 'nik' => 'MJL-ADM-001', 'email' => 'admin.mjl@payrollpjbm.test'],
                'employees' => [
                    ['name' => 'Siti Rahmawati', 'nik' => 'MJL-EMP-001', 'salary' => 85000],
                    ['name' => 'Deni Kurniawan', 'nik' => 'MJL-EMP-002', 'salary' => 80000],
                    ['name' => 'Rina Mulyani', 'nik' => 'MJL-EMP-003', 'salary' => 80000],
                ],
            ],
            'JTJ' => [
                'admin' => ['name' => 'Budi Santoso', 'nik' => 'JTJ-ADM-001', 'email' => 'admin.jtj@payrollpjbm.test'],
                'employees' => [
                    ['name' => 'Indah Permata', 'nik' => 'JTJ-EMP-001', 'salary' => 80000],
                    ['name' => 'Wahyu Prasetyo', 'nik' => 'JTJ-EMP-002', 'salary' => 80000],
                    ['name' => 'Lestari Dewi', 'nik' => 'JTJ-EMP-003', 'salary' => 75000],
                ],
            ],
            'TSM' => [
                'admin' => ['name' => 'Citra Lestari', 'nik' => 'TSM-ADM-001', 'email' => 'admin.tsm@payrollpjbm.test'],
                'employees' => [
                    ['name' => 'Eko Widodo', 'nik' => 'TSM-EMP-001', 'salary' => 90000],
                    ['name' => 'Nurul Hidayah', 'nik' => 'TSM-EMP-002', 'salary' => 85000],
                    ['name' => 'Fajar Ramadan', 'nik' => 'TSM-EMP-003', 'salary' => 80000],
                ],
            ],
        ];

        foreach ($branchData as $code => $data) {
            $branchId = $branches[$code] ?? null;

            // Admin
            $users[] = [
                'name' => $data['admin']['name'],
                'email' => $data['admin']['email'],
                'password' => Hash::make('password'),
                'branch_id' => $branchId,
                'daily_base_salary' => 0,
                'payout_preference' => 'monthly',
                'nik' => $data['admin']['nik'],
                'role' => 'admin',
                'hire_date' => '2024-01-01',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // Employees
            foreach ($data['employees'] as $emp) {
                $users[] = [
                    'name' => $emp['name'],
                    'email' => strtolower(str_replace(' ', '.', $emp['name'])) . '@payrollpjbm.test',
                    'password' => Hash::make('password'),
                    'branch_id' => $branchId,
                    'daily_base_salary' => $emp['salary'],
                    'payout_preference' => 'daily',
                    'nik' => $emp['nik'],
                    'role' => 'employee',
                    'hire_date' => '2024-03-01',
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('users')->insert($users);

        $this->command->info('UserSeeder: 1 superadmin + 3 admins + 9 employees seeded.');
        $this->command->table(
        ['NIK', 'Nama', 'Cabang', 'Role', 'Gaji/Hari'],
            collect($users)->map(fn($u) => [
        $u['nik'],
        $u['name'],
        $u['branch_id'] ? $branches->search($u['branch_id']) : '-',
        $u['role'],
        number_format($u['daily_base_salary'], 0, ',', '.'),
        ])->toArray()
        );
    }
}
