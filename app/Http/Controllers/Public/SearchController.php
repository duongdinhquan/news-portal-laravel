<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __invoke(SearchRequest $request): View
    {
        $data = $request->validated();
        $term = $data['q'] ?? '';
        $query = Article::published()->with(['currentPublishedRevision.coverMedia', 'author', 'category']);

        if ($term !== '') {
            $like = '%'.addcslashes($term, '%_\\').'%';
            $query->whereHas('currentPublishedRevision', function (Builder $revision) use ($like) {
                $revision->where('title', 'like', $like)
                    ->orWhere('excerpt', 'like', $like)
                    ->orWhere('content_html', 'like', $like);
            });
        }
        if (!empty($data['category'])) {
            $query->whereHas('category', fn (Builder $category) => $category->where('slug', $data['category']));
        }

        $articles = $query->latest('published_at')->paginate($data['limit'])->withQueryString();
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        return view('public.search', compact('articles', 'categories', 'term', 'data'));
    }
}
