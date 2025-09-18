<?php

namespace App\Domain\Drive\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class DriveNode extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'name',
        'slug',
        'parent_id',
        'path',
        'depth',
        'position',
        'is_trashed',
        'trashed_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_trashed' => 'boolean',
        'trashed_at' => 'datetime',
        'depth' => 'integer',
        'position' => 'integer',
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \App\Domain\Drive\Database\Factories\DriveNodeFactory::new();
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate path when creating/updating
        static::creating(function ($node) {
            if ($node->parent_id) {
                $parent = static::find($node->parent_id);
                $node->path = $parent->path . $node->parent_id . '/';
                $node->depth = $parent->depth + 1;
            } else {
                $node->path = '/';
                $node->depth = 0;
            }
        });

        static::updating(function ($node) {
            if ($node->isDirty('parent_id')) {
                if ($node->parent_id) {
                    $parent = static::find($node->parent_id);
                    $node->path = $parent->path . $node->parent_id . '/';
                    $node->depth = $parent->depth + 1;
                } else {
                    $node->path = '/';
                    $node->depth = 0;
                }
            }
        });
    }

    /**
     * Relationship: Parent node
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(DriveNode::class, 'parent_id');
    }

    /**
     * Relationship: Child nodes
     */
    public function children(): HasMany
    {
        return $this->hasMany(DriveNode::class, 'parent_id')
            ->orderBy('type', 'asc') // folders first
            ->orderBy('position', 'asc')
            ->orderBy('name', 'asc');
    }

    /**
     * Relationship: Folder details (when type = folder)
     */
    public function folder(): HasOne
    {
        return $this->hasOne(DriveFolder::class, 'drive_node_id');
    }

    /**
     * Relationship: File details (when type = file)
     */
    public function file(): HasOne
    {
        return $this->hasOne(DriveFile::class, 'drive_node_id');
    }

    /**
     * Relationship: Created by user
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship: Updated by user
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Relationship: Tags
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(DriveTag::class, 'drive_node_tag', 'drive_node_id', 'tag_id');
    }

    /**
     * Relationship: Favorites
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(DriveFavorite::class, 'drive_node_id');
    }

    /**
     * Relationship: Activities
     */
    public function activities(): HasMany
    {
        return $this->hasMany(DriveActivity::class, 'drive_node_id')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Scope: Only folders
     */
    public function scopeFolders(Builder $query): Builder
    {
        return $query->where('type', 'folder');
    }

    /**
     * Scope: Only files
     */
    public function scopeFiles(Builder $query): Builder
    {
        return $query->where('type', 'file');
    }

    /**
     * Scope: Not trashed
     */
    public function scopeNotTrashed(Builder $query): Builder
    {
        return $query->where('is_trashed', false);
    }

    /**
     * Scope: Trashed
     */
    public function scopeTrashed(Builder $query): Builder
    {
        return $query->where('is_trashed', true);
    }

    /**
     * Scope: Root nodes (no parent)
     */
    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope: Children of specific node
     */
    public function scopeChildrenOf(Builder $query, int $parentId): Builder
    {
        return $query->where('parent_id', $parentId);
    }

    /**
     * Scope: Descendants (subtree) using path
     */
    public function scopeDescendantsOf(Builder $query, int $ancestorId): Builder
    {
        $ancestor = static::find($ancestorId);
        if (!$ancestor) {
            return $query->whereRaw('1 = 0'); // No results
        }

        return $query->where('path', 'like', $ancestor->path . $ancestorId . '/%');
    }

    /**
     * Get ancestors (breadcrumb) using path
     */
    public function getAncestors()
    {
        if ($this->path === '/') {
            return collect();
        }

        $pathIds = array_filter(explode('/', trim($this->path, '/')));

        return static::whereIn('id', $pathIds)
            ->orderByRaw('FIELD(id, ' . implode(',', $pathIds) . ')')
            ->get();
    }

    /**
     * Get all descendants using path
     */
    public function getDescendants()
    {
        return static::where('path', 'like', $this->path . $this->id . '/%')->get();
    }

    /**
     * Check if node is a folder
     */
    public function isFolder(): bool
    {
        return $this->type === 'folder';
    }

    /**
     * Check if node is a file
     */
    public function isFile(): bool
    {
        return $this->type === 'file';
    }

    /**
     * Check if node is root
     */
    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * Check if node has children
     */
    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Move node to new parent
     */
    public function moveTo(?int $newParentId): bool
    {
        $oldPath = $this->path . $this->id . '/';

        $this->parent_id = $newParentId;
        $saved = $this->save(); // This will trigger boot methods to update path/depth

        if ($saved) {
            $newPath = $this->path . $this->id . '/';

            // Update all descendants' paths
            $this->updateDescendantsPaths($oldPath, $newPath);
        }

        return $saved;
    }

    /**
     * Update descendants paths after move
     */
    private function updateDescendantsPaths(string $oldPath, string $newPath): void
    {
        static::where('path', 'like', $oldPath . '%')
            ->update([
                'path' => DB::raw("REPLACE(path, '{$oldPath}', '{$newPath}')"),
                'depth' => DB::raw("depth + " . ($this->depth + 1 - substr_count($oldPath, '/'))),
            ]);
    }

    /**
     * Soft delete (move to trash)
     */
    public function moveToTrash(): bool
    {
        $this->is_trashed = true;
        $this->trashed_at = now();
        return $this->save();
    }

    /**
     * Restore from trash
     */
    public function restoreFromTrash(): bool
    {
        $this->is_trashed = false;
        $this->trashed_at = null;
        return $this->save();
    }
}
