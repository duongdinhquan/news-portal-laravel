<?php

namespace App\Http\Requests\Article;

use App\Models\Article;
use Illuminate\Foundation\Http\FormRequest;

class StoreArticleRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        foreach (['title', 'excerpt'] as $field) {
            if (is_string($this->input($field))) {
                $this->merge([$field => trim($this->input($field))]);
            }
        }

        $contentJson = $this->input('content_json');
        if (is_string($contentJson) && $contentJson !== '') {
            try {
                $this->merge(['content_json' => json_decode($contentJson, true, 512, JSON_THROW_ON_ERROR)]);
            } catch (\JsonException) {
                // Leave invalid JSON unchanged so the array validation rule rejects it.
            }
        }
    }

    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('create', Article::class);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:5', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'category_id' => ['required', 'uuid', 'exists:categories,id'],
            'content_json' => ['nullable', 'array'],
            'content_html' => ['required', 'string', 'max:1000000'],
            'cover_media_id' => ['nullable', 'uuid', 'exists:media,id'],
        ];
    }

    public function messages(): array
    {
        return ['title.min' => 'Tiêu đề cần ít nhất 5 ký tự.', 'category_id.required' => 'Vui lòng chọn chuyên mục.', 'content_html.required' => 'Nội dung bài viết không được để trống.'];
    }
}
