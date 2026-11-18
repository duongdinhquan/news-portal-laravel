<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;

class ArticlePolicy
{
    public function view(User $user, Article $article): bool
    {
        return $user->isAdmin() || ($user->isEditor() && $article->author_id === $user->id);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isEditor();
    }

    public function update(User $user, Article $article): bool
    {
        if ($user->isAdmin()) return true;
        if (!$user->isEditor() || $article->author_id !== $user->id) return false;
        return !in_array($article->workingRevision?->status, ['PENDING_REVIEW'], true);
    }

    public function delete(User $user, Article $article): bool
    {
        return $user->isAdmin() || ($user->isEditor() && $article->author_id === $user->id && !$article->current_published_revision_id);
    }

    public function submit(User $user, Article $article): bool
    {
        return $user->isAdmin() || ($user->isEditor() && $article->author_id === $user->id);
    }
}
