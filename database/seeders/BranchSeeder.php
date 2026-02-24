<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchSeeder extends Seeder
{
    /**
     * Seed the 3 retail branches.
     *
     * Code (short ID) is used by the fingerprint microservice to identify
     * which branch an attendance record belongs to. Keep it stable.
     */
    public function run(): void
    {
        $branches = [
            [
                'name' => 'Majalengka',
                'code' => 'MJL',
                'address' => 'Majalengka, Jawa Barat',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Jatitujuh',
                'code' => 'JTJ',
                'address' => 'Jatitujuh, Majalengka, Jawa Barat',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Tasikmalaya',
                'code' => 'TSM',
                'address' => 'Tasikmalaya, Jawa Barat',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // upsert: safe to re-run without duplicates
        DB::table('branches')->upsert(
            $branches,
        ['code'], // unique key to check against
        ['name', 'address', 'is_active', 'updated_at'] // columns to update if exists
        );
    }
}
