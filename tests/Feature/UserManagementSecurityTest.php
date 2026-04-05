<?php

use App\Models\Posyandu;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;

function createPosyanduFixture(string $name = 'Posyandu Melati'): Posyandu
{
    return Posyandu::create([
        'nama_posyandu' => $name,
        'kabupaten' => 'Kabupaten Contoh',
        'kecamatan' => 'Kecamatan Contoh',
        'desa' => 'Desa Contoh',
    ]);
}

test('operator desa deactivate kader requires reason', function () {
    $posyandu = createPosyanduFixture('Posyandu Mawar');

    $operator = User::factory()->create([
        'role' => 'operator-desa',
        'posyandu_id' => $posyandu->id,
        'kecamatan' => 'Kecamatan Contoh',
        'desa' => 'Desa Contoh',
    ]);

    $kader = User::factory()->create([
        'role' => 'kader',
        'posyandu_id' => $posyandu->id,
        'kecamatan' => 'Kecamatan Contoh',
        'desa' => 'Desa Contoh',
        'is_active' => true,
    ]);

    $this->actingAs($operator)
        ->patch(route('admin.users.deactivate', $kader), [])
        ->assertSessionHasErrors(['reason']);
});

test('operator desa reset password requires confirmation', function () {
    $posyandu = createPosyanduFixture('Posyandu Anggrek');

    $operator = User::factory()->create([
        'role' => 'operator-desa',
        'posyandu_id' => $posyandu->id,
    ]);

    $ketuaPosyandu = User::factory()->create([
        'role' => 'ketua-posyandu',
        'posyandu_id' => $posyandu->id,
    ]);

    $this->actingAs($operator)
        ->patch(route('admin.users.reset-password', $ketuaPosyandu), [
            'new_password' => 'PasswordBaru123',
        ])
        ->assertSessionHasErrors(['new_password']);
});

test('operator desa deactivate rejects unknown parameter', function () {
    $posyandu = createPosyanduFixture('Posyandu Flamboyan');

    $operator = User::factory()->create([
        'role' => 'operator-desa',
        'posyandu_id' => $posyandu->id,
        'kecamatan' => 'Kecamatan Contoh',
        'desa' => 'Desa Contoh',
    ]);

    $kader = User::factory()->create([
        'role' => 'kader',
        'posyandu_id' => $posyandu->id,
        'kecamatan' => 'Kecamatan Contoh',
        'desa' => 'Desa Contoh',
        'is_active' => true,
    ]);

    $this->actingAs($operator)
        ->from(route('admin.users.show', $kader))
        ->patch(route('admin.users.deactivate', $kader), [
            'reason' => 'User tidak aktif bertugas.',
            'is_superuser' => '1',
        ])
        ->assertRedirect(route('admin.users.show', $kader))
        ->assertSessionHasErrors(['request']);

    $kader->refresh();
    expect($kader->is_active)->toBeTrue();
});

test('operator desa reset password rejects unknown parameter', function () {
    $posyandu = createPosyanduFixture('Posyandu Cempaka');

    $operator = User::factory()->create([
        'role' => 'operator-desa',
        'posyandu_id' => $posyandu->id,
    ]);

    $oldPassword = 'PasswordLama123';
    $ketuaPosyandu = User::factory()->create([
        'role' => 'ketua-posyandu',
        'posyandu_id' => $posyandu->id,
        'password' => Hash::make($oldPassword),
    ]);

    $this->actingAs($operator)
        ->from(route('admin.users.show', $ketuaPosyandu))
        ->patch(route('admin.users.reset-password', $ketuaPosyandu), [
            'new_password' => 'PasswordBaru123',
            'new_password_confirmation' => 'PasswordBaru123',
            'is_superuser' => '1',
        ])
        ->assertRedirect(route('admin.users.show', $ketuaPosyandu))
        ->assertSessionHasErrors(['request']);

    $ketuaPosyandu->refresh();
    expect(Hash::check($oldPassword, $ketuaPosyandu->password))->toBeTrue();
});

