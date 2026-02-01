@extends('layouts.app')

@section('content')
<div class="p-6 bg-white shadow rounded-md max-w-2xl">

    <h2 class="text-xl font-bold mb-6">Import Pengguna</h2>

    @php
        $currentUser = Auth::user();
        $roleTargets = [
            'kader' => ['masyarakat'],
            'ketua-posyandu' => ['kader'],
            'operator-desa' => ['ketua-posyandu', 'kader'],
            'admin-kecamatan' => [],
            'admin-kabupaten' => ['ketua-timpembina-posyandu', 'kabid', 'admin-kecamatan', 'kades', 'bu-kades', 'operator-desa'],
            'admin' => ['admin-kabupaten', 'ketua-timpembina-posyandu', 'kabid', 'admin-kecamatan', 'kades', 'bu-kades', 'ketua-posyandu', 'operator-desa', 'kader', 'masyarakat']
        ];
        $roleLabels = [
            'masyarakat' => 'Masyarakat',
            'kader' => 'Kader',
            'ketua-posyandu' => 'Ketua Posyandu',
            'operator-desa' => 'Operator Desa',
            'admin-kecamatan' => 'Admin Kecamatan',
            'kabid' => 'Kabid',
            'admin-kabupaten' => 'Admin Kabupaten',
            'ketua-timpembina-posyandu' => 'Ketua Tim Pembina Posyandu',
            'kades' => 'Kades'
        ];
        $allowedRoles = $roleTargets[$currentUser->role] ?? [];
        $requestedRole = request('role');
        $roleToCreateKey = in_array($requestedRole, $allowedRoles, true)
            ? $requestedRole
            : ($allowedRoles[0] ?? 'user');
        $roleToCreate = $roleLabels[$roleToCreateKey] ?? 'User';
    @endphp
    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-md">
        <h3 class="font-semibold text-blue-900 mb-3">Informasi Import</h3>
        <ul class="text-sm text-blue-800 space-y-2">
            <li><strong>Role Anda:</strong> {{ ucfirst(str_replace('-', ' ', $currentUser->role)) }}</li>
            <li><strong>Akan membuat user:</strong> <span class="font-semibold text-blue-600">{{ $roleToCreate }}</span></li>
            @if($currentUser->role === 'ketua-posyandu')
                <li><strong>Posyandu:</strong> {{ $currentUser->posyandu->nama_posyandu }}</li>
            @elseif($currentUser->role === 'operator-desa')
                <li><strong>Desa:</strong> {{ $currentUser->posyandu->desa }}</li>
            @endif
            <li><strong>Catatan:</strong> Template Excel harus sesuai dengan format yang disediakan.</li>
        </ul>
    </div>

    @if(session('success'))
        <div class="p-4 mb-4 bg-green-100 text-green-800 rounded border border-green-300">
            {{ session('success') }}
            @if(session('imported') > 0)
                <br><strong>Total berhasil diimport: {{ session('imported') }} user</strong>
            @endif
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 mb-4 bg-red-100 text-red-800 rounded border border-red-300">
            <strong>Error:</strong>
            <ul class="mt-2 list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.users.import.process') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="role" value="{{ $roleToCreateKey }}">
        
        <div class="mb-4">
            <label class="block font-semibold mb-2">Upload File Excel:</label>
            <input type="file" 
                   name="file" 
                   accept=".xlsx,.xls,.csv" 
                   class="border p-3 rounded w-full @error('file') border-red-500 @enderror"
                   required>
            @error('file')
                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded text-sm text-yellow-800">
            <strong>Tips:</strong>
            <ul class="list-disc list-inside mt-2">
                <li>Gunakan template yang telah diunduh dari tombol "Download Template"</li>
                <li>Isi nama dan nomor telepon pada kolom yang tersedia</li>
                <li>Data desa, kecamatan, dan kabupaten sudah terisi otomatis</li>
                <li>Jangan ubah header atau struktur template</li>
            </ul>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700">
                Import Data
            </button>

            <a href="{{ route('admin.users.export.template', ['role' => $roleToCreateKey]) }}"
               target="_blank"
               class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                Download Template [{{ $roleToCreate }}]
            </a>

            <a href="{{ route('admin.users.index') }}"
               class="px-4 py-2 bg-gray-300 rounded text-gray-800 hover:bg-gray-400">
                Kembali
            </a>
        </div>
    </form>

</div>
@endsection
