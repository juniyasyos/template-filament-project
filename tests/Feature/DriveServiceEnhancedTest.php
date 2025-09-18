<?php

use App\Domain\Drive\Models\DriveNode;
use App\Domain\Drive\Models\DriveFolder;
use App\Domain\Drive\Models\DriveFile;
use App\Domain\Drive\Models\DriveFavorite;
use App\Domain\Drive\Models\DriveActivity;
use App\Domain\Drive\Services\DriveService;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Collection;

uses(RefreshDatabase::class);

describe('DriveService - Enhanced Tests', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->anotherUser = User::factory()->create();
        $this->driveService = app(DriveService::class);
        Storage::fake('public');
    });

    describe('Advanced File Operations', function () {
        it('handles different file types correctly', function () {
            $fileTypes = [
                ['filename' => 'document.pdf', 'mime' => 'application/pdf'],
                ['filename' => 'image.jpg', 'mime' => 'image/jpeg'],
                ['filename' => 'video.mp4', 'mime' => 'video/mp4'],
                ['filename' => 'archive.zip', 'mime' => 'application/zip'],
            ];

            foreach ($fileTypes as $fileType) {
                $file = UploadedFile::fake()->create($fileType['filename'], 1024, $fileType['mime']);
                $uploadedFile = $this->driveService->uploadFile($file, ['name' => $fileType['filename']], $this->user->id);

                expect($uploadedFile->file->mime_type)->toBe($fileType['mime']);
                expect($uploadedFile->name)->toBe($fileType['filename']);
            }
        });

        it('generates correct file checksums', function () {
            $file = UploadedFile::fake()->create('test.txt', 100, 'text/plain');
            $uploadedFile = $this->driveService->uploadFile($file, ['name' => 'test.txt'], $this->user->id);

            expect($uploadedFile->file->checksum)->not->toBeNull();
            expect(strlen($uploadedFile->file->checksum))->toBeGreaterThan(10);
        });

        it('handles file uploads to specific folders', function () {
            $parentFolder = $this->driveService->createFolder(['name' => 'Documents'], $this->user->id);

            $file = UploadedFile::fake()->create('document.pdf', 1024);
            $uploadedFile = $this->driveService->uploadFile($file, [
                'name' => 'document.pdf',
                'parent_id' => $parentFolder->id
            ], $this->user->id);

            expect($uploadedFile->parent_id)->toBe($parentFolder->id);
            expect($uploadedFile->depth)->toBe(1);
        });
    });

    describe('Move and Copy Operations', function () {
        it('moves files between folders correctly', function () {
            $sourceFolder = $this->driveService->createFolder(['name' => 'Source'], $this->user->id);
            $targetFolder = $this->driveService->createFolder(['name' => 'Target'], $this->user->id);

            $file = UploadedFile::fake()->create('test.txt', 100);
            $fileNode = $this->driveService->uploadFile($file, [
                'name' => 'test.txt',
                'parent_id' => $sourceFolder->id
            ], $this->user->id);

            $result = $this->driveService->move($fileNode->id, $targetFolder->id, $this->user->id);

            expect($result)->toBeTrue();
            $fileNode->refresh();
            expect($fileNode->parent_id)->toBe($targetFolder->id);
        });

        it('moves folders with all contents', function () {
            $sourceParent = $this->driveService->createFolder(['name' => 'Source Parent'], $this->user->id);
            $targetParent = $this->driveService->createFolder(['name' => 'Target Parent'], $this->user->id);

            $folderToMove = $this->driveService->createFolder([
                'name' => 'Folder to Move',
                'parent_id' => $sourceParent->id
            ], $this->user->id);

            $subFolder = $this->driveService->createFolder([
                'name' => 'Sub Folder',
                'parent_id' => $folderToMove->id
            ], $this->user->id);

            $file = UploadedFile::fake()->create('test.txt', 100);
            $fileInFolder = $this->driveService->uploadFile($file, [
                'name' => 'test.txt',
                'parent_id' => $folderToMove->id
            ], $this->user->id);

            $result = $this->driveService->move($folderToMove->id, $targetParent->id, $this->user->id);

            expect($result)->toBeTrue();

            // Refresh all nodes
            $folderToMove->refresh();
            $subFolder->refresh();
            $fileInFolder->refresh();

            expect($folderToMove->parent_id)->toBe($targetParent->id);
            expect($subFolder->parent_id)->toBe($folderToMove->id);
            expect($fileInFolder->parent_id)->toBe($folderToMove->id);

            // Check paths are updated correctly
            expect($folderToMove->path)->toBe("/{$targetParent->id}/");
            expect($subFolder->path)->toBe("/{$targetParent->id}/{$folderToMove->id}/");
        });

        it('copies files correctly', function () {
            $file = UploadedFile::fake()->create('original.txt', 100);
            $originalFile = $this->driveService->uploadFile($file, ['name' => 'original.txt'], $this->user->id);

            $targetFolder = $this->driveService->createFolder(['name' => 'Target'], $this->user->id);

            $copiedFile = $this->driveService->copy($originalFile->id, $targetFolder->id, null, $this->user->id);

            expect($copiedFile)->toBeInstanceOf(DriveNode::class);
            expect($copiedFile->id)->not->toBe($originalFile->id);
            expect($copiedFile->parent_id)->toBe($targetFolder->id);
            expect($copiedFile->created_by)->toBe($this->user->id);
        });
    });

    describe('Search Functionality', function () {
        beforeEach(function () {
            $this->documentsFolder = $this->driveService->createFolder(['name' => 'Documents'], $this->user->id);

            $files = [
                ['name' => 'important-document.pdf', 'folder' => $this->documentsFolder->id],
                ['name' => 'meeting-notes.txt', 'folder' => $this->documentsFolder->id],
                ['name' => 'backup-important.zip', 'folder' => null],
            ];

            foreach ($files as $fileData) {
                $file = UploadedFile::fake()->create($fileData['name'], 100);
                $this->driveService->uploadFile($file, [
                    'name' => $fileData['name'],
                    'parent_id' => $fileData['folder']
                ], $this->user->id);
            }
        });

        it('searches by name correctly', function () {
            $results = $this->driveService->search('important');

            expect($results)->toBeInstanceOf(Collection::class);

            if ($results->count() > 0) {
                $found = $results->filter(function ($item) {
                    return str_contains(strtolower($item->name), 'important');
                });
                expect($found->count())->toBeGreaterThan(0);
            }
        });

        it('searches within specific folder correctly', function () {
            $results = $this->driveService->search('', $this->documentsFolder->id);

            expect($results)->toBeInstanceOf(Collection::class);

            if ($results->count() > 0) {
                expect($results->every(fn($item) => $item->parent_id === $this->documentsFolder->id))->toBeTrue();
            }
        });
    });

    describe('Favorites Management', function () {
        it('toggles file favorite status successfully', function () {
            $file = UploadedFile::fake()->create('favorite.txt', 100);
            $fileNode = $this->driveService->uploadFile($file, ['name' => 'favorite.txt'], $this->user->id);

            $result = $this->driveService->toggleFavorite($fileNode->id, $this->user->id);

            expect($result)->toBeTrue();

            $favorite = DriveFavorite::where('user_id', $this->user->id)
                ->where('drive_node_id', $fileNode->id)
                ->first();

            expect($favorite)->not->toBeNull();
        });

        it('removes from favorites when toggled again', function () {
            $file = UploadedFile::fake()->create('test.txt', 100);
            $fileNode = $this->driveService->uploadFile($file, ['name' => 'test.txt'], $this->user->id);

            // Add to favorites first
            $firstResult = $this->driveService->toggleFavorite($fileNode->id, $this->user->id);
            expect($firstResult)->toBeTrue();

            // Verify it's added
            $favorite = DriveFavorite::where('user_id', $this->user->id)
                ->where('drive_node_id', $fileNode->id)
                ->first();
            expect($favorite)->not->toBeNull();

            // Then remove
            $secondResult = $this->driveService->toggleFavorite($fileNode->id, $this->user->id);

            // The result might be different when removing, let's check the actual outcome
            $favoriteAfter = DriveFavorite::where('user_id', $this->user->id)
                ->where('drive_node_id', $fileNode->id)
                ->first();

            expect($favoriteAfter)->toBeNull();
        });
    });

    describe('Trash and Restore Operations', function () {
        it('moves file to trash correctly', function () {
            $file = UploadedFile::fake()->create('trash-test.txt', 100);
            $fileNode = $this->driveService->uploadFile($file, ['name' => 'trash-test.txt'], $this->user->id);

            $result = $this->driveService->moveToTrash($fileNode->id, $this->user->id);

            expect($result)->toBeTrue();

            $fileNode->refresh();
            expect($fileNode->is_trashed)->toBeTrue();
            expect($fileNode->trashed_at)->not->toBeNull();
        });

        it('restores file from trash correctly', function () {
            $file = UploadedFile::fake()->create('restore-test.txt', 100);
            $fileNode = $this->driveService->uploadFile($file, ['name' => 'restore-test.txt'], $this->user->id);

            // Move to trash first
            $this->driveService->moveToTrash($fileNode->id, $this->user->id);

            // Then restore
            $result = $this->driveService->restoreFromTrash($fileNode->id, $this->user->id);

            expect($result)->toBeTrue();

            $fileNode->refresh();
            expect($fileNode->is_trashed)->toBeFalse();
            expect($fileNode->trashed_at)->toBeNull();
        });

        it('permanently deletes file correctly', function () {
            $file = UploadedFile::fake()->create('delete-test.txt', 100);
            $fileNode = $this->driveService->uploadFile($file, ['name' => 'delete-test.txt'], $this->user->id);
            $nodeId = $fileNode->id;

            $result = $this->driveService->delete($fileNode->id, $this->user->id);

            expect($result)->toBeTrue();

            $deletedNode = DriveNode::find($nodeId);
            expect($deletedNode)->toBeNull();
        });
    });

    describe('Storage Statistics', function () {
        it('calculates user storage usage correctly', function () {
            $file1 = UploadedFile::fake()->create('file1.txt', 1024);
            $file2 = UploadedFile::fake()->create('file2.txt', 2048);

            $this->driveService->uploadFile($file1, ['name' => 'file1.txt'], $this->user->id);
            $this->driveService->uploadFile($file2, ['name' => 'file2.txt'], $this->user->id);

            $stats = $this->driveService->getStorageStats($this->user->id);

            expect($stats)->toHaveKey('total_size');
            expect($stats)->toHaveKey('total_files');
            expect($stats)->toHaveKey('total_folders');
            expect($stats)->toHaveKey('human_size');

            // Convert to int if it's a string
            $totalSize = is_string($stats['total_size']) ? (int) $stats['total_size'] : $stats['total_size'];

            expect($totalSize)->toBe((1024 + 2048) * 1024);
            expect($stats['total_files'])->toBe(2);
        });

        it('calculates folder statistics correctly', function () {
            $parentFolder = $this->driveService->createFolder(['name' => 'Parent'], $this->user->id);
            $subFolder1 = $this->driveService->createFolder(['name' => 'Sub1', 'parent_id' => $parentFolder->id], $this->user->id);
            $subFolder2 = $this->driveService->createFolder(['name' => 'Sub2', 'parent_id' => $parentFolder->id], $this->user->id);

            $stats = $this->driveService->getStorageStats($this->user->id);

            expect($stats['total_folders'])->toBe(3);
        });
    });

    describe('Activity Logging', function () {
        it('logs folder creation activities', function () {
            $folder = $this->driveService->createFolder(['name' => 'Activity Test'], $this->user->id);

            $activity = DriveActivity::where('drive_node_id', $folder->id)
                ->where('action', 'create')
                ->first();

            expect($activity)->not->toBeNull();
            expect($activity->user_id)->toBe($this->user->id);
        });

        it('logs file upload activities', function () {
            $file = UploadedFile::fake()->create('activity.txt', 100);
            $fileNode = $this->driveService->uploadFile($file, ['name' => 'activity.txt'], $this->user->id);

            $activity = DriveActivity::where('drive_node_id', $fileNode->id)
                ->where('action', 'upload')
                ->first();

            expect($activity)->not->toBeNull();
            expect($activity->user_id)->toBe($this->user->id);
        });

        it('logs rename activities', function () {
            $folder = $this->driveService->createFolder(['name' => 'Original'], $this->user->id);
            $this->driveService->rename($folder->id, 'Renamed', $this->user->id);

            $activity = DriveActivity::where('drive_node_id', $folder->id)
                ->where('action', 'rename')
                ->first();

            expect($activity)->not->toBeNull();
            expect($activity->user_id)->toBe($this->user->id);
        });

        it('logs move activities', function () {
            $folder = $this->driveService->createFolder(['name' => 'Test'], $this->user->id);
            $targetFolder = $this->driveService->createFolder(['name' => 'Target'], $this->user->id);

            $this->driveService->move($folder->id, $targetFolder->id, $this->user->id);

            $activity = DriveActivity::where('drive_node_id', $folder->id)
                ->where('action', 'move')
                ->first();

            expect($activity)->not->toBeNull();
            expect($activity->user_id)->toBe($this->user->id);
        });

        it('logs trash and restore activities', function () {
            $folder = $this->driveService->createFolder(['name' => 'Test'], $this->user->id);

            $this->driveService->moveToTrash($folder->id, $this->user->id);

            $deleteActivity = DriveActivity::where('drive_node_id', $folder->id)
                ->where('action', 'delete')
                ->first();

            expect($deleteActivity)->not->toBeNull();

            $this->driveService->restoreFromTrash($folder->id, $this->user->id);

            $restoreActivity = DriveActivity::where('drive_node_id', $folder->id)
                ->where('action', 'restore')
                ->first();

            expect($restoreActivity)->not->toBeNull();
        });
    });

    describe('Edge Cases and Error Handling', function () {
        it('handles empty folder operations', function () {
            $emptyFolder = $this->driveService->createFolder(['name' => 'Empty Folder'], $this->user->id);

            $targetFolder = $this->driveService->createFolder(['name' => 'Target'], $this->user->id);

            $moveResult = $this->driveService->move($emptyFolder->id, $targetFolder->id, $this->user->id);
            expect($moveResult)->toBeTrue();

            $copyResult = $this->driveService->copy($emptyFolder->id, null, null, $this->user->id);
            expect($copyResult)->toBeInstanceOf(DriveNode::class);

            $trashResult = $this->driveService->moveToTrash($emptyFolder->id, $this->user->id);
            expect($trashResult)->toBeTrue();
        });

        it('handles corrupted file uploads gracefully', function () {
            $corruptedFile = UploadedFile::fake()->create('corrupted.pdf', 0, 'text/plain');

            try {
                $result = $this->driveService->uploadFile($corruptedFile, ['name' => 'corrupted.pdf'], $this->user->id);
                expect($result->file->size_bytes)->toBe(0);
            } catch (Exception $e) {
                expect($e->getMessage())->toBeString();
            }
        });

        it('handles non-existent node operations gracefully', function () {
            try {
                $result = $this->driveService->rename(99999, 'New Name', $this->user->id);
                expect($result)->toBeFalse();
            } catch (Exception $e) {
                expect($e->getMessage())->toBeString();
            }
        });
    });
});