test('admin kabupaten deactivate user requires reason', function () {
    $adminKabupaten = User::factory()->create([
        'role' => 'admin-kabupaten',
        'kabupaten' => 'Kabupaten Contoh',
    ]);

    $kabid = User::factory()->create([
        'role' => 'kabid',
        'kabupaten' => 'Kabupaten Contoh',
        'is_active' => true,
    ]);

    $this->actingAs($adminKabupaten)
        ->patch("/users/{$kabid->id}/deactivate-kabid", [])
        ->assertSessionHasErrors(['reason']);
});

test('admin kabupaten deactivate user rejects unknown parameter', function () {
    $adminKabupaten = User::factory()->create([
        'role' => 'admin-kabupaten',
        'kabupaten' => 'Kabupaten Contoh',
    ]);

    $kabid = User::factory()->create([
        'role' => 'kabid',
        'kabupaten' => 'Kabupaten Contoh',
        'is_active' => true,
    ]);

    $this->actingAs($adminKabupaten)
        ->from(route('admin.users.show', $kabid))
        ->patch("/users/{$kabid->id}/deactivate-kabid", [
            'reason' => 'Akun tidak lagi aktif bertugas.',
            'is_superuser' => '1',
        ])
        ->assertRedirect(route('admin.users.show', $kabid))
        ->assertSessionHasErrors(['request']);

    $kabid->refresh();
    expect($kabid->is_active)->toBeTrue();
});

test('admin kabupaten reset password rejects unknown parameter', function () {
    $adminKabupaten = User::factory()->create([
        'role' => 'admin-kabupaten',
        'kabupaten' => 'Kabupaten Contoh',
    ]);

    $oldPassword = 'PasswordLama123';
    $kabid = User::factory()->create([
        'role' => 'kabid',
        'kabupaten' => 'Kabupaten Contoh',
        'password' => Hash::make($oldPassword),
    ]);

    $this->actingAs($adminKabupaten)
        ->from(route('admin.users.show', $kabid))
        ->patch("/users/{$kabid->id}/reset-password-kabid", [
            'new_password' => 'PasswordBaru123',
            'new_password_confirmation' => 'PasswordBaru123',
            'is_superuser' => '1',
        ])
        ->assertRedirect(route('admin.users.show', $kabid))
        ->assertSessionHasErrors(['request']);

    $kabid->refresh();
    expect(Hash::check($oldPassword, $kabid->password))->toBeTrue();
});

test('admin kabupaten can deactivate kabid with valid payload', function () {
    $adminKabupaten = User::factory()->create([
        'role' => 'admin-kabupaten',
        'kabupaten' => 'Kabupaten Contoh',
    ]);

    $kabid = User::factory()->create([
        'role' => 'kabid',
        'kabupaten' => 'Kabupaten Contoh',
        'is_active' => true,
    ]);

    $reason = 'Akun dinonaktifkan karena pergantian penugasan.';

    $this->actingAs($adminKabupaten)
        ->from(route('dashboard'))
        ->patch("/users/{$kabid->id}/deactivate-kabid", [
            'reason' => $reason,
        ])
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('success', 'User berhasil dinonaktifkan.');

    $kabid->refresh();
    expect($kabid->is_active)->toBeFalse();
    expect($kabid->deactivation_reason)->toBe($reason);
    expect((string) $kabid->deactivated_by)->toBe((string) $adminKabupaten->id);
});

test('admin kabupaten can reset password for kabid in same kabupaten', function () {
    $adminKabupaten = User::factory()->create([
        'role' => 'admin-kabupaten',
        'kabupaten' => 'Kabupaten Contoh',
    ]);

    $oldPassword = 'PasswordLama123';
    $newPassword = 'PasswordBaru123';

    $kabid = User::factory()->create([
        'role' => 'kabid',
        'kabupaten' => 'Kabupaten Contoh',
        'password' => Hash::make($oldPassword),
    ]);

    $this->actingAs($adminKabupaten)
        ->from(route('dashboard'))
        ->patch("/users/{$kabid->id}/reset-password-kabid", [
            'new_password' => $newPassword,
            'new_password_confirmation' => $newPassword,
        ])
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('success', 'Password berhasil direset');

    $kabid->refresh();
    expect(Hash::check($newPassword, $kabid->password))->toBeTrue();
    expect(Hash::check($oldPassword, $kabid->password))->toBeFalse();
});

