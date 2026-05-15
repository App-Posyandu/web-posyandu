<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdatePosyanduRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && in_array($user->role, ['admin', 'operator-desa'], true);
    }

    public function rules(): array
    {
        return [
            'nama_posyandu' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9\s\-\.]+$/'],
            'kabupaten' => ['required', 'string', 'max:120', 'regex:/^[A-Za-z0-9\s\.\-_]+$/'],
            'kecamatan' => ['required', 'string', 'max:120', 'regex:/^[A-Za-z0-9\s\.\-_]+$/'],
            'desa' => ['required', 'string', 'max:120', 'regex:/^[A-Za-z0-9\s\.\-_]+$/'],
            'rw_list' => ['nullable', 'array', 'max:15'],
            'rw_list.*' => ['nullable', 'string', 'regex:/^RW\d{2}$/'],
            'rt_mapping' => ['nullable', 'array'],
            'rt_mapping.*' => ['nullable', 'array'],
            'rt_mapping.*.*' => ['nullable', 'string', 'regex:/^RT\d{3}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_posyandu.regex' => 'Nama posyandu hanya boleh huruf, angka, spasi, titik, dan tanda minus.',
            'kabupaten.regex' => 'Format kabupaten tidak valid.',
            'kecamatan.regex' => 'Format kecamatan tidak valid.',
            'desa.regex' => 'Format desa tidak valid.',
            'rw_list.max' => 'Maksimal 15 RW per posyandu.',
            'rw_list.*.regex' => 'Format RW harus: RW01, RW02, dst.',
            'rt_mapping.*.*.regex' => 'Format RT harus: RT001, RT002, dst.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $allowed = [
                '_token',
                '_method',
                'nama_posyandu',
                'kabupaten',
                'kecamatan',
                'desa',
                'rw_list',
                'rt_mapping',
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
            'nama_posyandu' => $this->normalizeText($this->input('nama_posyandu')),
            'kabupaten' => $this->normalizeText($this->input('kabupaten')),
            'kecamatan' => $this->normalizeText($this->input('kecamatan')),
            'desa' => $this->normalizeText($this->input('desa')),
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
