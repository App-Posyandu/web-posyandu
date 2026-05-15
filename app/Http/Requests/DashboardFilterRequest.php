<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class DashboardFilterRequest extends FormRequest
{
    public static function allowedQueryKeys(): array
    {
        return ['year', 'search', 'status', 'archived', 'ajax', 'page'];
    }

    public static function filterRules(?int $maxYear = null): array
    {
        $maxYear ??= now()->year;

        return [
            'search' => ['nullable', 'string', 'max:100', 'regex:/^[\pL\pN\s@\._\-,()]+$/u'],
            'status' => ['nullable', Rule::in(['Diproses', 'Disetujui', 'Ditolak'])],
            'archived' => ['nullable', 'boolean'],
            'ajax' => ['nullable', 'boolean'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return self::filterRules();
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $allowed = self::allowedQueryKeys();
            $extra = array_diff(array_keys($this->query()), $allowed);

            if (!empty($extra)) {
                $validator->errors()->add('request', 'Terdapat parameter tidak dikenali: ' . implode(', ', $extra));
            }
        });
    }

    protected function failedValidation(ValidatorContract $validator)
    {
        throw new HttpResponseException(response()->json([
            'error' => 'Invalid dashboard filter',
            'message' => 'Parameter filter dashboard tidak valid.',
            'errors' => $validator->errors(),
        ], 400));
    }

    protected function prepareForValidation(): void
    {
        $search = $this->normalizeText($this->query('search'));
        $status = $this->normalizeText($this->query('status'));
        $year = $this->query('year');

        if (is_string($year)) {
            $year = trim($year);
        }

        $this->merge([
            'year' => $this->emptyToNull($year),
            'search' => $search === '' ? null : $search,
            'status' => $status === '' ? null : $status,
            'archived' => $this->emptyToNull($this->query('archived')),
            'ajax' => $this->emptyToNull($this->query('ajax')),
            'page' => $this->emptyToNull($this->query('page')),
        ]);
    }

    private function normalizeText(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        return trim(preg_replace('/\s+/', ' ', $value));
    }

    private function emptyToNull(mixed $value): mixed
    {
        if ($value === '') {
            return null;
        }

        return $value;
    }
}
