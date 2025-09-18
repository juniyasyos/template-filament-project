<?php

namespace App\Domain\Drive\Database\Seeders;

use App\Domain\Drive\Models\DriveNode;
use App\Domain\Drive\Models\DriveFolder;
use App\Domain\Drive\Models\DriveFile;
use App\Domain\Drive\Models\DriveTag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DriveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Make sure we have a user to work with
        $user = User::first();
        if (!$user) {
            $user = User::factory()->create([
                'name' => 'Drive User',
                'email' => 'drive@example.com',
            ]);
        }

        // Create some tags first
        $tags = $this->createTags();

        // Create root folders
        $rootFolders = $this->createRootFolders($user->id);

        // Create subfolders and files
        foreach ($rootFolders as $rootFolder) {
            $this->createSubfoldersAndFiles($rootFolder, $user->id, $tags, 2); // 2 levels deep
        }

        $this->command->info('✅ Drive seeder completed successfully!');
        $this->command->info("📊 Created folders and files for user: {$user->name}");
    }

    /**
     * Create tags for organizing files
     */
    private function createTags(): array
    {
        $tagData = [
            ['name' => 'Important', 'color' => '#EF4444'],
            ['name' => 'Work', 'color' => '#3B82F6'],
            ['name' => 'Personal', 'color' => '#10B981'],
            ['name' => 'Archive', 'color' => '#6B7280'],
            ['name' => 'Draft', 'color' => '#F59E0B'],
        ];

        $tags = [];
        foreach ($tagData as $data) {
            $tags[] = DriveTag::updateOrCreate(
                ['name' => $data['name']],
                ['color' => $data['color']]
            );
        }

        return $tags;
    }

    /**
     * Create root folders
     */
    private function createRootFolders(int $userId): array
    {
        $folderData = [
            ['name' => 'Documents', 'icon' => 'document-duplicate', 'color' => '#3B82F6'],
            ['name' => 'Images', 'icon' => 'photo', 'color' => '#10B981'],
            ['name' => 'Videos', 'icon' => 'video-camera', 'color' => '#EF4444'],
            ['name' => 'Projects', 'icon' => 'briefcase', 'color' => '#8B5CF6'],
            ['name' => 'Archive', 'icon' => 'archive-box', 'color' => '#6B7280'],
        ];

        $folders = [];

        foreach ($folderData as $data) {
            // Create drive node
            $node = DriveNode::create([
                'type' => 'folder',
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'parent_id' => null,
                'position' => count($folders),
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            // Create folder details
            DriveFolder::create([
                'drive_node_id' => $node->id,
                'icon' => $data['icon'],
                'color' => $data['color'],
            ]);

            $folders[] = $node;
        }

        return $folders;
    }

    /**
     * Create subfolders and files recursively
     */
    private function createSubfoldersAndFiles(DriveNode $parent, int $userId, array $tags, int $maxDepth): void
    {
        if ($parent->depth >= $maxDepth) {
            return;
        }

        // Create 2-4 subfolders
        $subfolderCount = rand(2, 4);
        for ($i = 0; $i < $subfolderCount; $i++) {
            $subfolder = $this->createRandomFolder($parent->id, $userId, $i);

            // Add random tags
            if (rand(1, 3) === 1) { // 33% chance
                $randomTags = collect($tags)->random(rand(1, 2));
                $subfolder->tags()->attach($randomTags->pluck('id')->toArray());
            }

            // Recursively create more subfolders
            if (rand(1, 2) === 1) { // 50% chance
                $this->createSubfoldersAndFiles($subfolder, $userId, $tags, $maxDepth);
            }
        }

        // Create 3-8 files in this folder
        $fileCount = rand(3, 8);
        for ($i = 0; $i < $fileCount; $i++) {
            $file = $this->createRandomFile($parent->id, $userId, $i);

            // Add random tags
            if (rand(1, 4) === 1) { // 25% chance
                $randomTags = collect($tags)->random(rand(1, 2));
                $file->tags()->attach($randomTags->pluck('id')->toArray());
            }
        }
    }

    /**
     * Create a random folder
     */
    private function createRandomFolder(int $parentId, int $userId, int $position): DriveNode
    {
        $folderNames = [
            'Projects', 'Resources', 'Templates', 'Backup', 'Shared',
            'Private', 'Archive', 'Work', 'Personal', 'Downloads',
            'Uploads', 'Temp', 'Reports', 'Presentations', 'Spreadsheets'
        ];

        $icons = [
            'folder', 'folder-open', 'archive-box', 'document-duplicate',
            'briefcase', 'academic-cap', 'cog', 'star', 'home'
        ];

        $colors = [
            '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
            '#EC4899', '#06B6D4', '#84CC16', null
        ];

        $name = fake()->randomElement($folderNames) . ' ' . fake()->word();

        // Create drive node
        $node = DriveNode::create([
            'type' => 'folder',
            'name' => $name,
            'slug' => Str::slug($name),
            'parent_id' => $parentId,
            'position' => $position,
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);

        // Create folder details
        DriveFolder::create([
            'drive_node_id' => $node->id,
            'icon' => fake()->randomElement($icons),
            'color' => fake()->randomElement($colors),
        ]);

        return $node;
    }

    /**
     * Create a random file with fake media
     */
    private function createRandomFile(int $parentId, int $userId, int $position): DriveNode
    {
        $fileTypes = [
            ['name' => 'document', 'ext' => 'pdf', 'mime' => 'application/pdf', 'size' => [1024, 10240]],
            ['name' => 'spreadsheet', 'ext' => 'xlsx', 'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'size' => [512, 5120]],
            ['name' => 'presentation', 'ext' => 'pptx', 'mime' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'size' => [1024, 20480]],
            ['name' => 'image', 'ext' => 'jpg', 'mime' => 'image/jpeg', 'size' => [2048, 15360]],
            ['name' => 'image', 'ext' => 'png', 'mime' => 'image/png', 'size' => [1024, 10240]],
            ['name' => 'video', 'ext' => 'mp4', 'mime' => 'video/mp4', 'size' => [10240, 102400]],
            ['name' => 'archive', 'ext' => 'zip', 'mime' => 'application/zip', 'size' => [1024, 51200]],
            ['name' => 'text', 'ext' => 'txt', 'mime' => 'text/plain', 'size' => [10, 1024]],
        ];

        $fileType = fake()->randomElement($fileTypes);
        $fileName = fake()->words(rand(1, 3), true) . '_' . fake()->date('Y-m-d') . '.' . $fileType['ext'];
        $sizeBytes = rand($fileType['size'][0], $fileType['size'][1]) * 1024;

        // Create fake media record (we won't actually store files in seeder)
        $media = Media::create([
            'model_type' => 'App\\Domain\\Drive\\Models\\DriveFile',
            'model_id' => 0, // Will be updated after DriveFile is created
            'uuid' => fake()->uuid(),
            'collection_name' => 'drive_files',
            'name' => pathinfo($fileName, PATHINFO_FILENAME),
            'file_name' => $fileName,
            'mime_type' => $fileType['mime'],
            'disk' => 'public',
            'size' => $sizeBytes,
            'manipulations' => '{}',
            'custom_properties' => '{}',
            'generated_conversions' => '{}',
            'responsive_images' => '{}',
        ]);

        // Create drive node
        $node = DriveNode::create([
            'type' => 'file',
            'name' => $fileName,
            'slug' => Str::slug(pathinfo($fileName, PATHINFO_FILENAME)),
            'parent_id' => $parentId,
            'position' => $position,
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);

        // Create file details
        DriveFile::create([
            'drive_node_id' => $node->id,
            'media_id' => $media->id,
            'mime_type' => $fileType['mime'],
            'size_bytes' => $sizeBytes,
            'checksum' => fake()->sha256(),
            'disk' => 'public',
            'visibility' => fake()->randomElement(['private', 'public']),
            'version' => 1,
        ]);

        // Update media model_id
        $media->update(['model_id' => $node->id]);

        return $node;
    }
}
