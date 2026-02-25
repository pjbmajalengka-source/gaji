<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run in dependency order
        $this->call([
            BranchSeeder::class ,
            ShiftSeeder::class , // Seeds default "Shift Reguler Toko" + 7 day schedules
            UserSeeder::class , // Seeds 1 superadmin + 3 admins + 9 employees
        ]);
    }
}
