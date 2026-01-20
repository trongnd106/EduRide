<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ✅ Create notifications table
        Schema::create('notifications', function (Blueprint $table) {
            // Primary key
            $table->id();

            // Foreign key to users table
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');

            // Notification content
            $table->string('title');
            $table->text('body');
            $table->string('type');
            $table->json('data')->nullable();
            $table->string('image_url')->nullable();

            // Read status
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // ✅ Indexes
            $table->index(['user_id', 'is_read']);
            $table->index(['user_id', 'type']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
