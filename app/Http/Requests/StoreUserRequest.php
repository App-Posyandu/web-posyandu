<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        $currentRole = (string) ($this->user()?->role ?? '');
        $allowedRoles = $this->allowedRolesFor($currentRole);
        $role = $this->input('role');

        if (empty($role)) {
            return [
                'role' => ['required', Rule::in($allowedRoles)],
            ];
        }

        $baseRules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', Rule::in($allowedRoles)],
            'alamat' => ['required', 'string'],
            'no_telepon' => ['required', 'string', 'max:20', 'unique:users'],
            'tempat_lahir' => ['required', 'string', 'max:255'],
            'tanggal_lahir' => ['required', 'date'],
            'jenis_kelamin' => ['required', 'string'],
            'nik' => ['nullable', 'string', 'max:16', 'unique:users,nik'],
        ];

        $roleSpecificRules = [
            'ketua-timpembina-posyandu' => [
                'kabupaten' => ['required', 'string'],
            ],
            'kabid' => [
                'bidang_id' => ['required', 'uuid', 'exists:bidang_pengajuans,id'],
                'kabupaten' => ['required', 'string'],
            ],
            'kades' => [
                'kabupaten' => ['required', 'string'],
                'kecamatan' => ['required', 'string'],
                'desa' => ['required', 'string'],
            ],
            'bu-kades' => [
                'kabupaten' => ['required', 'string'],
                'kecamatan' => ['required', 'string'],
                'desa' => ['required', 'string'],
            ],
            'admin-kecamatan' => [
                'kabupaten' => ['required', 'string'],
                'kecamatan' => ['required', 'string'],
            ],
            'ketua-posyandu' => [
                'posyandu_id' => ['required', 'uuid', 'exists:posyandus,id'],
            ],
            'operator-desa' => [
                'kabupaten' => ['required', 'string'],
                'kecamatan' => ['required', 'string'],
                'desa' => ['required', 'string'],
            ],
            'kader' => [
                'bidang_id' => ['required', 'uuid', 'exists:bidang_pengajuans,id'],
                'posyandu_id' => $currentRole === 'ketua-posyandu'
                    ? ['nullable', 'uuid', 'exists:posyandus,id']
                    : ['required', 'uuid', 'exists:posyandus,id'],
            ],
        ];

        if (isset($roleSpecificRules[$role])) {
            $baseRules = array_merge($baseRules, $roleSpecificRules[$role]);
        }

        if ($role === 'masyarakat') {
            $baseRules['rw'] = ['required', 'string'];
            $baseRules['rt'] = ['required', 'string'];
            $baseRules['kabupaten'] = ['required', 'string'];
            $baseRules['kecamatan'] = ['required', 'string'];
            $baseRules['desa'] = ['required', 'string'];
            $baseRules['posyandu_id'] = ['required', 'uuid', 'exists:posyandus,id'];
        }

        return $baseRules;
    }

    public function messages(): array
    {
        return [
            // Role
            'role.required'                  => 'Role harus dipilih atau otomatis terisi.',
            'role.in'                        => 'Role yang dipilih tidak valid.',
            // Identitas
            'name.required'                  => 'Nama lengkap wajib diisi.',
            'name.max'                       => 'Nama maksimal 255 karakter.',
            'tempat_lahir.required'          => 'Tempat lahir wajib diisi.',
            'tanggal_lahir.required'         => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir.date'             => 'Format tanggal lahir tidak valid.',
            'jenis_kelamin.required'         => 'Jenis kelamin wajib dipilih.',
            'alamat.required'                => 'Alamat wajib diisi.',
            // NIK
            'nik.max'                        => 'NIK maksimal 16 digit.',
            'nik.unique'                     => 'NIK sudah terdaftar, gunakan NIK yang benar.',
            // Email
            'email.email'                    => 'Format email tidak valid.',
            'email.unique'                   => 'Email sudah terdaftar, gunakan email lain.',
            // No. Telepon
            'no_telepon.required'            => 'Nomor WhatsApp wajib diisi.',
            'no_telepon.max'                 => 'Nomor WhatsApp maksimal 20 digit.',
            'no_telepon.unique'              => 'Nomor WhatsApp sudah terdaftar, gunakan nomor lain.',
            // Password
            'password.required'              => 'Password wajib diisi.',
            'password.confirmed'             => 'Konfirmasi password tidak cocok.',
            'password.min'                   => 'Password minimal 8 karakter.',
            'password.mixed'                 => 'Password harus mengandung huruf besar dan kecil.',
            'password.numbers'               => 'Password harus mengandung setidaknya satu angka.',
            'password.symbols'               => 'Password harus mengandung setidaknya satu simbol.',
            'password.uncompromised'         => 'Password ini terlalu umum, gunakan password yang lebih aman.',
            // Wilayah
            'kabupaten.required'             => 'Kabupaten wajib dipilih.',
            'kecamatan.required'             => 'Kecamatan wajib dipilih.',
            'desa.required'                  => 'Desa wajib dipilih.',
            'rw.required'                    => 'RW wajib dipilih.',
            'rt.required'                    => 'RT wajib dipilih.',
            // Relasi
            'posyandu_id.required'           => 'Posyandu wajib dipilih.',
            'posyandu_id.exists'             => 'Posyandu tidak ditemukan, silakan pilih ulang.',
            'bidang_id.required'             => 'Bidang tugas wajib dipilih.',
            'bidang_id.exists'               => 'Bidang tugas tidak ditemukan, silakan pilih ulang.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $allowed = $this->allowedParameters();
            $extra = array_diff(array_keys($this->all()), $allowed);

            if (!empty($extra)) {
                $validator->errors()->add('request', 'Terdapat parameter tidak dikenali: ' . implode(', ', $extra));
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $currentRole = (string) ($this->user()?->role ?? '');
        $autoAssignMap = [
            'kader' => 'masyarakat',
            'operator-desa' => 'kader',
            'ketua-posyandu' => 'kader',
        ];

        $roleInput = $this->input('role');
        $normalizedRole = $this->normalizeText($roleInput);

        if ((empty($normalizedRole)) && isset($autoAssignMap[$currentRole])) {
            $normalizedRole = $autoAssignMap[$currentRole];
        }

        $this->merge([
            'role' => $normalizedRole,
            'name' => $this->normalizeText($this->input('name')),
            'email' => $this->normalizeText($this->input('email')),
            'nik' => $this->normalizeText($this->input('nik')),
            'alamat' => $this->normalizeText($this->input('alamat')),
            'no_telepon' => $this->normalizeText($this->input('no_telepon')),
            'tempat_lahir' => $this->normalizeText($this->input('tempat_lahir')),
            'jenis_kelamin' => $this->normalizeText($this->input('jenis_kelamin')),
            'kabupaten' => $this->normalizeText($this->input('kabupaten')),
            'kecamatan' => $this->normalizeText($this->input('kecamatan')),
            'desa' => $this->normalizeText($this->input('desa')),
            'rw' => $this->normalizeText($this->input('rw')),
            'rt' => $this->normalizeText($this->input('rt')),
            'source' => $this->normalizeText($this->input('source')),
        ]);
    }

    private function allowedRolesFor(string $role): array
    {
        $rolesMap = [
            'admin' => [
                'admin-kabupaten',
                'ketua-timpembina-posyandu',
                'kabid',
                'admin-kecamatan',
                'ketua-posyandu',
                'kades',
                'operator-desa',
                'kader',
                'masyarakat',
                'admin',
            ],
            'ketua-timpembina-posyandu' => [
                'kabid',
                'admin-kecamatan',
                'ketua-posyandu',
                'kades',
                'kader',
                'masyarakat',
            ],
            'admin-kabupaten' => [
                'kabid',
                'ketua-timpembina-posyandu',
                'admin-kecamatan',
                'kades',
                'bu-kades',
                'operator-desa',
                'admin-kabupaten',
            ],
            'kabid' => [
                'admin-kecamatan',
                'ketua-posyandu',
                'kader',
            ],
            'admin-kecamatan' => [
                'ketua-posyandu',
                'kader',
            ],
            'ketua-posyandu' => [
                'operator-desa',
                'kader',
            ],
            'operator-desa' => [
                'kades',
                'bu-kades',
                'ketua-posyandu',
                'kader',
            ],
            'kader' => [
                'masyarakat',
            ],
        ];

        return $rolesMap[$role] ?? [];
    }

    private function allowedParameters(): array
    {
        return [
            '_token',
            'source',
            'name',
            'no_telepon',
            'alamat',
            'nik',
            'tempat_lahir',
            'tanggal_lahir',
            'jenis_kelamin',
            'email',
            'password',
            'password_confirmation',
            'role',
            'jenis_wilayah',
            'kabupaten',
            'kecamatan',
            'desa',
            'posyandu_id',
            'bidang_id',
            'rw',
            'rt',
            'kabupaten_id',
            'kecamatan_id',

            // Hidden/helper fields used by current create-user form logic
            'admin_kabupaten_kabupaten',
            'operator_desa_kecamatan',
            'operator_desa_desa',
            'posyandu_kabupaten_val',
            'posyandu_kabupaten_id_val',
            'posyandu_kecamatan_val',
            'posyandu_kecamatan_id_val',
            'posyandu_desa_val',
        ];
    }

    private function normalizeText(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        return trim(preg_replace('/\s+/', ' ', $value));
    }
}
