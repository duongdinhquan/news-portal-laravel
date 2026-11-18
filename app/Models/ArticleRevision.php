<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ArticleRevision extends Model
{
    use HasUuids;

    protected $fillable = [
        'article_id', 'revision_number', 'title', 'excerpt', 'content_json', 'content_html',
        'cover_media_id', 'status', 'rejection_reason', 'reviewer_id', 'submitted_at', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'content_json' => 'array',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function coverMedia()
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
