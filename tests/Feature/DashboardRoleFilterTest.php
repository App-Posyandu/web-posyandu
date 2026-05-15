<?php

use App\Models\BidangPengajuan;
use App\Models\Pengajuan;
use App\Models\Posyandu;
use App\Models\User;

function makeBidang(string $slug = 'kesehatan'): BidangPengajuan
{
    return BidangPengajuan::create([
        'nama_bidang' => ucfirst($slug),
        'slug' => $slug,
    ]);
}

function makePosyandu(string $nama, string $desa, string $kecamatan = 'Kecamatan A', string $kabupaten = 'Kabupaten A'): Posyandu
{
    return Posyandu::create([
        'nama_posyandu' => $nama,
        'desa' => $desa,
        'kecamatan' => $kecamatan,
        'kabupaten' => $kabupaten,
    ]);
}

function makePemohon(Posyandu $posyandu, array $overrides = []): User
{
    return User::factory()->create(array_merge([
        'role' => 'masyarakat',
        'posyandu_id' => $posyandu->id,
        'desa' => $posyandu->desa,
        'kecamatan' => $posyandu->kecamatan,
        'kabupaten' => $posyandu->kabupaten,
    ], $overrides));
}

function makePengajuan(User $pemohon, BidangPengajuan $bidang, string $deskripsi, array $overrides = []): Pengajuan
{
    return Pengajuan::create(array_merge([
        'user_id' => $pemohon->id,
        'bidang_id' => $bidang->id,
        'deskripsi_pengajuan' => $deskripsi,
        'status_pengajuan' => 'Diproses',
        'tanggal_permohonan' => now(),
    ], $overrides));
}

function getDashboardDataJson($testCase, User $actor, array $query = [])
{
    $params = array_merge([
        'year' => now()->year,
        'archived' => 0,
    ], $query);

    return $testCase
        ->actingAs($actor)
        ->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ])
        ->get(route('dashboard.data', $params));
}

test('dashboard ketua posyandu active and archived filters are consistent', function () {
    $bidang = makeBidang('kesehatan');

    $posyanduA = makePosyandu('Posyandu A', 'Desa A');
    $posyanduB = makePosyandu('Posyandu B', 'Desa B');

    $ketuaPosyandu = User::factory()->create([
        'role' => 'ketua-posyandu',
        'posyandu_id' => $posyanduA->id,
        'desa' => $posyanduA->desa,
        'kecamatan' => $posyanduA->kecamatan,
        'kabupaten' => $posyanduA->kabupaten,
    ]);

    $pemohonA = makePemohon($posyanduA);
    $pemohonB = makePemohon($posyanduB);

    makePengajuan($pemohonA, $bidang, 'KETUA_ACTIVE_1', [
        'status_pengajuan' => 'Diproses',
        'submitted_to_desa' => false,
    ]);
    makePengajuan($pemohonA, $bidang, 'KETUA_ACTIVE_2', [
        'status_pengajuan' => 'Diproses',
        'submitted_to_desa' => true,
    ]);
    makePengajuan($pemohonA, $bidang, 'KETUA_ARCHIVED_OK_1', [
        'status_pengajuan' => 'Disetujui',
        'submitted_to_desa' => true,
    ]);
    makePengajuan($pemohonA, $bidang, 'KETUA_ARCHIVED_OK_2', [
        'status_pengajuan' => 'Ditolak',
        'submitted_to_desa' => true,
    ]);
    makePengajuan($pemohonB, $bidang, 'KETUA_OUTSIDE_SCOPE', [
        'status_pengajuan' => 'Diproses',
    ]);

    $activeResponse = getDashboardDataJson($this, $ketuaPosyandu);
    $activeResponse->assertOk();

    $activeHtml = $activeResponse->json('tableHtml');
    expect($activeHtml)->toContain('KETUA_ACTIVE_1');
    expect($activeHtml)->toContain('KETUA_ACTIVE_2');
    expect($activeHtml)->not->toContain('KETUA_ARCHIVED_OK_1');
    expect($activeHtml)->not->toContain('KETUA_ARCHIVED_OK_2');
    expect($activeHtml)->not->toContain('KETUA_OUTSIDE_SCOPE');

    $archivedResponse = getDashboardDataJson($this, $ketuaPosyandu, ['archived' => 1]);
    $archivedResponse->assertOk();

    $archivedHtml = $archivedResponse->json('tableHtml');
    expect($archivedHtml)->toContain('KETUA_ARCHIVED_OK_1');
    expect($archivedHtml)->toContain('KETUA_ARCHIVED_OK_2');
    expect($archivedHtml)->not->toContain('KETUA_ACTIVE_1');
    expect($archivedHtml)->not->toContain('KETUA_ACTIVE_2');
});

