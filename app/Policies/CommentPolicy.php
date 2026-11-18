<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    public function delete(User $user, Comment $comment): bool
    {
        return $user->isAdmin() || ($user->isReader() && $comment->user_id === $user->id && $comment->status === 'PENDING');
    }

    public function moderate(User $user): bool
    {
        return $user->isAdmin();
    }
}
