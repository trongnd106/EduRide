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
        Schema::create('students', function (Blueprint $table) {
            $table->integer('id')->unique()->autoIncrement();
            $table->integer('school_id')->nullable();
            $table->string('student_number')->unique();
            $table->string('email')->nullable();
            $table->string('full_name');
            $table->string('phone')->nullable();
            $table->boolean('gender');
            $table->date('dob');
            $table->integer('grade')->nullable();
            $table->integer('status')->default(0);
            $table->text('address')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
