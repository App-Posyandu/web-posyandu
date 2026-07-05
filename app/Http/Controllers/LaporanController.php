<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UsersExport;
use App\Models\Posyandu;
use Illuminate\Support\Facades\Auth;

class LaporanController extends Controller
{
    public function index()
    {
        return view('dashboard.partials.laporan');
    }

    /**
     * Returns scoped dropdown options for the export wizard.
     * Response: { posyandus: [{id, nama_posyandu, desa, kecamatan}], kecamatans: [], kabupatens: [] }
     */
    public function getExportOptions(Request $request)
    {
        $user = Auth::user();

        $posyandus  = collect();
        $kecamatans = collect();
        $kabupatens = collect();

        switch ($user->role) {
            case 'admin':
                $posyandus  = Posyandu::select('id', 'nama_posyandu', 'desa', 'kecamatan', 'kabupaten')
                    ->orderBy('nama_posyandu')->get();
                $kecamatans = Posyandu::distinct()->orderBy('kecamatan')->pluck('kecamatan')->filter()->values();
                $kabupatens = Posyandu::distinct()->orderBy('kabupaten')->pluck('kabupaten')->filter()->values();
                break;

            case 'admin-kabupaten':
            case 'ketua-timpembina-posyandu':
                if ($user->kabupaten) {
                    $posyandus  = Posyandu::select('id', 'nama_posyandu', 'desa', 'kecamatan', 'kabupaten')
                        ->where('kabupaten', 'ILIKE', '%' . $user->kabupaten . '%')
                        ->orderBy('nama_posyandu')->get();
                    $kecamatans = Posyandu::where('kabupaten', 'ILIKE', '%' . $user->kabupaten . '%')
                        ->distinct()->orderBy('kecamatan')->pluck('kecamatan')->filter()->values();
                }
                break;

            case 'kabid':
                if ($user->kabupaten) {
                    $kecamatans = Posyandu::where('kabupaten', 'ILIKE', '%' . $user->kabupaten . '%')
                        ->distinct()->orderBy('kecamatan')->pluck('kecamatan')->filter()->values();
                }
                break;

            case 'admin-kecamatan':
                if ($user->kecamatan) {
                    $posyandus = Posyandu::select('id', 'nama_posyandu', 'desa', 'kecamatan', 'kabupaten')
                        ->where('kecamatan', 'ILIKE', '%' . $user->kecamatan . '%')
                        ->orderBy('nama_posyandu')->get();
                }
                break;

            case 'kades':
            case 'bu-kades':
                if ($user->desa) {
                    $posyandus = Posyandu::select('id', 'nama_posyandu', 'desa', 'kecamatan', 'kabupaten')
                        ->where('desa', 'ILIKE', $user->desa)
                        ->orderBy('nama_posyandu')->get();
                }
                break;
        }

        return response()->json([
            'posyandus'  => $posyandus,
            'kecamatans' => $kecamatans,
            'kabupatens' => $kabupatens,
        ]);
    }

