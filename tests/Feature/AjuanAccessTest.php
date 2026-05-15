<?php

use App\Models\BidangPengajuan;
use App\Models\Pengajuan;
use App\Models\Posyandu;
use App\Models\User;

function createBidangKesehatan(): BidangPengajuan
{
    return BidangPengajuan::create([
        'nama_bidang' => 'Kesehatan',
        'slug' => 'kesehatan',
    ]);
}

function createPengajuanFor(User $user, BidangPengajuan $bidang): Pengajuan
{
    return Pengajuan::create([
        'user_id' => $user->id,
        'bidang_id' => $bidang->id,
        'deskripsi_pengajuan' => 'Pengajuan bantuan kesehatan untuk keluarga.',
        'status_pengajuan' => 'Diproses',
        'formulir_items' => ['Penyuluhan kesehatan'],
        'administrasi_items' => ['ktp' => 'ajuan_dokumen/ktp.png'],
        'tanggal_permohonan' => now(),
    ]);
}

function createPosyandu(string $desa = 'DESA A'): Posyandu
{
    return Posyandu::create([
        'nama_posyandu' => 'Posyandu ' . $desa,
        'desa' => $desa,
        'kecamatan' => 'KEC A',
        'kabupaten' => 'KAB A',
    ]);
}

test('masyarakat cannot view another user pengajuan', function () {
    $bidang = createBidangKesehatan();

    $owner = User::factory()->create([
        'role' => 'masyarakat',
        'kabupaten' => 'KAB A',
        'kecamatan' => 'KEC A',
        'desa' => 'DESA A',
    ]);

    $attacker = User::factory()->create([
        'role' => 'masyarakat',
        'kabupaten' => 'KAB A',
        'kecamatan' => 'KEC A',
        'desa' => 'DESA A',
    ]);

    $pengajuan = createPengajuanFor($owner, $bidang);

    $this->actingAs($attacker)
        ->get(route('ajuan.show', $pengajuan))
        ->assertForbidden();
});

test('masyarakat can view own pengajuan', function () {
    $bidang = createBidangKesehatan();

    $owner = User::factory()->create([
        'role' => 'masyarakat',
        'kabupaten' => 'KAB A',
        'kecamatan' => 'KEC A',
        'desa' => 'DESA A',
    ]);

    $pengajuan = createPengajuanFor($owner, $bidang);

    $this->actingAs($owner)
        ->get(route('ajuan.show', $pengajuan))
        ->assertOk();
});

test('admin kecamatan is forbidden to view pengajuan outside kecamatan', function () {
    $bidang = createBidangKesehatan();

    $adminKecamatan = User::factory()->create([
        'role' => 'admin-kecamatan',
        'kecamatan' => 'KECAMATAN ALPHA',
    ]);

    $targetUser = User::factory()->create([
        'role' => 'masyarakat',
        'kecamatan' => 'KECAMATAN BETA',
        'desa' => 'DESA BETA',
    ]);

    $pengajuan = createPengajuanFor($targetUser, $bidang);

    $this->actingAs($adminKecamatan)
        ->get(route('ajuan.show', $pengajuan))
        ->assertForbidden();
});

test('admin kecamatan can view pengajuan within same kecamatan', function () {
    $bidang = createBidangKesehatan();

    $adminKecamatan = User::factory()->create([
        'role' => 'admin-kecamatan',
        'kecamatan' => 'KECAMATAN ALPHA',
    ]);

    $targetUser = User::factory()->create([
        'role' => 'masyarakat',
        'kecamatan' => 'KECAMATAN ALPHA',
        'desa' => 'DESA ALPHA',
    ]);

    $pengajuan = createPengajuanFor($targetUser, $bidang);

    $this->actingAs($adminKecamatan)
        ->get(route('ajuan.show', $pengajuan))
        ->assertOk();
});

test('kades can see follow-up form for ketua-approved pengajuan even before explicit submit to desa', function () {
    $bidang = createBidangKesehatan();
    $posyandu = createPosyandu('DESA KADES');

    $kades = User::factory()->create([
        'role' => 'kades',
        'desa' => 'DESA KADES',
        'kecamatan' => 'KEC A',
        'kabupaten' => 'KAB A',
    ]);

    $pemohon = User::factory()->create([
        'role' => 'masyarakat',
        'posyandu_id' => $posyandu->id,
        'desa' => 'DESA KADES',
        'kecamatan' => 'KEC A',
        'kabupaten' => 'KAB A',
    ]);

    $pengajuan = createPengajuanFor($pemohon, $bidang);
    $pengajuan->update([
        'status_pengajuan' => 'Diproses',
        'approved_by_ketua' => true,
        'submitted_to_desa' => false,
    ]);

    $this->actingAs($kades)
        ->get(route('ajuan.show', $pengajuan))
        ->assertOk()
        ->assertSee('form-keputusan-kades', false);
});

