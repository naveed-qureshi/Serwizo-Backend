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
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'visible_password')) {
            Schema::table('users', function (Blueprint $table) {
                $table->text('visible_password')->nullable()->after('password');
            });
        }

        if (Schema::hasTable('workers') && !Schema::hasColumn('workers', 'visible_password')) {
            Schema::table('workers', function (Blueprint $table) {
                $table->text('visible_password')->nullable()->after('password');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'visible_password')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('visible_password');
            });
        }

        if (Schema::hasTable('workers') && Schema::hasColumn('workers', 'visible_password')) {
            Schema::table('workers', function (Blueprint $table) {
                $table->dropColumn('visible_password');
            });
        }
    }
};