test('admin kabupaten can reactivate kabid in same kabupaten', function () {
    $adminKabupaten = User::factory()->create([
        'role' => 'admin-kabupaten',
        'kabupaten' => 'Kabupaten Contoh',
    ]);

    $kabid = User::factory()->create([
        'role' => 'kabid',
        'kabupaten' => 'Kabupaten Contoh',
        'is_active' => false,
        'deactivated_at' => now()->subDay(),
        'deactivation_reason' => 'Dinonaktifkan sementara.',
    ]);

    $this->actingAs($adminKabupaten)
        ->from(route('dashboard'))
        ->patch(route('admin.users.reactivate-kabid', $kabid))
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('success', 'User berhasil diaktifkan kembali.');

    $kabid->refresh();
    expect($kabid->is_active)->toBeTrue();
    expect($kabid->deactivated_at)->toBeNull();
    expect($kabid->deactivation_reason)->toBeNull();
});

test('admin kabupaten cannot reactivate user outside kabupaten', function () {
    $adminKabupaten = User::factory()->create([
        'role' => 'admin-kabupaten',
        'kabupaten' => 'Kabupaten A',
    ]);

    $kabidDifferentKabupaten = User::factory()->create([
        'role' => 'kabid',
        'kabupaten' => 'Kabupaten B',
        'is_active' => false,
    ]);

    $this->actingAs($adminKabupaten)
        ->patch(route('admin.users.reactivate-kabid', $kabidDifferentKabupaten))
        ->assertForbidden();

    $kabidDifferentKabupaten->refresh();
    expect($kabidDifferentKabupaten->is_active)->toBeFalse();
});

test('ketua posyandu takeover reset requires reason and password confirmation', function () {
    $posyandu = createPosyanduFixture('Posyandu Dahlia');

    $ketuaPosyandu = User::factory()->create([
        'role' => 'ketua-posyandu',
        'posyandu_id' => $posyandu->id,
    ]);

    $kader = User::factory()->create([
        'role' => 'kader',
        'posyandu_id' => $posyandu->id,
        'is_active' => false,
    ]);

    $this->actingAs($ketuaPosyandu)
        ->post(route('ketua-posyandu.takeover.reset', $kader), [
            'new_password' => 'PasswordBaru123',
        ])
        ->assertSessionHasErrors(['new_password', 'reason']);
});

test('ketua posyandu can takeover reset inactive kader with valid payload', function () {
    $posyandu = createPosyanduFixture('Posyandu Kenanga');

    $ketuaPosyandu = User::factory()->create([
        'role' => 'ketua-posyandu',
        'posyandu_id' => $posyandu->id,
    ]);

    $kader = User::factory()->create([
        'role' => 'kader',
        'posyandu_id' => $posyandu->id,
        'is_active' => false,
    ]);

    $response = $this->actingAs($ketuaPosyandu)
        ->post(route('ketua-posyandu.takeover.reset', $kader), [
            'new_password' => 'PasswordBaru123',
            'new_password_confirmation' => 'PasswordBaru123',
            'reason' => 'Pengambilalihan akun karena kader lupa password.',
        ]);

    $response
        ->assertRedirect()
        ->assertSessionHas('success', 'Password kader berhasil direset.');

    $kader->refresh();

    expect($kader->is_active)->toBeTrue();
    expect(Hash::check('PasswordBaru123', $kader->password))->toBeTrue();
});

test('ketua posyandu takeover reset rejects unknown parameter', function () {
    $posyandu = createPosyanduFixture('Posyandu Melur');

    $ketuaPosyandu = User::factory()->create([
        'role' => 'ketua-posyandu',
        'posyandu_id' => $posyandu->id,
    ]);

    $oldPassword = 'PasswordLama123';
    $kader = User::factory()->create([
        'role' => 'kader',
        'posyandu_id' => $posyandu->id,
        'is_active' => false,
        'password' => Hash::make($oldPassword),
    ]);

    $this->actingAs($ketuaPosyandu)
        ->from(route('ketua-posyandu.takeover'))
        ->post(route('ketua-posyandu.takeover.reset', $kader), [
            'new_password' => 'PasswordBaru123',
            'new_password_confirmation' => 'PasswordBaru123',
            'reason' => 'Pengambilalihan akun karena kader lupa password.',
            'is_superuser' => '1',
        ])
        ->assertRedirect(route('ketua-posyandu.takeover'))
        ->assertSessionHasErrors(['request']);

    $kader->refresh();
    expect($kader->is_active)->toBeFalse();
    expect(Hash::check($oldPassword, $kader->password))->toBeTrue();
});