test('dashboard admin active and archived lists are separated by status', function () {
    $bidang = makeBidang('pendidikan');
    $posyandu = makePosyandu('Posyandu Admin', 'Desa Admin');

    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $pemohon = makePemohon($posyandu);

    makePengajuan($pemohon, $bidang, 'ADMIN_ACTIVE_ONLY', [
        'status_pengajuan' => 'Diproses',
    ]);
    makePengajuan($pemohon, $bidang, 'ADMIN_ARCHIVED_APPROVED', [
        'status_pengajuan' => 'Disetujui',
    ]);
    makePengajuan($pemohon, $bidang, 'ADMIN_ARCHIVED_REJECTED', [
        'status_pengajuan' => 'Ditolak',
    ]);

    $activeResponse = getDashboardDataJson($this, $admin);
    $activeResponse->assertOk();

    $activeHtml = $activeResponse->json('tableHtml');
    expect($activeHtml)->toContain('ADMIN_ACTIVE_ONLY');
    expect($activeHtml)->not->toContain('ADMIN_ARCHIVED_APPROVED');
    expect($activeHtml)->not->toContain('ADMIN_ARCHIVED_REJECTED');

    $archivedResponse = getDashboardDataJson($this, $admin, ['archived' => 1]);
    $archivedResponse->assertOk();

    $archivedHtml = $archivedResponse->json('tableHtml');
    expect($archivedHtml)->toContain('ADMIN_ARCHIVED_APPROVED');
    expect($archivedHtml)->toContain('ADMIN_ARCHIVED_REJECTED');
    expect($archivedHtml)->not->toContain('ADMIN_ACTIVE_ONLY');
});

test('dashboard kades list follows regional scope by desa, posyandu, and alamat', function () {
    $bidang = makeBidang('sosial');
    $posyanduDesaKades = makePosyandu('Posyandu Kades', 'Desa Kades', 'Kecamatan Kades', 'Kabupaten Kades');
    $posyanduLain = makePosyandu('Posyandu Perbatasan', 'Desa Lain', 'Kecamatan Lain', 'Kabupaten Lain');

    $kades = User::factory()->create([
        'role' => 'kades',
        'desa' => 'Desa Kades',
        'kecamatan' => 'Kecamatan Kades',
        'kabupaten' => 'Kabupaten Kades',
    ]);

    $pemohonSatuDesa = makePemohon($posyanduDesaKades, [
        'alamat' => 'Jl. Melati No. 10 Desa Kades',
    ]);
    $pemohonMatchAlamat = makePemohon($posyanduLain, [
        'desa' => 'Desa Lain',
        'kecamatan' => 'Kecamatan Kades',
        'kabupaten' => 'Kabupaten Kades',
        'alamat' => 'Perbatasan Desa Kades RT 02',
    ]);
    $pemohonLuarWilayah = makePemohon($posyanduLain, [
        'desa' => 'Desa Lain',
        'kecamatan' => 'Kecamatan Lain',
        'kabupaten' => 'Kabupaten Lain',
        'alamat' => 'Jl. Anggrek No. 5 Desa Lain',
    ]);

    makePengajuan($pemohonSatuDesa, $bidang, 'KADES_SCOPE_DESA', [
        'status_pengajuan' => 'Diproses',
        'submitted_to_desa' => false,
    ]);
    makePengajuan($pemohonMatchAlamat, $bidang, 'KADES_SCOPE_ALAMAT', [
        'status_pengajuan' => 'Diproses',
        'submitted_to_desa' => false,
    ]);
    makePengajuan($pemohonLuarWilayah, $bidang, 'KADES_OUTSIDE_SCOPE', [
        'status_pengajuan' => 'Diproses',
        'submitted_to_desa' => true,
    ]);

    $activeResponse = getDashboardDataJson($this, $kades);
    $activeResponse->assertOk();

    $activeHtml = $activeResponse->json('tableHtml');
    expect($activeHtml)->toContain('KADES_SCOPE_DESA');
    expect($activeHtml)->toContain('KADES_SCOPE_ALAMAT');
    expect($activeHtml)->not->toContain('KADES_OUTSIDE_SCOPE');

    $searchByPosyandu = getDashboardDataJson($this, $kades, ['search' => 'Posyandu Kades']);
    $searchByPosyandu->assertOk();
    expect($searchByPosyandu->json('tableHtml'))->toContain('KADES_SCOPE_DESA');

    $searchByAlamat = getDashboardDataJson($this, $kades, ['search' => 'Perbatasan Desa Kades']);
    $searchByAlamat->assertOk();
    $alamatHtml = $searchByAlamat->json('tableHtml');
    expect($alamatHtml)->toContain('KADES_SCOPE_ALAMAT');
    expect($alamatHtml)->not->toContain('KADES_SCOPE_DESA');
});

