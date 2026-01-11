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
            $table->dropIndex(['trip_id', 'point_id', 'status']);
            $table->dropIndex(['point_id', 'status']);
            $table->dropIndex(['status']);
        });

        Schema::table('point_students', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('point_students', function (Blueprint $table) {
            $table->integer('status')->default(0)->comment('0 = Chưa điểm danh, 1 = Đã điểm danh')->after('type');
        });

        Schema::table('point_students', function (Blueprint $table) {
            $table->index('status');
            $table->index(['point_id', 'status']);
            $table->index(['trip_id', 'point_id', 'status']);
        });
    }
};
