<?php

namespace App\Domain\Drive\Repositories;

use App\Domain\Drive\Models\DriveNode;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface DriveNodeRepositoryInterface
{
    /**
     * Find node by ID
     */
    public function find(int $id): ?DriveNode;

    /**
     * Find node by ID with relations
     */
    public function findWithRelations(int $id, array $relations = []): ?DriveNode;

    /**
     * Get root nodes (no parent)
     */
    public function getRoots(): Collection;

    /**
     * Get children of a node
     */
    public function getChildren(int $parentId, bool $includeTrashed = false): Collection;

    /**
     * Get descendants of a node using path
     */
    public function getDescendants(int $ancestorId, bool $includeTrashed = false): Collection;

    /**
     * Get ancestors of a node using path
     */
    public function getAncestors(int $nodeId): Collection;

    /**
     * Find nodes by path pattern
     */
    public function findByPath(string $pathPattern): Collection;

    /**
     * Search nodes by name
     */
    public function searchByName(string $query, ?int $parentId = null): Collection;

    /**
     * Get trashed nodes
     */
    public function getTrashed(): Collection;

    /**
     * Get recent nodes
     */
    public function getRecent(int $limit = 10): Collection;

    /**
     * Get nodes by type (folder/file)
     */
    public function getByType(string $type, ?int $parentId = null): Collection;

    /**
     * Create a new node
     */
    public function create(array $data): DriveNode;

    /**
     * Update a node
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete a node (hard delete)
     */
    public function delete(int $id): bool;

    /**
     * Move node to trash
     */
    public function moveToTrash(int $id): bool;

    /**
     * Restore node from trash
     */
    public function restoreFromTrash(int $id): bool;

    /**
     * Move node to new parent
     */
    public function moveTo(int $nodeId, ?int $newParentId): bool;

    /**
     * Get node with children count
     */
    public function withChildrenCount(int $nodeId): ?DriveNode;

    /**
     * Get nodes by user (created by)
     */
    public function getByUser(int $userId): Collection;

    /**
     * Get favorite nodes for user
     */
    public function getFavorites(int $userId): Collection;

    /**
     * Paginate nodes
     */
    public function paginate(?int $parentId = null, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get tree structure starting from node
     */
    public function getTree(?int $rootId = null, int $maxDepth = null): Collection;

    /**
     * Get nodes with specific depth
     */
    public function getByDepth(int $depth): Collection;
}
