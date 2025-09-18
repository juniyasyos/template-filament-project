<?php

use App\Domain\Drive\Models\DriveNode;
use App\Domain\Drive\Models\DriveFolder;
use App\Domain\Drive\Models\DriveFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('DriveNode Model', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
    });

    describe('path and depth auto-generation', function () {
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

        it('sets correct path and depth for nested nodes', function () {
            $grandParent = DriveNode::factory()->folder()->create([
                'parent_id' => null,
                'created_by' => $this->user->id,
            ]);

            $parent = DriveNode::factory()->folder()->create([
                'parent_id' => $grandParent->id,
                'created_by' => $this->user->id,
            ]);

            $child = DriveNode::factory()->folder()->create([
                'parent_id' => $parent->id,
                'created_by' => $this->user->id,
            ]);

            expect($child->path)->toBe("/{$grandParent->id}/{$parent->id}/");
            expect($child->depth)->toBe(2);
        });
    });

    describe('relationships', function () {
        it('has correct parent-child relationships', function () {
            $parent = DriveNode::factory()->folder()->create([
                'created_by' => $this->user->id,
            ]);

            $child = DriveNode::factory()->folder()->create([
                'parent_id' => $parent->id,
                'created_by' => $this->user->id,
            ]);

            expect($child->parent->id)->toBe($parent->id);
            expect($parent->children->contains($child))->toBeTrue();
            expect($parent->hasChildren())->toBeTrue();
        });

        it('has folder relationship for folder type', function () {
            $folderNode = DriveNode::factory()->folder()->create([
                'created_by' => $this->user->id,
            ]);

            DriveFolder::factory()->create([
                'drive_node_id' => $folderNode->id,
            ]);

            expect($folderNode->folder)->toBeInstanceOf(DriveFolder::class);
            expect($folderNode->isFolder())->toBeTrue();
            expect($folderNode->isFile())->toBeFalse();
        });
    });

    describe('scopes', function () {
        it('filters folders correctly', function () {
            DriveNode::factory()->folder()->create(['created_by' => $this->user->id]);
            DriveNode::factory()->file()->create(['created_by' => $this->user->id]);

            $folders = DriveNode::folders()->get();
            $files = DriveNode::files()->get();

            expect($folders)->toHaveCount(1);
            expect($files)->toHaveCount(1);
            expect($folders->first()->type)->toBe('folder');
            expect($files->first()->type)->toBe('file');
        });

        it('filters trashed nodes correctly', function () {
            $normalNode = DriveNode::factory()->folder()->create([
                'created_by' => $this->user->id,
                'is_trashed' => false,
            ]);

            $trashedNode = DriveNode::factory()->folder()->create([
                'created_by' => $this->user->id,
                'is_trashed' => true,
                'trashed_at' => now(),
            ]);

            $notTrashed = DriveNode::notTrashed()->get();
            $trashed = DriveNode::trashed()->get();

            expect($notTrashed->contains($normalNode))->toBeTrue();
            expect($notTrashed->contains($trashedNode))->toBeFalse();
            expect($trashed->contains($trashedNode))->toBeTrue();
            expect($trashed->contains($normalNode))->toBeFalse();
        });

        it('finds descendants correctly', function () {
            $parent = DriveNode::factory()->folder()->create([
                'created_by' => $this->user->id,
            ]);

            $child1 = DriveNode::factory()->folder()->create([
                'parent_id' => $parent->id,
                'created_by' => $this->user->id,
            ]);

            $child2 = DriveNode::factory()->folder()->create([
                'parent_id' => $parent->id,
                'created_by' => $this->user->id,
            ]);

            $grandchild = DriveNode::factory()->folder()->create([
                'parent_id' => $child1->id,
                'created_by' => $this->user->id,
            ]);

            $descendants = DriveNode::descendantsOf($parent->id)->get();

            expect($descendants)->toHaveCount(3);
            expect($descendants->contains($child1))->toBeTrue();
            expect($descendants->contains($child2))->toBeTrue();
            expect($descendants->contains($grandchild))->toBeTrue();
        });
    });

    describe('move operations', function () {
        it('can move node to new parent', function () {
            $oldParent = DriveNode::factory()->folder()->create([
                'created_by' => $this->user->id,
            ]);

            $newParent = DriveNode::factory()->folder()->create([
                'created_by' => $this->user->id,
            ]);

            $child = DriveNode::factory()->folder()->create([
                'parent_id' => $oldParent->id,
                'created_by' => $this->user->id,
            ]);

            $result = $child->moveTo($newParent->id);

            expect($result)->toBeTrue();

            $child->refresh();
            expect($child->parent_id)->toBe($newParent->id);
            expect($child->path)->toBe("/{$newParent->id}/");
        });

        it('can move to root level', function () {
            $parent = DriveNode::factory()->folder()->create([
                'created_by' => $this->user->id,
            ]);

            $child = DriveNode::factory()->folder()->create([
                'parent_id' => $parent->id,
                'created_by' => $this->user->id,
            ]);

            $result = $child->moveTo(null);

            expect($result)->toBeTrue();

            $child->refresh();
            expect($child->parent_id)->toBeNull();
            expect($child->path)->toBe('/');
            expect($child->depth)->toBe(0);
        });
    });

    describe('trash operations', function () {
        it('can move to trash and restore', function () {
            $node = DriveNode::factory()->folder()->create([
                'created_by' => $this->user->id,
            ]);

            // Move to trash
            $result = $node->moveToTrash();
            expect($result)->toBeTrue();
            expect($node->is_trashed)->toBeTrue();
            expect($node->trashed_at)->not->toBeNull();

            // Restore from trash
            $result = $node->restoreFromTrash();
            expect($result)->toBeTrue();
            expect($node->is_trashed)->toBeFalse();
            expect($node->trashed_at)->toBeNull();
        });
    });

    describe('ancestor methods', function () {
        it('gets ancestors correctly', function () {
            $grandParent = DriveNode::factory()->folder()->create([
                'created_by' => $this->user->id,
            ]);

            $parent = DriveNode::factory()->folder()->create([
                'parent_id' => $grandParent->id,
                'created_by' => $this->user->id,
            ]);

            $child = DriveNode::factory()->folder()->create([
                'parent_id' => $parent->id,
                'created_by' => $this->user->id,
            ]);

            $ancestors = $child->getAncestors();

            expect($ancestors)->toHaveCount(2);
            expect($ancestors->first()->id)->toBe($grandParent->id);
            expect($ancestors->last()->id)->toBe($parent->id);
        });

        it('gets descendants correctly', function () {
            $parent = DriveNode::factory()->folder()->create([
                'created_by' => $this->user->id,
            ]);

            $child1 = DriveNode::factory()->folder()->create([
                'parent_id' => $parent->id,
                'created_by' => $this->user->id,
            ]);

            $child2 = DriveNode::factory()->folder()->create([
                'parent_id' => $parent->id,
                'created_by' => $this->user->id,
            ]);

            $grandchild = DriveNode::factory()->folder()->create([
                'parent_id' => $child1->id,
                'created_by' => $this->user->id,
            ]);

            $descendants = $parent->getDescendants();

            expect($descendants)->toHaveCount(3);
        });
    });
});
