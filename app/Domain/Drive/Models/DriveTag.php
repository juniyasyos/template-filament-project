<?php

namespace App\Domain\Drive\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DriveTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'color',
    ];

    /**
     * Relationship: Drive nodes
     */
    public function driveNodes(): BelongsToMany
    {
        return $this->belongsToMany(DriveNode::class, 'drive_node_tag', 'tag_id', 'drive_node_id');
    }
}
