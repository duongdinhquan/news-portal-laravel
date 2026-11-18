<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Article\ReviewArticleRequest;
use App\Models\Article;
use App\Services\ArticleReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function __construct(private readonly ArticleReviewService $review) {}

    public function index(): View
    {
        $articles = Article::with(['workingRevision', 'currentPublishedRevision', 'author', 'category'])->latest('updated_at')->paginate(20);
        return view('admin.articles.index', compact('articles'));
    }

    public function queue(): View
    {
        $articles = Article::whereHas('workingRevision', fn ($q) => $q->where('status', 'PENDING_REVIEW'))
            ->with(['workingRevision.coverMedia', 'author', 'category'])->latest('updated_at')->paginate(20);
        return view('admin.articles.queue', compact('articles'));
    }

    public function publish(Article $article): RedirectResponse
    {
        $this->review->publish($article, request()->user());
        return back()->with('success', 'Đã duyệt và xuất bản bài viết.');
    }

    public function reject(ReviewArticleRequest $request, Article $article): RedirectResponse
    {
        $this->review->reject($article, $request->user(), $request->validated('rejection_reason'));
        return back()->with('success', 'Đã từ chối bài viết và gửi lý do cho Editor.');
    }

    public function archive(Article $article): RedirectResponse
    {
        $this->review->archive($article, request()->user());
        return back()->with('success', 'Đã lưu trữ bài viết.');
    }
}
