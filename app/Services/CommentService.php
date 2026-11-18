<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CommentService
{
    public function __construct(private readonly AuditLogService $auditLog) {}

    public function create(Article $article, User $user, string $content): Comment
    {
        abort_unless($article->current_published_revision_id && !$article->archived_at, 404);
        return Comment::create([
            'article_id' => $article->id,
            'user_id' => $user->id,
            'content' => trim($content),
            'status' => 'PENDING',
        ]);
    }

    public function moderate(Comment $comment, User $admin, string $status): Comment
    {
        return DB::transaction(function () use ($comment, $admin, $status) {
            $comment->update([
                'status' => $status,
                'moderated_by' => $admin->id,
                'moderated_at' => now(),
            ]);
            $this->auditLog->record($admin, 'comment.moderated', $comment, ['status' => $status]);
            return $comment->fresh();
        });
    }
}
