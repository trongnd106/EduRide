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
        Schema::table('drivers', function (Blueprint $table) {
            // Drop license_expiry column
            $table->dropColumn('license_expiry');
            
            // Drop dob column and add age column
            $table->dropColumn('dob');
            $table->integer('age')->nullable()->after('gender');
            
            // Add new columns
            $table->string('email')->nullable()->after('phone');
            $table->text('address')->nullable()->after('email');
            $table->string('image_url')->nullable()->after('address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            // Restore license_expiry
            $table->date('license_expiry')->after('license_number');
            
            // Restore dob and drop age
            $table->dropColumn('age');
            $table->date('dob')->nullable()->after('gender');
            
            // Drop new columns
            $table->dropColumn(['image_url', 'address', 'email']);
        });
    }
};
