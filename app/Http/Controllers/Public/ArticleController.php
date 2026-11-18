<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function show(string $slug): View
    {
        $article = Article::published()
            ->where('slug', $slug)
            ->with(['currentPublishedRevision.coverMedia', 'author', 'category'])
            ->firstOrFail();

        $comments = $article->comments()->where('status', 'APPROVED')->with('user')->latest()->paginate(10);
        $related = Article::published()->where('category_id', $article->category_id)->where('id', '!=', $article->id)
            ->with(['currentPublishedRevision.coverMedia', 'category'])->latest('published_at')->take(4)->get();

        return view('public.article', compact('article', 'comments', 'related'));
    }
}
