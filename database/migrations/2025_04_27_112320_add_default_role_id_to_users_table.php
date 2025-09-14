<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'default_role_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('default_role_id')->nullable()->after('password');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'default_role_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('default_role_id');
            });
        }
    }
};

