<?php

use App\Domain\Drive\Models\DriveNode;
use App\Domain\Drive\Models\DriveFolder;
use App\Domain\Drive\Models\DriveFile;
use App\Domain\Drive\Models\DriveFavorite;
use App\Domain\Drive\Models\DriveTag;
use App\Domain\Drive\Models\DriveActivity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('DriveNode Model - Comprehensive Tests', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->anotherUser = User::factory()->create();
    });

    describe('Model Structure and Relationships', function () {
        it('has correct fillable attributes', function () {
            $fillable = (new DriveNode)->getFillable();

            expect($fillable)->toContain('type');
            expect($fillable)->toContain('name');
            expect($fillable)->toContain('slug');
            expect($fillable)->toContain('parent_id');
            expect($fillable)->toContain('path');
            expect($fillable)->toContain('depth');
            expect($fillable)->toContain('position');
            expect($fillable)->toContain('is_trashed');
            expect($fillable)->toContain('trashed_at');
            expect($fillable)->toContain('created_by');
            expect($fillable)->toContain('updated_by');
        });

        it('has correct casts', function () {
            $node = new DriveNode();
            $casts = $node->getCasts();

            expect($casts['is_trashed'])->toBe('boolean');
            expect($casts['trashed_at'])->toBe('datetime');
            expect($casts['depth'])->toBe('integer');
            expect($casts['position'])->toBe('integer');
        });

        it('has parent relationship', function () {
            $parent = DriveNode::factory()->folder()->create(['created_by' => $this->user->id]);
            $child = DriveNode::factory()->folder()->create([
                'parent_id' => $parent->id,
                'created_by' => $this->user->id
            ]);

            expect($child->parent)->toBeInstanceOf(DriveNode::class);
            expect($child->parent->id)->toBe($parent->id);
        });

        it('has children relationship', function () {
            $parent = DriveNode::factory()->folder()->create(['created_by' => $this->user->id]);
            $child1 = DriveNode::factory()->folder()->create([
                'parent_id' => $parent->id,
                'created_by' => $this->user->id
            ]);
            $child2 = DriveNode::factory()->file()->create([
                'parent_id' => $parent->id,
                'created_by' => $this->user->id
            ]);

            $parent->refresh();
            expect($parent->children)->toHaveCount(2);
            expect($parent->children->pluck('id')->toArray())
                ->toContain($child1->id)
                ->toContain($child2->id);
        });

        it('has folder relationship for folder type', function () {
            $folderNode = DriveNode::factory()->folder()->create(['created_by' => $this->user->id]);

            // Create the folder relationship manually since factory might not do it
            DriveFolder::create(['drive_node_id' => $folderNode->id]);

            expect($folderNode->folder)->toBeInstanceOf(DriveFolder::class);
            expect($folderNode->folder->drive_node_id)->toBe($folderNode->id);
        });

        it('has file relationship for file type', function () {
            $fileNode = DriveNode::factory()->file()->create(['created_by' => $this->user->id]);

            // Create the file relationship manually since factory might not do it
            DriveFile::create([
                'drive_node_id' => $fileNode->id,
                'mime_type' => 'text/plain',
                'size_bytes' => 1024,
                'disk' => 'local'
            ]);

            expect($fileNode->file)->toBeInstanceOf(DriveFile::class);
            expect($fileNode->file->drive_node_id)->toBe($fileNode->id);
        });

        it('has creator relationship', function () {
            $node = DriveNode::factory()->folder()->create(['created_by' => $this->user->id]);

            expect($node->creator)->toBeInstanceOf(User::class);
            expect($node->creator->id)->toBe($this->user->id);
        });

        it('has updater relationship', function () {
            $node = DriveNode::factory()->folder()->create([
                'created_by' => $this->user->id,
                'updated_by' => $this->anotherUser->id
            ]);

            expect($node->updater)->toBeInstanceOf(User::class);
            expect($node->updater->id)->toBe($this->anotherUser->id);
        });

        it('has favorites relationship', function () {
            $node = DriveNode::factory()->folder()->create(['created_by' => $this->user->id]);

            DriveFavorite::create([
                'user_id' => $this->user->id,
                'drive_node_id' => $node->id
            ]);

            expect($node->favorites)->toHaveCount(1);
            expect($node->favorites->first()->user_id)->toBe($this->user->id);
        });

        it('has tags relationship', function () {
            $node = DriveNode::factory()->folder()->create(['created_by' => $this->user->id]);
            $tag = DriveTag::create(['name' => 'important', 'color' => '#ff0000']);

            $node->tags()->attach($tag->id);

            expect($node->tags)->toHaveCount(1);
            expect($node->tags->first()->name)->toBe('important');
        });

        it('has activities relationship', function () {
            $node = DriveNode::factory()->folder()->create(['created_by' => $this->user->id]);

            DriveActivity::create([
                'user_id' => $this->user->id,
                'drive_node_id' => $node->id,
                'action' => 'create',
                'metadata' => json_encode(['name' => $node->name])
            ]);

            expect($node->activities)->toHaveCount(1);
            expect($node->activities->first()->action)->toBe('create');
        });
    });

    describe('Path and Depth Auto-generation', function () {
        it('sets correct path and depth for root nodes', function () {
            $rootNode = DriveNode::factory()->folder()->create([
                'parent_id' => null,
                'created_by' => $this->user->id,
            ]);

            expect($rootNode->path)->toBe('/');
            expect($rootNode->depth)->toBe(0);
            expect($rootNode->isRoot())->toBeTrue();
        });

        it('sets correct path and depth for child nodes', function () {
            $parentNode = DriveNode::factory()->folder()->create([
                'parent_id' => null,
                'created_by' => $this->user->id,
            ]);

            $childNode = DriveNode::factory()->folder()->create([
                'parent_id' => $parentNode->id,
                'created_by' => $this->user->id,
            ]);

            expect($childNode->path)->toBe("/{$parentNode->id}/");
            expect($childNode->depth)->toBe(1);
            expect($childNode->isRoot())->toBeFalse();
        });

        it('sets correct path and depth for deeply nested nodes', function () {
            $level1 = DriveNode::factory()->folder()->create([
                'parent_id' => null,
                'created_by' => $this->user->id,
            ]);

            $level2 = DriveNode::factory()->folder()->create([
                'parent_id' => $level1->id,
                'created_by' => $this->user->id,
            ]);

            $level3 = DriveNode::factory()->folder()->create([
                'parent_id' => $level2->id,
                'created_by' => $this->user->id,
            ]);

            expect($level3->path)->toBe("/{$level1->id}/{$level2->id}/");
            expect($level3->depth)->toBe(2);
        });

        it('updates path when parent changes', function () {
            $parent1 = DriveNode::factory()->folder()->create([
                'parent_id' => null,
                'created_by' => $this->user->id,
            ]);

            $parent2 = DriveNode::factory()->folder()->create([
                'parent_id' => null,
                'created_by' => $this->user->id,
            ]);

            $child = DriveNode::factory()->folder()->create([
                'parent_id' => $parent1->id,
                'created_by' => $this->user->id,
            ]);

            expect($child->path)->toBe("/{$parent1->id}/");
            expect($child->depth)->toBe(1);

            // Move to new parent
            $child->update(['parent_id' => $parent2->id]);
            $child->refresh();

            expect($child->path)->toBe("/{$parent2->id}/");
            expect($child->depth)->toBe(1);
        });
    });

    describe('Scopes', function () {
        beforeEach(function () {
            $this->folder1 = DriveNode::factory()->folder()->create(['created_by' => $this->user->id]);
            $this->folder2 = DriveNode::factory()->folder()->create(['created_by' => $this->user->id]);
            $this->file1 = DriveNode::factory()->file()->create(['created_by' => $this->user->id]);
            $this->file2 = DriveNode::factory()->file()->create(['created_by' => $this->user->id]);
            $this->trashedNode = DriveNode::factory()->folder()->trashed()->create(['created_by' => $this->user->id]);
        });

        it('filters folders correctly', function () {
            $folders = DriveNode::folders()->get();

            expect($folders)->toHaveCount(3); // 2 active folders + 1 trashed folder
            expect($folders->pluck('id')->toArray())
                ->toContain($this->folder1->id)
                ->toContain($this->folder2->id)
                ->toContain($this->trashedNode->id) // trashed node is also a folder
                ->not->toContain($this->file1->id)
                ->not->toContain($this->file2->id);
        });

        it('filters files correctly', function () {
            $files = DriveNode::files()->get();

            expect($files)->toHaveCount(2);
            expect($files->pluck('id')->toArray())
                ->toContain($this->file1->id)
                ->toContain($this->file2->id)
                ->not->toContain($this->folder1->id)
                ->not->toContain($this->folder2->id);
        });

        it('filters active nodes correctly', function () {
            $activeNodes = DriveNode::notTrashed()->get();

            expect($activeNodes)->toHaveCount(4);
            expect($activeNodes->pluck('id')->toArray())
                ->not->toContain($this->trashedNode->id);
        });

        it('filters trashed nodes correctly', function () {
            $trashedNodes = DriveNode::trashed()->get();

            expect($trashedNodes)->toHaveCount(1);
            expect($trashedNodes->first()->id)->toBe($this->trashedNode->id);
        });

        it('filters root nodes correctly', function () {
            // Create some child nodes
            DriveNode::factory()->folder()->create([
                'parent_id' => $this->folder1->id,
                'created_by' => $this->user->id
            ]);

            $rootNodes = DriveNode::roots()->get();

            expect($rootNodes)->toHaveCount(5); // 2 folders + 2 files + 1 trashed (all root level)
        });

        it('finds descendants correctly', function () {
            $parent = DriveNode::factory()->folder()->create(['created_by' => $this->user->id]);
            $child1 = DriveNode::factory()->folder()->create([
                'parent_id' => $parent->id,
                'created_by' => $this->user->id
            ]);
            $grandchild = DriveNode::factory()->file()->create([
                'parent_id' => $child1->id,
                'created_by' => $this->user->id
            ]);

            $descendants = DriveNode::descendantsOf($parent->id)->get();

            expect($descendants)->toHaveCount(2);
            expect($descendants->pluck('id')->toArray())
                ->toContain($child1->id)
                ->toContain($grandchild->id);
        });

        it('finds ancestors correctly', function () {
            $grandparent = DriveNode::factory()->folder()->create(['created_by' => $this->user->id]);
            $parent = DriveNode::factory()->folder()->create([
                'parent_id' => $grandparent->id,
                'created_by' => $this->user->id
            ]);
            $child = DriveNode::factory()->folder()->create([
                'parent_id' => $parent->id,
                'created_by' => $this->user->id
            ]);

            $ancestors = $child->getAncestors();

            expect($ancestors)->toHaveCount(2);
            expect($ancestors->pluck('id')->toArray())
                ->toContain($grandparent->id)
                ->toContain($parent->id);
        });

        it('filters by creator correctly', function () {
            DriveNode::factory()->folder()->create(['created_by' => $this->anotherUser->id]);

            $userNodes = DriveNode::where('created_by', $this->user->id)->get();

            // Should find 5 nodes created by $this->user (2 folders + 2 files + 1 trashed)
            expect($userNodes)->toHaveCount(5);
            expect($userNodes->pluck('created_by')->unique()->toArray())->toBe([$this->user->id]);
        });

        it('filters by updater correctly', function () {
            DriveNode::factory()->folder()->create([
                'created_by' => $this->user->id,
                'updated_by' => $this->anotherUser->id
            ]);

            $updatedNodes = DriveNode::where('updated_by', $this->anotherUser->id)->get();

            expect($updatedNodes)->toHaveCount(1);
            expect($updatedNodes->first()->updated_by)->toBe($this->anotherUser->id);
        });
    });

    describe('Helper Methods', function () {
        it('correctly identifies root nodes', function () {
            $rootNode = DriveNode::factory()->folder()->create([
                'parent_id' => null,
                'created_by' => $this->user->id
            ]);

            $childNode = DriveNode::factory()->folder()->create([
                'parent_id' => $rootNode->id,
                'created_by' => $this->user->id
            ]);

            expect($rootNode->isRoot())->toBeTrue();
            expect($childNode->isRoot())->toBeFalse();
        });

        it('correctly identifies file nodes', function () {
            $fileNode = DriveNode::factory()->file()->create(['created_by' => $this->user->id]);
            $folderNode = DriveNode::factory()->folder()->create(['created_by' => $this->user->id]);

            expect($fileNode->isFile())->toBeTrue();
            expect($folderNode->isFile())->toBeFalse();
        });

        it('correctly identifies folder nodes', function () {
            $fileNode = DriveNode::factory()->file()->create(['created_by' => $this->user->id]);
            $folderNode = DriveNode::factory()->folder()->create(['created_by' => $this->user->id]);

            expect($folderNode->isFolder())->toBeTrue();
            expect($fileNode->isFolder())->toBeFalse();
        });

        it('correctly identifies trashed nodes', function () {
            $activeNode = DriveNode::factory()->folder()->create(['created_by' => $this->user->id]);
            $trashedNode = DriveNode::factory()->folder()->trashed()->create(['created_by' => $this->user->id]);

            expect($activeNode->is_trashed)->toBeFalse();
            expect($trashedNode->is_trashed)->toBeTrue();
        });

        it('gets breadcrumb trail correctly', function () {
            $grandparent = DriveNode::factory()->folder()->create([
                'name' => 'Grandparent',
                'created_by' => $this->user->id
            ]);

            $parent = DriveNode::factory()->folder()->create([
                'name' => 'Parent',
                'parent_id' => $grandparent->id,
                'created_by' => $this->user->id
            ]);

            $child = DriveNode::factory()->folder()->create([
                'name' => 'Child',
                'parent_id' => $parent->id,
                'created_by' => $this->user->id
            ]);

            $ancestors = $child->getAncestors();

            expect($ancestors)->toHaveCount(2);
            expect($ancestors->first()->name)->toBe('Grandparent');
            expect($ancestors->last()->name)->toBe('Parent');
        });

        it('gets ancestors correctly', function () {
            $grandparent = DriveNode::factory()->folder()->create(['created_by' => $this->user->id]);
            $parent = DriveNode::factory()->folder()->create([
                'parent_id' => $grandparent->id,
                'created_by' => $this->user->id
            ]);
            $child = DriveNode::factory()->folder()->create([
                'parent_id' => $parent->id,
                'created_by' => $this->user->id
            ]);

            $ancestors = $child->getAncestors();

            expect($ancestors)->toHaveCount(2);
            expect($ancestors->pluck('id')->toArray())
                ->toContain($grandparent->id)
                ->toContain($parent->id);
        });

        it('gets descendants correctly', function () {
            $parent = DriveNode::factory()->folder()->create(['created_by' => $this->user->id]);
            $child1 = DriveNode::factory()->folder()->create([
                'parent_id' => $parent->id,
                'created_by' => $this->user->id
            ]);
            $child2 = DriveNode::factory()->file()->create([
                'parent_id' => $parent->id,
                'created_by' => $this->user->id
            ]);
            $grandchild = DriveNode::factory()->file()->create([
                'parent_id' => $child1->id,
                'created_by' => $this->user->id
            ]);

            $descendants = $parent->getDescendants();

            expect($descendants)->toHaveCount(3);
            expect($descendants->pluck('id')->toArray())
                ->toContain($child1->id)
                ->toContain($child2->id)
                ->toContain($grandchild->id);
        });

        it('checks if node is ancestor of another', function () {
            $grandparent = DriveNode::factory()->folder()->create(['created_by' => $this->user->id]);
            $parent = DriveNode::factory()->folder()->create([
                'parent_id' => $grandparent->id,
                'created_by' => $this->user->id
            ]);
            $child = DriveNode::factory()->folder()->create([
                'parent_id' => $parent->id,
                'created_by' => $this->user->id
            ]);
            $sibling = DriveNode::factory()->folder()->create([
                'parent_id' => $grandparent->id,
                'created_by' => $this->user->id
            ]);

            // Check if grandparent's path is contained in child's path
            expect($child->path)->toContain((string)$grandparent->id);
            expect($child->path)->toContain((string)$parent->id);
            expect($child->path)->not->toContain((string)$sibling->id);
        });

        it('checks if node is descendant of another', function () {
            $grandparent = DriveNode::factory()->folder()->create(['created_by' => $this->user->id]);
            $parent = DriveNode::factory()->folder()->create([
                'parent_id' => $grandparent->id,
                'created_by' => $this->user->id
            ]);
            $child = DriveNode::factory()->folder()->create([
                'parent_id' => $parent->id,
                'created_by' => $this->user->id
            ]);

            // Check descendants
            $descendants = $grandparent->getDescendants();
            expect($descendants->pluck('id')->toArray())
                ->toContain($parent->id)
                ->toContain($child->id);
        });
    });

    describe('Validation and Business Rules', function () {
        it('prevents duplicate names in same parent folder', function () {
            $parent = DriveNode::factory()->folder()->create(['created_by' => $this->user->id]);

            DriveNode::factory()->folder()->create([
                'name' => 'Duplicate Name',
                'parent_id' => $parent->id,
                'created_by' => $this->user->id
            ]);

            expect(function () use ($parent) {
                DriveNode::factory()->folder()->create([
                    'name' => 'Duplicate Name',
                    'parent_id' => $parent->id,
                    'created_by' => $this->user->id
                ]);
            })->toThrow(Exception::class);
        });

        it('allows same names in different parent folders', function () {
            $parent1 = DriveNode::factory()->folder()->create(['created_by' => $this->user->id]);
            $parent2 = DriveNode::factory()->folder()->create(['created_by' => $this->user->id]);

            $node1 = DriveNode::factory()->folder()->create([
                'name' => 'Same Name',
                'parent_id' => $parent1->id,
                'created_by' => $this->user->id
            ]);

            $node2 = DriveNode::factory()->folder()->create([
                'name' => 'Same Name',
                'parent_id' => $parent2->id,
                'created_by' => $this->user->id
            ]);

            expect($node1->name)->toBe('Same Name');
            expect($node2->name)->toBe('Same Name');
            expect($node1->parent_id)->toBe($parent1->id);
            expect($node2->parent_id)->toBe($parent2->id);
        });

        it('allows same names when one is trashed', function () {
            $parent = DriveNode::factory()->folder()->create(['created_by' => $this->user->id]);

            $trashedNode = DriveNode::factory()->folder()->trashed()->create([
                'name' => 'Same Name',
                'parent_id' => $parent->id,
                'created_by' => $this->user->id
            ]);

            $activeNode = DriveNode::factory()->folder()->create([
                'name' => 'Same Name',
                'parent_id' => $parent->id,
                'created_by' => $this->user->id
            ]);

            expect($trashedNode->name)->toBe('Same Name');
            expect($activeNode->name)->toBe('Same Name');
            expect($trashedNode->is_trashed)->toBeTrue();
            expect($activeNode->is_trashed)->toBeFalse();
        });
    });

    describe('Soft Delete Behavior', function () {
        it('soft deletes node correctly', function () {
            $node = DriveNode::factory()->folder()->create(['created_by' => $this->user->id]);

            expect($node->is_trashed)->toBeFalse();
            expect($node->trashed_at)->toBeNull();

            $node->moveToTrash();
            $node->refresh();

            expect($node->is_trashed)->toBeTrue();
            expect($node->trashed_at)->not->toBeNull();
        });

        it('restores soft deleted node correctly', function () {
            $node = DriveNode::factory()->folder()->trashed()->create(['created_by' => $this->user->id]);

            expect($node->is_trashed)->toBeTrue();
            expect($node->trashed_at)->not->toBeNull();

            $node->restoreFromTrash();
            $node->refresh();

            expect($node->is_trashed)->toBeFalse();
            expect($node->trashed_at)->toBeNull();
        });

        it('force deletes node permanently', function () {
            $node = DriveNode::factory()->folder()->create(['created_by' => $this->user->id]);
            $nodeId = $node->id;

            $node->forceDelete();

            expect(DriveNode::find($nodeId))->toBeNull();
        });
    });

    describe('Event Handling', function () {
        it('triggers creating event before node creation', function () {
            $eventTriggered = false;

            DriveNode::creating(function ($node) use (&$eventTriggered) {
                $eventTriggered = true;
                expect($node->exists)->toBeFalse();
            });

            DriveNode::factory()->folder()->create(['created_by' => $this->user->id]);

            expect($eventTriggered)->toBeTrue();
        });

        it('triggers created event after node creation', function () {
            $eventTriggered = false;

            DriveNode::created(function ($node) use (&$eventTriggered) {
                $eventTriggered = true;
                expect($node->exists)->toBeTrue();
            });

            DriveNode::factory()->folder()->create(['created_by' => $this->user->id]);

            expect($eventTriggered)->toBeTrue();
        });

        it('triggers updating event before node update', function () {
            $node = DriveNode::factory()->folder()->create(['created_by' => $this->user->id]);
            $eventTriggered = false;

            DriveNode::updating(function ($node) use (&$eventTriggered) {
                $eventTriggered = true;
                expect($node->isDirty())->toBeTrue();
            });

            $node->update(['name' => 'Updated Name']);

            expect($eventTriggered)->toBeTrue();
        });

        it('triggers updated event after node update', function () {
            $node = DriveNode::factory()->folder()->create(['created_by' => $this->user->id]);
            $eventTriggered = false;

            DriveNode::updated(function ($node) use (&$eventTriggered) {
                $eventTriggered = true;
                expect($node->wasChanged())->toBeTrue();
            });

            $node->update(['name' => 'Updated Name']);

            expect($eventTriggered)->toBeTrue();
        });
    });
});
