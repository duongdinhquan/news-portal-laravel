<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }

    public function rules(): array
    {
        return [
            'current_password' => ['required', function (string $attribute, mixed $value, \Closure $fail): void {
                if (!Hash::check((string) $value, $this->user()->password)) $fail('Mật khẩu hiện tại không chính xác.');
            }],
            'password' => ['required', 'confirmed', Password::min(10)->letters()->numbers()],
        ];
    }
}
