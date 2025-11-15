@extends('dashboard.layouts.dashboard')
@section('title', 'Detail Pengajuan')
@section('content')
    <div x-data="{
        showModal: false,
        fileUrl: '',
        fileTitle: '',
        fileType: '',
        isLoadingModal: false
    }" class="flex-grow gap-5 flex-col flex items-center justify-center py-12">
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
                        @if ($ajuan->status === 'Ditolak' && auth()->user()->role === 'masyarakat')
                            <a href="{{ route('ajuan.edit', $ajuan) }}"
                                class="px-4 py-2 bg-yellow-500 text-white rounded-md text-sm font-semibold hover:bg-yellow-600">Ubah</a>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-4 gap-6 border-t border-b py-6">
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
                        <dt class="text-sm font-medium text-gray-500">Status Pengajuan</dt>
                        <dd class="mt-1">
                            <div class="flex flex-col space-y-1">
                                @if ($ajuan->sudah_verifikasi)
                                    <span
                                        class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Sudah Verifikasi
                                    </span>
                                @else
                                    <span
                                        class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Belum Verifikasi
                                    </span>
                                @endif

                                @if ($ajuan->kunjungan_lapangan)
                                    <span
                                        class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Kunjungan Lapangan
                                    </span>
                                @else
                                    <span
                                        class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Belum Kunjungan
                                    </span>
                                @endif
                            </div>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status Saat Ini</dt>
                        <dd class="mt-1">
                            @if ($ajuan->status_pengajuan == 'Disetujui')
                                <span
                                    class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Disetujui</span>
                            @elseif ($ajuan->status_pengajuan == 'Ditolak')
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
                                    <a href="{{ route('ajuan.dokumen.download', ['ajuan' => $ajuan, 'key' => $key]) }}"
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
                                    <div class="bg-pink-500 w-4 h-4 rounded-full -ml-[33px] mr-4 border-4 border-white">
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
        @if (auth()->user()->role === 'kader')

            {{-- Modal Preview Dokumen --}}
            <div x-show="showModal" x-cloak x-transition.opacity
                class="fixed inset-0 bg-black/60 flex items-center justify-center z-50" @click.self="showModal = false">
                <div
                    class="bg-white rounded-xl shadow-2xl w-11/12 md:w-3/4 h-[85vh] relative overflow-hidden flex flex-col">
                    <button @click="showModal=false"
                        class="absolute top-3 right-3 text-gray-600 hover:text-black text-2xl font-bold z-20">✕</button>
                    <h2 class="text-lg font-semibold text-center py-3 border-b" x-text="fileTitle"></h2>
                    <div class="flex-1 flex items-center justify-center bg-gray-100 relative">
                        <div x-show="isLoadingModal"
                            class="absolute inset-0 flex flex-col items-center justify-center bg-gray-100/80 z-10">
                            <div class="w-12 h-12 border-4 border-t-pink-500 border-gray-200 rounded-full animate-spin">
                            </div>
                            <p class="text-gray-600 mt-3">Memuat dokumen...</p>
                        </div>
                        <iframe x-show="fileType === 'pdf' || fileType === 'pdf_path'" :src="fileUrl"
                            @load="isLoadingModal=false" class="w-full h-full border-0 rounded-b-xl"></iframe>
                        <img x-show="fileType === 'image'" :src="fileUrl" @load="isLoadingModal = false"
                            class="max-h-full max-w-full object-contain rounded-b-xl">
                        <div x-show="fileType === 'pdf_base64'" @load="isLoadingModal = false"
                            class="p-4 text-center text-gray-500">
                            <i class="bi bi-file-earmark-check-fill text-3xl text-green-500"></i><br>
                            File PDF Terdaftar.<br><span class="text-xs">(Preview tidak tersedia, silakan unduh)</span>
                        </div>
                        <p x-show="fileType === 'unknown' || fileType === 'unsupported'" @load="isLoadingModal = false"
                            class="text-gray-500 italic">File tidak dapat dipratinjau.</p>
                    </div>
                </div>
            </div>

            {{-- Container Accordion 3 Tahap --}}
            @if ($ajuan->status_pengajuan === 'Diproses')
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl p-8 w-full max-w-5xl" x-data="{
                    step1Open: {{ !$ajuan->sudah_verifikasi ? 'true' : 'false' }},
                    step2Open: {{ $ajuan->sudah_verifikasi && !$ajuan->kunjungan_lapangan ? 'true' : 'false' }},
                    step3Open: {{ $ajuan->sudah_verifikasi && $ajuan->kunjungan_lapangan && !$ajuan->ttd_kader ? 'true' : 'false' }}
                }">

                    <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Proses Verifikasi Pengajuan</h2>

                    {{-- ========================================= --}}
                    {{-- TAHAP 1: Verifikasi Dokumen --}}
                    {{-- ========================================= --}}
                    <div
                        class="mb-4 border rounded-lg overflow-hidden {{ $ajuan->sudah_verifikasi ? 'bg-green-50 border-green-300' : 'bg-white border-gray-300' }}">

                        {{-- Header Accordion --}}
                        <button @click="step1Open = !step1Open" type="button"
                            class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-gray-50 transition">
                            <div class="flex items-center space-x-3">
                                @if ($ajuan->sudah_verifikasi)
                                    <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                @else
                                    <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z"
                                            clip-rule="evenodd" />
                                    </svg>
                                @endif
                                <span class="font-semibold text-lg">
                                    Tahap 1: Verifikasi Dokumen
                                    @if ($ajuan->sudah_verifikasi)
                                        <span class="text-sm text-green-600 ml-2">(Selesai)</span>
                                    @else
                                        <span class="text-sm text-blue-600 ml-2">(Aktif)</span>
                                    @endif
                                </span>
                            </div>
                            <svg class="w-5 h-5 transition-transform" :class="step1Open ? 'rotate-180' : ''"
                                fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>

                        {{-- Content Accordion --}}
                        <div x-show="step1Open" x-collapse>
                            <div class="px-6 py-4 border-t">
                                <form method="POST" action="{{ route('ajuan.verify', $ajuan) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="verification_step" value="1">

                                    {{-- Error Validasi --}}
                                    @if ($errors->has('verified_formulir_items') || $errors->has('verified_administrasi_items'))
                                        <div
                                            class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                                            <strong class="font-bold">Validasi Gagal!</strong>
                                            <span class="block sm:inline">Anda harus mencentang SEMUA item dan
                                                dokumen.</span>
                                        </div>
                                    @endif

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                        {{-- Detail Permohonan --}}
                                        <div>
                                            <h3 class="font-semibold mb-4 border-b pb-2">Detail Permohonan Diajukan</h3>
                                            <div class="space-y-3">
                                                @forelse ($ajuan->formulir_items ?? [] as $item)
                                                    <label
                                                        class="flex items-center justify-between p-3 rounded-md bg-gray-50 border">
                                                        <span
                                                            class="text-sm text-gray-700 pr-4">{{ $item }}</span>
                                                        <input type="checkbox" name="verified_formulir_items[]"
                                                            value="{{ $item }}"
                                                            {{ $ajuan->sudah_verifikasi ? 'checked disabled' : '' }}
                                                            class="h-5 w-5 rounded border-gray-400 text-pink-600 shadow-sm focus:ring-pink-500">
                                                    </label>
                                                @empty
                                                    <p class="text-gray-500 text-sm">Tidak ada item permohonan.</p>
                                                @endforelse
                                            </div>
                                        </div>

                                        {{-- Dokumen Administrasi --}}
                                        <div>
                                            <h3 class="font-semibold mb-4 border-b pb-2">Dokumen Administrasi Terlampir
                                            </h3>
                                            <div class="space-y-3">
                                                @forelse ($ajuan->administrasi_items ?? [] as $key => $path)
                                                    @php
                                                        $label =
                                                            $templateData['administrasi_items'][$key] ??
                                                            ucfirst(str_replace('_', ' ', $key));
                                                        $streamUrl = route('ajuan.dokumen.stream', [
                                                            'ajuan' => $ajuan,
                                                            'key' => $key,
                                                        ]);
                                                        $fileType = 'unknown';
                                                        if (\Illuminate\Support\Str::startsWith($path, 'data:image')) {
                                                            $fileType = 'image';
                                                        } elseif (
                                                            \Illuminate\Support\Str::startsWith(
                                                                $path,
                                                                'data:application/pdf',
                                                            )
                                                        ) {
                                                            $fileType = 'pdf';
                                                        } elseif (
                                                            \Illuminate\Support\Str::endsWith($path, [
                                                                '.jpg',
                                                                '.jpeg',
                                                                '.png',
                                                                '.gif',
                                                            ])
                                                        ) {
                                                            $fileType = 'image';
                                                        } elseif (\Illuminate\Support\Str::endsWith($path, ['.pdf'])) {
                                                            $fileType = 'pdf';
                                                        }
                                                    @endphp
                                                    <div class="p-3 rounded-md bg-gray-50 border">
                                                        <label class="flex items-center justify-between">
                                                            <span class="text-sm text-gray-700">{{ $label }}</span>
                                                            <input type="checkbox"
                                                                name="verified_administrasi_items[{{ $key }}]"
                                                                value="1"
                                                                {{ $ajuan->sudah_verifikasi ? 'checked disabled' : '' }}
                                                                class="h-5 w-5 rounded border-gray-400 text-pink-600 shadow-sm focus:ring-pink-500">
                                                        </label>
                                                        <div class="mt-2 ml-1 flex items-center space-x-4">
                                                            <button type="button"
                                                                class="px-3 py-1 text-sm text-blue-600 border border-blue-600 rounded hover:bg-blue-50"
                                                                @click="showModal=true; fileUrl='{{ $streamUrl }}'; fileTitle='{{ $label }}'; fileType='{{ $fileType }}';">
                                                                Lihat Dokumen
                                                            </button>
                                                            <a href="{{ route('ajuan.dokumen.download', ['ajuan' => $ajuan, 'key' => $key]) }}"
                                                                class="text-xs text-green-600 hover:underline">Unduh</a>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <p class="text-gray-500 text-sm">Tidak ada dokumen.</p>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Catatan --}}
                                    <div class="mt-6">
                                        <label for="catatan_tolak" class="block font-medium text-sm text-gray-700">
                                            Catatan (Wajib jika ditolak)
                                        </label>
                                        <textarea id="catatan_tolak" name="catatan" rows="3" {{ $ajuan->sudah_verifikasi ? 'disabled' : '' }}
                                            class="mt-1 block w-full border-gray-300 focus:border-pink-500 focus:ring-pink-500 rounded-md shadow-sm"
                                            placeholder="Tambahkan catatan jika pengajuan ditolak..."></textarea>
                                        <x-input-error :messages="$errors->get('catatan')" class="mt-2" />
                                    </div>

                                    {{-- Tombol Aksi --}}
                                    @if (!$ajuan->sudah_verifikasi)
                                        <div class="flex items-center justify-end mt-8 space-x-4">
                                            <button type="submit" name="tolak_langsung" value="Ditolak"
                                                class="py-2 px-6 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                                Dokumen Tidak Sesuai
                                            </button>
                                            <button type="submit"
                                                class="inline-flex items-center px-6 py-2 bg-green-600 text-white font-semibold text-sm rounded-md hover:bg-green-700">
                                                Setujui Verifikasi
                                            </button>
                                        </div>
                                    @endif
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- ========================================= --}}
                    {{-- TAHAP 2: Konfirmasi Kunjungan Lapangan --}}
                    {{-- ========================================= --}}
                    <div
                        class="mb-4 border rounded-lg overflow-hidden {{ $ajuan->kunjungan_lapangan ? 'bg-green-50 border-green-300' : ($ajuan->sudah_verifikasi ? 'bg-white border-gray-300' : 'bg-gray-100 border-gray-200') }}">

                        {{-- Header Accordion --}}
                        <button @click="step2Open = !step2Open" type="button"
                            {{ !$ajuan->sudah_verifikasi ? 'disabled' : '' }}
                            class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-gray-50 transition {{ !$ajuan->sudah_verifikasi ? 'cursor-not-allowed opacity-50' : '' }}">
                            <div class="flex items-center space-x-3">
                                @if ($ajuan->kunjungan_lapangan)
                                    <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                @elseif ($ajuan->sudah_verifikasi)
                                    <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z"
                                            clip-rule="evenodd" />
                                    </svg>
                                @else
                                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd" />
                                    </svg>
                                @endif
                                <span class="font-semibold text-lg">
                                    Tahap 2: Kunjungan Lapangan
                                    @if ($ajuan->kunjungan_lapangan)
                                        <span class="text-sm text-green-600 ml-2">(Selesai)</span>
                                    @elseif ($ajuan->sudah_verifikasi)
                                        <span class="text-sm text-blue-600 ml-2">(Aktif)</span>
                                    @else
                                        <span class="text-sm text-gray-400 ml-2">(Terkunci)</span>
                                    @endif
                                </span>
                            </div>
                            <svg class="w-5 h-5 transition-transform" :class="step2Open ? 'rotate-180' : ''"
                                fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>

                        {{-- Content Accordion --}}
                        @if ($ajuan->sudah_verifikasi)
                            <div x-show="step2Open" x-collapse>
                                <div class="px-6 py-4 border-t">
                                    <form method="POST" action="{{ route('ajuan.verify', $ajuan) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="verification_step" value="2">

                                        {{-- Informasi Kunjungan --}}
                                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                                            <div class="flex items-start space-x-3">
                                                <svg class="w-6 h-6 text-blue-600 flex-shrink-0 mt-0.5" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <div>
                                                    <h4 class="font-semibold text-blue-900 mb-1">Informasi Penting</h4>
                                                    <p class="text-sm text-blue-800">
                                                        Kunjungan lapangan adalah tahap <strong>wajib</strong> dalam proses
                                                        verifikasi.
                                                        Pastikan Anda telah melakukan kunjungan ke lokasi pemohon untuk
                                                        memverifikasi kebenaran data dan kondisi aktual di lapangan.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Checklist Kunjungan --}}
                                        <div class="bg-white border border-gray-200 rounded-lg p-5 mb-4">
                                            <h4 class="font-semibold text-gray-800 mb-3">Checklist Kunjungan Lapangan</h4>
                                            <div class="space-y-2 text-sm text-gray-700">
                                                <div class="flex items-center space-x-2">
                                                    <svg class="w-5 h-5 text-green-600" fill="currentColor"
                                                        viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                    <span>Mengunjungi alamat sesuai dengan data pengajuan</span>
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    <svg class="w-5 h-5 text-green-600" fill="currentColor"
                                                        viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                    <span>Memverifikasi identitas pemohon</span>
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    <svg class="w-5 h-5 text-green-600" fill="currentColor"
                                                        viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                    <span>Memeriksa kondisi dan kebutuhan aktual di lapangan</span>
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    <svg class="w-5 h-5 text-green-600" fill="currentColor"
                                                        viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                    <span>Mendokumentasikan hasil kunjungan (foto/bukti)</span>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Konfirmasi Checkbox --}}
                                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-5 mb-4">
                                            <label class="flex items-start space-x-3 cursor-pointer">
                                                <input type="checkbox" name="konfirmasi_kunjungan" value="1"
                                                    {{ $ajuan->kunjungan_lapangan ? 'checked disabled' : 'required' }}
                                                    class="h-5 w-5 mt-0.5 rounded border-gray-400 text-pink-600 shadow-sm focus:ring-pink-500">
                                                <span class="text-sm text-gray-800">
                                                    <strong>Saya menyatakan bahwa kunjungan lapangan telah
                                                        dilaksanakan</strong>
                                                    dan data yang tertera pada pengajuan sesuai dengan kondisi aktual di
                                                    lapangan.
                                                </span>
                                            </label>
                                            <x-input-error :messages="$errors->get('konfirmasi_kunjungan')" class="mt-2" />
                                        </div>

                                        {{-- Catatan Kunjungan (Opsional) --}}
                                        <div class="mb-6">
                                            <label for="catatan_kunjungan"
                                                class="block font-medium text-sm text-gray-700 mb-2">
                                                Catatan Hasil Kunjungan <span
                                                    class="text-gray-500 font-normal">(Opsional)</span>
                                            </label>
                                            <textarea id="catatan_kunjungan" name="catatan_kunjungan" rows="4"
                                                {{ $ajuan->kunjungan_lapangan ? 'disabled' : '' }}
                                                class="block w-full border-gray-300 focus:border-pink-500 focus:ring-pink-500 rounded-md shadow-sm"
                                                placeholder="Contoh: Lokasi sesuai, kondisi rumah layak, keluarga memenuhi kriteria bantuan..."></textarea>
                                            <p class="text-xs text-gray-500 mt-1">Catatan ini akan tersimpan dalam riwayat
                                                pengajuan</p>
                                        </div>

                                        {{-- Tombol Submit --}}
                                        @if (!$ajuan->kunjungan_lapangan)
                                            <div class="flex justify-end">
                                                <button type="submit"
                                                    class="inline-flex items-center px-6 py-2 bg-blue-600 text-white font-semibold text-sm rounded-md hover:bg-blue-700 shadow-md">
                                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    Konfirmasi Kunjungan Selesai
                                                </button>
                                            </div>
                                        @endif
                                    </form>
                                </div>
                            </div>
                        @endif
                    </div>
                    {{-- ========================================= --}}
                    {{-- TAHAP 3: Keputusan Akhir --}}
                    {{-- ========================================= --}}
                    <div
                        class="border rounded-lg overflow-hidden {{ $ajuan->ttd_kader ? 'bg-green-50 border-green-300' : ($ajuan->kunjungan_lapangan ? 'bg-white border-gray-300' : 'bg-gray-100 border-gray-200') }}">

                        {{-- Header Accordion --}}
                        <button @click="step3Open = !step3Open" type="button"
                            {{ !$ajuan->kunjungan_lapangan ? 'disabled' : '' }}
                            class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-gray-50 transition {{ !$ajuan->kunjungan_lapangan ? 'cursor-not-allowed opacity-50' : '' }}">
                            <div class="flex items-center space-x-3">
                                @if ($ajuan->ttd_kader)
                                    <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                @elseif ($ajuan->kunjungan_lapangan)
                                    <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z"
                                            clip-rule="evenodd" />
                                    </svg>
                                @else
                                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd" />
                                    </svg>
                                @endif
                                <span class="font-semibold text-lg">
                                    Tahap 3: Keputusan Akhir
                                    @if ($ajuan->ttd_kader)
                                        <span class="text-sm text-green-600 ml-2">(Selesai)</span>
                                    @elseif ($ajuan->kunjungan_lapangan)
                                        <span class="text-sm text-blue-600 ml-2">(Aktif)</span>
                                    @else
                                        <span class="text-sm text-gray-400 ml-2">(Terkunci)</span>
                                    @endif
                                </span>
                            </div>
                            <svg class="w-5 h-5 transition-transform" :class="step3Open ? 'rotate-180' : ''"
                                fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>

                        {{-- Content Accordion --}}
                        @if ($ajuan->kunjungan_lapangan)
                            <div x-show="step3Open" x-collapse>
                                <div class="px-6 py-4 border-t">
                                    <form method="POST" action="{{ route('ajuan.verify', $ajuan) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="verification_step" value="3">

                                        {{-- Informasi Ringkas --}}
                                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6">
                                            <div class="flex items-start space-x-3">
                                                <svg class="w-6 h-6 text-amber-600 flex-shrink-0 mt-0.5" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                                <div>
                                                    <h4 class="font-semibold text-amber-900 mb-1">Keputusan Akhir</h4>
                                                    <p class="text-sm text-amber-800">
                                                        Ini adalah tahap terakhir dari proses verifikasi. Keputusan yang
                                                        Anda buat akan menentukan
                                                        apakah pengajuan ini <strong>disetujui</strong> atau
                                                        <strong>ditolak</strong>.
                                                        Pastikan keputusan sudah dipertimbangkan dengan matang berdasarkan
                                                        hasil kunjungan lapangan.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Ringkasan Verifikasi --}}
                                        <div class="bg-white border border-gray-200 rounded-lg p-5 mb-6">
                                            <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                                                <svg class="w-5 h-5 mr-2 text-gray-600" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                Ringkasan Proses Verifikasi
                                            </h4>
                                            <div class="space-y-2 text-sm">
                                                <div class="flex justify-between py-2 border-b">
                                                    <span class="text-gray-600">Tahap 1 - Verifikasi Dokumen:</span>
                                                    <span class="font-semibold text-green-600 flex items-center">
                                                        <svg class="w-4 h-4 mr-1" fill="currentColor"
                                                            viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd"
                                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                                clip-rule="evenodd" />
                                                        </svg>
                                                        Selesai
                                                    </span>
                                                </div>
                                                <div class="flex justify-between py-2 border-b">
                                                    <span class="text-gray-600">Tahap 2 - Kunjungan Lapangan:</span>
                                                    <span class="font-semibold text-green-600 flex items-center">
                                                        <svg class="w-4 h-4 mr-1" fill="currentColor"
                                                            viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd"
                                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                                clip-rule="evenodd" />
                                                        </svg>
                                                        Selesai
                                                    </span>
                                                </div>
                                                <div class="flex justify-between py-2">
                                                    <span class="text-gray-600">Item Permohonan Terverifikasi:</span>
                                                    <span class="font-semibold text-gray-800">
                                                        @php
                                                            $verifiedItems = is_string($ajuan->verified_formulir_items)
                                                                ? json_decode($ajuan->verified_formulir_items, true)
                                                                : $ajuan->verified_formulir_items ?? [];
                                                        @endphp
                                                        {{ count($verifiedItems) }} item
                                                    </span>
                                                </div>
                                                <div class="flex justify-between py-2">
                                                    <span class="text-gray-600">Dokumen Terverifikasi:</span>
                                                    <span class="font-semibold text-gray-800">
                                                        @php
                                                            $verifiedDocs = is_string(
                                                                $ajuan->verified_administrasi_items,
                                                            )
                                                                ? json_decode($ajuan->verified_administrasi_items, true)
                                                                : $ajuan->verified_administrasi_items ?? [];
                                                        @endphp
                                                        {{ count($verifiedDocs) }} dokumen
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Pilihan Status Akhir --}}
                                        <div class="mb-6">
                                            <label for="status" class="block font-medium text-sm text-gray-700 mb-2">
                                                Status Pengajuan Akhir <span class="text-red-500">*</span>
                                            </label>
                                            <select id="status" name="status" required
                                                {{ $ajuan->ttd_kader ? 'disabled' : '' }}
                                                class="block w-full border-gray-300 focus:border-pink-500 focus:ring-pink-500 rounded-md shadow-sm">
                                                <option value="" disabled selected>Pilih Keputusan Akhir...</option>
                                                <option value="Disetujui">✓ Setujui Pengajuan</option>
                                                <option value="Ditolak">✗ Tolak Pengajuan</option>
                                            </select>
                                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                                        </div>

                                        {{-- Catatan Akhir --}}
                                        <div class="mb-6">
                                            <label for="catatan_final"
                                                class="block font-medium text-sm text-gray-700 mb-2">
                                                Catatan Akhir
                                                <span class="text-gray-500 font-normal">(Wajib jika ditolak)</span>
                                            </label>
                                            <textarea id="catatan_final" name="catatan" rows="4" {{ $ajuan->ttd_kader ? 'disabled' : '' }}
                                                class="block w-full border-gray-300 focus:border-pink-500 focus:ring-pink-500 rounded-md shadow-sm"
                                                placeholder="Berikan alasan atau catatan untuk keputusan ini..."></textarea>
                                            <x-input-error :messages="$errors->get('catatan')" class="mt-2" />
                                        </div>

                                        {{-- TTD Kader --}}
                                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-5 mb-6">
                                            <label class="flex items-start space-x-3 cursor-pointer">
                                                <input type="checkbox" name="ttd_kader" value="1"
                                                    {{ $ajuan->ttd_kader ? 'checked disabled' : 'required' }}
                                                    class="h-5 w-5 mt-0.5 rounded border-gray-400 text-pink-600 shadow-sm focus:ring-pink-500">
                                                <span class="text-sm text-gray-800">
                                                    <strong>Saya (Kader) menyetujui keputusan akhir ini</strong> dan
                                                    bertanggung jawab
                                                    atas keputusan yang telah dibuat berdasarkan hasil verifikasi dokumen
                                                    dan kunjungan lapangan.
                                                </span>
                                            </label>
                                            <x-input-error :messages="$errors->get('ttd_kader')" class="mt-2" />
                                        </div>

                                        {{-- Tombol Submit --}}
                                        @if (!$ajuan->ttd_kader)
                                            <div class="flex justify-end space-x-3">
                                                <a href="{{ route('ajuan.index') }}"
                                                    class="inline-flex items-center px-6 py-2 bg-gray-200 text-gray-700 font-semibold text-sm rounded-md hover:bg-gray-300">
                                                    Batal
                                                </a>
                                                <button type="submit"
                                                    class="inline-flex items-center px-6 py-2 bg-pink-600 text-white font-semibold text-sm rounded-md hover:bg-pink-700 shadow-md">
                                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    Simpan Keputusan Akhir
                                                </button>
                                            </div>
                                        @else
                                            <div class="bg-green-100 border border-green-300 rounded-lg p-4 text-center">
                                                <svg class="w-12 h-12 text-green-600 mx-auto mb-2" fill="currentColor"
                                                    viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                <p class="font-semibold text-green-800">Keputusan Akhir Telah Disimpan</p>
                                                <p class="text-sm text-green-700 mt-1">Proses verifikasi telah selesai</p>
                                            </div>
                                        @endif
                                    </form>
                                </div>
                            </div>
                        @endif
                    </div>
            @endif
        @endif
    </div>
@endsection
