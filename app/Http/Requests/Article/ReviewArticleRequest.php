<?php

namespace App\Http\Requests\Article;

use Illuminate\Foundation\Http\FormRequest;

class ReviewArticleRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('rejection_reason'))) $this->merge(['rejection_reason' => trim($this->input('rejection_reason'))]);
    }

    public function authorize(): bool { return $this->user()?->isAdmin() ?? false; }

    public function rules(): array
    {
        return ['rejection_reason' => ['required', 'string', 'min:5', 'max:2000']];
    }
}
