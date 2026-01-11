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
        Schema::table('point_students', function (Blueprint $table) {
            // Xóa cột method
            $table->dropColumn('method');
        });

        // Đặt status là nullable
        Schema::table('point_students', function (Blueprint $table) {
            $table->integer('status')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('point_students', function (Blueprint $table) {
            // Khôi phục status không nullable
            $table->integer('status')->default(0)->nullable(false)->change();
            
            // Khôi phục cột method
            $table->integer('method')->comment('0 = Thủ công,1 = QR')->after('status');
        });
    }
};
