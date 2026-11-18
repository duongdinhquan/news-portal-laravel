<?php

namespace App\Services;

use App\Models\Article;
use App\Models\ArticleRevision;
use App\Models\Media;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ArticleService
{
    public function __construct(
        private readonly ContentSanitizer $sanitizer,
        private readonly AuditLogService $auditLog,
    ) {}

    public function create(User $author, array $data): Article
    {
        return DB::transaction(function () use ($author, $data) {
            $article = Article::create([
                'slug' => $this->uniqueSlug($data['title']),
                'author_id' => $author->id,
                'category_id' => $data['category_id'],
            ]);

            $this->assertMediaAttachable($author, $data['cover_media_id'] ?? null);
            $this->assertInlineMediaAttachable($author, $data['content_html']);
            $revision = $article->revisions()->create([
                'revision_number' => 1,
                'title' => $data['title'],
                'excerpt' => $data['excerpt'] ?? null,
                'content_json' => $data['content_json'] ?? null,
                'content_html' => $this->sanitizer->sanitize($data['content_html']),
                'cover_media_id' => $data['cover_media_id'] ?? null,
                'status' => 'DRAFT',
            ]);
            $article->update(['working_revision_id' => $revision->id]);
            $this->auditLog->record($author, 'article.created', $article, ['revision_id' => $revision->id]);
            return $article->fresh(['workingRevision', 'category']);
        });
    }

    public function update(Article $article, User $actor, array $data): Article
    {
        return DB::transaction(function () use ($article, $actor, $data) {
            $article = Article::query()->lockForUpdate()->findOrFail($article->id);
            if (!$actor->isAdmin() && $article->author_id !== $actor->id) {
                abort(403);
            }

            $working = $article->workingRevision()->lockForUpdate()->first();
            if ($working && $working->status === 'PENDING_REVIEW') {
                throw ValidationException::withMessages(['article' => 'Bài viết đang chờ duyệt, không thể chỉnh sửa.']);
            }

            $this->assertMediaAttachable($actor, $data['cover_media_id'] ?? null);
            $this->assertInlineMediaAttachable($actor, $data['content_html']);
            $revisionNumber = (int) $article->revisions()->max('revision_number') + 1;
            $revision = $working;

            // Published articles are edited as a new revision; the currently published pointer stays untouched.
            if (!$revision || $revision->status === 'PUBLISHED' || $revision->status === 'SUPERSEDED') {
                $revision = new ArticleRevision(['revision_number' => $revisionNumber]);
                $revision->article_id = $article->id;
            }

            $revision->fill([
                'title' => $data['title'],
                'excerpt' => $data['excerpt'] ?? null,
                'content_json' => $data['content_json'] ?? null,
                'content_html' => $this->sanitizer->sanitize($data['content_html']),
                'cover_media_id' => $data['cover_media_id'] ?? null,
                'status' => 'DRAFT',
                'rejection_reason' => null,
                'reviewer_id' => null,
                'submitted_at' => null,
                'reviewed_at' => null,
            ]);
            $revision->save();

            $article->category_id = $data['category_id'];
            $article->working_revision_id = $revision->id;
            $article->save();
            $this->auditLog->record($actor, 'article.updated', $article, ['revision_id' => $revision->id]);
            return $article->fresh(['workingRevision', 'currentPublishedRevision', 'category']);
        });
    }

    public function submit(Article $article, User $actor): ArticleRevision
    {
        return DB::transaction(function () use ($article, $actor) {
            $article = Article::query()->lockForUpdate()->findOrFail($article->id);
            if (!$actor->isAdmin() && $article->author_id !== $actor->id) abort(403);
            $revision = $article->workingRevision()->lockForUpdate()->first();
            if (!$revision || !in_array($revision->status, ['DRAFT', 'REJECTED'], true)) {
                throw ValidationException::withMessages(['article' => 'Chỉ bản nháp hoặc bài bị từ chối mới có thể gửi duyệt.']);
            }
            $revision->update(['status' => 'PENDING_REVIEW', 'submitted_at' => now(), 'rejection_reason' => null]);
            $this->auditLog->record($actor, 'article.submitted', $article, ['revision_id' => $revision->id]);
            return $revision->fresh();
        });
    }

    private function assertMediaAttachable(User $actor, ?string $mediaId): void
    {
        if (!$mediaId) return;
        $media = Media::query()->findOrFail($mediaId);
        if (!$actor->isAdmin() && $media->uploaded_by_id !== $actor->id) abort(403, 'Bạn không có quyền sử dụng ảnh này.');
    }


    private function assertInlineMediaAttachable(User $actor, string $html): void
    {
        if (!preg_match_all('/<img\b[^>]*\bsrc\s*=\s*(["\'])(.*?)\1/i', $html, $matches)) return;

        foreach ($matches[2] as $source) {
            $source = html_entity_decode($source, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $parts = parse_url($source);
            $path = is_array($parts) ? ($parts['path'] ?? '') : '';
            if (!str_starts_with($path, '/storage/')) continue; // Sanitizer will remove non-local images.

            $storagePath = substr($path, strlen('/storage/'));
            $media = Media::query()->where('storage_disk', 'public')->where('storage_path', $storagePath)->first();
            if (!$media) {
                throw ValidationException::withMessages(['content_html' => 'Ảnh trong nội dung không thuộc thư viện media hợp lệ.']);
            }
            if (!$actor->isAdmin() && $media->uploaded_by_id !== $actor->id) {
                abort(403, 'Bạn không có quyền sử dụng ảnh của tài khoản khác.');
            }
        }
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title, '-', 'vi') ?: 'bai-viet';
        $slug = $base;
        while (Article::where('slug', $slug)->exists()) $slug = $base.'-'.Str::lower(Str::random(5));
        return $slug;
    }
}
