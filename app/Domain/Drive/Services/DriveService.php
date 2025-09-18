<?php

namespace App\Domain\Drive\Services;

use App\Domain\Drive\Models\DriveNode;
use App\Domain\Drive\Models\DriveFolder;
use App\Domain\Drive\Models\TempFileUpload;
use App\Domain\Drive\Models\DriveFile;
use App\Domain\Drive\Models\DriveActivity;
use App\Domain\Drive\Repositories\DriveNodeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DriveService
{
    protected DriveNodeRepositoryInterface $repository;

    public function __construct(DriveNodeRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Create a new folder
     */
    public function createFolder(array $data, ?int $userId = null): DriveNode
    {
        return DB::transaction(function () use ($data, $userId) {
            // Create drive node
            $nodeData = [
                'type' => 'folder',
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'parent_id' => $data['parent_id'] ?? null,
                'position' => $data['position'] ?? 0,
                'created_by' => $userId,
                'updated_by' => $userId,
            ];

            $node = $this->repository->create($nodeData);

            // Create folder details
            $folderData = [
                'drive_node_id' => $node->id,
                'color' => $data['color'] ?? null,
                'icon' => $data['icon'] ?? 'folder',
                'cover_media_id' => $data['cover_media_id'] ?? null,
            ];

            DriveFolder::create($folderData);

            // Log activity
            $this->logActivity($node->id, $userId, 'create', [
                'folder_name' => $node->name,
                'parent_id' => $node->parent_id,
            ]);

            return $node->load('folder');
        });
    }

    /**
     * Upload and create a new file
     */
    public function uploadFile(UploadedFile $file, array $data, ?int $userId = null): DriveNode
    {
        return DB::transaction(function () use ($file, $data, $userId) {
            // Create drive node first
            $nodeData = [
                'type' => 'file',
                'name' => $data['name'] ?? $file->getClientOriginalName(),
                'parent_id' => $data['parent_id'] ?? null,
                'created_by' => $userId,
                'visibility' => $data['visibility'] ?? 'private',
            ];

            $node = $this->repository->create($nodeData);

            // Create DriveFile with node ID as primary key
            $driveFile = new DriveFile();
            $driveFile->drive_node_id = $node->id;
            $driveFile->mime_type = $file->getMimeType();
            $driveFile->size_bytes = $file->getSize();
            $driveFile->disk = config('filesystems.default');
            $driveFile->visibility = $data['visibility'] ?? 'private';
            $driveFile->version = 1;
            $driveFile->save();

            // Store file using Spatie Media Library on DriveFile model
            $media = $driveFile->addMedia($file)
                ->usingName($data['name'] ?? $file->getClientOriginalName())
                ->usingFileName($file->getClientOriginalName())
                ->toMediaCollection('drive_files');

            // Update DriveFile with media reference and checksum
            $driveFile->media_id = $media->id;
            $driveFile->checksum = hash_file('md5', $media->getPath());
            $driveFile->save();

            // Log activity
            $this->logActivity($node->id, $userId, 'upload', [
                'file_name' => $node->name,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
            ]);

            return $node->load('file');
        });
    }

    /**
     * Rename a node
     */
    public function rename(int $nodeId, string $newName, ?int $userId = null): bool
    {
        return DB::transaction(function () use ($nodeId, $newName, $userId) {
            $node = $this->repository->find($nodeId);

            if (!$node) {
                return false;
            }

            $oldName = $node->name;

            $updated = $this->repository->update($nodeId, [
                'name' => $newName,
                'slug' => Str::slug($newName),
                'updated_by' => $userId,
            ]);

            if ($updated) {
                $this->logActivity($nodeId, $userId, 'rename', [
                    'old_name' => $oldName,
                    'new_name' => $newName,
                ]);
            }

            return $updated;
        });
    }

    /**
     * Move node to new parent
     */
    public function move(int $nodeId, ?int $newParentId, ?int $userId = null): bool
    {
        return DB::transaction(function () use ($nodeId, $newParentId, $userId) {
            $node = $this->repository->find($nodeId);

            if (!$node) {
                return false;
            }

            $oldParentId = $node->parent_id;

            $moved = $this->repository->moveTo($nodeId, $newParentId);

            if ($moved) {
                $this->repository->update($nodeId, ['updated_by' => $userId]);

                $this->logActivity($nodeId, $userId, 'move', [
                    'old_parent_id' => $oldParentId,
                    'new_parent_id' => $newParentId,
                    'node_name' => $node->name,
                ]);
            }

            return $moved;
        });
    }

    /**
     * Copy node to new location
     */
    public function copy(int $nodeId, ?int $newParentId, ?string $newName = null, ?int $userId = null): ?DriveNode
    {
        return DB::transaction(function () use ($nodeId, $newParentId, $newName, $userId) {
            $originalNode = $this->repository->findWithRelations($nodeId, ['folder', 'file']);

            if (!$originalNode) {
                return null;
            }

            // Create copy of the node
            $copyData = [
                'type' => $originalNode->type,
                'name' => $newName ?? ($originalNode->name . ' (Copy)'),
                'parent_id' => $newParentId ?? $originalNode->parent_id,
                'position' => 0,
                'created_by' => $userId,
                'updated_by' => $userId,
            ];

            $newNode = $this->repository->create($copyData);

            // Copy type-specific data
            if ($originalNode->isFolder() && $originalNode->folder) {
                DriveFolder::create([
                    'drive_node_id' => $newNode->id,
                    'color' => $originalNode->folder->color,
                    'icon' => $originalNode->folder->icon,
                    'cover_media_id' => $originalNode->folder->cover_media_id,
                ]);

                // Recursively copy children
                $children = $this->repository->getChildren($originalNode->id);
                foreach ($children as $child) {
                    $this->copy($child->id, $newNode->id, null, $userId);
                }
            } elseif ($originalNode->isFile() && $originalNode->file) {
                // For files, we could copy the media or reference the same media
                // Here we'll reference the same media for simplicity
                DriveFile::create([
                    'drive_node_id' => $newNode->id,
                    'media_id' => $originalNode->file->media_id,
                    'mime_type' => $originalNode->file->mime_type,
                    'size_bytes' => $originalNode->file->size_bytes,
                    'checksum' => $originalNode->file->checksum,
                    'disk' => $originalNode->file->disk,
                    'visibility' => $originalNode->file->visibility,
                    'version' => 1,
                ]);
            }

            $this->logActivity($newNode->id, $userId, 'copy', [
                'original_node_id' => $originalNode->id,
                'original_name' => $originalNode->name,
                'copy_name' => $newNode->name,
                'parent_id' => $newParentId,
            ]);

            return $newNode;
        });
    }

    /**
     * Move node to trash
     */
    public function moveToTrash(int $nodeId, ?int $userId = null): bool
    {
        return DB::transaction(function () use ($nodeId, $userId) {
            $node = $this->repository->find($nodeId);

            if (!$node) {
                return false;
            }

            $trashed = $this->repository->moveToTrash($nodeId);

            if ($trashed) {
                $this->repository->update($nodeId, ['updated_by' => $userId]);

                $this->logActivity($nodeId, $userId, 'delete', [
                    'node_name' => $node->name,
                    'node_type' => $node->type,
                ]);
            }

            return $trashed;
        });
    }

    /**
     * Restore node from trash
     */
    public function restoreFromTrash(int $nodeId, ?int $userId = null): bool
    {
        return DB::transaction(function () use ($nodeId, $userId) {
            $node = $this->repository->find($nodeId);

            if (!$node) {
                return false;
            }

            $restored = $this->repository->restoreFromTrash($nodeId);

            if ($restored) {
                $this->repository->update($nodeId, ['updated_by' => $userId]);

                $this->logActivity($nodeId, $userId, 'restore', [
                    'node_name' => $node->name,
                    'node_type' => $node->type,
                ]);
            }

            return $restored;
        });
    }

    /**
     * Permanently delete node
     */
    public function delete(int $nodeId, ?int $userId = null): bool
    {
        return DB::transaction(function () use ($nodeId, $userId) {
            $node = $this->repository->findWithRelations($nodeId, ['file.spatieMedia']);

            if (!$node) {
                return false;
            }

            // Delete associated media files
            if ($node->isFile() && $node->file && $node->file->spatieMedia) {
                $node->file->spatieMedia->delete();
            }

            // Delete children recursively if it's a folder
            if ($node->isFolder()) {
                $children = $this->repository->getChildren($nodeId, true); // Include trashed
                foreach ($children as $child) {
                    $this->delete($child->id, $userId);
                }
            }

            return $this->repository->delete($nodeId);
        });
    }

    /**
     * Get breadcrumb for node
     */
    public function getBreadcrumb(int $nodeId): Collection
    {
        return $this->repository->getAncestors($nodeId);
    }

    /**
     * Search nodes
     */
    public function search(string $query, ?int $parentId = null): Collection
    {
        return $this->repository->searchByName($query, $parentId);
    }

    /**
     * Get node tree
     */
    public function getTree(?int $rootId = null, int $maxDepth = 3): Collection
    {
        return $this->repository->getTree($rootId, $maxDepth);
    }

    /**
     * Toggle favorite status
     */
    public function toggleFavorite(int $nodeId, int $userId): bool
    {
        $node = $this->repository->find($nodeId);

        if (!$node) {
            return false;
        }

        $favorite = $node->favorites()->where('user_id', $userId)->first();

        if ($favorite) {
            $favorite->delete();
            $isFavorited = false;
        } else {
            $node->favorites()->create(['user_id' => $userId, 'created_at' => now()]);
            $isFavorited = true;
        }

        $this->logActivity($nodeId, $userId, $isFavorited ? 'favorite' : 'unfavorite', [
            'node_name' => $node->name,
        ]);

        return $isFavorited;
    }

    /**
     * Get storage stats
     */
    public function getStorageStats(?int $userId = null): array
    {
        $query = DriveFile::query();

        if ($userId) {
            $query->whereHas('driveNode', function ($q) use ($userId) {
                $q->where('created_by', $userId);
            });
        }

        $totalSize = $query->sum('size_bytes');
        $fileCount = $query->count();

        $folderCount = DriveNode::folders()
            ->notTrashed()
            ->when($userId, function ($q) use ($userId) {
                $q->where('created_by', $userId);
            })
            ->count();

        return [
            'total_size' => $totalSize,
            'total_files' => $fileCount,
            'total_folders' => $folderCount,
            'human_size' => $this->formatBytes($totalSize),
        ];
    }

    /**
     * Log activity
     */
    protected function logActivity(int $nodeId, ?int $userId, string $action, array $meta = []): void
    {
        DriveActivity::create([
            'drive_node_id' => $nodeId,
            'user_id' => $userId,
            'action' => $action,
            'meta' => $meta,
            'created_at' => now(),
        ]);
    }

    /**
     * Format bytes to human readable format
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
