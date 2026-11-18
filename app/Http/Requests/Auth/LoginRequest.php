<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['email' => is_string($this->input('email')) ? mb_strtolower(trim($this->input('email'))) : $this->input('email')]);
    }

    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return ['email' => ['required', 'email'], 'password' => ['required', 'string', 'max:255']];
    }
}
