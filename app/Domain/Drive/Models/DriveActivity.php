<?php

namespace App\Domain\Drive\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class DriveActivity extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'drive_node_id',
        'user_id',
        'action',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Relationship: Drive node
     */
    public function driveNode(): BelongsTo
    {
        return $this->belongsTo(DriveNode::class, 'drive_node_id');
    }

    /**
     * Relationship: User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get formatted action description
     */
    public function getActionDescriptionAttribute(): string
    {
        $actions = [
            'create' => 'Created',
            'rename' => 'Renamed',
            'move' => 'Moved',
            'copy' => 'Copied',
            'delete' => 'Deleted',
            'restore' => 'Restored',
            'upload' => 'Uploaded',
            'download' => 'Downloaded',
        ];

        return $actions[$this->action] ?? ucfirst($this->action);
    }
}
