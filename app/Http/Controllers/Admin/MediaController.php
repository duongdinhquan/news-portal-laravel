<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Media\UploadMediaRequest;
use App\Models\Media;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function index(): View
    {
        $items = Media::with('uploadedBy')->latest()->paginate(30);
        return view('admin.media.index', compact('items'));
    }

    public function store(UploadMediaRequest $request, MediaService $service): JsonResponse|RedirectResponse
    {
        $media = $service->upload($request->file('image'), $request->user());
        if ($request->expectsJson()) {
            return response()->json(['id' => $media->id, 'url' => $media->url, 'mime_type' => $media->mime_type], 201);
        }
        return back()->with('success', 'Đã tải ảnh lên thư viện media.');
    }

    public function destroy(Request $request, Media $media, MediaService $service): RedirectResponse
    {
        $this->authorize('delete', $media);
        $service->delete($media, $request->user());
        return back()->with('success', 'Đã xóa media.');
    }
}
