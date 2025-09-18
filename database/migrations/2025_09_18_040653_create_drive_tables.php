<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // === 1) Core drive_nodes table ===
        Schema::create('drive_nodes', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['folder', 'file'])->index();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('path', 2048); // materialized path: "/1/5/18/"
            $table->smallInteger('depth')->default(0)->index();
            $table->integer('position')->default(0);
            $table->boolean('is_trashed')->default(false)->index();
            $table->timestamp('trashed_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('parent_id');
            $table->unique(['parent_id', 'name', 'is_trashed'], 'uq_parent_name_trash');

            // Foreign keys
            $table->foreign('parent_id')->references('id')->on('drive_nodes')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });

        // Add prefix index for path column after table creation
        DB::statement('CREATE INDEX idx_path_prefix ON drive_nodes (path(255))');

        // === 2) Drive folders details ===
        Schema::create('drive_folders', function (Blueprint $table) {
            $table->unsignedBigInteger('drive_node_id')->primary();
            $table->unsignedBigInteger('cover_media_id')->nullable();
            $table->string('color', 24)->nullable();
            $table->string('icon', 64)->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('drive_node_id')->references('id')->on('drive_nodes')->onDelete('cascade');
            $table->foreign('cover_media_id')->references('id')->on('media')->onDelete('set null');
        });

        // === 3) Drive files details ===
        Schema::create('drive_files', function (Blueprint $table) {
            $table->unsignedBigInteger('drive_node_id')->primary();
            $table->unsignedBigInteger('media_id')->nullable();
            $table->string('mime_type');
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('checksum', 128)->nullable()->index();
            $table->string('disk', 64);
            $table->enum('visibility', ['private', 'public'])->default('private');
            $table->integer('version')->default(1);
            $table->timestamps();

            // Indexes
            $table->index('media_id');

            // Foreign keys
            $table->foreign('drive_node_id')->references('id')->on('drive_nodes')->onDelete('cascade');
            $table->foreign('media_id')->references('id')->on('media')->onDelete('cascade');
        });

        // === 4) Favorites ===
        Schema::create('drive_favorites', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('drive_node_id');
            $table->timestamp('created_at')->nullable();

            // Indexes
            $table->unique(['user_id', 'drive_node_id'], 'uq_user_node');
            $table->index('drive_node_id', 'idx_fav_node');

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('drive_node_id')->references('id')->on('drive_nodes')->onDelete('cascade');
        });

        // === 5) Tags ===
        Schema::create('drive_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name', 64)->unique();
            $table->string('color', 24)->nullable();
            $table->timestamps();
        });

        // === 6) Node Tag pivot ===
        Schema::create('drive_node_tag', function (Blueprint $table) {
            $table->unsignedBigInteger('drive_node_id');
            $table->unsignedBigInteger('tag_id');

            $table->primary(['drive_node_id', 'tag_id']);

            // Foreign keys
            $table->foreign('drive_node_id')->references('id')->on('drive_nodes')->onDelete('cascade');
            $table->foreign('tag_id')->references('id')->on('drive_tags')->onDelete('cascade');
        });

        // === 7) Activities ===
        Schema::create('drive_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('drive_node_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->enum('action', ['create', 'rename', 'move', 'copy', 'delete', 'restore', 'upload', 'download', 'favorite', 'unfavorite']);
            $table->json('meta')->nullable();
            $table->timestamp('created_at')->nullable();

            // Indexes
            $table->index('drive_node_id', 'idx_act_node');
            $table->index('user_id', 'idx_act_user');
            $table->index(['action', 'created_at'], 'idx_action_created');

            // Foreign keys
            $table->foreign('drive_node_id')->references('id')->on('drive_nodes')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        // === 8) Attachables (polymorphic root anchor) ===
        Schema::create('drive_attachables', function (Blueprint $table) {
            $table->id();
            $table->string('attachable_type', 191);
            $table->unsignedBigInteger('attachable_id');
            $table->unsignedBigInteger('root_node_id');

            // Indexes
            $table->unique(['attachable_type', 'attachable_id'], 'uq_attachable');
            $table->index('root_node_id', 'idx_attach_root');

            // Foreign keys
            $table->foreign('root_node_id')->references('id')->on('drive_nodes')->onDelete('cascade');
        });

        // === 9) Optional: Closure table for heavy subtree operations ===
        Schema::create('drive_node_paths', function (Blueprint $table) {
            $table->unsignedBigInteger('ancestor_id');
            $table->unsignedBigInteger('descendant_id');
            $table->smallInteger('depth'); // 0 = self

            $table->primary(['ancestor_id', 'descendant_id']);
            $table->index(['descendant_id', 'depth'], 'idx_desc_depth');

            // Foreign keys
            $table->foreign('ancestor_id')->references('id')->on('drive_nodes')->onDelete('cascade');
            $table->foreign('descendant_id')->references('id')->on('drive_nodes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drive_node_paths');
        Schema::dropIfExists('drive_attachables');
        Schema::dropIfExists('drive_activities');
        Schema::dropIfExists('drive_node_tag');
        Schema::dropIfExists('drive_tags');
        Schema::dropIfExists('drive_favorites');
        Schema::dropIfExists('drive_files');
        Schema::dropIfExists('drive_folders');
        Schema::dropIfExists('drive_nodes');
    }
};
