<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('http://localhost:8001/admin/database/users');
app()->instance('request', $request);

$paginator = Illuminate\Support\Facades\DB::table('users')->paginate(10);
echo "Path: " . $paginator->path() . "\n";
echo "URL 2: " . $paginator->url(2) . "\n";
