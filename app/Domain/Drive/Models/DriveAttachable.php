<?php

namespace App\Domain\Drive\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DriveAttachable extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'attachable_type',
        'attachable_id',
        'root_node_id',
    ];

    /**
     * Polymorphic relationship to attachable model
     */
    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Relationship: Root drive node
     */
    public function rootNode(): BelongsTo
    {
        return $this->belongsTo(DriveNode::class, 'root_node_id');
    }
}