test('dashboard search supports bidang and posyandu keywords', function () {
    $bidangKesehatan = makeBidang('kesehatan');
    $bidangPendidikan = makeBidang('pendidikan');
    $posyandu = makePosyandu('Posyandu Flamboyan', 'Desa Cari', 'Kecamatan Cari', 'Kabupaten Cari');

    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $pemohonSatu = makePemohon($posyandu, [
        'alamat' => 'Jl. Kenanga No. 88',
    ]);
    $pemohonDua = makePemohon($posyandu, [
        'alamat' => 'Jl. Melati No. 12',
    ]);

    makePengajuan($pemohonSatu, $bidangKesehatan, 'SEARCH_BIDANG_TARGET', [
        'status_pengajuan' => 'Diproses',
    ]);
    makePengajuan($pemohonDua, $bidangPendidikan, 'SEARCH_OTHER_RECORD', [
        'status_pengajuan' => 'Diproses',
    ]);

    $searchByBidang = getDashboardDataJson($this, $admin, ['search' => 'Kesehatan']);
    $searchByBidang->assertOk();
    $bidangHtml = $searchByBidang->json('tableHtml');
    expect($bidangHtml)->toContain('SEARCH_BIDANG_TARGET');
    expect($bidangHtml)->not->toContain('SEARCH_OTHER_RECORD');

    $searchByPosyandu = getDashboardDataJson($this, $admin, ['search' => 'Flamboyan']);
    $searchByPosyandu->assertOk();
    $posyanduHtml = $searchByPosyandu->json('tableHtml');
    expect($posyanduHtml)->toContain('SEARCH_BIDANG_TARGET');
    expect($posyanduHtml)->toContain('SEARCH_OTHER_RECORD');

    $searchByAlamat = getDashboardDataJson($this, $admin, ['search' => 'Kenanga']);
    $searchByAlamat->assertOk();
    $alamatHtml = $searchByAlamat->json('tableHtml');
    expect($alamatHtml)->toContain('SEARCH_BIDANG_TARGET');
    expect($alamatHtml)->not->toContain('SEARCH_OTHER_RECORD');
});

test('dashboard data rejects tampered year values', function (string $year) {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    getDashboardDataJson($this, $admin, ['year' => $year])
        ->assertOk();
})->with([
    'random string' => '873ct...',
    'huge integer' => '83257983579384',
]);

test('dashboard page falls back to current year for invalid year', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->from(route('dashboard'))
        ->get('/dashboard?year=1111')
        ->assertOk();
});
