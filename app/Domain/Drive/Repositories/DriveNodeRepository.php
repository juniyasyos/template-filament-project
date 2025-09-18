<?php

namespace App\Domain\Drive\Repositories;

use App\Domain\Drive\Models\DriveNode;
use App\Domain\Drive\Repositories\DriveNodeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class DriveNodeRepository implements DriveNodeRepositoryInterface
{
    protected DriveNode $model;

    public function __construct(DriveNode $model)
    {
        $this->model = $model;
    }

    public function find(int $id): ?DriveNode
    {
        return $this->model->find($id);
    }

    public function findWithRelations(int $id, array $relations = []): ?DriveNode
    {
        return $this->model->with($relations)->find($id);
    }

    public function getRoots(): Collection
    {
        return $this->model->roots()
            ->notTrashed()
            ->orderBy('type', 'asc') // folders first
            ->orderBy('position', 'asc')
            ->orderBy('name', 'asc')
            ->get();
    }

    public function getChildren(int $parentId, bool $includeTrashed = false): Collection
    {
        $query = $this->model->childrenOf($parentId);

        if (!$includeTrashed) {
            $query->notTrashed();
        }

        return $query->with(['folder', 'file'])
            ->orderBy('type', 'asc')
            ->orderBy('position', 'asc')
            ->orderBy('name', 'asc')
            ->get();
    }

    public function getDescendants(int $ancestorId, bool $includeTrashed = false): Collection
    {
        $query = $this->model->descendantsOf($ancestorId);

        if (!$includeTrashed) {
            $query->notTrashed();
        }

        return $query->orderBy('depth', 'asc')
            ->orderBy('path', 'asc')
            ->get();
    }

    public function getAncestors(int $nodeId): Collection
    {
        $node = $this->find($nodeId);

        if (!$node) {
            return collect();
        }

        return $node->getAncestors();
    }

    public function findByPath(string $pathPattern): Collection
    {
        return $this->model->where('path', 'like', $pathPattern)
            ->notTrashed()
            ->orderBy('path', 'asc')
            ->get();
    }

    public function searchByName(string $query, ?int $parentId = null): Collection
    {
        $queryBuilder = $this->model->where('name', 'like', "%{$query}%")
            ->notTrashed();

        if ($parentId !== null) {
            $queryBuilder->where('parent_id', $parentId);
        }

        return $queryBuilder->with(['folder', 'file'])
            ->orderBy('type', 'asc')
            ->orderBy('name', 'asc')
            ->get();
    }

    public function getTrashed(): Collection
    {
        return $this->model->trashed()
            ->orderBy('trashed_at', 'desc')
            ->get();
    }

    public function getRecent(int $limit = 10): Collection
    {
        return $this->model->notTrashed()
            ->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getByType(string $type, ?int $parentId = null): Collection
    {
        $query = $this->model->where('type', $type)->notTrashed();

        if ($parentId !== null) {
            $query->where('parent_id', $parentId);
        }

        return $query->orderBy('name', 'asc')->get();
    }

    public function create(array $data): DriveNode
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->model->where('id', $id)->update($data);
    }

    public function delete(int $id): bool
    {
        return $this->model->where('id', $id)->delete();
    }

    public function moveToTrash(int $id): bool
    {
        $node = $this->find($id);

        if (!$node) {
            return false;
        }

        return $node->moveToTrash();
    }

    public function restoreFromTrash(int $id): bool
    {
        $node = $this->find($id);

        if (!$node) {
            return false;
        }

        return $node->restoreFromTrash();
    }

    public function moveTo(int $nodeId, ?int $newParentId): bool
    {
        $node = $this->find($nodeId);

        if (!$node) {
            return false;
        }

        // Validate that we're not moving a node into its own subtree
        if ($newParentId && $this->isDescendant($newParentId, $nodeId)) {
            return false;
        }

        return $node->moveTo($newParentId);
    }

    public function withChildrenCount(int $nodeId): ?DriveNode
    {
        return $this->model->withCount('children')->find($nodeId);
    }

    public function getByUser(int $userId): Collection
    {
        return $this->model->where('created_by', $userId)
            ->notTrashed()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getFavorites(int $userId): Collection
    {
        return $this->model->whereHas('favorites', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->notTrashed()
        ->with(['folder', 'file', 'favorites'])
        ->orderBy('name', 'asc')
        ->get();
    }

    public function paginate(?int $parentId = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->notTrashed();

        if ($parentId !== null) {
            $query->where('parent_id', $parentId);
        } else {
            $query->whereNull('parent_id'); // Root nodes
        }

        return $query->with(['folder', 'file'])
            ->orderBy('type', 'asc')
            ->orderBy('position', 'asc')
            ->orderBy('name', 'asc')
            ->paginate($perPage);
    }

    /**
     * Check if target node is a descendant of source node
     */
    private function isDescendant(int $targetId, int $sourceId): bool
    {
        $sourceNode = $this->find($sourceId);

        if (!$sourceNode) {
            return false;
        }

        return $this->model->where('id', $targetId)
            ->where('path', 'like', $sourceNode->path . $sourceId . '/%')
            ->exists();
    }

    /**
     * Get nodes with specific depth
     */
    public function getByDepth(int $depth): Collection
    {
        return $this->model->where('depth', $depth)
            ->notTrashed()
            ->orderBy('path', 'asc')
            ->get();
    }

    /**
     * Get tree structure starting from node
     */
    public function getTree(?int $rootId = null, int $maxDepth = null): Collection
    {
        $query = $this->model->notTrashed();

        if ($rootId) {
            // Get descendants of specific root
            $rootNode = $this->find($rootId);
            if ($rootNode) {
                $query->where(function ($q) use ($rootNode, $rootId) {
                    $q->where('id', $rootId)
                      ->orWhere('path', 'like', $rootNode->path . $rootId . '/%');
                });
            }
        } else {
            // Get all nodes
        }

        if ($maxDepth !== null) {
            $baseDepth = $rootId ? $this->find($rootId)?->depth ?? 0 : 0;
            $query->where('depth', '<=', $baseDepth + $maxDepth);
        }

        return $query->orderBy('path', 'asc')
            ->orderBy('position', 'asc')
            ->with(['folder', 'file'])
            ->get();
    }
}
