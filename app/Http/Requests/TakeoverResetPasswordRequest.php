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

    public function messages(): array
    {
        return [
            'new_password.required'  => 'Password baru wajib diisi.',
            'new_password.min'       => 'Password baru minimal 8 karakter.',
            'new_password.confirmed' => 'Konfirmasi password tidak cocok.',
            'reason.required'        => 'Alasan pengambilalihan wajib diisi.',
            'reason.max'             => 'Alasan maksimal 500 karakter.',
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
