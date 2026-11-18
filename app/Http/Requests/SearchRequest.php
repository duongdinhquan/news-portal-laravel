<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'q' => is_string($this->query('q')) ? trim($this->query('q')) : $this->query('q'),
            'page' => max(1, (int) $this->query('page', 1)),
            'limit' => min(50, max(1, (int) $this->query('limit', 10))),
        ]);
    }

    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:120'],
            'page' => ['integer', 'min:1'],
            'limit' => ['integer', 'min:1', 'max:50'],
            'category' => ['nullable', 'string', 'max:120'],
        ];
    }
}
