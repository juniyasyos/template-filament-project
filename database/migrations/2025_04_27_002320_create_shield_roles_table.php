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
        Schema::create('shield_roles', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->string('name');
            $table->foreignId('team_id')->nullable();
            $table->string('created_by_name')->nullable();
            $table->json('access')->nullable();
            $table->string('guard')->default('web');
            $table->timestamps();
        });

        Schema::create('shield_role_user', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('shield_roles');
            $table->foreignId('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shield_roles');
        Schema::dropIfExists('shield_role_user');
    }
};
