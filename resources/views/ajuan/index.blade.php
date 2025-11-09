@extends('dashboard.layouts.dashboard')
@section('title', 'List Pengajuan')
@section('content')
    <div class="w-full max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl p-8">
            @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-6" role="alert">
                    <div class="flex">
                        <div class="py-1">
                            <i class="bi bi-check-circle-fill mr-3"></i>
                        </div>
                        <div>
                            <p class="font-bold">Berhasil</p>
                            <p class="text-sm">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif
            <div class="flex md:flex-row justify-between items-center mb-6 gap-4">
                {{-- ✅ TAMPILKAN BIDANG JIKA USER ADALAH KADER --}}
                @if (auth()->user()->role === 'kader' && auth()->user()->bidang)
                    <h2 class="text-2xl font-bold text-gray-800">
                        List Pengajuan
                        <span class="text-pink-600">{{ auth()->user()->bidang->nama_bidang }}</span>
                    </h2>
                @else
                    <h2 class="text-2xl font-bold text-gray-800">List Pengajuan</h2>
                @endif

                <div class="flex items-center gap-2">
                    @if (auth()->user()->role === 'kader')
                        <a href="{{ route('dashboard.partials.pilih-user') }}"
                            class="px-4 py-2 bg-pink-500 text-white rounded-md text-sm font-semibold hover:bg-pink-600">
                            <i class="bi bi-plus-circle-fill mr-2"></i>Tambah Ajuan
                        </a>
                    @endif
                    {{-- Form untuk filter dan search --}}
                    <form action="{{ route('ajuan.index') }}" method="GET"
                        class="flex flex-col md:flex-row items-center gap-2 w-full md:w-auto">

                        {{-- Filter berdasarkan Status --}}
                        <select name="status" onchange="this.form.submit()"
                            class="border-gray-300 rounded-md shadow-sm text-sm w-full md:w-auto">
                            <option value="">Semua Status</option>
                            <option value="Diproses" @selected(request('status') == 'Diproses')>Diproses</option>
                            <option value="Disetujui" @selected(request('status') == 'Disetujui')>Disetujui</option>
                            <option value="Ditolak" @selected(request('status') == 'Ditolak')>Ditolak</option>
                        </select>

                        {{-- Search Input dengan Tombol Submit Terintegrasi --}}
                        <div class="relative w-full md:w-auto">
                            <input type="text" name="search" placeholder="Cari berdasarkan nama..."
                                value="{{ request('search') }}"
                                class="w-full md:w-64 pl-4 pr-10 py-2 border border-gray-300 rounded-md text-sm focus:ring-pink-500 focus:border-pink-500">
                            <button type="submit" class="absolute inset-y-0 right-0 flex items-center pr-3">
                                <i class="bi bi-search text-gray-400"></i>
                            </button>
                        </div>

                        {{-- Link untuk Reset/Clear Filter --}}
                        @if (request('search') || request('status'))
                            <a href="{{ route('ajuan.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Reset</a>
                        @endif
                    </form>
                </div>
            </div>

            {{-- ✅ TAMPILKAN INFO BADGE JIKA KADER --}}
            @if (auth()->user()->role === 'kader' && auth()->user()->bidang)
                <div class="mb-4 p-3 bg-pink-50 border-l-4 border-pink-500 rounded-lg">
                    <div class="flex items-center">
                        <i class="bi bi-info-circle-fill text-pink-500 mr-2"></i>
                        <p class="text-sm text-gray-700">
                            Anda mengelola pengajuan di <strong>{{ auth()->user()->bidang->nama_bidang }}</strong>
                            untuk <strong>{{ auth()->user()->posyandu->nama_posyandu ?? 'Posyandu Anda' }}</strong>
                        </p>
                    </div>
                </div>
            @endif

            @include('ajuan.table', ['semuaAjuan' => $semuaAjuan])

            <div class="mt-4 flex justify-between items-center text-sm text-gray-600">
                {{ $semuaAjuan->links() }}
            </div>

        </div>
    </div>
@endsection
