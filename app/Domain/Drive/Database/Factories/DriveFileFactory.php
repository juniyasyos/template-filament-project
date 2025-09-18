<?php

namespace App\Domain\Drive\Database\Factories;

use App\Domain\Drive\Models\DriveFile;
use App\Domain\Drive\Models\DriveNode;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Drive\Models\DriveFile>
 */
class DriveFileFactory extends Factory
{
    protected $model = DriveFile::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $mimeTypes = [
            'application/pdf' => ['.pdf', 1024, 10240], // 1KB - 10MB
            'application/msword' => ['.doc', 512, 5120], // 0.5KB - 5MB
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['.docx', 512, 5120],
            'application/vnd.ms-excel' => ['.xls', 256, 2560],
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['.xlsx', 256, 2560],
            'image/jpeg' => ['.jpg', 2048, 20480], // 2KB - 20MB
            'image/png' => ['.png', 1024, 15360],
            'image/gif' => ['.gif', 512, 5120],
            'video/mp4' => ['.mp4', 10240, 1048576], // 10MB - 1GB
            'video/avi' => ['.avi', 10240, 1048576],
            'audio/mp3' => ['.mp3', 2048, 20480], // 2MB - 20MB
            'audio/wav' => ['.wav', 5120, 51200],
            'application/zip' => ['.zip', 1024, 102400], // 1MB - 100MB
            'text/plain' => ['.txt', 10, 1024], // 10B - 1MB
        ];

        $mimeType = $this->faker->randomElement(array_keys($mimeTypes));
        [$extension, $minSize, $maxSize] = $mimeTypes[$mimeType];
        $sizeBytes = $this->faker->numberBetween($minSize, $maxSize) * 1024; // Convert to bytes

        return [
            'drive_node_id' => DriveNode::factory()->file(),
            'media_id' => Media::factory(), // Will need to create this
            'mime_type' => $mimeType,
            'size_bytes' => $sizeBytes,
            'checksum' => $this->faker->sha256(),
            'disk' => 'public',
            'visibility' => $this->faker->randomElement(['private', 'public']),
            'version' => 1,
        ];
    }

    /**
     * Create a file with specific mime type.
     */
    public function withMimeType(string $mimeType): static
    {
        return $this->state(fn (array $attributes) => [
            'mime_type' => $mimeType,
        ]);
    }

    /**
     * Create an image file.
     */
    public function image(): static
    {
        return $this->state(fn (array $attributes) => [
            'mime_type' => $this->faker->randomElement(['image/jpeg', 'image/png', 'image/gif']),
            'size_bytes' => $this->faker->numberBetween(1024, 15360) * 1024,
        ]);
    }

    /**
     * Create a document file.
     */
    public function document(): static
    {
        return $this->state(fn (array $attributes) => [
            'mime_type' => $this->faker->randomElement([
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            ]),
            'size_bytes' => $this->faker->numberBetween(512, 5120) * 1024,
        ]);
    }

    /**
     * Create a video file.
     */
    public function video(): static
    {
        return $this->state(fn (array $attributes) => [
            'mime_type' => $this->faker->randomElement(['video/mp4', 'video/avi', 'video/mov']),
            'size_bytes' => $this->faker->numberBetween(10240, 1048576) * 1024,
        ]);
    }

    /**
     * Create a public file.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'visibility' => 'public',
        ]);
    }

    /**
     * Create a private file.
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'visibility' => 'private',
        ]);
    }
}
