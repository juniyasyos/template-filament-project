<?php

namespace App\Domain\Drive\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class DriveFavorite extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'drive_node_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Relationship: User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Drive node
     */
    public function driveNode(): BelongsTo
    {
        return $this->belongsTo(DriveNode::class, 'drive_node_id');
    }
}
