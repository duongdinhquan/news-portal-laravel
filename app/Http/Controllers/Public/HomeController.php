<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $base = Article::published()->with(['currentPublishedRevision.coverMedia', 'author', 'category']);
        $featured = (clone $base)->latest('published_at')->take(4)->get();
        $latest = (clone $base)->latest('published_at')->take(12)->get();
        $categoryBlocks = Category::where('is_active', true)->orderBy('sort_order')->with([
            'articles' => fn ($query) => $query->published()->with(['currentPublishedRevision.coverMedia', 'author'])->latest('published_at')->limit(4),
        ])->get();

        return view('public.home', compact('featured', 'latest', 'categoryBlocks'));
    }
}
