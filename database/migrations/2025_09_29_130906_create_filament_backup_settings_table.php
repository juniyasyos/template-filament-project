<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('filament_backup_settings', function (Blueprint $table): void {
            $table->id();
            $table->boolean('enabled')->default(true);
            $table->boolean('allow_manual_runs')->default(true);
            $table->boolean('require_password')->default(false);
            $table->string('password')->nullable();
            $table->boolean('encrypt_backups')->default(false);
            $table->string('encryption_password')->nullable();
            $table->boolean('use_queue')->default(true);
            $table->string('queue')->nullable();
            $table->string('notification_channel')->nullable();
            $table->json('notification_targets')->nullable();
            $table->boolean('scheduled')->default(false);
            $table->string('schedule_cron')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->timestamp('last_run_at')->nullable();
            $table->unsignedInteger('retention_days')->nullable();
            $table->unsignedInteger('retention_copies')->nullable();
            $table->json('allowed_disks')->nullable();
            $table->json('options')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('filament_backup_settings');
    }
};
