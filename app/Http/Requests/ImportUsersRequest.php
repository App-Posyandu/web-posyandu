<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ImportUsersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
            'role' => ['nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $allowed = ['_token', '_method', 'file', 'role'];
            $extra = array_diff(array_keys($this->all()), $allowed);

            if (!empty($extra)) {
                $validator->errors()->add('request', 'Terdapat parameter tidak dikenali: ' . implode(', ', $extra));
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'role' => $this->normalizeText($this->input('role')),
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
