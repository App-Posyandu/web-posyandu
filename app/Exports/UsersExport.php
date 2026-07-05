<?php

namespace App\Exports;

use App\Models\Pengajuan;
use App\Models\Posyandu;
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
    protected $kecamatan;
    protected $kabupaten;
    protected $year;
    protected $role;
    protected $user;

    public function __construct(
        string $bidang = 'all',
        string $desa = 'all',
        ?string $posyanduId = null,
        ?string $kecamatan = null,
        ?string $kabupaten = null,
        ?int $year = null
    ) {
        $this->bidang     = $bidang;
        $this->desa       = $desa;
        $this->posyanduId = $posyanduId;
        $this->kecamatan  = $kecamatan;
        $this->kabupaten  = $kabupaten;
        $this->year       = $year;
        $this->user       = Auth::user();
        $this->role       = $this->user?->role;
    }

    public function collection()
    {
        $query = Pengajuan::query();
        $user  = $this->user;

        // === SECURITY LAYER: Role-based base scope ===
        // This cannot be bypassed by parameters — narrows dataset to what role is allowed to see.
        switch ($this->role) {
            case 'admin':
                break;

            case 'ketua-timpembina-posyandu':
            case 'admin-kabupaten':
                if ($user->kabupaten) {
                    $kab = $user->kabupaten;
                    $query->whereHas('user', fn ($q) => $q->where('kabupaten', 'ILIKE', '%' . $kab . '%'));
                } else {
                    $query->whereRaw('1 = 0');
                }
                break;

            case 'kabid':
                if ($user->kabupaten) {
                    $kab = $user->kabupaten;
                    $query->whereHas('user', fn ($q) => $q->where('kabupaten', 'ILIKE', '%' . $kab . '%'));
                } else {
                    $query->whereRaw('1 = 0');
                }
                if ($user->bidang_id) {
                    $query->where('bidang_id', $user->bidang_id);
                } else {
                    $query->whereRaw('1 = 0');
                }
                break;

            case 'admin-kecamatan':
                if ($user->kecamatan) {
                    $kec = $user->kecamatan;
                    $query->whereHas('user', fn ($q) => $q->where('kecamatan', 'ILIKE', '%' . $kec . '%'));
                } else {
                    $query->whereRaw('1 = 0');
                }
                break;

            case 'kades':
            case 'bu-kades':
                if ($user->desa) {
                    $userDesa = $user->desa;
                    $query->whereHas('user', fn ($q) => $q->where('desa', $userDesa));
                } else {
                    $query->whereRaw('1 = 0');
                }
                $query->where('submitted_to_desa', true);
                break;

            case 'ketua-posyandu':
                if ($user->posyandu_id) {
                    $pid = $user->posyandu_id;
                    $query->whereHas('user', fn ($q) => $q->where('posyandu_id', $pid));
                } else {
                    $query->whereRaw('1 = 0');
                }
                break;

            default:
                $query->whereRaw('1 = 0');
        }

        // === FILTER LAYER: Explicit scope parameters (narrow within role scope) ===
        // Priority: posyanduId > desa > kecamatan > kabupaten
        if ($this->posyanduId) {
            $pid = $this->posyanduId;
            $query->whereHas('user', fn ($q) => $q->where('posyandu_id', $pid));
        } elseif ($this->desa && $this->desa !== 'all') {
            $d = $this->desa;
            $query->whereHas('user.posyandu', fn ($q) => $q->where('desa', $d));
        } elseif ($this->kecamatan) {
            $kec = $this->kecamatan;
            $query->whereHas('user', fn ($q) => $q->where('kecamatan', 'ILIKE', '%' . $kec . '%'));
        } elseif ($this->kabupaten) {
            $kab = $this->kabupaten;
            $query->whereHas('user', fn ($q) => $q->where('kabupaten', 'ILIKE', '%' . $kab . '%'));
        }

        // Bidang filter by name (for kabid the bidang_id scope already handles security)
        if ($this->bidang !== 'all') {
            $b = $this->bidang;
            $query->whereHas('bidang', fn ($q) => $q->where('nama_bidang', $b));
        }

        if ($this->year) {
            $query->whereYear('created_at', $this->year);
        }

        return $query->with(['user.posyandu', 'histories', 'bidang'])->get();
    }

    public function startCell(): string
    {
        return 'A8';
    }

    public function headings(): array
    {
        return [
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
    }

    private function formatTanggalIndonesia($tanggal, $tipeFormat)
    {
        if (!$tanggal) return '';

        $namaHari = [
            'Sunday'    => 'Minggu',
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu',
        ];
        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $timestamp = strtotime($tanggal);
        $hari   = $namaHari[date('l', $timestamp)];
        $bulan  = $namaBulan[(int) date('n', $timestamp)];
        $tahun  = date('Y', $timestamp);
        $tgl    = date('j', $timestamp);

        return $tipeFormat === 'lengkap'
            ? "{$hari}, {$tgl} {$bulan} {$tahun}"
            : ($tipeFormat === 'singkat' ? "{$tgl} {$bulan} {$tahun}" : date('d-m-Y', $timestamp));
    }

    public function map($pengajuan): array
    {
        $this->rowNumber++;

        $data = [
            $this->rowNumber,
            $this->formatTanggalIndonesia($pengajuan->created_at, 'lengkap'),
            $pengajuan->user->name ?? '-',
            $pengajuan->user->alamat ?? '-',
            ($pengajuan->user->tempat_lahir ?? '-') . ', ' . $this->formatTanggalIndonesia($pengajuan->user->tanggal_lahir, 'singkat'),
            ($pengajuan->user->jenis_kelamin ?? '') === 'Laki-laki' ? '✓' : '',
            ($pengajuan->user->jenis_kelamin ?? '') === 'Perempuan' ? '✓' : '',
            $pengajuan->deskripsi_pengajuan,
            $pengajuan->sudah_verifikasi === 'true' ? '✓' : '',
            $pengajuan->kunjungan_lapangan === 'true' ? '✓' : '',
            $pengajuan->status_pengajuan === 'Disetujui' ? '✓' : '',
            $pengajuan->status_pengajuan === 'Ditolak' ? '✓' : '',
        ];

        // Keterangan column: show extra context when scope is broad
        $hasAllBidang = $this->bidang === 'all';
        $hasAllDesa   = $this->desa === 'all' && !$this->posyanduId;

        if ($hasAllBidang && $hasAllDesa) {
            $desa   = $pengajuan->user->posyandu->desa ?? '-';
            $bidang = $pengajuan->bidang->nama_bidang ?? '-';
            $data[] = "{$desa}, {$bidang}";
        } elseif ($hasAllBidang) {
            $data[] = $pengajuan->bidang->nama_bidang ?? '-';
        } elseif ($hasAllDesa) {
            $data[] = $pengajuan->user->posyandu->desa ?? '-';
        } else {
            $data[] = '';
        }

        return $data;
    }

    public function drawings()
    {
        $drawings = [];
        $logos = [
            ['path' => public_path('assets/image/logo/logo_kebumen.png'), 'width' => 90, 'height' => 60, 'offsetX' => -30],
            ['path' => public_path('assets/image/logo/logo_posyandu.png'), 'width' => 85, 'height' => 60, 'offsetX' => 80],
            ['path' => public_path('assets/image/logo/logo_sapaposyandu.png'), 'width' => 90, 'height' => 60, 'offsetX' => 200],
        ];

        foreach ($logos as $logo) {
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

                // Resolve header context from filter params
                $namaPosyandu = '-';
                $desaHeader   = '-';
                $kecamatan    = '-';

                if ($this->posyanduId) {
                    $posyandu     = Posyandu::find($this->posyanduId);
                    $namaPosyandu = strtoupper($posyandu->nama_posyandu ?? '-');
                    $desaHeader   = strtoupper($posyandu->desa ?? '-');
                    $kecamatan    = strtoupper($posyandu->kecamatan ?? '-');
                } elseif ($this->desa && $this->desa !== 'all') {
                    $posyandu   = Posyandu::where('desa', 'ILIKE', $this->desa)->first();
                    $desaHeader = strtoupper($this->desa);
                    $kecamatan  = strtoupper($posyandu->kecamatan ?? '-');
                } elseif ($this->kecamatan) {
                    $kecamatan  = strtoupper($this->kecamatan);
                    $desaHeader = 'ALL';
                } elseif ($this->kabupaten) {
                    $desaHeader = 'ALL';
                    $kecamatan  = 'ALL';
                } else {
                    // role-scoped "all" — try first record
                    $first = $this->collection()->first();
                    if ($first) {
                        $posyandu     = $first->user->posyandu ?? null;
                        $namaPosyandu = strtoupper($posyandu->nama_posyandu ?? '-');
                        $desaHeader   = strtoupper($posyandu->desa ?? '-');
                        $kecamatan    = strtoupper($posyandu->kecamatan ?? '-');
                    }
                }

                foreach (['POSYANDU', 'DESA', 'KECAMATAN'] as $prefix) {
                    $namaPosyandu = trim(str_replace($prefix, '', $namaPosyandu));
                    $desaHeader   = trim(str_replace($prefix, '', $desaHeader));
                    $kecamatan    = trim(str_replace($prefix, '', $kecamatan));
                }

                $sheet->mergeCells('A2:M2');
                $sheet->setCellValue('A2', 'REKAPITULASI PERMOHONAN LAYANAN STANDAR PELAYANAN MINIMAL POSYANDU DI KABUPATEN KEBUMEN');
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A2')->getFont()->setBold(true);

                $sheet->mergeCells('A3:M3');
                if ($this->posyanduId) {
                    $sheet->setCellValue('A3', "NAMA POSYANDU {$namaPosyandu}, DESA {$desaHeader}, KECAMATAN {$kecamatan}, KABUPATEN KEBUMEN");
                } elseif ($this->desa && $this->desa !== 'all') {
                    $sheet->setCellValue('A3', "DESA " . strtoupper($this->desa) . ", KECAMATAN {$kecamatan}, KABUPATEN KEBUMEN");
                } elseif ($this->kecamatan) {
                    $sheet->setCellValue('A3', "KECAMATAN " . strtoupper($this->kecamatan) . ", KABUPATEN KEBUMEN");
                } elseif ($this->kabupaten) {
                    $sheet->setCellValue('A3', "KABUPATEN " . strtoupper($this->kabupaten));
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

                $merges = ['A7:A8', 'B7:B8', 'C7:C8', 'D7:D8', 'E7:E8', 'F7:G7', 'H7:H8', 'I7:J7', 'K7:L7', 'M7:M8', 'A6:B6'];
                foreach ($merges as $range) {
                    $sheet->mergeCells($range);
                }

                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle('A7:M' . $highestRow)->applyFromArray([
                    'borders'   => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['argb' => '000000'],
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                        'wrapText'   => true,
                    ],
                ]);

                $sheet->getStyle('A7:M8')->getFont()->setBold(true);
                foreach (range('A', 'M') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
                $sheet->getRowDimension(1)->setRowHeight(80);
            },
        ];
    }
}
