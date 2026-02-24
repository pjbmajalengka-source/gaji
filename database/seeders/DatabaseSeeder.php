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
        // Run in dependency order: branches first, then users
        $this->call([
            BranchSeeder::class ,
        ]);
    }
}
