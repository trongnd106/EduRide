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
        Schema::table('vehicles', function (Blueprint $table) {
            // Drop school_id column
            $table->dropColumn('school_id');
            
            // Add new columns
            $table->integer('type')->nullable()->after('id');
            $table->string('model', 100)->nullable()->after('brand');
            $table->string('color', 50)->nullable()->after('model');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // Restore school_id
            $table->integer('school_id')->nullable()->after('id');
            
            // Drop new columns
            $table->dropColumn(['color', 'model', 'type']);
        });
    }
};
