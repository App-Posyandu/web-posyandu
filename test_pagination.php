<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$paginator = Illuminate\Support\Facades\DB::table('users')->paginate(10);
$html = view('vendor.pagination.tailwind', compact('paginator'))->render();

file_put_contents('test_pagination.html', $html);
