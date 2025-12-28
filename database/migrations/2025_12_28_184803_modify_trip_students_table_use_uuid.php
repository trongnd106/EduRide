<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Thay đổi cột id từ string thành uuid() để Laravel tự động sinh UUID
     */
    public function up(): void
    {
        Schema::table('trip_students', function (Blueprint $table) {
            // Xóa foreign key và primary key cũ
            $table->dropPrimary(['id']);
            
            // Thay đổi cột id thành uuid
            $table->uuid('id')->change();
            
            // Thêm lại primary key
            $table->primary('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trip_students', function (Blueprint $table) {
            // Rollback: chuyển lại thành string
            $table->dropPrimary(['id']);
            $table->string('id')->change();
            $table->primary('id');
        });
    }
};
