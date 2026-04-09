<?php

use App\Models\BidangPengajuan;
use App\Models\Pengajuan;
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
