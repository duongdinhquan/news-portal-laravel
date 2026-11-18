<?php

namespace App\Http\Controllers;

use App\Http\Requests\Comment\StoreCommentRequest;
use App\Models\Article;
use App\Models\Comment;
use App\Services\CommentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    // tu dong khoi chay khi co router goi den controller nay ,
    // ki thuat injection de inject service vao controller
    public function __construct(private readonly CommentService $comments) {}

    public function store(StoreCommentRequest $request, string $article): RedirectResponse
    {
        // thuc hien scopePublished
        // firstOrFail() la ban ghi dau tien hoac tra ve 404 neu khong co ban ghi nao
        $post = Article::published()->where('slug', $article)->firstOrFail();
        
        $this->comments->create($post, $request->user(), $request->validated('content'));
        return redirect()->route('articles.show', $post->slug.'#comments')->with('success', 'Bình luận đã được gửi và đang chờ kiểm duyệt.');
    }

    public function destroy(Request $request, Comment $comment): RedirectResponse
    {
        $this->authorize('delete', $comment);
        $comment->delete();
        return back()->with('success', 'Đã xóa bình luận.');
    }
}