test('admin users index rejects invalid status filter', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->from(route('admin.users.index'))
        ->get(route('admin.users.index', ['status' => 'blocked']))
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHasErrors(['status']);
});

test('admin update user rejects invalid jenis kelamin', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $targetUser = User::factory()->create([
        'role' => 'kader',
    ]);

    $this->actingAs($admin)
        ->from(route('admin.users.edit', $targetUser))
        ->put(route('admin.users.update', $targetUser), [
            'name' => 'Nama Diperbarui',
            'email' => 'updated-' . $targetUser->id . '@example.test',
            'jenis_kelamin' => 'Tidak diketahui',
        ])
        ->assertRedirect(route('admin.users.edit', $targetUser))
        ->assertSessionHasErrors(['jenis_kelamin']);
});

test('admin update user rejects unknown parameter', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $targetUser = User::factory()->create([
        'role' => 'kader',
    ]);

    $newEmail = 'unknown-update-' . $targetUser->id . '@example.test';

    $this->actingAs($admin)
        ->from(route('admin.users.edit', $targetUser))
        ->put(route('admin.users.update', $targetUser), [
            'name' => 'Nama Uji Unknown Param',
            'email' => $newEmail,
            'is_superuser' => '1',
        ])
        ->assertRedirect(route('admin.users.edit', $targetUser))
        ->assertSessionHasErrors(['request']);

    $targetUser->refresh();
    expect($targetUser->email)->not->toBe($newEmail);
});

test('import process rejects non spreadsheet file', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->from(route('admin.import.importPage'))
        ->post(route('admin.import.importProcess'), [
            'file' => UploadedFile::fake()->create('users.txt', 10, 'text/plain'),
        ])
        ->assertRedirect(route('admin.import.importPage'))
        ->assertSessionHasErrors(['file']);
});

test('import process rejects unknown parameter', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->from(route('admin.import.importPage'))
        ->post(route('admin.import.importProcess'), [
            'role' => 'kader',
            'file' => UploadedFile::fake()->create('users.csv', 10, 'text/csv'),
            'is_superuser' => '1',
        ])
        ->assertRedirect(route('admin.import.importPage'))
        ->assertSessionHasErrors(['request']);
});

test('import excel endpoint rejects csv file', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->from(route('admin.import.importPage'))
        ->post(route('admin.users.import'), [
            'file' => UploadedFile::fake()->create('users.csv', 10, 'text/csv'),
        ])
        ->assertRedirect(route('admin.import.importPage'))
        ->assertSessionHasErrors(['file']);
});

test('import excel endpoint rejects unknown parameter', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->from(route('admin.import.importPage'))
        ->post(route('admin.users.import'), [
            'role' => 'kader',
            'file' => UploadedFile::fake()->create('users.xlsx', 10, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
            'is_superuser' => '1',
        ])
        ->assertRedirect(route('admin.import.importPage'))
        ->assertSessionHasErrors(['request']);
});

test('admin store user requires role when not auto assigned', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->from(route('admin.users.create'))
        ->post(route('admin.users.store'), [])
        ->assertRedirect(route('admin.users.create'))
        ->assertSessionHasErrors(['role']);
});

test('operator desa store user auto assigns role to kader', function () {
    $operator = User::factory()->create([
        'role' => 'operator-desa',
        'kabupaten' => 'Kabupaten Contoh',
        'kecamatan' => 'Kecamatan Contoh',
        'desa' => 'Desa Contoh',
    ]);

    $this->actingAs($operator)
        ->from(route('admin.users.create'))
        ->post(route('admin.users.store'), [
            'name' => 'Calon Kader',
            'email' => 'calon-kader@example.test',
            'password' => 'PasswordBaru123',
            'password_confirmation' => 'PasswordBaru123',
            'alamat' => 'Alamat Test',
            'no_telepon' => '081234567891',
            'tempat_lahir' => 'Kebumen',
            'tanggal_lahir' => '2001-01-01',
            'jenis_kelamin' => 'Perempuan',
        ])
        ->assertRedirect(route('admin.users.create'))
        ->assertSessionHasErrors(['bidang_id', 'posyandu_id'])
        ->assertSessionDoesntHaveErrors(['role']);
});

