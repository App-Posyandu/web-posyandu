<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePengajuanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'bidang_pelayanan' => ['required', 'string', 'max:64', 'regex:/^[a-z0-9\-]+$/', 'exists:bidang_pengajuans,slug'],
            'deskripsi_pengajuan' => ['required', 'string', 'min:10', 'max:2000'],
            'permohonan_items' => ['nullable', 'array', 'max:50'],
            'permohonan_items.*' => ['string', 'max:255'],
            'lainnya_text' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $allowed = [
                '_token',
                'bidang_pelayanan',
                'deskripsi_pengajuan',
                'permohonan_items',
                'lainnya_text',
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
            'bidang_pelayanan' => is_string($this->input('bidang_pelayanan'))
                ? trim(strtolower($this->input('bidang_pelayanan')))
                : $this->input('bidang_pelayanan'),
            'deskripsi_pengajuan' => is_string($this->input('deskripsi_pengajuan'))
                ? trim(preg_replace('/\s+/', ' ', $this->input('deskripsi_pengajuan')))
                : $this->input('deskripsi_pengajuan'),
            'lainnya_text' => is_string($this->input('lainnya_text'))
                ? trim(preg_replace('/\s+/', ' ', $this->input('lainnya_text')))
                : $this->input('lainnya_text'),
        ]);
    }
}
