<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ModerateCommentRequest;
use App\Models\Comment;
use App\Services\CommentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CommentController extends Controller
{
    public function index(): View
    {
        $comments = Comment::with(['user', 'article.currentPublishedRevision'])->latest()->paginate(25);
        return view('admin.comments.index', compact('comments'));
    }

    public function moderate(ModerateCommentRequest $request, Comment $comment, CommentService $service): RedirectResponse
    {
        $this->authorize('moderate', Comment::class);
        $service->moderate($comment, $request->user(), $request->validated('status'));
        return back()->with('success', 'Đã cập nhật trạng thái bình luận.');
    }
}
