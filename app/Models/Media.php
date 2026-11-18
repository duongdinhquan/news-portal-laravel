<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasUuids;

    protected $fillable = [
        'storage_disk', 'storage_path', 'original_name', 'mime_type', 'size_bytes',
        'width', 'height', 'uploaded_by_id',
    ];

    protected $appends = ['url'];

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by_id');
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->storage_disk)->url($this->storage_path);
    }
}