test('store user rejects unknown parameter', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $email = 'unknown-param-check@example.test';

    $this->actingAs($admin)
        ->from(route('admin.users.create'))
        ->post(route('admin.users.store'), [
            'name' => 'User Uji',
            'email' => $email,
            'password' => 'PasswordBaru123',
            'password_confirmation' => 'PasswordBaru123',
            'role' => 'admin',
            'alamat' => 'Alamat Uji',
            'no_telepon' => '081234567892',
            'tempat_lahir' => 'Kebumen',
            'tanggal_lahir' => '1999-01-01',
            'jenis_kelamin' => 'Laki-laki',
            'is_superuser' => '1',
        ])
        ->assertRedirect(route('admin.users.create'))
        ->assertSessionHasErrors(['request']);

    $this->assertDatabaseMissing('users', [
        'email' => $email,
    ]);
});

test('kader store masyarakat accepts helper wilayah ids', function () {
    $posyandu = Posyandu::create([
        'nama_posyandu' => 'Posyandu Sakura',
        'kabupaten' => 'Kabupaten Contoh',
        'kecamatan' => 'Kecamatan Contoh',
        'desa' => 'Desa Contoh',
        'rw_list' => ['RW01'],
        'rt_mapping' => [
            'RW01' => ['RT001'],
        ],
    ]);

    $kader = User::factory()->create([
        'role' => 'kader',
        'kabupaten' => 'Kabupaten Contoh',
        'kecamatan' => 'Kecamatan Contoh',
        'desa' => 'Desa Contoh',
        'posyandu_id' => $posyandu->id,
        'kabupaten_id' => '33.05',
        'kecamatan_id' => '33.05.01',
    ]);

    $this->actingAs($kader)
        ->post(route('admin.users.store'), [
            'name' => 'Warga Baru',
            'email' => 'warga-baru@example.test',
            'password' => 'PasswordBaru123',
            'password_confirmation' => 'PasswordBaru123',
            'alamat' => 'Alamat Warga',
            'no_telepon' => '081234567893',
            'tempat_lahir' => 'Kebumen',
            'tanggal_lahir' => '2002-02-02',
            'jenis_kelamin' => 'Perempuan',
            'kabupaten' => 'Kabupaten Contoh',
            'kecamatan' => 'Kecamatan Contoh',
            'desa' => 'Desa Contoh',
            'posyandu_id' => $posyandu->id,
            'rw' => 'RW01',
            'rt' => 'RT001',
            'kabupaten_id' => '33.05',
            'kecamatan_id' => '33.05.01',
        ])
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHas('success', 'User baru berhasil ditambahkan.');

    $this->assertDatabaseHas('users', [
        'email' => 'warga-baru@example.test',
        'role' => 'masyarakat',
    ]);
});

test('dashboard rejects invalid search payload', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->from(route('dashboard'))
        ->get(route('dashboard', ['search' => '<script>alert(1)</script>']))
        ->assertRedirect(route('dashboard'))
        ->assertSessionHasErrors(['search']);
});

test('dashboard rejects invalid status filter', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->from(route('dashboard'))
        ->get(route('dashboard', ['status' => 'APPROVED']))
        ->assertRedirect(route('dashboard'))
        ->assertSessionHasErrors(['status']);
});

test('dashboard rejects array year parameter', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->from(route('dashboard'))
        ->get('/dashboard?year[]=2026')
        ->assertRedirect(route('dashboard'))
        ->assertSessionHasErrors(['year']);
});

test('dashboard rejects unknown query parameter', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->from(route('dashboard'))
        ->get(route('dashboard', ['search' => 'aman', 'is_superuser' => '1']))
        ->assertRedirect(route('dashboard'))
        ->assertSessionHasErrors(['request']);
});

test('dashboard accepts valid filter parameters', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->get(route('dashboard', [
            'year' => now()->year,
            'search' => 'Nama Warga',
            'status' => 'Diproses',
            'archived' => '0',
        ]))
        ->assertOk();
});
