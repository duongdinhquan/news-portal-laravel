<?php

namespace App\Services;

use App\Models\Media;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MediaService
{
    public function __construct(private readonly AuditLogService $auditLog) {}

    public function upload(UploadedFile $file, User $actor): Media
    {
        $maxBytes = (int) config('news.max_image_size_mb', 5) * 1024 * 1024;
        if ($file->getSize() > $maxBytes) {
            $limitMb = (int) config('news.max_image_size_mb', 5);
            throw ValidationException::withMessages(['image' => "Ảnh vượt quá dung lượng cho phép ({$limitMb} MB)."]);
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $detectedMime = $finfo->file($file->getPathname());
        $imageInfo = @getimagesize($file->getPathname());
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];
        if (!$imageInfo || !isset($allowed[$detectedMime]) || ($imageInfo['mime'] ?? null) !== $detectedMime) {
            throw ValidationException::withMessages(['image' => 'Tệp tải lên không phải ảnh JPEG, PNG hoặc WebP hợp lệ.']);
        }

        [$width, $height] = [$imageInfo[0], $imageInfo[1]];
        if ($width < 1 || $height < 1 || $width * $height > (int) config('news.max_image_pixels', 20000000)) {
            throw ValidationException::withMessages(['image' => 'Kích thước ảnh vượt quá giới hạn pixel cho phép.']);
        }

        // getimagesize checks the header; decode once as an additional malformed-file check.
        $decoded = @imagecreatefromstring((string) file_get_contents($file->getPathname()));
        if ($decoded === false) {
            throw ValidationException::withMessages(['image' => 'Không thể giải mã ảnh tải lên.']);
        }
        imagedestroy($decoded);

        $extension = $allowed[$detectedMime];
        $filename = (string) Str::uuid().'.'.$extension;
        $directory = 'media/'.now()->format('Y/m');
        $path = $file->storePubliclyAs($directory, $filename, 'public');

        $media = Media::create([
            'storage_disk' => 'public',
            'storage_path' => $path,
            'original_name' => Str::limit(basename($file->getClientOriginalName()), 190, ''),
            'mime_type' => $detectedMime,
            'size_bytes' => (int) $file->getSize(),
            'width' => $width,
            'height' => $height,
            'uploaded_by_id' => $actor->id,
        ]);
        $this->auditLog->record($actor, 'media.uploaded', $media, ['mime_type' => $detectedMime, 'size_bytes' => $media->size_bytes]);
        return $media;
    }

    public function delete(Media $media, User $actor): void
    {
        $inlinePath = '/storage/'.$media->storage_path;
        $isUsed = \App\Models\ArticleRevision::query()
            ->where('cover_media_id', $media->id)
            ->orWhere('content_html', 'like', '%'.$inlinePath.'%')
            ->exists();
        if ($isUsed) {
            throw ValidationException::withMessages(['media' => 'Ảnh đang được sử dụng trong bài viết, không thể xóa.']);
        }
        Storage::disk($media->storage_disk)->delete($media->storage_path);
        $this->auditLog->record($actor, 'media.deleted', $media);
        $media->delete();
    }
}
