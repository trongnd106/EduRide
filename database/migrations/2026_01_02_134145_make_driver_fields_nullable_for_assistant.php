<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Use DB::statement to modify columns and handle unique constraint
        // MySQL allows multiple NULL values in unique columns
        DB::statement('ALTER TABLE `drivers` MODIFY `cccd` VARCHAR(20) NULL');
        DB::statement('ALTER TABLE `drivers` MODIFY `gender` INT NULL');
        DB::statement('ALTER TABLE `drivers` MODIFY `license_number` VARCHAR(50) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert columns to not nullable
        // Note: This will fail if there are NULL values in the columns
        DB::statement('ALTER TABLE `drivers` MODIFY `cccd` VARCHAR(20) NOT NULL');
        DB::statement('ALTER TABLE `drivers` MODIFY `gender` INT NOT NULL');
        DB::statement('ALTER TABLE `drivers` MODIFY `license_number` VARCHAR(50) NOT NULL');
    }
};
