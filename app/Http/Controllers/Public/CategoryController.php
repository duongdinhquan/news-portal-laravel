<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function show(string $slug): View
    {
        $category = Category::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $articles = $category->articles()->published()->with(['currentPublishedRevision.coverMedia', 'author', 'category'])
            ->latest('published_at')->paginate(12);
        return view('public.category', compact('category', 'articles'));
    }
}
