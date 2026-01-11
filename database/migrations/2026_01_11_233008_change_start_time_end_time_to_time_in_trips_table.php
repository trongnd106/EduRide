<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Chuyển đổi cột start_time từ datetime sang time
        DB::statement('ALTER TABLE `trips` MODIFY `start_time` TIME NULL');
        
        // Chuyển đổi cột end_time từ datetime sang time
        DB::statement('ALTER TABLE `trips` MODIFY `end_time` TIME NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Khôi phục lại cột start_time từ time sang datetime
        DB::statement('ALTER TABLE `trips` MODIFY `start_time` DATETIME NULL');
        
        // Khôi phục lại cột end_time từ time sang datetime
        DB::statement('ALTER TABLE `trips` MODIFY `end_time` DATETIME NULL');
    }
};
