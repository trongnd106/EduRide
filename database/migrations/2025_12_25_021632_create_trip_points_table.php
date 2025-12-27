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
        Schema::create('trip_points', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->unsignedBigInteger('trip_id');
            $table->unsignedBigInteger('point_id');
            $table->integer('order')->nullable()->comment('Thứ tự của điểm trong chuyến');
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('trip_id')->references('id')->on('trips')->onDelete('cascade');
            $table->foreign('point_id')->references('id')->on('points')->onDelete('cascade');
            
            // Indexes
            $table->index('trip_id');
            $table->index('point_id');
            
            // Unique constraint: một điểm chỉ có thể xuất hiện một lần trong một chuyến
            $table->unique(['trip_id', 'point_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_points');
    }
};
