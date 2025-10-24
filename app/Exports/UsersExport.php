<?php

namespace App\Exports;

use App\Models\Pengajuan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing;

class UsersExport implements FromCollection, WithHeadings, WithMapping, WithEvents, WithDrawings
{
    protected $drawings = [];

    public function collection()
    {
        return Pengajuan::all();
    }

    public function headings(): array
    {
        return Schema::getColumnListing('pengajuans');
    }

    public function map($pengajuan): array
    {
        // Format kolom formulir_items menjadi list ke bawah
        $items = $pengajuan->formulir_items;
        if (is_string($items)) {
            $decoded = json_decode($items, true);
            $items = json_last_error() === JSON_ERROR_NONE ? $decoded : [$items];
        } elseif (!is_array($items)) {
            $items = [$items];
        }

        $formulirList = '';
        foreach ($items as $i => $item) {
            $formulirList .= ($i + 1) . '. ' . $item . "\n";
        }

        // administrasi_items akan diganti dengan gambar, jadi kosongkan saja kolomnya
        return [
            $pengajuan->id,
            $pengajuan->user_id,
            $pengajuan->bidang_id,
            $pengajuan->deskripsi_pengajuan,
            $pengajuan->status,
            $formulirList,
            '', // gambar akan muncul di sini (in-cell)
            $pengajuan->created_at,
            $pengajuan->updated_at,
        ];
    }

    public function drawings()
    {
        $rows = Pengajuan::select('id', 'administrasi_items')->get();
        $drawings = [];
        $rowIndex = 2; // mulai baris data (setelah header)

        foreach ($rows as $pengajuan) {
            $admin = $pengajuan->administrasi_items;

            // Handle array atau JSON
            if (is_string($admin)) {
                $admin = json_decode($admin, true);
            }

            if (!is_array($admin)) {
                $rowIndex++;
                continue;
            }

            $imgY = 0; // offset vertikal dalam sel
            foreach ($admin as $key => $path) {
                $fullPath = storage_path('app/public/' . ltrim($path, '/'));
                if (!file_exists($fullPath)) {
                    continue;
                }

                // Buat resource GD dari gambar
                $gdImage = @imagecreatefromjpeg($fullPath);
                if (!$gdImage) {
                    continue;
                }

                $drawing = new MemoryDrawing();
                $drawing->setName($key);
                $drawing->setDescription($key);
                $drawing->setImageResource($gdImage);
                $drawing->setRenderingFunction(MemoryDrawing::RENDERING_JPEG);
                $drawing->setMimeType(MemoryDrawing::MIMETYPE_DEFAULT);
                $drawing->setHeight(60); // tinggi gambar dalam sel
                $drawing->setCoordinates('G' . $rowIndex);
                $drawing->setOffsetY($imgY); // jarak antar gambar di dalam satu sel

                $drawings[] = $drawing;

                // naikin posisi gambar kedua agar tidak tumpuk
                $imgY += 65;
            }

            $rowIndex++;
        }

        return $drawings;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Wrap text di kolom F
                $sheet->getStyle('F')->getAlignment()->setWrapText(true);

                // Lebar & tinggi kolom agar gambar muat
                foreach (range('A', 'I') as $col) {
                    $sheet->getColumnDimension($col)->setWidth(25);
                }

                $highestRow = $sheet->getHighestRow();
                for ($row = 2; $row <= $highestRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(130);
                }

                // Rata tengah vertikal & horizontal
                $sheet->getStyle('A1:I' . $highestRow)->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Header tebal + warna abu
                $sheet->getStyle('A1:I1')->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E0E0E0'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
            },
        ];
    }
}
