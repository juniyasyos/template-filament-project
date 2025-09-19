<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed predefined users
        $this->call([
            UserSeeder::class,
            \App\Domain\Drive\Database\Seeders\DriveSeeder::class,
        ]);

        // Optionally generate additional sample users
        // User::factory(10)->create();
    }
}
