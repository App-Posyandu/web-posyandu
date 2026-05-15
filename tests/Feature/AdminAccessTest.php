<?php

use App\Models\Posyandu;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('kabid is forbidden from admin users page', function () {
    $kabid = User::factory()->create([
        'role' => 'kabid',
    ]);

    $this->actingAs($kabid)
        ->get(route('admin.users.index'))
        ->assertForbidden();
});

test('kabid is forbidden from admin settings page', function () {
    $kabid = User::factory()->create([
        'role' => 'kabid',
    ]);

    $this->actingAs($kabid)
        ->get(route('admin.settings.index'))
        ->assertForbidden();
});

test('kades is forbidden from admin posyandu page', function () {
    $kades = User::factory()->create([
        'role' => 'kades',
    ]);

    $this->actingAs($kades)
        ->get(route('admin.posyandu.index'))
        ->assertForbidden();
});

test('bu kades is forbidden from admin posyandu page', function () {
    $buKades = User::factory()->create([
        'role' => 'bu-kades',
    ]);

    $this->actingAs($buKades)
        ->get(route('admin.posyandu.index'))
        ->assertForbidden();
});

test('ketua posyandu is forbidden from admin posyandu page', function () {
    $ketuaPosyandu = User::factory()->create([
        'role' => 'ketua-posyandu',
    ]);

    $this->actingAs($ketuaPosyandu)
        ->get(route('admin.posyandu.index'))
        ->assertForbidden();
});

test('operator desa can access admin posyandu page', function () {
    $posyandu = Posyandu::create([
        'nama_posyandu' => 'Posyandu Melati',
        'kabupaten' => 'Kabupaten Contoh',
        'kecamatan' => 'Kecamatan Contoh',
        'desa' => 'Desa Contoh',
    ]);

    $operator = User::factory()->create([
        'role' => 'operator-desa',
        'posyandu_id' => $posyandu->id,
        'desa' => 'Desa Contoh',
        'kecamatan' => 'Kecamatan Contoh',
        'kabupaten' => 'Kabupaten Contoh',
    ]);

    $this->actingAs($operator)
        ->get(route('admin.posyandu.index'))
        ->assertOk();
});

test('operator desa can reset password for ketua posyandu in the same posyandu', function () {
    $posyandu = Posyandu::create([
        'nama_posyandu' => 'Posyandu Melati',
        'kabupaten' => 'Kabupaten Contoh',
        'kecamatan' => 'Kecamatan Contoh',
        'desa' => 'Desa Contoh',
    ]);

    $operator = User::factory()->create([
        'role' => 'operator-desa',
        'posyandu_id' => $posyandu->id,
    ]);

    $ketuaPosyandu = User::factory()->create([
        'role' => 'ketua-posyandu',
        'posyandu_id' => $posyandu->id,
    ]);

    $response = $this->actingAs($operator)
        ->patch(route('admin.users.reset-password', $ketuaPosyandu), [
            'new_password' => 'PasswordBaru123',
            'new_password_confirmation' => 'PasswordBaru123',
        ]);

    $response
        ->assertRedirect()
        ->assertSessionHas('success', 'Password kader berhasil direset');

    $ketuaPosyandu->refresh();
    expect(Hash::check('PasswordBaru123', $ketuaPosyandu->password))->toBeTrue();
});