<?php

namespace App\Domain\Drive\Database\Factories;

use App\Domain\Drive\Models\DriveFolder;
use App\Domain\Drive\Models\DriveNode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Drive\Models\DriveFolder>
 */
class DriveFolderFactory extends Factory
{
    protected $model = DriveFolder::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'drive_node_id' => DriveNode::factory()->folder(),
            'cover_media_id' => null, // Optional cover image
            'color' => $this->faker->randomElement([
                '#3B82F6', // blue
                '#10B981', // green
                '#F59E0B', // amber
                '#EF4444', // red
                '#8B5CF6', // violet
                '#EC4899', // pink
                '#06B6D4', // cyan
                '#84CC16', // lime
                null, // no color
            ]),
            'icon' => $this->faker->randomElement([
                'folder', 'folder-open', 'archive-box', 'document-duplicate',
                'photo', 'video-camera', 'musical-note', 'code-bracket',
                'academic-cap', 'briefcase', 'home', 'star', null
            ]),
        ];
    }

    /**
     * Create a folder with specific color.
     */
    public function withColor(string $color): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => $color,
        ]);
    }

    /**
     * Create a folder with specific icon.
     */
    public function withIcon(string $icon): static
    {
        return $this->state(fn (array $attributes) => [
            'icon' => $icon,
        ]);
    }
}
