<?php

namespace App\Http\Requests\Article;

use App\Models\Article;
use Illuminate\Foundation\Http\FormRequest;

class SubmitArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $article = $this->route('article');
        return $this->user() && $article instanceof Article && $this->user()->can('submit', $article);
    }

    public function rules(): array { return []; }
}
