<?php

namespace App\Domain\Drive\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriveNodePath extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = ['ancestor_id', 'descendant_id'];

    protected $fillable = [
        'ancestor_id',
        'descendant_id',
        'depth',
    ];

    protected $casts = [
        'depth' => 'integer',
    ];

    /**
     * Relationship: Ancestor node
     */
    public function ancestor(): BelongsTo
    {
        return $this->belongsTo(DriveNode::class, 'ancestor_id');
    }

    /**
     * Relationship: Descendant node
     */
    public function descendant(): BelongsTo
    {
        return $this->belongsTo(DriveNode::class, 'descendant_id');
    }
}
