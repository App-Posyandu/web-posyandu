@extends('dashboard.layouts.dashboard')
@section('title', 'Formulir Pengajuan')
@section('content')
    <div class="w-full sm:max-w-3xl mt-6 px-6 py-8 bg-white shadow-md overflow-hidden sm:rounded-lg z-10">

        <form method="POST" action="{{ route('ajuan.store.permohonan') }}">
            @csrf
            @php
                $user = auth()->user();
                $isKader = $user->role === 'kader';
            @endphp
            @if ($isKader)
                <h2 class="text-2xl font-bold text-center text-gray-800 mb-8">Formulir Permohonan
                    <span class="text-pink-600">{{ $user->bidang->nama_bidang }}</span>
                </h2>
            @else
                <h2 class="text-2xl font-bold text-center text-gray-800 mb-8">Formulir Permohonan</h2>
            @endif
            <div class="mb-6">
                @if ($isKader)
                    {{-- Kader: Dropdown disembunyikan, ganti dengan teks --}}
                    {{-- <p class="mt-1 text-lg font-semibold text-gray-800">
                {{ $user->bidang->nama_bidang }}
            </p> --}}

                    {{-- Hidden input agar tetap terkirim --}}
                    <input type="hidden" name="bidang_pelayanan" value="{{ $user->bidang->slug }}">

                    {{-- <p class="text-xs text-gray-500 mt-1">Anda hanya dapat membuat pengajuan untuk bidang tugas Anda.</p> --}}
                @else
                    {{-- Admin/Masyarakat: Dropdown normal --}}
                    <select id="bidang_pelayanan" onchange="window.location.href=this.value;"
                        class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">

                        @foreach ($allBidangs as $itemBidang)
                            <option value="{{ route('ajuan.create', $itemBidang->slug) }}" @selected($bidang->slug == $itemBidang->slug)>
                                {{ $itemBidang->nama_bidang }}
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>


            <div class="space-y-4">
                @foreach ($items as $item)
                    @if ($item === 'Lainnya...')
                        <div x-data="{ checked: false }" class="p-4 border rounded-md">
                            <label class="flex items-center">
                                <input type="checkbox" x-model="checked" name="permohonan_items[]" value="Lainnya..."
                                    class="rounded border-gray-300 text-pink-600 shadow-sm focus:ring-pink-500">
                                <span
                                    class="ms-3 text-gray-700 text-base md:text-lg font-semibold">{{ $item }}</span>
                            </label>
                            <div x-show="checked" x-transition class="mt-2">
                                <x-text-input type="text" name="lainnya_text" class="block w-full"
                                    placeholder="Silakan ketik permohonan Anda..." x-bind:disabled="!checked" />
                            </div>
                        </div>
                    @else
                        <label class="flex items-center">
                            <input type="checkbox" name="permohonan_items[]" value="{{ $item }}"
                                class="rounded border-gray-300 text-pink-600 shadow-sm focus:ring-pink-500">
                            <span class="ms-3 text-gray-700">{{ $item }}</span>
                        </label>
                    @endif
                @endforeach
                <div class="mt-6">
                    <label for="deskripsi_pengajuan" class="block font-medium text-lg md:text-xl text-gray-700">Deskripsi
                        Pengajuan</label>
                    <textarea id="deskripsi_pengajuan" name="deskripsi_pengajuan"
                        class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        rows="4" placeholder="Jelaskan secara singkat tujuan atau detail pengajuan Anda di sini...">{{ old('deskripsi_pengajuan') }}</textarea>
                    <x-input-error :messages="$errors->get('deskripsi_pengajuan')" class="mt-2" />
                </div>
            </div>

            <div class="flex items-center justify-end mt-8 space-x-4">
                <a href="{{ route('dashboard') }}" id="batal-pengajuan"
                    class="py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm md:text-base font-medium text-gray-700 bg-white hover:bg-gray-50">Batalkan
                    Pengajuan</a>
                <button type="submit"
                    class="inline-flex items-center px-8 py-2 bg-pink-500 border border-transparent rounded-md font-semibold text-sm md:text-base text-white hover:bg-pink-600">Selanjutnya</button>
            </div>
        </form>
    </div>
    @push('scripts')
        <script>
            document.getElementById('batal-pengajuan').addEventListener('click', function(e) {
                e.preventDefault();

                Swal.fire({
                    title: 'Batalkan Pengajuan?',
                    text: 'Yakin ingin membatalkan dan kembali ke dashboard?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, batalkan',
                    cancelButtonText: 'Tidak'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = this.href;
                    }
                });
            });
        </script>
    @endpush
@endsection