    /**
     * Unified export endpoint.
     *
     * Query params:
     *   scope       : all | kabupaten | kecamatan | desa | posyandu | bidang
     *   kabupaten   : required when scope=kabupaten
     *   kecamatan   : required when scope=kecamatan
     *   desa        : required when scope=desa
     *   posyandu_id : required when scope=posyandu
     *   bidang      : optional additional bidang filter (required when scope=bidang)
     *   year        : optional year filter
     */
    public function exportUnified(Request $request)
    {
        $scope      = $request->query('scope', 'all');
        $kabupaten  = $request->query('kabupaten');
        $kecamatan  = $request->query('kecamatan');
        $desa       = $request->query('desa');
        $posyanduId = $request->query('posyandu_id');
        $bidang     = $request->query('bidang', 'all');
        $year       = $request->query('year') ? (int) $request->query('year') : null;

        $user = Auth::user();
        $role = $user->role;

        // Authorization matrix: which scopes each role may request
        $allowed = [
            'admin'                     => ['all', 'kabupaten', 'kecamatan', 'desa', 'posyandu', 'bidang'],
            'admin-kabupaten'           => ['all', 'kecamatan', 'desa', 'posyandu', 'bidang'],
            'ketua-timpembina-posyandu' => ['all', 'kecamatan', 'desa', 'posyandu', 'bidang'],
            'kabid'                     => ['all', 'kecamatan', 'desa'],
            'admin-kecamatan'           => ['all', 'desa', 'posyandu', 'bidang'],
            'kades'                     => ['all', 'posyandu', 'bidang'],
            'bu-kades'                  => ['all', 'posyandu', 'bidang'],
            'ketua-posyandu'            => ['all', 'bidang'],
        ];

        if (!isset($allowed[$role]) || !in_array($scope, $allowed[$role])) {
            abort(403, 'Anda tidak memiliki izin untuk export ini.');
        }

        // Scope-specific authorization
        switch ($scope) {
            case 'kabupaten':
                if (!$kabupaten) abort(400, 'Parameter kabupaten diperlukan.');
                break;

            case 'kecamatan':
                if (!$kecamatan) abort(400, 'Parameter kecamatan diperlukan.');
                if (in_array($role, ['admin-kabupaten', 'ketua-timpembina-posyandu', 'kabid']) && $user->kabupaten) {
                    $valid = Posyandu::where('kecamatan', 'ILIKE', '%' . $kecamatan . '%')
                        ->where('kabupaten', 'ILIKE', '%' . $user->kabupaten . '%')
                        ->exists();
                    if (!$valid) abort(403, 'Kecamatan tidak ditemukan dalam kabupaten Anda.');
                }
                break;

            case 'desa':
                if (!$desa) abort(400, 'Parameter desa diperlukan.');
                if (in_array($role, ['kades', 'bu-kades']) && strtolower($desa) !== strtolower($user->desa ?? '')) {
                    abort(403, 'Anda hanya bisa export desa Anda: ' . $user->desa);
                }
                if ($role === 'admin-kecamatan' && $user->kecamatan) {
                    $valid = Posyandu::where('desa', 'ILIKE', '%' . $desa . '%')
                        ->where('kecamatan', 'ILIKE', '%' . $user->kecamatan . '%')
                        ->exists();
                    if (!$valid) abort(403, 'Desa tidak termasuk dalam kecamatan Anda.');
                }
                if (in_array($role, ['admin-kabupaten', 'ketua-timpembina-posyandu']) && $user->kabupaten) {
                    $valid = Posyandu::where('desa', 'ILIKE', '%' . $desa . '%')
                        ->where('kabupaten', 'ILIKE', '%' . $user->kabupaten . '%')
                        ->exists();
                    if (!$valid) abort(403, 'Desa tidak termasuk dalam kabupaten Anda.');
                }
                break;

            case 'posyandu':
                if (!$posyanduId) abort(400, 'Parameter posyandu_id diperlukan.');
                $posyandu = Posyandu::find($posyanduId);
                if (!$posyandu) abort(404, 'Posyandu tidak ditemukan.');

                if ($role === 'ketua-posyandu' && (string) $posyanduId !== (string) $user->posyandu_id) {
                    abort(403, 'Anda hanya bisa export posyandu Anda.');
                }
                if (in_array($role, ['kades', 'bu-kades'])) {
                    if (strtolower($posyandu->desa ?? '') !== strtolower($user->desa ?? '')) {
                        abort(403, 'Posyandu tidak termasuk dalam desa Anda.');
                    }
                }
                if ($role === 'admin-kecamatan' && $user->kecamatan) {
                    if (stripos($posyandu->kecamatan ?? '', $user->kecamatan) === false) {
                        abort(403, 'Posyandu tidak termasuk dalam kecamatan Anda.');
                    }
                }
                if (in_array($role, ['admin-kabupaten', 'ketua-timpembina-posyandu']) && $user->kabupaten) {
                    if (stripos($posyandu->kabupaten ?? '', $user->kabupaten) === false) {
                        abort(403, 'Posyandu tidak termasuk dalam kabupaten Anda.');
                    }
                }
                break;

            case 'bidang':
                if (!$bidang || $bidang === 'all') abort(400, 'Parameter bidang diperlukan.');
                break;
        }

        // Kabid: bidang is auto-injected from their assigned bidang
        if ($role === 'kabid') {
            $bidang = $user->bidang?->nama_bidang ?? 'all';
        }

        $exportParams = $this->resolveExportParams($scope, $kabupaten, $kecamatan, $desa, $posyanduId, $bidang);

        $export = new UsersExport(
            $exportParams['bidang'],
            $exportParams['desa'],
            $exportParams['posyanduId'],
            $exportParams['kecamatan'],
            $exportParams['kabupaten'],
            $year
        );

        $filename = $this->buildFilename($scope, $kabupaten, $kecamatan, $desa, $posyanduId, $bidang);

        return Excel::download($export, $filename);
    }

    private function resolveExportParams($scope, $kabupaten, $kecamatan, $desa, $posyanduId, $bidang): array
    {
        return [
            'bidang'     => $bidang,
            'desa'       => $scope === 'desa' ? $desa : 'all',
            'posyanduId' => $scope === 'posyandu' ? $posyanduId : null,
            'kecamatan'  => $scope === 'kecamatan' ? $kecamatan : null,
            'kabupaten'  => $scope === 'kabupaten' ? $kabupaten : null,
        ];
    }

    private function buildFilename($scope, $kabupaten, $kecamatan, $desa, $posyanduId, $bidang): string
    {
        $base = 'Laporan_Data_Pengajuan';
        switch ($scope) {
            case 'kabupaten':
                return $base . '_' . strtoupper(str_replace(' ', '_', $kabupaten ?? 'Kabupaten')) . '.xlsx';
            case 'kecamatan':
                return $base . '_Kec_' . strtoupper(str_replace(' ', '_', $kecamatan ?? '')) . '.xlsx';
            case 'desa':
                return $base . '_Desa_' . strtoupper(str_replace(' ', '_', $desa ?? '')) . '.xlsx';
            case 'posyandu':
                $nama = $posyanduId ? (Posyandu::find($posyanduId)?->nama_posyandu ?? 'Posyandu') : 'Posyandu';
                return $base . '_' . strtoupper(str_replace(' ', '_', $nama)) . '.xlsx';
            case 'bidang':
                return $base . '_' . strtoupper(str_replace(' ', '_', $bidang ?? 'Bidang')) . '.xlsx';
            default:
                return $base . '_Keseluruhan.xlsx';
        }
    }
}
