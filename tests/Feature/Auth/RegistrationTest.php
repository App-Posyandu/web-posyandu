<?php

use App\Models\Posyandu;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $posyandu = Posyandu::create([
        'nama_posyandu' => 'Posyandu Test',
        'kabupaten' => 'KABUPATEN TEST',
        'kecamatan' => 'KECAMATAN TEST',
        'desa' => 'DESA TEST',
    ]);

    $response = $this->post('/register', [
        'name' => 'Test User',
        'tempat_lahir' => 'Kebumen',
        'tanggal_lahir' => '2000-01-01',
        'jenis_kelamin' => 'Laki-laki',
        'desa' => 'DESA TEST',
        'kecamatan' => 'KECAMATAN TEST',
        'kabupaten' => 'KABUPATEN TEST',
        'posyandu_id' => $posyandu->id,
        'rw' => 'RW01',
        'rt' => 'RT001',
        'no_telepon' => '081234567890',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});
