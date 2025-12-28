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
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->unsignedBigInteger('driver_id')->nullable()->comment('Tài xế (drivers.position = 1)');
            $table->unsignedBigInteger('assistant_id')->nullable()->comment('Phụ xe (drivers.position = 2)');
            $table->unsignedBigInteger('vehicle_id')->nullable();
            $table->integer('total_students')->default(0)->nullable();
            $table->integer('curr_students')->default(0)->nullable();
            $table->integer('type')->default(0)->comment('0 = Đón, 1 = Trả');
            $table->integer('status')->default(0)->comment('0 = Chưa bắt đầu, 1 = Đang diễn ra, 2 = Đã hoàn thành');
            $table->datetime('start_time')->nullable();
            $table->datetime('end_time')->nullable();
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('set null');
            $table->foreign('assistant_id')->references('id')->on('drivers')->onDelete('set null');
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('set null');
            
            // Indexes
            $table->index('driver_id');
            $table->index('assistant_id');
            $table->index('vehicle_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
