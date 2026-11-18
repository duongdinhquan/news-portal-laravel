<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasUuids;

    protected $fillable = [
        'slug', 'author_id', 'category_id', 'current_published_revision_id',
        'working_revision_id', 'published_at', 'archived_at',
    ];

    protected function casts(): array
    {
        return ['published_at' => 'datetime', 'archived_at' => 'datetime'];
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function revisions()
    {
        return $this->hasMany(ArticleRevision::class);
    }

    public function currentPublishedRevision()
    {
        return $this->belongsTo(ArticleRevision::class, 'current_published_revision_id');
    }

    public function workingRevision()
    {
        return $this->belongsTo(ArticleRevision::class, 'working_revision_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('current_published_revision_id')
            ->whereNull('archived_at')
            ->whereNotNull('published_at');
    }
}
