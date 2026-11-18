<?php

namespace App\Http\Controllers\Editor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Media\UploadMediaRequest;
use App\Models\Article;
use App\Models\Media;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function __construct(private readonly MediaService $media) {}

    public function index(Request $request): View
    {
        $items = Media::where('uploaded_by_id', $request->user()->id)->latest()->paginate(24);
        $articles = Article::where('author_id', $request->user()->id)->latest()->get(['id', 'slug']);
        return view('editor.media.index', compact('items', 'articles'));
    }

    public function store(UploadMediaRequest $request): JsonResponse|RedirectResponse
    {
        $data = $request->validated();
        $user = $request->user();
        if ($user->isEditor()) {
            $articleId = $data['article_id'] ?? null;
            abort_unless($articleId, 422, 'Hãy chọn bài viết mà ảnh này phục vụ.');
            $article = Article::findOrFail($articleId);
            $this->authorize('update', $article);
        }
        $media = $this->media->upload($request->file('image'), $user);
        if ($request->expectsJson()) {
            return response()->json(['id' => $media->id, 'url' => $media->url, 'mime_type' => $media->mime_type, 'width' => $media->width, 'height' => $media->height], 201);
        }
        return back()->with('success', 'Đã tải ảnh lên thư viện media.');
    }

    public function destroy(Request $request, Media $media): RedirectResponse
    {
        $this->authorize('delete', $media);
        abort_unless($media->uploaded_by_id === $request->user()->id, 403);
        $this->media->delete($media, $request->user());
        return back()->with('success', 'Đã xóa ảnh.');
    }
}
