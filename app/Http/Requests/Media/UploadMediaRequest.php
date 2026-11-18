<?php

namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

class UploadMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && in_array($this->user()->role, ['ADMIN', 'EDITOR'], true);
    }

    public function rules(): array
    {
        return [
            'image' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:'.((int) config('news.max_image_size_mb', 5) * 1024)],
            'article_id' => ['nullable', 'uuid', 'exists:articles,id'],
        ];
    }
}
