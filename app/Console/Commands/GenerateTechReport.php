<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Barryvdh\DomPDF\Facade\Pdf;

class GenerateTechReport extends Command
{
    protected $signature = 'report:generate';
    protected $description = 'Generate PDF laporan teknologi lengkap (require & dev) dari composer.json dan package.json';

    public function handle()
    {
        $this->info('Memulai analisis FULL (Production + Dev)...');

        $this->info('Membaca composer.json...');
        $composerPath = base_path('composer.json');

        if (!File::exists($composerPath)) {
            $this->error('File composer.json tidak ditemukan!');
            return;
        }

        $composerData = json_decode(File::get($composerPath), true);

        $reqMain = $composerData['require'] ?? [];
        $reqDev  = $composerData['require-dev'] ?? [];

        $backendPackages = [];

        $processComposer = function ($list, $type) use (&$backendPackages) {
            foreach ($list as $package => $version) {
                if ($package === 'php' || str_starts_with($package, 'ext-')) continue;

                $desc = 'Library Backend';
                $vendorPath = base_path("vendor/{$package}/composer.json");

                if (File::exists($vendorPath)) {
                    $vData = json_decode(File::get($vendorPath), true);
                    $desc = $vData['description'] ?? $desc;
                }

                $backendPackages[] = [
                    'name' => $package,
                    'version' => $version,
                    'type' => $type,
                    'desc' => $desc
                ];
                $this->line("   [$type] PHP: $package");
            }
        };

        $processComposer($reqMain, 'PROD');
        $processComposer($reqDev,  'DEV');

        $this->info('Membaca package.json...');
        $npmPath = base_path('package.json');
        $frontendPackages = [];

        if (File::exists($npmPath)) {
            $npmData = json_decode(File::get($npmPath), true);

            $depMain = $npmData['dependencies'] ?? [];
            $depDev  = $npmData['devDependencies'] ?? [];

            $processNpm = function ($list, $type) use (&$frontendPackages) {
                foreach ($list as $package => $version) {
                    $desc = 'Library Frontend';
                    $nodePath = base_path("node_modules/{$package}/package.json");

                    if (File::exists($nodePath)) {
                        $nData = json_decode(File::get($nodePath), true);
                        $desc = $nData['description'] ?? $desc;
                    }

                    $frontendPackages[] = [
                        'name' => $package,
                        'version' => $version,
                        'type' => $type,
                        'desc' => $desc
                    ];
                    $this->line("   [$type] JS: $package");
                }
            };

            $processNpm($depMain, 'PROD');
            $processNpm($depDev,  'DEV');
        }

        $this->info('Sedang mencetak PDF Laporan Lengkap...');

        $html = $this->generateHtml($backendPackages, $frontendPackages);

        $pdf = Pdf::loadHTML($html)->setPaper('a4', 'portrait');
        $filename = 'Laporan_Teknologi_Lengkap.pdf';
        $pdf->save(public_path($filename));

        $this->info("SUKSES! File tersimpan di: public/$filename");
    }

    private function generateHtml($backend, $frontend)
    {
        $date = date('d F Y');

        $css = "
            body { font-family: sans-serif; color: #333; font-size: 10pt; }
            h1 { text-align: center; color: #2d3748; border-bottom: 2px solid #cbd5e0; padding-bottom: 10px; margin-bottom: 20px; }
            h3 { background-color: #2c5282; color: white; padding: 8px; border-radius: 4px; margin-top: 25px; font-size: 12pt; }
            table { width: 100%; border-collapse: collapse; margin-top: 5px; }
            th, td { border: 1px solid #e2e8f0; padding: 6px; text-align: left; vertical-align: top; }
            th { background-color: #edf2f7; color: #4a5568; font-size: 9pt; }

            .badge { padding: 3px 5px; border-radius: 4px; font-size: 8pt; font-weight: bold; display: inline-block; min-width: 40px; text-align: center; }
            .badge-prod { background-color: #c6f6d5; color: #22543d; border: 1px solid #9ae6b4; }
            .badge-dev { background-color: #fed7d7; color: #822727; border: 1px solid #feb2b2; }

            .ver { font-family: monospace; color: #555; }
            .footer { margin-top: 30px; text-align: right; font-size: 8pt; color: #a0aec0; border-top: 1px solid #eee; padding-top: 5px;}
        ";

        $buildTableRows = function ($items) {
            $html = "";
            foreach ($items as $item) {
                $badgeClass = ($item['type'] === 'PROD') ? 'badge-prod' : 'badge-dev';

                $html .= "<tr>
                    <td width='10%'><span class='badge $badgeClass'>{$item['type']}</span></td>
                    <td width='30%'><strong>{$item['name']}</strong></td>
                    <td width='15%' class='ver'>{$item['version']}</td>
                    <td>{$item['desc']}</td>
                </tr>";
            }
            return $html;
        };

        $rowsBackend = $buildTableRows($backend);
        $rowsFrontend = $buildTableRows($frontend);

        return "
        <html>
            <head><style>$css</style></head>
            <body>
                <h1>List Package</h1>
                <p>Berikut adalah rincian seluruh dependensi (dependencies) yang terdeteksi dalam file konfigurasi sistem, mencakup library utama (Production) dan library pendukung pengembangan (Development).</p>

                <h3>A. Backend (PHP / Composer)</h3>
                <table>
                    <thead><tr><th>Tipe</th><th>Package</th><th>Versi</th><th>Deskripsi</th></tr></thead>
                    <tbody>$rowsBackend</tbody>
                </table>

                <h3>B. Frontend (Node.js / NPM)</h3>
                <table>
                    <thead><tr><th>Tipe</th><th>Package</th><th>Versi</th><th>Deskripsi</th></tr></thead>
                    <tbody>$rowsFrontend</tbody>
                </table>

                <div class='footer'>Generated automatically by Artisan Command on $date</div>
            </body>
        </html>";
    }
}
