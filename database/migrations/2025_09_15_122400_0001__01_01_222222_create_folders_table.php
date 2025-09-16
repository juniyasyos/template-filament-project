<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('folders', function (Blueprint $table) {
            $table->id();

            // Hierarchy support
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->string('path')->nullable()->index(); // For faster hierarchy queries
            $table->unsignedInteger('depth')->default(0)->index(); // Folder depth level

            //Morph
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();

            //Folder
            $table->string('name')->index();
            $table->string('collection')->nullable()->index();
            $table->string('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->nullable();

            //Options
            $table->boolean('is_protected')->default(false)->nullable();
            $table->string('password')->nullable();
            $table->boolean('is_hidden')->default(false)->nullable();
            $table->boolean('is_favorite')->default(false)->nullable();

            $table->timestamps();

            // Foreign key constraint for hierarchy
            $table->foreign('parent_id')->references('id')->on('folders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('folders');
    }
};
