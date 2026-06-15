<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        $user = $this->route('user');
        $userId = $user instanceof User ? $user->id : $user;

        return [
            'name' => ['required', 'string', 'max:255'],
            'nik' => ['nullable', 'string', 'digits:16', Rule::unique(User::class, 'nik')->ignore($userId)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class, 'email')->ignore($userId)],
            'no_telepon' => ['nullable', 'string', 'max:20'],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'jenis_kelamin' => ['nullable', 'string', 'in:Laki-laki,Perempuan'],
            'alamat' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            // Identitas
            'name.required'         => 'Nama lengkap wajib diisi.',
            'name.max'              => 'Nama maksimal 255 karakter.',
            'tanggal_lahir.date'    => 'Format tanggal lahir tidak valid.',
            'jenis_kelamin.in'      => 'Jenis kelamin tidak valid.',
            // NIK
            'nik.digits'            => 'NIK harus tepat 16 digit angka.',
            'nik.unique'            => 'NIK sudah terdaftar, gunakan NIK yang benar.',
            // Email
            'email.required'        => 'Email wajib diisi.',
            'email.email'           => 'Format email tidak valid.',
            'email.max'             => 'Email maksimal 255 karakter.',
            'email.unique'          => 'Email sudah terdaftar, gunakan email lain.',
            // No. Telepon
            'no_telepon.max'        => 'Nomor WhatsApp maksimal 20 digit.',
            // Password
            'password.min'          => 'Password minimal 8 karakter.',
            'password.confirmed'    => 'Konfirmasi password tidak cocok.',
            // Alasan
            'reason.max'            => 'Alasan maksimal 500 karakter.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $allowed = [
                '_token',
                '_method',
                'name',
                'nik',
                'email',
                'no_telepon',
                'tempat_lahir',
                'tanggal_lahir',
                'jenis_kelamin',
                'alamat',
                'is_active',
                'password',
                'password_confirmation',
                'reason',
            ];

            $extra = array_diff(array_keys($this->all()), $allowed);
            if (!empty($extra)) {
                $validator->errors()->add('request', 'Terdapat parameter tidak dikenali: ' . implode(', ', $extra));
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->normalizeText($this->input('name')),
            'nik' => $this->normalizeText($this->input('nik')),
            'email' => $this->normalizeText($this->input('email')),
            'no_telepon' => $this->normalizeText($this->input('no_telepon')),
            'tempat_lahir' => $this->normalizeText($this->input('tempat_lahir')),
            'jenis_kelamin' => $this->normalizeText($this->input('jenis_kelamin')),
            'alamat' => $this->normalizeText($this->input('alamat')),
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
