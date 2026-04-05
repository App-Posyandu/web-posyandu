<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KaderIndexFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'search' => ['nullable', 'string', 'max:100', 'regex:/^[\pL\pN\s@\._\-,()]+$/u'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'search' => $this->normalizeText($this->input('search')),
        ]);
    }

    private function normalizeText(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        return trim(preg_replace('/\s+/', ' ', $value));
    }
}
