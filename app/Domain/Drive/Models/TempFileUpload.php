<?php

namespace App\Domain\Drive\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Temporary model for file uploads using Spatie MediaLibrary
 * Used for processing file uploads before creating DriveFile records
 */
class TempFileUpload extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'temp_uploads';

    public $timestamps = false;

    protected $fillable = [];

    /**
     * Define media collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('drive_files')
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png', 'text/plain', 'application/vnd.ms-excel']);
    }
}
