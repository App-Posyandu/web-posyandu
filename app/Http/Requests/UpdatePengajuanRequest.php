<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePengajuanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'deskripsi_pengajuan' => ['required', 'string', 'min:10', 'max:2000'],
            'permohonan_items' => ['required', 'array', 'min:1', 'max:50'],
            'permohonan_items.*' => ['string', 'max:255'],
            'lainnya_text' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'deskripsi_pengajuan.required' => 'Deskripsi pengajuan wajib diisi.',
            'deskripsi_pengajuan.min' => 'Inputan kurang dari :min karakter.',
            'deskripsi_pengajuan.max' => 'Inputan melebihi batas maksimal :max karakter.',
            
            'permohonan_items.required' => 'Minimal pilih 1 permohonan.',
            'permohonan_items.min' => 'Minimal pilih 1 permohonan.',
            'permohonan_items.max' => 'Pilihan permohonan maksimal :max item.',
            'lainnya_text.max' => 'Teks lainnya maksimal :max karakter.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'deskripsi_pengajuan' => is_string($this->input('deskripsi_pengajuan'))
                ? trim(preg_replace('/\s+/', ' ', $this->input('deskripsi_pengajuan')))
                : $this->input('deskripsi_pengajuan'),
            'lainnya_text' => is_string($this->input('lainnya_text'))
                ? trim(preg_replace('/\s+/', ' ', $this->input('lainnya_text')))
                : $this->input('lainnya_text'),
        ]);
    }
}
