<?php

namespace App\Services;

use App\Models\Article;
use App\Models\ArticleRevision;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ArticleReviewService
{
    public function __construct(private readonly AuditLogService $auditLog) {}

    public function publish(Article $article, User $admin): ArticleRevision
    {
        return DB::transaction(function () use ($article, $admin) {
            $article = Article::query()->lockForUpdate()->findOrFail($article->id);
            $revision = $article->workingRevision()->lockForUpdate()->first();
            if (!$revision || $revision->status !== 'PENDING_REVIEW') {
                throw ValidationException::withMessages(['article' => 'Bài viết không ở trạng thái chờ duyệt.']);
            }
            if ($article->current_published_revision_id) {
                ArticleRevision::whereKey($article->current_published_revision_id)->update(['status' => 'SUPERSEDED']);
            }
            $revision->update([
                'status' => 'PUBLISHED',
                'reviewer_id' => $admin->id,
                'reviewed_at' => now(),
                'rejection_reason' => null,
            ]);
            $article->update([
                'current_published_revision_id' => $revision->id,
                'working_revision_id' => null,
                'published_at' => $article->published_at ?? now(),
                'archived_at' => null,
            ]);
            $this->auditLog->record($admin, 'article.published', $article, ['revision_id' => $revision->id]);
            return $revision->fresh();
        });
    }

    public function reject(Article $article, User $admin, string $reason): ArticleRevision
    {
        return DB::transaction(function () use ($article, $admin, $reason) {
            $article = Article::query()->lockForUpdate()->findOrFail($article->id);
            $revision = $article->workingRevision()->lockForUpdate()->first();
            if (!$revision || $revision->status !== 'PENDING_REVIEW') {
                throw ValidationException::withMessages(['article' => 'Bài viết không ở trạng thái chờ duyệt.']);
            }
            $revision->update([
                'status' => 'REJECTED',
                'rejection_reason' => $reason,
                'reviewer_id' => $admin->id,
                'reviewed_at' => now(),
            ]);
            $this->auditLog->record($admin, 'article.rejected', $article, ['revision_id' => $revision->id, 'reason' => $reason]);
            return $revision->fresh();
        });
    }

    public function archive(Article $article, User $admin): void
    {
        DB::transaction(function () use ($article, $admin) {
            $article->update(['archived_at' => now()]);
            $this->auditLog->record($admin, 'article.archived', $article);
        });
    }
}
