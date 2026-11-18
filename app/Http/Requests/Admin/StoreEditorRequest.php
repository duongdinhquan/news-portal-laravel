<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreEditorRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['email' => is_string($this->input('email')) ? mb_strtolower(trim($this->input('email'))) : $this->input('email')]);
    }

    public function authorize(): bool { return $this->user()?->isAdmin() ?? false; }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', Password::min(10)->letters()->numbers()],
        ];
    }
}
