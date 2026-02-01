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

class UsersTemplateExport implements FromArray, WithHeadings, WithEvents, WithDrawings, WithCustomStartCell
{
    protected $dataRows;
    protected $roleToCreate;

    public function __construct(array $dataRows, $roleToCreate = null)
    {
        $this->dataRows = $dataRows;
        $this->roleToCreate = $roleToCreate;
        Log::info('UserTemplateExport initialized', [
            'total_rows' => count($dataRows),
            'role_to_create' => $roleToCreate
        ]);
    }

    public function array(): array
    {
        $data = [];
        
        foreach ($this->dataRows as $index => $row) {
            $data[] = [
                $index + 1,
                '',
                '',
                $row['desa'] ?? '',
                $row['kecamatan'] ?? '',
                $row['kabupaten'] ?? 'KEBUMEN'
            ];
        }
        
        Log::info('Template data generated', [
            'rows' => count($data),
            'role_to_create' => $this->roleToCreate
        ]);
        return $data;
    }

    public function startCell(): string
    {
        return 'A6';
    }

    public function headings(): array
    {
        return [
            'no',
            'nama',
            'nomor_telepon',
            'desa',
            'kecamatan',
            'kabupaten'
        ];
    }

    public function drawings()
    {
        $drawings = [];

        $logos = [
            [
                'path' => public_path('assets/image/logo/logo_kebumen.png'),
                'width' => 85,
                'height' => 60,
                'offsetX' => 0,
                'position' => 'C1',
            ],
            [
                'path' => public_path('assets/image/logo/logo_posyandu.png'),
                'width' => 85,
                'height' => 60,
                'offsetX' => 100,
                'position' => 'C1',
            ],
            [
                'path' => public_path('assets/image/logo/logo_sapaposyandu.png'),
                'width' => 95,
                'height' => 60,
                'offsetX' => 80,
                'position' => 'D1',
            ],
        ];


        foreach ($logos as $logo) {
            if (file_exists($logo['path'])) {
                $drawing = new Drawing();
                $drawing->setPath($logo['path']);
                $drawing->setHeight($logo['height']);
                $drawing->setWidth($logo['width']);
                $drawing->setCoordinates($logo['position']);
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

                $sheet->getRowDimension(1)->setRowHeight(80);

                $roleDisplayMap = [
                    'masyarakat' => 'MASYARAKAT',
                    'operator-desa' => 'OPERATOR DESA',
                    'kader' => 'KADER',
                    'ketua-kader' => 'KETUA KADER',
                    'admin-kecamatan' => 'ADMIN KECAMATAN',
                    'kabid' => 'KABID',
                    'admin-kabupaten' => 'ADMIN KABUPATEN'
                ];
                $roleDisplay = $roleDisplayMap[$this->roleToCreate] ?? 'USER';

                $sheet->mergeCells('A2:F2');
                $sheet->setCellValue('A2', 'TEMPLATE IMPORT USER [' . $roleDisplay . '] KABUPATEN KEBUMEN');
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

                $sheet->mergeCells('A3:F3');
                $instruksi = 'Isi kolom NAMA dan NOMOR TELEPON saja. Kolom lainnya sudah otomatis terisi berdasarkan data Posyandu.';
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

                $sheet->getRowDimension(4)->setRowHeight(10);
                $sheet->getRowDimension(5)->setRowHeight(10);

                $headers = ['NO.', 'NAMA', 'NOMOR TELEPON', 'DESA', 'KECAMATAN', 'KABUPATEN'];
                foreach ($headers as $index => $header) {
                    $column = chr(65 + $index);
                    $sheet->setCellValue($column . '6', $header);
                }

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

                $highestRow = 6 + count($this->dataRows);

                $sheet->getStyle('B7:C' . $highestRow)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFEFF6FF']
                    ]
                ]);

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

                $sheet->getStyle('A7:A' . $highestRow)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle('B7:B' . $highestRow)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT);

                $sheet->getStyle('C7:C' . $highestRow)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT);

                $sheet->getStyle('D7:D' . $highestRow)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT);

                $sheet->getStyle('E7:E' . $highestRow)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle('F7:F' . $highestRow)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getColumnDimension('A')->setWidth(6);
                $sheet->getColumnDimension('B')->setWidth(25);
                $sheet->getColumnDimension('C')->setWidth(18);
                $sheet->getColumnDimension('D')->setWidth(20);
                $sheet->getColumnDimension('E')->setWidth(20);
                $sheet->getColumnDimension('F')->setWidth(15);

                for ($row = 7; $row <= $highestRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(22);
                }

                $sheet->getProtection()->setSheet(true);
                $sheet->getStyle('B7:C' . $highestRow)->getProtection()->setLocked(false);
            },
        ];
    }
}