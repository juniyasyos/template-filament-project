<?php

namespace App\Domain\Drive\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DriveFolder extends Model
{
    use HasFactory;

    protected $fillable = [
        'drive_node_id',
        'cover_media_id',
        'color',
        'icon',
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \App\Domain\Drive\Database\Factories\DriveFolderFactory::new();
    }

    /**
     * Relationship: Parent drive node
     */
    public function driveNode(): BelongsTo
    {
        return $this->belongsTo(DriveNode::class, 'drive_node_id');
    }

    /**
     * Relationship: Cover media
     */
    public function coverMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }
}
