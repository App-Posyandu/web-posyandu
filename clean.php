<?php
$files = [
    "resources/views/admin/posyandu/create.blade.php",
    "resources/views/admin/posyandu/index.blade.php",
    "resources/views/admin/users/create.blade.php",
    "resources/views/ajuan/cetak.blade.php",
    "resources/views/ajuan/cetak_dokumen.blade.php",
    "resources/views/ajuan/cetak_ringkasan.blade.php",
    "resources/views/ajuan/table.blade.php",
    "resources/views/dashboard/partials/admin.blade.php",
    "resources/views/dashboard/partials/masyarakat.blade.php",
    "resources/views/dashboard/partials/table.blade.php",
    "resources/views/layouts/partials/header-new.blade.php",
    "resources/views/livewire/ajuan-index.blade.php",
    "resources/views/livewire/user-index.blade.php",
    "resources/views/profile/partials/update-profile-information-form.blade.php"
];

foreach ($files as $f) {
    if (!file_exists($f)) continue;
    $c = file_get_contents($f);
    
    // Remove Str::title(strtolower(...))
    $c = preg_replace("/\\\\\\\\Illuminate\\\\\\\\Support\\\\\\\\Str::title\(strtolower\((.+?)\)\)/", "$1", $c);
    
    // Also handle \Illuminate\Support\Str::title
    $c = preg_replace("/\\\\Illuminate\\\\Support\\\\Str::title\(strtolower\((.+?)\)\)/", "$1", $c);
    
    // Remove capitalize classes
    $c = str_replace(" class=\"capitalize\"", "", $c);
    $c = str_replace(" capitalize\"", "\"", $c);
    $c = str_replace(" capitalize", "", $c);
    
    file_put_contents($f, $c);
    echo "Cleaned $f\n";
}

