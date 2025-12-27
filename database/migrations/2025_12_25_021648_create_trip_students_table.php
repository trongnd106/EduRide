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
        Schema::create('trip_students', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->unsignedBigInteger('trip_id');
            $table->integer('student_id');
            $table->integer('status')->default(0)->comment('0 = Chưa điểm danh, 1 = Đã điểm danh');
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('trip_id')->references('id')->on('trips')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            
            // Indexes
            $table->index('trip_id');
            $table->index('student_id');
            $table->index('status');
            
            // Unique constraint: một học sinh chỉ có thể xuất hiện một lần trong một chuyến
            $table->unique(['trip_id', 'student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_students');
    }
};