test('kades approval auto marks submitted_to_desa when ketua-approved pengajuan was not explicitly submitted', function () {
    $bidang = createBidangKesehatan();
    $posyandu = createPosyandu('DESA KADES');

    $kades = User::factory()->create([
        'role' => 'kades',
        'desa' => 'DESA KADES',
        'kecamatan' => 'KEC A',
        'kabupaten' => 'KAB A',
    ]);

    $pemohon = User::factory()->create([
        'role' => 'masyarakat',
        'posyandu_id' => $posyandu->id,
        'desa' => 'DESA KADES',
        'kecamatan' => 'KEC A',
        'kabupaten' => 'KAB A',
    ]);

    $pengajuan = createPengajuanFor($pemohon, $bidang);
    $pengajuan->update([
        'status_pengajuan' => 'Diproses',
        'approved_by_ketua' => true,
        'submitted_to_desa' => false,
    ]);

    $response = $this->actingAs($kades)->post(route('ajuan.kades-approval', $pengajuan), [
        'keputusan' => 'ditindaklanjuti',
        'tindak_lanjut' => 'Tindak lanjut disetujui untuk segera diproses oleh pemdes.',
        'catatan' => 'Pengajuan layak diproses dan disetujui oleh kades.',
    ]);

    $response->assertRedirect(route('ajuan.index'));
    $response->assertSessionHas('success');

    $pengajuan->refresh();

    expect($pengajuan->status_pengajuan)->toBe('Disetujui');
    expect($pengajuan->approved_by_kades)->toBeTrue();
    expect($pengajuan->submitted_to_desa)->toBeTrue();
});

test('kades can view pengajuan when pemohon alamat matches desa scope', function () {
    $bidang = createBidangKesehatan();
    $posyandu = createPosyandu('DESA LAIN');

    $kades = User::factory()->create([
        'role' => 'kades',
        'desa' => 'DESA KADES',
        'kecamatan' => 'KEC A',
        'kabupaten' => 'KAB A',
    ]);

    $pemohon = User::factory()->create([
        'role' => 'masyarakat',
        'posyandu_id' => $posyandu->id,
        'desa' => 'DESA LAIN',
        'kecamatan' => 'KEC A',
        'kabupaten' => 'KAB A',
        'alamat' => 'Perbatasan wilayah DESA KADES RT 02',
    ]);

    $pengajuan = createPengajuanFor($pemohon, $bidang);

    $this->actingAs($kades)
        ->get(route('ajuan.show', $pengajuan))
        ->assertOk();
});

test('kader cannot view pengajuan outside bidang scope', function () {
    $bidangTarget = createBidangKesehatan();
    $bidangOther = BidangPengajuan::create([
        'nama_bidang' => 'Pendidikan',
        'slug' => 'pendidikan',
    ]);
    $posyandu = createPosyandu('DESA BIDANG');

    $kader = User::factory()->create([
        'role' => 'kader',
        'bidang_id' => $bidangTarget->id,
        'posyandu_id' => $posyandu->id,
        'desa' => 'DESA BIDANG',
        'kecamatan' => 'KEC A',
        'kabupaten' => 'KAB A',
    ]);

    $pemohon = User::factory()->create([
        'role' => 'masyarakat',
        'posyandu_id' => $posyandu->id,
        'desa' => 'DESA BIDANG',
        'kecamatan' => 'KEC A',
        'kabupaten' => 'KAB A',
    ]);

    $pengajuan = createPengajuanFor($pemohon, $bidangOther);

    $this->actingAs($kader)
        ->get(route('ajuan.show', $pengajuan))
        ->assertForbidden();
});

test('ajuan index rejects tampered year values', function (string $year) {
    $admin = User::factory()->create([
        'role' => 'admin-kecamatan',
    ]);
    
    $this->actingAs($admin)
        ->get(route('ajuan.index', ['year' => $year]))
        ->assertOk();
})->with([
    'random string' => '873ct...',
    'huge integer' => '83257983579384',
]);

test('step 1 verification persists selected keputusan in history', function () {
    $bidang = createBidangKesehatan();
    $posyandu = createPosyandu('DESA VERIF');

    $kader = User::factory()->create([
        'role' => 'kader',
        'bidang_id' => $bidang->id,
        'posyandu_id' => $posyandu->id,
        'desa' => 'DESA VERIF',
        'kecamatan' => 'KEC A',
        'kabupaten' => 'KAB A',
    ]);

    $pemohon = User::factory()->create([
        'role' => 'masyarakat',
        'posyandu_id' => $posyandu->id,
        'desa' => 'DESA VERIF',
        'kecamatan' => 'KEC A',
        'kabupaten' => 'KAB A',
    ]);

    $pengajuan = createPengajuanFor($pemohon, $bidang);

    $this->actingAs($kader)
        ->patch(route('ajuan.verify', $pengajuan), [
            'verification_step' => 1,
            'keputusan' => 'lanjut',
            'catatan' => 'Dokumen lengkap dan dapat dilanjutkan ke tahap kunjungan.',
            'verified_formulir_items' => ['Penyuluhan kesehatan'],
            'verified_administrasi_items' => ['ktp' => 1],
        ])
        ->assertRedirect(route('ajuan.index'));

    $this->assertDatabaseHas('histories', [
        'pengajuan_id' => $pengajuan->id,
        'status' => 'Menunggu Kunjungan',
        'pilih_keputusan' => 'lanjut',
    ]);
});
