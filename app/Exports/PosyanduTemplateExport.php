<?php

namespace App\Exports;

use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class PosyanduTemplateExport implements FromArray, WithHeadings, WithEvents, WithDrawings, WithCustomStartCell
{
    protected $dataRows;

    /**
     * Constructor menerima array data rows yang sudah berisi desa dan kecamatan
     * Format: [['desa' => 'SEMPU', 'kecamatan' => 'GOMBONG'], ...]
     */
    public function __construct(array $dataRows)
    {
        $this->dataRows = $dataRows;
        Log::info('PosyanduTemplateExport initialized', ['total_rows' => count($dataRows)]);
    }

    /**
     * Generate array untuk template dengan nomor urut dan data auto-fill
     */
    public function array(): array
    {
        $data = [];

        foreach ($this->dataRows as $index => $row) {
            $data[] = [
                $index + 1,                    // NO (auto increment)
                '',                            // NAMA POSYANDU (user isi)
                $row['desa'] ?? '',           // DESA (auto-fill dari API)
                $row['kecamatan'] ?? '',      // KECAMATAN (auto-fill dari API)
                'KEBUMEN',                     // KABUPATEN (fixed)
                'JAWA TENGAH'                  // PROVINSI (fixed)
            ];
        }

        Log::info('Template data generated', ['rows' => count($data)]);
        return $data;
    }

    public function startCell(): string
    {
        return 'A6'; // Data mulai dari baris 6
    }

    public function headings(): array
    {
        return [
            'NO.',
            'NAMA POSYANDU',
            'DESA',
            'KECAMATAN',
            'KABUPATEN',
            'PROVINSI'
        ];
    }

    /** LOGO DI ATAS TABEL **/
    public function drawings()
    {
        $drawings = [];

        $logos = [
            [
                'path' => public_path('assets/image/logo/logo_kebumen.png'),
                'width' => 85,
                'height' => 60,
                'offsetX' => -30, // kiri dari tengah
            ],
            [
                'path' => public_path('assets/image/logo/logo_posyandu.png'),
                'width' => 85,
                'height' => 60,
                'offsetX' => 80, // tengah
            ],
            [
                'path' => public_path('assets/image/logo/logo_sapaposyandu.png'),
                'width' => 95,
                'height' => 60,
                'offsetX' => 250, // kanan dari tengah
            ],
        ];

        foreach ($logos as $logo) {
            if (file_exists($logo['path'])) {
                $drawing = new Drawing();
                $drawing->setPath($logo['path']);
                $drawing->setHeight($logo['height']);
                $drawing->setWidth($logo['width']);
                $drawing->setCoordinates('C1');
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

                // Set row height untuk logo
                $sheet->getRowDimension(1)->setRowHeight(80);

                // JUDUL UTAMA - Baris 2
                $sheet->mergeCells('A2:F2');
                $sheet->setCellValue('A2', 'TEMPLATE IMPORT POSYANDU KABUPATEN KEBUMEN');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                        'color' => ['argb' => 'FF1E40AF']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true
                    ]
                ]);
                $sheet->getRowDimension(2)->setRowHeight(30);

                // INSTRUKSI - Baris 3
                $sheet->mergeCells('A3:F3');
                $instruksi = 'Isi kolom NAMA POSYANDU saja. Kolom lainnya sudah otomatis terisi.';
                $sheet->setCellValue('A3', $instruksi);
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => [
                        'italic' => true,
                        'size' => 10,
                        'color' => ['argb' => 'FF991B1B']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFFEF2F2']
                    ],
                ]);
                $sheet->getRowDimension(3)->setRowHeight(25);

                // Baris kosong 4, 5
                $sheet->getRowDimension(4)->setRowHeight(10);
                $sheet->getRowDimension(5)->setRowHeight(10);

                // HEADER TABEL - Baris 6
                $headers = ['NO.', 'NAMA POSYANDU', 'DESA', 'KECAMATAN', 'KABUPATEN', 'PROVINSI'];
                foreach ($headers as $index => $header) {
                    $column = chr(65 + $index); // A, B, C, D, E, F
                    $sheet->setCellValue($column . '6', $header);
                }

                // Style untuk header
                $sheet->getStyle('A6:F6')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 11,
                        'color' => ['argb' => 'FFFFFFFF']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF1E40AF']
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]);

                // Get highest row untuk styling data
                $highestRow = 6 + count($this->dataRows);

                // Highlight kolom NAMA POSYANDU (user harus isi ini)
                $sheet->getStyle('B7:B' . $highestRow)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFEFF6FF'] // Light blue
                    ]
                ]);

                // Style untuk semua data (border dan alignment)
                $sheet->getStyle('A6:F' . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true
                    ]
                ]);

                // Alignment khusus per kolom
                // NO - Center
                $sheet->getStyle('A7:A' . $highestRow)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // NAMA POSYANDU - Left (user isi)
                $sheet->getStyle('B7:B' . $highestRow)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // DESA - Left
                $sheet->getStyle('C7:C' . $highestRow)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // KECAMATAN - Center
                $sheet->getStyle('D7:D' . $highestRow)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // KABUPATEN - Center
                $sheet->getStyle('E7:E' . $highestRow)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // PROVINSI - Center
                $sheet->getStyle('F7:F' . $highestRow)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Set column widths
                $sheet->getColumnDimension('A')->setWidth(6);   // NO
                $sheet->getColumnDimension('B')->setWidth(30);  // NAMA POSYANDU (lebih lebar untuk user input)
                $sheet->getColumnDimension('C')->setWidth(20);  // DESA
                $sheet->getColumnDimension('D')->setWidth(20);  // KECAMATAN
                $sheet->getColumnDimension('E')->setWidth(15);  // KABUPATEN
                $sheet->getColumnDimension('F')->setWidth(15);  // PROVINSI
    
                // Set row height untuk data
                for ($row = 7; $row <= $highestRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(22);
                }

                // Lock semua cell kecuali NAMA POSYANDU
                $sheet->getProtection()->setSheet(true);
                $sheet->getStyle('B7:B' . $highestRow)->getProtection()->setLocked(false);
            },
        ];
    }
}