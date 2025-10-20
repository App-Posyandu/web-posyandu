<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>List Pengajuan - {{ config('app.name', 'Laravel') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>


<body class="font-sans antialiased">
    <div class="relative min-h-screen bg-gray-100">
        <x-colorful-background />

        <div class="relative z-10 flex flex-col min-h-screen">
            @include('layouts.partials.header-new')

            <main class="flex-grow gap-5 flex-col flex items-center justify-center py-12">
                <div class="w-full max-w-4xl mx-auto">
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl p-8">

                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-800">Detail Pengajuan</h2>
                                <p class="text-sm text-gray-500">Diajukan pada:
                                    {{ \Carbon\Carbon::parse($ajuan->created_at)->format('d F Y') }}</p>
                            </div>
                            <div>

                                <a href="{{ route('ajuan.index') }}"
                                    class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md text-sm font-semibold hover:bg-gray-300">Kembali</a>
                                @if ($ajuan->status === 'Ditolak' && Auth::user()->role === 'masyarakat')
                                    <a href="{{ route('ajuan.edit', $ajuan) }}"
                                        class="px-4 py-2 bg-yellow-500 text-white rounded-md text-sm font-semibold hover:bg-yellow-600">Ubah</a>
                                @endif
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 border-t border-b py-6">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Nama Pengaju</dt>
                                <dd class="mt-1 text-gray-900 font-semibold">
                                    {{ $ajuan->user?->name ?? 'Pengguna Telah Dihapus' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Bidang Layanan</dt>
                                <dd class="mt-1 text-gray-900 font-semibold">
                                    {{ $ajuan->bidang->nama_bidang ?? 'Bidang' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status Saat Ini</dt>
                                <dd class="mt-1">
                                    @if ($ajuan->status == 'Disetujui')
                                        <span
                                            class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Disetujui</span>
                                    @elseif ($ajuan->status == 'Ditolak')
                                        <span
                                            class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>
                                    @else
                                        <span
                                            class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Diproses</span>
                                    @endif
                                </dd>
                            </div>
                        </div>

                        <div class="mt-6">
                            <h3 class="font-semibold mb-2">Deskripsi Permohonan</h3>
                            <p class="text-gray-700 bg-gray-50 p-4 rounded-md">{{ $ajuan->deskripsi_pengajuan }}</p>
                        </div>

                        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <h3 class="font-semibold mb-2">Detail Permohonan Dipilih</h3>
                                <ul class="space-y-2">
                                    @forelse ($ajuan->formulir_items as $item)
                                        <li class="flex items-center text-gray-700">
                                            <i class="bi bi-check-square-fill text-green-500 mr-3"></i>
                                            <span>{{ $item }}</span>
                                        </li>
                                    @empty
                                        <li class="text-gray-500">Tidak ada item permohonan yang dipilih.</li>
                                    @endforelse
                                </ul>
                            </div>
                            <div>
                                <h3 class="font-semibold mb-2">Dokumen Terlampir</h3>
                                <ul class="space-y-2">
                                    @forelse ($ajuan->administrasi_items as $key => $path)
                                        <li class="flex items-center">
                                            <a href="{{ route('ajuan.dokumen.download', ['path' => $path]) }}"
                                                target="_blank" class="text-blue-600 hover:underline flex items-center">
                                                <i class="bi bi-file-earmark-arrow-down-fill text-blue-500 mr-2"></i>
                                                <span>{{ ucfirst(str_replace('_', ' ', $key)) }}</span>
                                            </a>
                                        </li>
                                    @empty
                                        <li class="text-gray-500">Tidak ada dokumen yang diunggah.</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>

                        <div class="mt-8">
                            <h3 class="font-semibold mb-4">Riwayat Pengajuan</h3>
                            <div class="border-l-2 border-gray-200 pl-6">
                                @forelse ($ajuan->histories->sortByDesc('created_at') as $history)
                                    <div class="mb-6">
                                        <div class="flex items-center mb-1">
                                            <div
                                                class="bg-pink-500 w-4 h-4 rounded-full -ml-[33px] mr-4 border-4 border-white">
                                            </div>
                                            <p class="font-bold text-gray-800">{{ $history->status }}</p>
                                        </div>
                                        <p class="text-sm text-gray-500 ml-5">
                                            {{ \Carbon\Carbon::parse($history->created_at)->format('d F Y, H:i') }}</p>
                                        @if ($history->catatan)
                                            <p class="mt-2 text-sm text-gray-600 bg-gray-50 p-3 rounded-md ml-5">
                                                {{ $history->catatan }}</p>
                                        @endif
                                    </div>
                                @empty
                                    <p class="text-gray-500">Belum ada riwayat untuk pengajuan ini.</p>
                                @endforelse
                            </div>
                        </div>

                    </div>
                </div>
                @if (Auth::user()->role == 'kader')
                    @if ($ajuan->status === 'Diproses')
                        <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl p-8">
                            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Verifikasi Pengajuan</h2>

                            <form method="POST" action="{{ route('ajuan.verify', $ajuan) }}">
                                @csrf
                                @method('PATCH')

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div>
                                        <h3 class="font-semibold mb-4 border-b pb-2">Verifikasi Detail Permohonan</h3>
                                        <div class="space-y-3">
                                            {{-- Loop melalui SEMUA item yang MUNGKIN ada di bidang ini (dari template) --}}
                                            @foreach ($templateData['formulir_items'] as $item)
                                                @if ($item === 'Lainnya...')
                                                    @php
                                                        $lainnyaItem = collect($ajuan->formulir_items ?? [])->first(
                                                            fn($i) => str_starts_with($i, 'Lainnya: '),
                                                        );
                                                        $isLainnyaChecked = !is_null($lainnyaItem);
                                                        $lainnyaTextValue = $isLainnyaChecked
                                                            ? str_replace('Lainnya: ', '', $lainnyaItem)
                                                            : '';
                                                    @endphp
                                                    <div
                                                        class="p-2 rounded-md {{ $isLainnyaChecked ? 'bg-green-50' : 'bg-gray-50' }}">
                                                        <label class="flex items-center">
                                                            <input type="checkbox" class="h-5 w-5 rounded border-gray-300 text-pink-600 shadow-sm focus:ring-pink-500"
                                                                @checked($isLainnyaChecked)>
                                                            <span
                                                                class="ms-3 text-sm font-semibold text-gray-700">{{ $item }}</span>
                                                        </label>
                                                        {{-- Tampilkan input teks HANYA jika memang diisi oleh pengguna --}}
                                                        @if ($isLainnyaChecked)
                                                            <div class="mt-2 pl-8">
                                                                <p class="text-xs text-gray-500">Isian Pengguna:</p>
                                                                <p
                                                                    class="p-2 bg-white border rounded-md text-sm text-gray-800">
                                                                    {{ $lainnyaTextValue }}</p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @else
                                                    <label
                                                        class="flex items-center p-2 rounded-md {{ in_array($item, $ajuan->formulir_items ?? []) ? 'bg-green-50' : 'bg-gray-50' }}">
                                                        <input type="checkbox"
                                                            class="h-5 w-5 rounded border-gray-300 text-pink-600 shadow-sm focus:ring-pink-500"
                                                            {{-- Centang otomatis jika item ini ada di dalam data pengajuan yang sudah diisi --}} @checked(in_array($item, $ajuan->formulir_items ?? []))>
                                                        <span
                                                            class="ms-3 text-sm text-gray-700">{{ $item }}</span>
                                                    </label>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>

                                    <div>
                                        <h3 class="font-semibold mb-4 border-b pb-2">Verifikasi Dokumen Administrasi
                                        </h3>
                                        <div class="space-y-3">
                                            {{-- Loop melalui SEMUA syarat dokumen yang MUNGKIN ada (dari template) --}}
                                            @foreach ($templateData['administrasi_items'] as $key => $label)
                                                <div
                                                    class="p-2 rounded-md {{ isset($ajuan->administrasi_items[$key]) ? 'bg-green-50' : 'bg-red-50 border-l-4 border-red-400' }}">
                                                    <label class="flex items-center">
                                                        <input type="checkbox"
                                                            class="h-5 w-5 rounded border-gray-300 text-pink-600 shadow-sm focus:ring-pink-500"
                                                            @checked(isset($ajuan->administrasi_items[$key]))>
                                                        <span
                                                            class="ms-3 text-sm text-gray-700">{{ $label }}</span>
                                                    </label>
                                                    @if (isset($ajuan->administrasi_items[$key]))
                                                        <a href="{{ route('ajuan.dokumen.download', ['path' => $ajuan->administrasi_items[$key]]) }}"
                                                            class="text-xs text-blue-600 hover:underline ml-8">Lihat/Unduh
                                                            Dokumen</a>
                                                    @else
                                                        <span class="text-xs text-red-500 ml-8">Dokumen tidak
                                                            diunggah</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-8 border-t pt-6 space-y-4">
                                    <div>
                                        <label for="catatan" class="block font-medium text-sm text-gray-700">Catatan
                                            (Opsional)</label>
                                        <textarea id="catatan" name="catatan" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                            placeholder="Tambahkan catatan jika pengajuan ditolak..."></textarea>
                                    </div>
                                    <div>
                                        <label for="status" class="block font-medium text-sm text-gray-700">Ubah
                                            Status Pengajuan</label>
                                        <select id="status" name="status"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                            <option value="Disetujui">Setujui Pengajuan</option>
                                            <option value="Ditolak">Tolak Pengajuan</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="flex items-center justify-end mt-6">
                                    <button type="submit"
                                        class="inline-flex items-center px-6 py-2 bg-green-600 text-white font-semibold text-sm rounded-md hover:bg-green-700">
                                        Simpan Verifikasi
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif
                @endif
            </main>
        </div>
    </div>
</body>

</html>
