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
        Schema::table('users', function (Blueprint $table) {
            // Drop email_verified_at column
            $table->dropColumn('email_verified_at');
            
            // Add status column
            $table->integer('status')->default(0)->after('password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Restore email_verified_at
            $table->timestamp('email_verified_at')->nullable()->after('email');
            
            // Drop status column
            $table->dropColumn('status');
        });
    }
};
