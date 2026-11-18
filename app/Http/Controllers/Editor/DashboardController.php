<?php

namespace App\Http\Controllers\Editor;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $articles = Article::where('author_id', $request->user()->id)->with('workingRevision')->latest('updated_at')->take(8)->get();
        $counts = [
            'DRAFT' => Article::where('author_id', $request->user()->id)->whereHas('workingRevision', fn ($q) => $q->where('status', 'DRAFT'))->count(),
            'PENDING_REVIEW' => Article::where('author_id', $request->user()->id)->whereHas('workingRevision', fn ($q) => $q->where('status', 'PENDING_REVIEW'))->count(),
            'PUBLISHED' => Article::where('author_id', $request->user()->id)->whereNotNull('current_published_revision_id')->count(),
            'REJECTED' => Article::where('author_id', $request->user()->id)->whereHas('workingRevision', fn ($q) => $q->where('status', 'REJECTED'))->count(),
        ];
        return view('editor.dashboard', compact('articles', 'counts'));
    }
}
