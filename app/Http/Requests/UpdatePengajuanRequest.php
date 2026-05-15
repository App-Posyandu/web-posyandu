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
