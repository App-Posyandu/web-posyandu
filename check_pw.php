<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Simulate exactly what the blade template does
$posyandu = \App\Models\Posyandu::with(['users.bidang'])->first();

if (!$posyandu) {
    echo "No posyandu found!\n";
    exit;
}

echo "=== Posyandu: {$posyandu->nama_posyandu} ===\n\n";

$ketuaPosyandu = $posyandu->users->firstWhere('role', 'ketua-posyandu');
$operatorPosyandu = \App\Models\User::where('role', 'operator-desa')
    ->where('kabupaten', $posyandu->kabupaten)
    ->where('kecamatan', $posyandu->kecamatan)
    ->where('desa', $posyandu->desa)
    ->first();
$kaders = $posyandu->users->where('role', 'kader')->values();

$posyanduData = [
    'nama' => $posyandu->nama_posyandu,
    'ketua' => $ketuaPosyandu
        ? [
            'name' => $ketuaPosyandu->name,
            'email' => $ketuaPosyandu->email,
            'password' => $ketuaPosyandu->default_password ?? 'password123',
        ]
        : null,
    'operator' => $operatorPosyandu
        ? [
            'name' => $operatorPosyandu->name,
            'email' => $operatorPosyandu->email,
            'password' => $operatorPosyandu->default_password ?? 'password123',
        ]
        : null,
    'kaders' => $kaders
        ->map(fn($k) => [
            'name' => $k->name,
            'email' => $k->email,
            'bidang' => $k->bidang->nama_bidang ?? '-',
            'password' => $k->default_password ?? 'password123',
        ])
        ->toArray(),
];

echo "JSON that gets sent to showDetailPosyandu():\n";
echo json_encode($posyanduData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

// Now simulate a password reset
echo "\n=== Simulating password reset for ketua ===\n";
if ($ketuaPosyandu) {
    $newPassword = 'newTestPw_' . time();
    $ketuaPosyandu->update([
        'password' => \Illuminate\Support\Facades\Hash::make($newPassword),
        'default_password' => $newPassword,
    ]);
    
    // Reload and check
    $ketuaPosyandu->refresh();
    echo "After reset - default_password in DB: {$ketuaPosyandu->default_password}\n";
    echo "After reset - password hash starts with: " . substr($ketuaPosyandu->password, 0, 20) . "...\n";
    
    // Simulate blade again
    $posyanduData['ketua']['password'] = $ketuaPosyandu->default_password ?? 'password123';
    echo "After reset - JSON password field: {$posyanduData['ketua']['password']}\n";
}
