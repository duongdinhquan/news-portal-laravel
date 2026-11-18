<?php

namespace App\Http\Requests\Comment;

use Illuminate\Foundation\Http\FormRequest;
// cac method trong class nay duoc chay tu dong khi khai bao o controller
class StoreCommentRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('content'))) $this->merge(['content' => trim($this->input('content'))]);
    }
    // built-in method cua FormRequest, tra ve true thi moi cho phep request nay duoc xu ly tiep
    // nhin vao config/auth.php, co guard la web, dung session de lay thong tin user tu session, neu khong co session thi tra ve null
    public function authorize(): bool { return $this->user()?->isReader() ?? false; }

    public function rules(): array
    {
        return ['content' => ['required', 'string', 'min:3', 'max:2000']];
    }
}
