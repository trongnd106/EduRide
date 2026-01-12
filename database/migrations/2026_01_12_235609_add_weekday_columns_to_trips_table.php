<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->boolean('is_mon')->default(0)->comment('Thứ 2');
            $table->boolean('is_tue')->default(0)->comment('Thứ 3');
            $table->boolean('is_wed')->default(0)->comment('Thứ 4');
            $table->boolean('is_thu')->default(0)->comment('Thứ 5');
            $table->boolean('is_fri')->default(0)->comment('Thứ 6');
            $table->boolean('is_sat')->default(0)->comment('Thứ 7');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn(['is_mon', 'is_tue', 'is_wed', 'is_thu', 'is_fri', 'is_sat']);
        });
    }
};
