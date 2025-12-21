<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncate tables
        DB::table('parents')->truncate();
        DB::table('vehicles')->truncate();
        DB::table('drivers')->truncate();
        DB::table('students')->truncate();
        DB::table('schools')->truncate();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Run seeders
        $this->call([
            SchoolSeeder::class,
            StudentSeeder::class,
            DriverSeeder::class,
            VehicleSeeder::class,
            StudentParentSeeder::class,
        ]);
    }
}
