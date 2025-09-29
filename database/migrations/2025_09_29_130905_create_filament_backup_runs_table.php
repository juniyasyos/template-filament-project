<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('filament_backup_runs', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('status', 32)->index();
            $table->string('option', 32)->index();
            $table->json('disks')->nullable();
            $table->string('filename')->nullable();
            $table->string('disk')->nullable();
            $table->unsignedBigInteger('size_in_bytes')->nullable();
            $table->longText('output')->nullable();
            $table->text('exception_message')->nullable();
            $table->longText('exception_trace')->nullable();
            $table->nullableMorphs('initiator');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('filament_backup_runs');
    }
};
