<?php

use App\Domain\Drive\Models\DriveNode;
use App\Domain\Drive\Models\DriveFolder;
use App\Domain\Drive\Models\DriveFile;
use App\Domain\Drive\Services\DriveService;
use App\Domain\Drive\Repositories\DriveNodeRepositoryInterface;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('DriveService', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->driveService = app(DriveService::class);
    });

    describe('createFolder', function () {
        it('can create a folder successfully', function () {
            $folderData = [
                'name' => 'Test Folder',
                'parent_id' => null,
                'color' => '#3B82F6',
                'icon' => 'folder',
            ];

            $folder = $this->driveService->createFolder($folderData, $this->user->id);

            expect($folder)->toBeInstanceOf(DriveNode::class);
            expect($folder->type)->toBe('folder');
            expect($folder->name)->toBe('Test Folder');
            expect($folder->created_by)->toBe($this->user->id);
            expect($folder->folder)->not->toBeNull();
            expect($folder->folder->color)->toBe('#3B82F6');
            expect($folder->folder->icon)->toBe('folder');
        });

        it('can create a subfolder', function () {
            // Create parent folder first
            $parentFolder = $this->driveService->createFolder([
                'name' => 'Parent Folder',
                'parent_id' => null,
            ], $this->user->id);

            // Create subfolder
            $subfolderData = [
                'name' => 'Sub Folder',
                'parent_id' => $parentFolder->id,
            ];

            $subfolder = $this->driveService->createFolder($subfolderData, $this->user->id);

            expect($subfolder->parent_id)->toBe($parentFolder->id);
            expect($subfolder->depth)->toBe(1);
            expect($subfolder->path)->toBe("/{$parentFolder->id}/");
        });
    });

    describe('uploadFile', function () {
        it('can upload a file successfully', function () {
            Storage::fake('public');

            $file = UploadedFile::fake()->create('test.pdf', 1024, 'application/pdf');
            $fileData = [
                'name' => 'Test Document.pdf',
                'parent_id' => null,
                'visibility' => 'private',
            ];

            $uploadedFile = $this->driveService->uploadFile($file, $fileData, $this->user->id);

            expect($uploadedFile)->toBeInstanceOf(DriveNode::class);
            expect($uploadedFile->type)->toBe('file');
            expect($uploadedFile->name)->toBe('Test Document.pdf');
            expect($uploadedFile->created_by)->toBe($this->user->id);
            expect($uploadedFile->file)->not->toBeNull();
            expect($uploadedFile->file->mime_type)->toBe('application/pdf');
            expect($uploadedFile->file->visibility)->toBe('private');
        });
    });

    describe('rename', function () {
        it('can rename a node successfully', function () {
            $folder = $this->driveService->createFolder([
                'name' => 'Original Name',
            ], $this->user->id);

            $result = $this->driveService->rename($folder->id, 'New Name', $this->user->id);

            expect($result)->toBeTrue();

            $folder->refresh();
            expect($folder->name)->toBe('New Name');
            expect($folder->updated_by)->toBe($this->user->id);
        });
    });

    describe('move', function () {
        it('can move a node to new parent', function () {
            $parentFolder = $this->driveService->createFolder([
                'name' => 'Parent Folder',
            ], $this->user->id);

            $targetFolder = $this->driveService->createFolder([
                'name' => 'Target Folder',
            ], $this->user->id);

            $childFolder = $this->driveService->createFolder([
                'name' => 'Child Folder',
                'parent_id' => $parentFolder->id,
            ], $this->user->id);

            $result = $this->driveService->move($childFolder->id, $targetFolder->id, $this->user->id);

            expect($result)->toBeTrue();

            $childFolder->refresh();
            expect($childFolder->parent_id)->toBe($targetFolder->id);
            expect($childFolder->path)->toBe("/{$targetFolder->id}/");
        });

        it('prevents moving a folder into its own subtree', function () {
            $parentFolder = $this->driveService->createFolder([
                'name' => 'Parent Folder',
            ], $this->user->id);

            $childFolder = $this->driveService->createFolder([
                'name' => 'Child Folder',
                'parent_id' => $parentFolder->id,
            ], $this->user->id);

            // Try to move parent into its own child (should fail)
            $result = $this->driveService->move($parentFolder->id, $childFolder->id, $this->user->id);

            expect($result)->toBeFalse();
        });
    });

    describe('copy', function () {
        it('can copy a folder', function () {
            $originalFolder = $this->driveService->createFolder([
                'name' => 'Original Folder',
                'color' => '#10B981',
                'icon' => 'star',
            ], $this->user->id);

            $targetFolder = $this->driveService->createFolder([
                'name' => 'Target Folder',
            ], $this->user->id);

            $copiedFolder = $this->driveService->copy(
                $originalFolder->id,
                $targetFolder->id,
                'Copied Folder',
                $this->user->id
            );

            expect($copiedFolder)->toBeInstanceOf(DriveNode::class);
            expect($copiedFolder->name)->toBe('Copied Folder');
            expect($copiedFolder->parent_id)->toBe($targetFolder->id);
            expect($copiedFolder->type)->toBe('folder');

            $copiedFolder->load('folder');
            expect($copiedFolder->folder->color)->toBe('#10B981');
            expect($copiedFolder->folder->icon)->toBe('star');
        });
    });

    describe('moveToTrash and restoreFromTrash', function () {
        it('can move a node to trash and restore it', function () {
            $folder = $this->driveService->createFolder([
                'name' => 'Test Folder',
            ], $this->user->id);

            // Move to trash
            $result = $this->driveService->moveToTrash($folder->id, $this->user->id);
            expect($result)->toBeTrue();

            $folder->refresh();
            expect($folder->is_trashed)->toBeTrue();
            expect($folder->trashed_at)->not->toBeNull();

            // Restore from trash
            $result = $this->driveService->restoreFromTrash($folder->id, $this->user->id);
            expect($result)->toBeTrue();

            $folder->refresh();
            expect($folder->is_trashed)->toBeFalse();
            expect($folder->trashed_at)->toBeNull();
        });
    });

    describe('search', function () {
        it('can search nodes by name', function () {
            $folder1 = $this->driveService->createFolder([
                'name' => 'Important Documents',
            ], $this->user->id);

            $folder2 = $this->driveService->createFolder([
                'name' => 'Random Folder',
            ], $this->user->id);

            $results = $this->driveService->search('Important');

            expect($results)->toHaveCount(1);
            expect($results->first()->id)->toBe($folder1->id);
        });
    });

    describe('toggleFavorite', function () {
        it('can toggle favorite status', function () {
            $folder = $this->driveService->createFolder([
                'name' => 'Test Folder',
            ], $this->user->id);

            // Add to favorites
            $result = $this->driveService->toggleFavorite($folder->id, $this->user->id);
            expect($result)->toBeTrue();
            expect($folder->favorites()->where('user_id', $this->user->id)->exists())->toBeTrue();

            // Remove from favorites
            $result = $this->driveService->toggleFavorite($folder->id, $this->user->id);
            expect($result)->toBeFalse();
            expect($folder->favorites()->where('user_id', $this->user->id)->exists())->toBeFalse();
        });
    });

    describe('getStorageStats', function () {
        it('can get storage statistics', function () {
            Storage::fake('public');

            // Create some files
            $file1 = UploadedFile::fake()->create('test1.pdf', 1024); // 1MB
            $file2 = UploadedFile::fake()->create('test2.jpg', 2048); // 2MB

            $this->driveService->uploadFile($file1, ['name' => 'test1.pdf'], $this->user->id);
            $this->driveService->uploadFile($file2, ['name' => 'test2.jpg'], $this->user->id);

            $stats = $this->driveService->getStorageStats($this->user->id);

            expect($stats)->toHaveKey('total_size');
            expect($stats)->toHaveKey('total_files');
            expect($stats)->toHaveKey('total_folders');
            expect($stats)->toHaveKey('human_size');
            expect($stats['total_files'])->toBe(2);
        });
    });
});
