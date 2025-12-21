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
        Schema::table('students', function (Blueprint $table) {
            // Drop school_id column
            $table->dropColumn('school_id');
            
            // Add student_parent_id column
            $table->integer('student_parent_id')->nullable()->after('id');
            
            // Add latitude and longitude columns for GPS coordinates
            $table->decimal('latitude', 10, 8)->nullable()->after('address');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Restore school_id
            $table->integer('school_id')->nullable()->after('id');
            
            // Drop new columns
            $table->dropColumn(['longitude', 'latitude', 'student_parent_id']);
        });
    }
};
