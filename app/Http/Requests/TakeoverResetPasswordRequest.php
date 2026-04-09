<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class TakeoverResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
            'reason' => ['required', 'string', 'max:500'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $allowed = ['_token', '_method', 'new_password', 'new_password_confirmation', 'reason'];
            $extra = array_diff(array_keys($this->all()), $allowed);

            if (!empty($extra)) {
                $validator->errors()->add('request', 'Terdapat parameter tidak dikenali: ' . implode(', ', $extra));
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'reason' => $this->normalizeText($this->input('reason')),
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
