<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\Comment;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            'users' => User::count(),
            'editors' => User::where('role', 'EDITOR')->count(),
            'articles' => Article::count(),
            'pending_articles' => Article::whereHas('workingRevision', fn ($q) => $q->where('status', 'PENDING_REVIEW'))->count(),
            'pending_comments' => Comment::where('status', 'PENDING')->count(),
            'categories' => Category::count(),
        ];
        $pending = Article::whereHas('workingRevision', fn ($q) => $q->where('status', 'PENDING_REVIEW'))
            ->with(['workingRevision', 'author', 'category'])->latest('updated_at')->take(6)->get();
        return view('admin.dashboard', compact('stats', 'pending'));
    }
}
