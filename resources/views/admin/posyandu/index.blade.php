@extends('dashboard.layouts.dashboard')
@section('title', 'Manajemen Posyandu')
@section('content')
<div class="w-full mx-auto sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl p-4 sm:p-6 lg:p-8">
            @if (session('created_kaders'))
                <div
                    class="mb-6 bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-300 rounded-xl shadow-lg p-6">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                        </div>

                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-green-800 mb-3">
                                Posyandu & 6 Akun Kader Berhasil Dibuat!
                            </h3>

                            <div class="bg-white rounded-lg p-4 mb-4">
                                <p class="text-sm text-gray-700 mb-3">
                                    <strong>PENTING:</strong> Salin dan simpan credentials di bawah ini.
                                    Informasi ini hanya ditampilkan sekali!
                                </p>

                                <div class="space-y-3">
                                    @foreach (session('created_kaders') as $index => $kaderInfo)
                                        <div class="border-l-4 border-blue-500 bg-blue-50 p-3 rounded">
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1">
                                                    <p class="font-semibold text-gray-800">
                                                        {{ $index + 1 }}. Kader {{ $kaderInfo['bidang'] }}
                                                    </p>
                                                    <div class="mt-2 space-y-1 text-sm">
                                                        <div class="flex items-center gap-2">
                                                            <span class="text-gray-600 w-32">Email:</span>
                                                            <code class="bg-white px-2 py-1 rounded border text-gray-800">
                                                                {{ $kaderInfo['email'] }}
                                                            </code>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <span class="text-gray-600 w-32">Password:</span>
                                                            <code
                                                                class="bg-white px-2 py-1 rounded border text-red-600 font-bold">
                                                                {{ $kaderInfo['password'] }}
                                                            </code>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Copy Button --}}
                                                <button onclick="copyKaderCredentials({{ $index }})"
                                                    class="ml-4 px-3 py-2 bg-blue-500 text-white text-xs rounded hover:bg-blue-600 transition">
                                                    Copy
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="bg-amber-50 border border-amber-300 rounded-lg p-4">
                                <p class="text-sm text-amber-800">
                                    <strong>Catatan:</strong>
                                </p>
                                <ul class="list-disc list-inside text-sm text-amber-700 mt-2 space-y-1">
                                    <li>Semua kader sudah <strong>terverifikasi</strong> dan <strong>aktif</strong></li>
                                    <li>Password default: <code class="bg-white px-2 py-1 rounded">password123</code></li>
                                    <li>Kader dapat login menggunakan <strong>email</strong> atau <strong>nomor
                                            telepon</strong></li>
                                    <li>Ketua Posyandu dapat <strong>melengkapi data</strong> kader (NIK, tanggal lahir,
                                        dll)
                                    </li>
                                    <li>Ketua Posyandu dapat <strong>reset password</strong> kader jika diperlukan</li>
                                </ul>
                            </div>

                            {{-- Action Buttons --}}
                            <div class="mt-4 flex gap-3">
                                <a href="{{ route('admin.posyandu.print-credentials', session('posyandu_id')) }}"
                                    target="_blank"
                                    class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                                        </path>
                                    </svg>
                                    Print Credentials (PDF)
                                </a>

                                <button onclick="downloadKaderCredentials()"
                                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                        </path>
                                    </svg>
                                    Download as TXT
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <div class="text-gray-900">

                <div class="px-4 sm:px-0">
                    @include('components.all-notifications')
                </div>

                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                    <!-- Kiri: Judul & Info (50%) -->
                    <div class="w-full md:w-1/2 flex flex-col md:flex-row items-start md:items-center gap-4">
                        <h2 class="text-2xl font-bold text-gray-800 whitespace-nowrap">List Posyandu</h2>
                        @if (auth()->user()->role === 'admin-kabupaten')
                            <div class="text-sm text-gray-500">
                                <i class="bi bi-eye mr-1"></i>
                                <strong>{{ auth()->user()->kabupaten ? auth()->user()->kabupaten : '-' }}</strong>
                            </div>
                        @elseif (auth()->user()->role === 'ketua-posyandu')
                            <div class="text-sm text-gray-500">
                                <i class="bi bi-info-circle mr-1"></i>
                                <strong>{{ auth()->user()->posyandu->nama_posyandu ?? '-' }}</strong>
                            </div>
                        @endif
                    </div>

                    <!-- Kanan: Filter & Search (50%) -->
                    <div class="w-full md:w-1/2 flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-2">
                        @if (in_array(auth()->user()->role, ['admin', 'operator-desa']))
                            <a href="{{ route('admin.posyandu.create') }}"
                                class="whitespace-nowrap w-full sm:w-auto text-center px-4 py-2 bg-pink-500 text-white rounded-md text-sm font-semibold hover:bg-pink-600 transition">
                                Tambah
                            </a>
                        @endif

                        <form method="GET" action="{{ route('admin.posyandu.index') }}" class="flex-1 flex flex-col sm:flex-row gap-2">
                            <div class="w-full sm:w-auto">
                                <select name="perPage" onchange="this.form.submit()" class="w-20 min-w-[105px] border-gray-300 rounded-md shadow-sm text-sm focus:ring-pink-500 focus:border-pink-500">
                                    <option value="10" {{ request('perPage') == 10 ? 'selected' : '' }}>10 Baris</option>
                                    <option value="25" {{ request('perPage') == 25 ? 'selected' : '' }}>25 Baris</option>
                                    <option value="50" {{ request('perPage') == 50 ? 'selected' : '' }}>50 Baris</option>
                                    <option value="100" {{ request('perPage') == 100 ? 'selected' : '' }}>100 Baris</option>
                                </select>
                            </div>
                            
                            <div class="flex-1 relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <i class="bi bi-search text-gray-400"></i>
                                </div>
                                <input type="text" name="search" value="{{ request('search') }}"
                                    placeholder="Cari..."
                                    class="block w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md shadow-sm focus:border-pink-500 focus:ring-pink-500 text-sm">
                            </div>
                            
                            <button type="submit"
                                class="w-full sm:w-auto px-4 py-2 bg-pink-500 text-white rounded-md text-sm font-semibold hover:bg-pink-600 transition">
                                Cari
                            </button>
                            @if (request('search'))
                                <a href="{{ route('admin.posyandu.index') }}"
                                    class="w-full sm:w-auto text-center px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-sm font-semibold hover:bg-gray-300 transition">
                                    Reset
                                </a>
                            @endif
                        </form>
                    </div>
                </div>    
                
                @if (request('search'))
                    <p class="mt-2 text-sm text-gray-500">
                        Menampilkan hasil pencarian untuk: <strong>"{{ request('search') }}"</strong>
                    </p>
                @endif

                @if (auth()->user()->role === 'operator-desa')
                    <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                        <div class="flex items-start">
                            <i class="bi bi-info-circle-fill text-blue-500 mr-3 mt-0.5"></i>
                            <div class="text-sm text-blue-700">
                                <p class="font-semibold mb-1">Informasi untuk Operator Desa</p>
                                <ul class="list-disc list-outside space-y-1 ml-4">
                                    <li>Anda hanya dapat melihat dan mengelola <strong>1 posyandu</strong> yang ditugaskan
                                        kepada Anda</li>
                                    <li>Anda bertanggung jawab untuk setup <strong>RW/RT</strong> yang dilayani posyandu
                                    </li>
                                    <li>Setelah setup RW/RT, user dapat memilih RW/RT saat registrasi</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                @elseif (auth()->user()->role === 'admin-kabupaten')
                    <div class="mb-6 bg-purple-50 border-l-4 border-purple-500 p-4 rounded">
                        <div class="flex items-start">
                            <i class="bi bi-info-circle-fill text-purple-500 mr-3 mt-0.5"></i>
                            <div class="text-sm text-purple-700">
                                <p class="font-semibold mb-1">Informasi untuk Admin Kabupaten</p>
                                <ul class="list-disc list-outside space-y-1 ml-4">
                                    <li>Anda dapat <strong>melihat</strong> semua posyandu yang ada di kabupaten Anda</li>
                                    <li>Untuk <strong>mengelola</strong> posyandu (edit/hapus), silakan hubungi Operator
                                        Desa terkait</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="hidden md:block w-full max-w-full overflow-x-auto">
                    <table class="min-w-full whitespace-nowrap divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    No
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nama Posyandu
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Ketua Posyandu
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Lokasi
                                </th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Jumlah RW
                                </th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Jumlah RT
                                </th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($posyandus as $index => $posyandu)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $posyandus->firstItem() + $index }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $posyandu->nama_posyandu }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $ketua = $posyandu->users->where('role', 'ketua-posyandu')->first();
                                        @endphp
                                        @if ($ketua)
                                            <div class="text-sm text-gray-900 font-medium">{{ $ketua->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $ketua->email }}</div>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Belum ada ketua</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">{{ $posyandu->desa ? $posyandu->desa : '' }}</div>
                                        <div class="text-xs text-gray-500">{{ $posyandu->kecamatan ? $posyandu->kecamatan : '' }},
                                            {{ $posyandu->kabupaten ? $posyandu->kabupaten : '' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-purple-100 text-purple-800">
                                            {{ count($posyandu->rw_list ?? []) }} RW
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @php
                                            $totalRt = 0;
                                            if (is_array($posyandu->rt_mapping)) {
                                                foreach ($posyandu->rt_mapping as $rtList) {
                                                    if (is_array($rtList)) {
                                                        $totalRt += count($rtList);
                                                    }
                                                }
                                            }
                                        @endphp
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">
                                            {{ $totalRt }} RT
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                        <div class="flex items-center justify-center gap-2 flex-wrap">
                                            @php
                                                $ketuaPosyandu = $posyandu->users->firstWhere('role', 'ketua-posyandu');
                                                $operatorPosyandu = \App\Models\User::where('role', 'operator-desa')
                                                    ->where('kabupaten', $posyandu->kabupaten)
                                                    ->where('kecamatan', $posyandu->kecamatan)
                                                    ->where('desa', $posyandu->desa)
                                                    ->first();
                                                $kaders = $posyandu->users->where('role', 'kader')->values();
                                                $posyanduData = [
                                                    'nama' => $posyandu->nama_posyandu,
                                                    'kabupaten' => $posyandu->kabupaten,
                                                    'kecamatan' => $posyandu->kecamatan,
                                                    'desa' => $posyandu->desa,
                                                    'rw_list' => $posyandu->rw_list ?? [],
                                                    'rt_mapping' => $posyandu->rt_mapping ?? [],
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
                                                        ->map(
                                                            fn($k) => [
                                                                'name' => $k->name,
                                                                'email' => $k->email,
                                                                'bidang' => $k->bidang->nama_bidang ?? '-',
                                                                'password' => $k->default_password ?? 'password123',
                                                            ],
                                                        )
                                                        ->toArray(),
                                                ];
                                            @endphp
                                            <button type="button"
                                                onclick='showDetailPosyandu(@json($posyanduData))'
                                                class="px-3 py-2 bg-green-500 text-white text-xs font-semibold rounded hover:bg-green-600 transition inline-flex items-center gap-1">
                                                <i class="bi bi-eye"></i> Lihat
                                            </button>
                                                @if (in_array(auth()->user()->role, ['admin', 'operator-desa']))
                                                <a href="{{ route('admin.posyandu.edit', $posyandu) }}"
                                                    class="px-3 py-2 bg-blue-500 text-white text-xs font-semibold rounded hover:bg-blue-600 transition inline-flex items-center gap-1">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500 text-sm">
                                        Belum ada data posyandu.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="block md:hidden space-y-4 mt-4">
                    @forelse($posyandus as $index => $posyandu)
                        <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                            <div class="bg-gradient-to-r from-pink-50 to-purple-50 px-4 py-3 border-b border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <div class="h-10 w-10 rounded-full bg-pink-100 flex items-center justify-center text-pink-600 font-bold text-lg">
                                            {{ strtoupper(substr($posyandu->nama_posyandu, 0, 1)) }}
                                        </div>
                                        <div>
                                            <h3 class="text-sm font-semibold text-gray-900">{{ $posyandu->nama_posyandu }}</h3>
                                            <p class="text-xs text-gray-500">
                                                <i class="bi bi-geo-alt-fill text-gray-400 mr-1"></i>
                                                {{ $posyandu->desa ? $posyandu->desa : '' }}
                                            </p>
                                        </div>
                                    </div>
                                    <span class="text-xs font-medium text-gray-500">
                                        #{{ $posyandus->firstItem() + $index }}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="px-4 py-3 space-y-3">
                                @php
                                    $ketua = $posyandu->users->where('role', 'ketua-posyandu')->first();
                                    $totalRt = 0;
                                    if (is_array($posyandu->rt_mapping)) {
                                        foreach ($posyandu->rt_mapping as $rtList) {
                                            if (is_array($rtList)) {
                                                $totalRt += count($rtList);
                                            }
                                        }
                                    }
                                    $ketuaPosyandu = $ketua;
                                    $operatorPosyandu = \App\Models\User::where('role', 'operator-desa')
                                        ->where('kabupaten', $posyandu->kabupaten)
                                        ->where('kecamatan', $posyandu->kecamatan)
                                        ->where('desa', $posyandu->desa)
                                        ->first();
                                    $kaders = $posyandu->users->where('role', 'kader')->values();
                                    $posyanduData = [
                                        'nama' => $posyandu->nama_posyandu,
                                        'kabupaten' => $posyandu->kabupaten,
                                        'kecamatan' => $posyandu->kecamatan,
                                        'desa' => $posyandu->desa,
                                        'rw_list' => $posyandu->rw_list ?? [],
                                        'rt_mapping' => $posyandu->rt_mapping ?? [],
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
                                            ->map(
                                                fn($k) => [
                                                    'name' => $k->name,
                                                    'email' => $k->email,
                                                    'bidang' => $k->bidang->nama_bidang ?? '-',
                                                    'password' => $k->default_password ?? 'password123',
                                                ],
                                            )
                                            ->toArray(),
                                    ];
                                @endphp
                                
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 w-24 text-xs font-medium text-gray-500">Ketua</div>
                                    <div class="flex-1">
                                        @if ($ketua)
                                            <p class="text-sm font-medium text-gray-900">{{ $ketua->name }}</p>
                                            <p class="text-xs text-gray-500">{{ $ketua->email }}</p>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Belum ada ketua</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 w-24 text-xs font-medium text-gray-500">Wilayah</div>
                                    <div class="flex-1">
                                        <p class="text-sm text-gray-900">
                                            {{ count($posyandu->rw_list ?? []) }} RW / {{ $totalRt }} RT
                                        </p>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            {{ $posyandu->kecamatan ? $posyandu->kecamatan : '' }}, {{ $posyandu->kabupaten ? $posyandu->kabupaten : '' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex flex-col sm:flex-row gap-2 mt-4 pt-4 border-t border-gray-200 bg-gray-50 px-4 sm:px-6 pb-4">
                                <button type="button"
                                    onclick='showDetailPosyandu(@json($posyanduData))'
                                    class="w-full sm:w-auto px-3 py-2 bg-green-500 text-white rounded-md text-sm font-medium text-center hover:bg-green-600 transition">
                                    <i class="bi bi-eye mr-1"></i> Lihat Detail
                                </button>

                                @if (in_array(auth()->user()->role, ['admin', 'operator-desa']))
                                    <a href="{{ route('admin.posyandu.edit', $posyandu) }}"
                                        class="w-full sm:w-auto px-3 py-2 bg-blue-500 text-white rounded-md text-sm font-medium text-center hover:bg-blue-600 transition">
                                        <i class="bi bi-pencil mr-1"></i> Edit
                                    </a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="bg-white rounded-lg shadow-md p-8 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <i class="bi bi-building text-5xl text-gray-300 mb-4"></i>
                                <p class="text-gray-500 text-base font-medium">Belum ada data posyandu</p>
                            </div>
                        </div>
                    @endforelse
                </div>

                <div class="mt-4">
                    {{ $posyandus->links('vendor.pagination.tailwind') }}
                </div>
            </div>

    {{-- Modal Detail RW/RT --}}
    <div x-data="{
        show: false,
        data: { nama: '', desa: '', rw_list: [], rt_mapping: {} }
    }" x-show="show" @open-detail.window="show = true; data = $event.detail"
        class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">

        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="show = false"></div>

        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div
                class="relative bg-white rounded-xl shadow-2xl max-w-2xl w-full p-6 overflow-hidden transition-all transform">
                {{-- Header --}}
                <div class="flex justify-between items-center border-b pb-3 mb-4">
                    <h3 class="text-xl font-bold text-gray-800">
                        Detail Wilayah: <span x-text="data.nama" class="text-pink-600"></span>
                    </h3>
                    <button @click="show = false" class="text-gray-400 hover:text-gray-600">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                {{-- Content --}}
                <div class="max-h-[60vh] overflow-y-auto pr-2">
                    <p class="text-sm text-gray-600 mb-4 italic">
                        Daftar RW dan RT yang terdaftar di Desa <span x-text="data.desa"
                            class="font-semibold text-gray-800"></span>
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <template x-for="rw in data.rw_list" :key="rw">
                            <div class="border rounded-lg bg-gray-50 p-3">
                                <div class="flex items-center gap-2 border-b border-gray-200 pb-2 mb-2">
                                    <span class="bg-pink-100 text-pink-700 px-2 py-0.5 rounded text-xs font-bold"
                                        x-text="rw"></span>
                                    <span class="text-xs text-gray-500 font-medium uppercase tracking-wider">Rukun
                                        Warga</span>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <template x-for="rt in (data.rt_mapping[rw] || [])" :key="rt">
                                        <span
                                            class="bg-white border border-gray-300 text-gray-700 px-2 py-1 rounded-md text-xs shadow-sm"
                                            x-text="rt"></span>
                                    </template>
                                    <template x-if="!(data.rt_mapping[rw] || []).length">
                                        <span class="text-xs text-gray-400 italic">Tidak ada RT terdaftar</span>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    <template x-if="!data.rw_list.length">
                        <div class="text-center py-10">
                            <i class="bi bi-exclamation-triangle text-amber-500 text-4xl mb-3"></i>
                            <p class="text-gray-500">Belum ada data RW/RT untuk posyandu ini.</p>
                        </div>
                    </template>
                </div>

                {{-- Footer --}}
                <div class="mt-6 flex justify-end">
                    <button @click="show = false"
                        class="px-5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
    @push('scripts')
        <script>
            function showDetailPosyandu(data) {
                const {
                    nama,
                    kabupaten,
                    kecamatan,
                    desa,
                    rw_list: rwList,
                    rt_mapping: rtMapping,
                    ketua,
                    operator,
                    kaders
                } = data;

                let contentHtml = `<div class="text-left mt-4" style="max-height: 500px; overflow-y: auto; padding: 5px;">

                    <!-- Lokasi -->
                    <div class="mb-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                        <h4 class="font-semibold text-blue-800 mb-2 flex items-center gap-2">
                            <i class="bi bi-geo-alt-fill"></i> Lokasi
                        </h4>
                        <div class="grid grid-cols-3 gap-2 text-sm">
                            <div>
                                <span class="text-gray-500">Kabupaten:</span>
                                <p class="font-medium text-gray-800">${kabupaten || '-'}</p>
                            </div>
                            <div>
                                <span class="text-gray-500">Kecamatan:</span>
                                <p class="font-medium text-gray-800">${kecamatan || '-'}</p>
                            </div>
                            <div>
                                <span class="text-gray-500">Desa:</span>
                                <p class="font-medium text-gray-800">${desa || '-'}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4 p-3 bg-purple-50 rounded-lg border border-purple-200">
                        <h4 class="font-semibold text-purple-800 mb-3 flex items-center gap-2">
                            <i class="bi bi-people-fill"></i> Struktur Posyandu
                        </h4>
                        <div class="mb-3">
                            <span class="inline-block px-2 py-0.5 bg-pink-500 text-white text-xs rounded font-medium mb-1">Ketua Posyandu</span>
                            ${ketua ? `
                                        <div class="ml-2 text-sm border bg-white p-2 rounded">
                                            <p class="font-medium text-gray-800">${ketua.name}</p>
                                            <div class="flex items-center gap-2 mt-1 mb-1 p-1 bg-gray-50 rounded border border-gray-200 w-full">
                                                <div class="flex-1 min-w-0">
                                                    <span class="text-xs text-gray-700 font-medium">Email:</span>
                                                    <code class="ml-1 text-xs text-gray-800">${ketua.email || '-'}</code>
                                                </div>
                                                ${ketua.email ? `<button type="button" onclick="copyText('${ketua.email}', 'Email')" class="flex-shrink-0 px-2 py-1 bg-gray-200 text-gray-700 hover:bg-gray-300 text-xs rounded transition flex items-center gap-1"><i class="bi bi-clipboard"></i> Copy</button>` : ''}
                                            </div>
                                            <div class="flex items-center gap-2 p-1 bg-amber-50 rounded border border-amber-200 w-full">
                                                <div class="flex-1 min-w-0">
                                                    <span class="text-xs text-amber-700 font-medium">Password:</span>
                                                    <code class="ml-1 text-xs text-red-600 font-bold">${ketua.password}</code>
                                                </div>
                                                <button type="button" onclick="copyText('${ketua.password}', 'Password')"
                                                    class="flex-shrink-0 px-2 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600 transition flex items-center gap-1">
                                                    <i class="bi bi-clipboard"></i> Copy
                                                </button>
                                            </div>
                                        </div>
                                    ` : '<p class="ml-2 text-sm text-gray-400 italic">Belum ada</p>'}
                        </div>

                        <!-- Operator Desa -->
                        <div class="mb-3">
                            <span class="inline-block px-2 py-0.5 bg-indigo-500 text-white text-xs rounded font-medium mb-1">Operator Desa</span>
                            ${operator ? `
                                        <div class="ml-2 text-sm border bg-white p-2 rounded">
                                            <p class="font-medium text-gray-800">${operator.name}</p>
                                            <div class="flex items-center gap-2 mt-1 mb-1 p-1 bg-gray-50 rounded border border-gray-200 w-full">
                                                <div class="flex-1 min-w-0">
                                                    <span class="text-xs text-gray-700 font-medium">Email:</span>
                                                    <code class="ml-1 text-xs text-gray-800">${operator.email || '-'}</code>
                                                </div>
                                                ${operator.email ? `<button type="button" onclick="copyText('${operator.email}', 'Email')" class="flex-shrink-0 px-2 py-1 bg-gray-200 text-gray-700 hover:bg-gray-300 text-xs rounded transition flex items-center gap-1"><i class="bi bi-clipboard"></i> Copy</button>` : ''}
                                            </div>
                                            <div class="flex items-center gap-2 p-1 bg-amber-50 rounded border border-amber-200 w-full">
                                                <div class="flex-1 min-w-0">
                                                    <span class="text-xs text-amber-700 font-medium">Password:</span>
                                                    <code class="ml-1 text-xs text-red-600 font-bold">${operator.password}</code>
                                                </div>
                                                <button type="button" onclick="copyText('${operator.password}', 'Password')"
                                                    class="flex-shrink-0 px-2 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600 transition flex items-center gap-1">
                                                    <i class="bi bi-clipboard"></i> Copy
                                                </button>
                                            </div>
                                        </div>
                                    ` : '<p class="ml-2 text-sm text-gray-400 italic">Belum ada</p>'}
                        </div>

                        <!-- Kader -->
                        <div>
                            <span class="inline-block px-2 py-0.5 bg-green-500 text-white text-xs rounded font-medium mb-2">Kader (${kaders.length})</span>
                            ${kaders.length > 0 ? `
                                        <div class="ml-2 grid grid-cols-2 gap-2">
                                            ${kaders.map((k, idx) => `
                                        <div class="text-sm p-2 bg-white rounded border">
                                            <p class="text-[10px] font-bold text-indigo-600 uppercase tracking-wide mb-0.5">Kader ${k.bidang}</p>
                                            <p class="font-medium text-gray-800">${k.name}</p>
                                            <div class="flex items-center gap-2 mt-1 mb-1 p-1 bg-gray-50 rounded border border-gray-200 w-full">
                                                <div class="flex-1 min-w-0 overflow-hidden">
                                                    <span class="text-xs text-gray-700 font-medium hidden">Email:</span>
                                                    <code class="text-xs text-gray-800 truncate block">${k.email || '-'}</code>
                                                </div>
                                                ${k.email ? `<button type="button" onclick="copyText('${k.email}', 'Email')" class="flex-shrink-0 px-1.5 py-1 bg-gray-200 text-gray-700 hover:bg-gray-300 text-[10px] rounded transition"><i class="bi bi-clipboard"></i></button>` : ''}
                                            </div>
                                            <div class="flex items-center gap-2 p-1 bg-amber-50 rounded border border-amber-200 w-full">
                                                <div class="flex-1 min-w-0 overflow-hidden">
                                                    <span class="text-xs text-amber-700 font-medium hidden">Password:</span>
                                                    <code class="text-xs text-red-600 font-bold block">${k.password}</code>
                                                </div>
                                                <button type="button" onclick="copyText('${k.password}', 'Password')"
                                                    class="flex-shrink-0 px-1.5 py-1 bg-blue-500 text-white text-[10px] rounded hover:bg-blue-600 transition">
                                                    <i class="bi bi-clipboard"></i>
                                                </button>
                                            </div>
                                        </div>
                                    `).join('')}
                                        </div>
                                    ` : '<p class="ml-2 text-sm text-gray-400 italic">Belum ada kader</p>'}
                        </div>
                    </div>

                    <!-- Wilayah RW/RT -->
                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                        <h4 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                            <i class="bi bi-house-door-fill"></i> Wilayah Pelayanan
                        </h4>
                        <div class="grid grid-cols-1 gap-2">`;

                if (rwList && rwList.length > 0) {
                    rwList.forEach(rw => {
                        const rts = rtMapping[rw] || [];
                        contentHtml += `
                            <div class="border rounded-lg p-2 bg-white border-gray-200">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="bg-pink-500 text-white px-2 py-0.5 rounded text-xs font-bold">${rw}</span>
                                    <span class="text-xs text-gray-500">→</span>
                                    <div class="flex flex-wrap gap-1">
                                        ${rts.map(rt => `<span class="bg-gray-100 text-gray-700 px-1.5 py-0.5 rounded text-xs">${rt}</span>`).join('')}
                                        ${rts.length === 0 ? '<span class="text-xs text-gray-400 italic">Tidak ada RT</span>' : ''}
                                    </div>
                                </div>
                            </div>`;
                    });
                } else {
                    contentHtml += `<div class="text-center py-4 text-gray-500 text-sm">Belum ada data RW/RT terdaftar.</div>`;
                }

                contentHtml += `</div></div></div>`;

                Swal.fire({
                    title: `<span class="text-xl font-bold text-gray-800">${nama}</span>`,
                    html: contentHtml,
                    width: '700px',
                    confirmButtonText: 'Tutup',
                    confirmButtonColor: '#ec4899',
                    customClass: {
                        popup: 'rounded-2xl shadow-xl',
                        title: 'border-b pb-4'
                    },
                    showCloseButton: true
                });
            }
        </script>
        <script>
            function copyText(text, label) {
                if (!text) return;
                navigator.clipboard.writeText(text).then(() => {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: label + ' berhasil disalin!',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });
                }).catch(() => {
                    const textarea = document.createElement('textarea');
                    textarea.value = text;
                    document.body.appendChild(textarea);
                    textarea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textarea);
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: label + ' berhasil disalin!',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });
                });
            }
        </script>
        <script>
            function confirmDeletePosyandu(event, formId) {
                event.preventDefault();
                console.log('Delete function called with form ID:', formId);
                Swal.fire({
                    title: 'Hapus Posyandu?',
                    text: 'Yakin ingin menghapus posyandu ini?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280'
                }).then((result) => {
                    if (result.isConfirmed) {
                        console.log('Submitting form with ID:', formId);
                        document.getElementById(formId).submit();
                    }
                });
                return false;
            }
        </script>
        <script>
            function downloadKaderCredentials() {
                const kaders = @json(session('created_kaders'));

                if (!kaders || kaders.length === 0) {
                    alert('Data credentials tidak tersedia');
                    return;
                }

                let content = '==============================================\n';
                content += '   CREDENTIALS KADER AUTO-GENERATED\n';
                content += '==============================================\n\n';
                content += 'Tanggal: ' + new Date().toLocaleString('id-ID') + '\n';
                content += '----------------------------------------------\n\n';

                kaders.forEach((kader, i) => {
                    content += (i + 1) + '. Kader ' + kader.bidang + '\n';
                    content += '   Email    : ' + kader.email + '\n';
                    content += '   Password : ' + kader.password + '\n';
                    content += '----------------------------------------------\n';
                });

                content += '\n';
                content += 'PENTING:\n';
                content += '- Simpan file ini dengan aman!\n';
                content += '- Password default: password123\n';
                content += '- Kader dapat login menggunakan email atau nomor telepon\n';
                content += '- Ketua Posyandu wajib meminta kader untuk mengganti password\n';
                content += '\n';
                content += '==============================================\n';

                const blob = new Blob([content], {
                    type: 'text/plain;charset=utf-8'
                });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'kader_credentials_' + Date.now() + '.txt';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);
            }
        </script>
    @endpush
    </div>
</div>
@endsection
