<?php

namespace App\Exports;

use App\Models\Pengajuan;
use App\Models\BidangPengajuan;
use Illuminate\Support\Facades\Auth;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class UsersExport implements FromCollection, WithHeadings, WithMapping, WithEvents, WithDrawings, WithCustomStartCell
{
    protected $rowNumber = 0;
    protected $bidang;
    protected $desa;
    protected $posyanduId;
    protected $role;
    protected $user;

    public function __construct($bidang = 'all', $desa = 'all', $posyanduId = null)
    {
        $this->bidang = $bidang;
        $this->desa = $desa;
        $this->posyanduId = $posyanduId;
        $this->user = Auth::user();
        $this->role = $this->user?->role;
    }


    public function collection()
    {
        $query = Pengajuan::query();

        if ($this->posyanduId) {
            $query->whereHas('user', function ($q) {
                $q->where('posyandu_id', $this->posyanduId);
            });
        }
        else if ($this->desa && $this->desa !== 'all') {
            $query->whereHas('user.posyandu', function ($q) {
                $q->where('desa', $this->desa);
            });
        }

        if ($this->bidang !== 'all') {
            $query->whereHas('bidang', function ($q) {
                $q->where('nama_bidang', $this->bidang);
            });
        }
        switch ($this->role) {
            case 'ketua-posyandu':
                if (!$this->posyanduId && $this->user->posyandu_id) {
                    $userPosyanduId = $this->user->posyandu_id;
                    $query->whereHas('user', fn($q) => $q->where('posyandu_id', $userPosyanduId));
                }
                $query->where(function ($q) {
                    $q->where(function ($subQ) {
                        $subQ->where('kunjungan_lapangan', true)
                            ->where('approved_by_timpembina', false)
                            ->where('status_pengajuan', 'Diproses');
                    })
                    ->orWhere('approved_by_timpembina', true)
                    ->orWhereIn('status_pengajuan', ['Disetujui', 'Ditolak']);
                });
                break;

            case 'kades':
            case 'bu-kades':
                if ($this->user->desa) {
                    $userDesa = $this->user->desa;
                    $query->whereHas('user', fn($q) => $q->where('desa', $userDesa));
                }
                $query->where(function ($q) {
                    $q->where('submitted_to_desa', true)
                        ->orWhereIn('status_pengajuan', ['Disetujui', 'Ditolak']);
                });
                break;

            case 'admin-kecamatan':
                if ($this->user->kecamatan) {
                    $query->whereHas('user', fn($q) => $q->where('kecamatan', 'LIKE', '%' . $this->user->kecamatan . '%'));
                }
                break;

            case 'kabid':
                if ($this->user->bidang_id) {
                    $query->where('bidang_id', $this->user->bidang_id);
                }
                break;

            case 'admin':
            case 'admin-kabupaten':
            case 'ketua-timpembina-posyandu':
                break;

            default:
                break;
        }

        return $query->with(['user.posyandu', 'histories', 'bidang'])->get();
    }


    public function startCell(): string
    {
        return 'A8';
    }

    public function headings(): array
    {
        $base = [
            'NO',
            'HARI/TANGGAL',
            'NAMA',
            'ALAMAT',
            'TEMPAT TGL LAHIR',
            'L',
            'P',
            'DESKRIPSI PERMOHONAN LAYANAN',
            'SUDAH',
            'KUNJUNGAN',
            'DISETUJUI',
            'DITOLAK',
            'KETERANGAN',
        ];

        return $base;
    }


    private function formatTanggalIndonesia($tanggal, $tipeFormat)
    {
        if (!$tanggal) return '';

        $namaHari = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu'
        ];
        $namaBulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        $timestamp = strtotime($tanggal);
        $hari = $namaHari[date('l', $timestamp)];
        $bulan = $namaBulan[(int)date('n', $timestamp)];
        $tahun = date('Y', $timestamp);
        $tgl = date('j', $timestamp);

        return $tipeFormat == "lengkap"
            ? "{$hari}, {$tgl} {$bulan} {$tahun}"
            : ($tipeFormat == "singkat" ? "{$tgl} {$bulan} {$tahun}" : date('d-m-Y', $timestamp));
    }

    public function map($pengajuan): array
    {
        $this->rowNumber++;
        $latestHistory = $pengajuan->histories->sortByDesc('created_at')->first();

        $data = [
            $this->rowNumber,
            $this->formatTanggalIndonesia($pengajuan->created_at, 'lengkap'),
            $pengajuan->user->name ?? '-',
            $pengajuan->user->alamat ?? '-',
            $pengajuan->user->tempat_lahir . ', ' . $this->formatTanggalIndonesia($pengajuan->user->tanggal_lahir, 'singkat'),
            $pengajuan->user->jenis_kelamin == 'Laki-laki' ? '✓' : '',
            $pengajuan->user->jenis_kelamin == 'Perempuan' ? '✓' : '',
            $pengajuan->deskripsi_pengajuan,
            $pengajuan->sudah_verifikasi == 'true' ? '✓' : '',
            $pengajuan->kunjungan_lapangan == 'true' ? '✓' : '',
            $pengajuan->status_pengajuan == 'Disetujui' ? '✓' : '',
            $pengajuan->status_pengajuan == 'Ditolak' ? '✓' : '',
        ];

        if ($this->bidang === 'all' && $this->desa === 'all') {
            $desa   = $pengajuan->user->posyandu->desa ?? '-';
            $bidang = $pengajuan->bidang->nama_bidang ?? '-';
            $data[] = "{$desa}, {$bidang}";
        }

        else if ($this->bidang === 'all') {
            $data[] =  $pengajuan->bidang->nama_bidang ?? '-';
        }

        else if ($this->desa === 'all') {
            $data[] = $pengajuan->user->posyandu->desa ?? '-';
        }

        return $data;
    }

    public function drawings()
    {
        $drawings = [];

        $logos = [
            [
                'path' => public_path('assets/image/logo/logo_kebumen.png'),
                'width' => 90,
                'height' => 60,
                'offsetX' => -30,
            ],
            [
                'path' => public_path('assets/image/logo/logo_posyandu.png'),
                'width' => 85,
                'height' => 60,
                'offsetX' => 80,
            ],
            [
                'path' => public_path('assets/image/logo/logo_sapaposyandu.png'),
                'width' => 90,
                'height' => 60,
                'offsetX' => 200,
            ],
        ];

        foreach ($logos as $index => $logo) {
            if (file_exists($logo['path'])) {
                $drawing = new Drawing();
                $drawing->setPath($logo['path']);
                $drawing->setHeight($logo['height']);
                $drawing->setWidth($logo['width']);

                $drawing->setCoordinates('H1');
                $drawing->setOffsetX($logo['offsetX']);
                $drawing->setOffsetY(5);

                $drawings[] = $drawing;
            }
        }

        return $drawings;
    }


    public function registerEvents(): array
    {

        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $pengajuanPertama = $this->collection()->first();
                $posyandu = $pengajuanPertama->user->posyandu ?? null;

                $namaPosyandu = strtoupper($posyandu->nama_posyandu ?? '-');
                $desa = strtoupper($posyandu->desa ?? '-');
                $kecamatan = strtoupper($posyandu->kecamatan ?? '-');

                if (str_contains($namaPosyandu, 'POSYANDU')) {
                    $namaPosyandu = str_replace('POSYANDU', '', $namaPosyandu);
                    $namaPosyandu = trim($namaPosyandu);
                }

                if (str_contains($desa, 'DESA')) {
                    $desa = str_replace('DESA', '', $desa);
                    $desa = trim($desa);
                }
                if (str_contains($kecamatan, 'KECAMATAN')) {
                    $kecamatan = str_replace('KECAMATAN', '', $kecamatan);
                    $kecamatan = trim($kecamatan);
                }


                $sheet->mergeCells('A2:M2');
                $sheet->setCellValue('A2', 'REKAPITULASI PERMOHONAN LAYANAN STANDAR PELAYANAN MINIMAL POSYANDU DI KABUPATEN KEBUMEN');
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A2')->getFont()->setBold(true);

                $sheet->mergeCells('A3:M3');
                if ($this->desa !== 'all') {
                    $sheet->setCellValue('A3', "NAMA POSYANDU {$namaPosyandu}, DESA {$desa}, KECAMATAN {$kecamatan}, KABUPATEN KEBUMEN");
                } else {
                    $sheet->setCellValue('A3', "KABUPATEN KEBUMEN");
                }
                $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A3')->getFont()->setBold(true);

                if ($this->bidang !== 'all') {
                    $sheet->mergeCells('A6:B6');
                    $sheet->setCellValue('A6', strtoupper($this->bidang));
                    $sheet->getStyle('A6')->getFont()->setBold(true);
                }



                $sheet->setCellValue('A7', 'NO.');
                $sheet->setCellValue('B7', 'HARI/TANGGAL');
                $sheet->setCellValue('C7', 'NAMA');
                $sheet->setCellValue('D7', 'ALAMAT');
                $sheet->setCellValue('E7', 'TEMPAT TGL LAHIR');
                $sheet->setCellValue('F7', 'JENIS KELAMIN');
                $sheet->setCellValue('H7', 'DESKRIPSI PERMOHONAN LAYANAN');
                $sheet->setCellValue('I7', 'TINDAK LANJUT PENGADUAN (√)');
                $sheet->setCellValue('K7', 'STATUS PENGAJUAN');
                $sheet->setCellValue('M7', 'KETERANGAN');

                $sheet->setCellValue('F8', 'L');
                $sheet->setCellValue('G8', 'P');
                $sheet->setCellValue('I8', 'SUDAH');
                $sheet->setCellValue('J8', 'KUNJUNGAN');
                $sheet->setCellValue('K8', 'DISETUJUI');
                $sheet->setCellValue('L8', 'DITOLAK');
                $merge = [
                    'A7:A8',
                    'B7:B8',
                    'C7:C8',
                    'D7:D8',
                    'E7:E8',
                    'F7:G7',
                    'H7:H8',
                    'I7:J7',
                    'K7:L7',
                    'M7:M8',
                    'A6:B6'
                ];
                $maxCell = 'M';
                foreach ($merge as $range) $sheet->mergeCells($range);

                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle('A7:M' . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                ]);

                $sheet->getStyle('A7:M8')->getFont()->setBold(true);
                foreach (range('A', 'M') as $col)
                    $sheet->getColumnDimension($col)->setAutoSize(true);

                $sheet->getRowDimension(1)->setRowHeight(80);
            },
        ];
    }
}