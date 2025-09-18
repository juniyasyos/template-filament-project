<?php

namespace App\Domain\Drive\Database\Factories;

use App\Domain\Drive\Models\DriveNode;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Drive\Models\DriveNode>
 */
class DriveNodeFactory extends Factory
{
    protected $model = DriveNode::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->words(rand(1, 3), true);

        return [
            'type' => $this->faker->randomElement(['folder', 'file']),
            'name' => $name,
            'slug' => Str::slug($name),
            'parent_id' => null, // Will be set explicitly when needed
            'path' => '/', // Will be auto-generated in model boot
            'depth' => 0, // Will be auto-generated in model boot
            'position' => $this->faker->numberBetween(0, 100),
            'is_trashed' => false,
            'trashed_at' => null,
            'created_by' => User::factory(),
            'updated_by' => null,
        ];
    }

    /**
     * Indicate that the node is a folder.
     */
    public function folder(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'folder',
            'name' => $this->faker->randomElement([
                'Documents', 'Images', 'Videos', 'Projects', 'Archive',
                'Work', 'Personal', 'Photos', 'Backup', 'Downloads',
                'Music', 'Resources', 'Templates', 'Shared', 'Private'
            ]) . ' ' . $this->faker->word(),
        ]);
    }

    /**
     * Indicate that the node is a file.
     */
    public function file(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'file',
            'name' => $this->faker->randomElement([
                'document', 'report', 'presentation', 'spreadsheet', 'image',
                'photo', 'video', 'music', 'archive', 'backup', 'template',
                'invoice', 'contract', 'manual', 'guide'
            ]) . '_' . $this->faker->dateFormat('Y-m-d') . $this->faker->randomElement([
                '.pdf', '.docx', '.xlsx', '.pptx', '.jpg', '.png', '.mp4',
                '.mp3', '.zip', '.txt', '.csv', '.html', '.json'
            ]),
        ]);
    }

    /**
     * Indicate that the node is trashed.
     */
    public function trashed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_trashed' => true,
            'trashed_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Create a node with a specific parent.
     */
    public function withParent(int $parentId): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parentId,
        ]);
    }

    /**
     * Create a root node.
     */
    public function root(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => null,
            'path' => '/',
            'depth' => 0,
        ]);
    }

    /**
     * Create a node with specific name.
     */
    public function withName(string $name): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $name,
            'slug' => Str::slug($name),
        ]);
    }
}
