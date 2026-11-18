<?php

namespace App\Http\Controllers\Editor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Article\StoreArticleRequest;
use App\Http\Requests\Article\SubmitArticleRequest;
use App\Http\Requests\Article\UpdateArticleRequest;
use App\Models\Article;
use App\Models\Category;
use App\Services\ArticleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function __construct(private readonly ArticleService $articles) {}

    public function index(Request $request): View
    {
        $articles = Article::where('author_id', $request->user()->id)
            ->with(['workingRevision', 'currentPublishedRevision', 'category'])
            ->latest('updated_at')->paginate(12);
        return view('editor.articles.index', compact('articles'));
    }

    public function create(): View
    {
        return view('editor.articles.form', ['article' => null, 'revision' => null, 'categories' => Category::where('is_active', true)->orderBy('sort_order')->get()]);
    }

    public function store(StoreArticleRequest $request): RedirectResponse
    {
        $article = $this->articles->create($request->user(), $request->validated());
        return redirect()->route('editor.articles.edit', $article)->with('success', 'Đã lưu bản nháp.');
    }

    public function edit(Request $request, Article $article): View
    {
        $this->authorize('view', $article);
        $revision = $article->workingRevision ?: $article->currentPublishedRevision;
        abort_unless($revision, 404);
        return view('editor.articles.form', [
            'article' => $article->load('workingRevision', 'currentPublishedRevision'),
            'revision' => $revision,
            'categories' => Category::where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function update(UpdateArticleRequest $request, Article $article): RedirectResponse
    {
        $this->authorize('update', $article);
        $this->articles->update($article, $request->user(), $request->validated());
        return redirect()->route('editor.articles.edit', $article)->with('success', 'Đã cập nhật bản nháp.');
    }

    public function submit(SubmitArticleRequest $request, Article $article): RedirectResponse
    {
        $this->articles->submit($article, $request->user());
        return redirect()->route('editor.articles.index')->with('success', 'Đã gửi bài viết đến Admin để duyệt.');
    }
}
