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
        DB::statement("ALTER TABLE `trip_students` MODIFY `status` INT DEFAULT 0 COMMENT '-1=Chưa lên xe, 0=Đang trên xe, 1=Đã xuống xe'");

        Schema::table('trip_students', function (Blueprint $table) {
            $table->integer('check_in')->default(0)->comment('0=chưa điểm danh, 1=đã điểm danh')->after('status');
        });

        Schema::table('trip_students', function (Blueprint $table) {
            $table->integer('method')->nullable()->comment('0=thủ công, 1=qr')->after('check_in');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trip_students', function (Blueprint $table) {
            $table->dropColumn('method');
            $table->dropColumn('check_in');
        });

        DB::statement("ALTER TABLE `trip_students` MODIFY `status` INT DEFAULT 0 COMMENT '-1=Chưa lên xe, 0=Đang trên xe, 1=Đã xuống xe'");
    }
};
