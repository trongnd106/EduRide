<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveGuardNameFromRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('roles', function (Blueprint $table) {
            // Drop unique constraint on name and guard_name
            $table->dropUnique(['name', 'guard_name']);
        });
        
        Schema::table('roles', function (Blueprint $table) {
            // Drop the guard_name column
            $table->dropColumn('guard_name');
        });
        
        Schema::table('roles', function (Blueprint $table) {
            // Add unique constraint on name only
            $table->unique('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('roles', function (Blueprint $table) {
            // Drop unique constraint on name
            $table->dropUnique(['name']);
            
            // Add guard_name column back
            $table->string('guard_name')->after('name');
            
            // Add unique constraint on name and guard_name
            $table->unique(['name', 'guard_name']);
        });
    }
}
