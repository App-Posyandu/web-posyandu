<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ResetUserPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $allowed = ['_token', '_method', 'new_password', 'new_password_confirmation'];
            $extra = array_diff(array_keys($this->all()), $allowed);

            if (!empty($extra)) {
                $validator->errors()->add('request', 'Terdapat parameter tidak dikenali: ' . implode(', ', $extra));
            }
        });
    }
}
