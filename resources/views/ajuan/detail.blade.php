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

            {{-- TAHAP 1: Verifikasi Dokumen --}}
            {{-- Tampil jika status 'Diproses' DAN 'sudah_verifikasi' masih false --}}
            @if ($ajuan->status_pengajuan === 'Diproses' && !$ajuan->sudah_verifikasi)
                <div x-show="showModal" x-cloak x-transition.opacity
                    class="fixed inset-0 bg-black/60 flex items-center justify-center z-50" @click.self="showModal = false">

                    <div
                        class="bg-white rounded-xl shadow-2xl w-11/12 md:w-3/4 h-[85vh] relative overflow-hidden flex flex-col">
                        <button @click="showModal=false"
                            class="absolute top-3 right-3 text-gray-600 hover:text-black text-2xl font-bold z-20">
                            ✕
                        </button>

                        <h2 class="text-lg font-semibold text-center py-3 border-b" x-text="fileTitle"></h2>

                        <div class="flex-1 flex items-center justify-center bg-gray-100 relative">

                            <div x-show="isLoadingModal"
                                class="absolute inset-0 flex flex-col items-center justify-center bg-gray-100/80 z-10">
                                <div class="w-12 h-12 border-4 border-t-pink-500 border-gray-200 rounded-full animate-spin">
                                </div>
                                <p class="text-gray-600 mt-3">Memuat dokumen...</p>
                            </div>

                            <iframe x-show="fileType === 'pdf' || fileType === 'pdf_path'" :src="fileUrl"
                                @load="isLoadingModal=false" class="w-full h-full border-0 rounded-b-xl">
                            </iframe>

                            <img x-show="fileType === 'image'" :src="fileUrl" @load="isLoadingModal = false"
                                class="max-h-full max-w-full object-contain rounded-b-xl">

                            {{-- Placeholder untuk PDF Base64 (tidak bisa di-preview di iframe) --}}
                            <div x-show="fileType === 'pdf_base64'" @load="isLoadingModal = false"
                                class="p-4 text-center text-gray-500">
                                <i class="bi bi-file-earmark-check-fill text-3xl text-green-500"></i>
                                <br>File PDF Terdaftar.<br><span class="text-xs">(Preview tidak tersedia untuk PDF
                                    terdaftar, silakan unduh)</span>
                            </div>

                            <p x-show="fileType === 'unknown' || fileType === 'unsupported'" @load="isLoadingModal = false"
                                class="text-gray-500 italic">
                                File tidak dapat dipratinjau.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl p-8 w-full max-w-4xl">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Tahap 1: Verifikasi Dokumen</h2>

                    <form method="POST" action="{{ route('ajuan.verify', $ajuan) }}">
                        @csrf
                        @method('PATCH')
                        {{-- Penanda bahwa ini adalah submit Tahap 1 --}}
                        <input type="hidden" name="verification_step" value="1">

                        {{-- Tampilkan Error Validasi Checklist --}}
                        @if ($errors->has('verified_formulir_items') || $errors->has('verified_administrasi_items'))
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"
                                role="alert">
                                <strong class="font-bold">Validasi Gagal!</strong>
                                <span class="block sm:inline">Anda harus mencentang SEMUA item dan dokumen untuk
                                    melanjutkan.</span>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <h3 class="font-semibold mb-4 border-b pb-2">Detail Permohonan Diajukan</h3>
                                <div class="space-y-3">
                                    @forelse ($ajuan->formulir_items ?? [] as $item)
                                        <label class="flex items-center justify-between p-3 rounded-md bg-gray-50 border">
                                            <span class="text-sm text-gray-700 pr-4">{{ $item }}</span>
                                            <input type="checkbox" name="verified_formulir_items[]"
                                                value="{{ $item }}"
                                                class="h-5 w-5 rounded border-gray-400 text-pink-600 shadow-sm focus:ring-pink-500">
                                        </label>
                                    @empty
                                        <p class="text-gray-500 text-sm">Tidak ada item permohonan.</p>
                                    @endforelse
                                </div>
                            </div>

                            <div>
                                <h3 class="font-semibold mb-4 border-b pb-2">Dokumen Administrasi Terlampir</h3>
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
                                            $fileType = 'unknown'; // Default
                                            if (\Illuminate\Support\Str::startsWith($path, 'data:image')) {
                                                $fileType = 'image';
                                            } elseif (
                                                \Illuminate\Support\Str::startsWith($path, 'data:application/pdf')
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
                                                    class="h-5 w-5 rounded border-gray-400 text-pink-600 shadow-sm focus:ring-pink-500">
                                            </label>
                                            <div class="mt-2 ml-1 flex items-center space-x-4">
                                                <button type="button"
                                                    class="px-3 py-1 text-sm text-blue-600 border border-blue-600 rounded hover:bg-blue-50"
                                                    @click="
                                                        showModal = true;
                                                        fileUrl = '{{ $streamUrl }}';
                                                        fileTitle = '{{ $label }}';
                                                        fileType = '{{ $fileType }}';
                                                    ">
                                                    Lihat Dokumen
                                                </button>

                                                <a href="{{ route('ajuan.dokumen.download', ['ajuan' => $ajuan, 'key' => $key]) }}"
                                                    class="text-xs text-green-600 hover:underline">
                                                    Unduh
                                                </a>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-gray-500 text-sm">Tidak ada dokumen yang diunggah.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <label for="catatan_tolak" class="block font-medium text-sm text-gray-700">Catatan (Wajib jika
                                ditolak)</label>
                            <textarea id="catatan_tolak" name="catatan" rows="3"
                                class="mt-1 block w-full border-gray-300 focus:border-pink-500 focus:ring-pink-500 rounded-md shadow-sm"
                                placeholder="Tambahkan catatan jika pengajuan ditolak..."></textarea>
                            <x-input-error :messages="$errors->get('catatan')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-8 space-x-4">
                            <button type="submit" name="tolak_langsung" value="Ditolak"
                                class="py-2 px-6 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                Dokumen tidak sesuai
                            </button>
                            <button type="submit"
                                class="inline-flex items-center px-6 py-2 bg-green-600 text-white font-semibold text-sm rounded-md hover:bg-green-700">
                                Setujui Verifikasi
                            </button>
                        </div>
                    </form>
                </div>

                {{-- TAHAP 2: Tindak Lanjut --}}
                {{-- Tampil jika status 'Diproses' DAN 'sudah_verifikasi' sudah true --}}
            @elseif ($ajuan->status_pengajuan === 'Diproses' && $ajuan->sudah_verifikasi)
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl p-8 w-full max-w-4xl"
                    x-data="{ kunjungan_lapangan: '{{ $ajuan->kunjungan_lapangan ? '1' : '0' }}' }">

                    <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Tahap 2: Tindak Lanjut & Keputusan</h2>

                    <form method="POST" action="{{ route('ajuan.verify', $ajuan) }}">
                        @csrf
                        @method('PATCH')
                        {{-- Penanda bahwa ini adalah submit Tahap 2 --}}
                        <input type="hidden" name="verification_step" value="2">

                        <div class="block mb-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="ttd_kader" value="1" required
                                    class="h-5 w-5 rounded border-gray-400 text-pink-600 shadow-sm focus:ring-pink-500">
                                <span class="ms-3 text-sm text-gray-700">Saya (Kader) menyetujui tindak lanjut ini.</span>
                            </label>
                            <x-input-error :messages="$errors->get('ttd_kader')" class="mt-2" />
                        </div>

                        {{-- <div class="mt-6 border-t pt-6">
                            <h3 class="font-semibold mb-4 text-gray-700">Perlu Kunjungan Lapangan?</h3>
                            <div class="space-y-3">
                                <label class="flex items-center p-3 border rounded-md">
                                    <input type="radio" name="kunjungan_lapangan" value="1"
                                        x-model="kunjungan_lapangan" class="h-5 w-5 ...">
                                    <span class="ms-3 text-sm text-gray-700">Ya, perlu kunjungan lapangan</span>
                                </label>
                                <label class="flex items-center p-3 border rounded-md">
                                    <input type="radio" name="kunjungan_lapangan" value="0"
                                        x-model="kunjungan_lapangan" class="h-5 w-5 ...">
                                    <span class="ms-3 text-sm text-gray-700">Tidak, tidak perlu kunjungan lapangan</span>
                                </label>
                            </div>
                        </div> --}}

                        <div x-show="kunjungan_lapangan === '0'" class="mt-6" x-transition>
                            <label for="status" class="block font-medium text-sm text-gray-700">Status Pengajuan
                                Akhir</label>
                            <select id="status" name="status"
                                class="mt-1 block w-full border-gray-300 focus:border-pink-500 focus:ring-pink-500 rounded-md shadow-sm">
                                <option value="Disetujui">Setujui Pengajuan</option>
                                <option value="Ditolak">Tolak Pengajuan</option>
                            </select>
                        </div>

                        <div class="mt-6">
                            <label for="catatan_final" class="block font-medium text-sm text-gray-700">Catatan Akhir
                                (Opsional)</label>
                            <textarea id="catatan_final" name="catatan" rows="3"
                                class="mt-1 block w-full border-gray-300 focus:border-pink-500 focus:ring-pink-500 rounded-md shadow-sm"
                                placeholder="Tambahkan catatan..."></textarea>
                        </div>

                        <div class="flex items-center justify-end mt-8 space-x-4">
                            <button type="submit"
                                class="inline-flex items-center px-6 py-2 font-semibold text-sm text-white rounded-md"
                                :class="kunjungan_lapangan === '1' ? 'bg-pink-500 hover:bg-pink-600' :
                                    'bg-green-600 hover:bg-green-700'">
                                <span x-show="kunjungan_lapangan === '0'">Simpan Keputusan Akhir</span>
                                <span x-show="kunjungan_lapangan === '1'">Simpan (Lanjut Kunjungan)</span>
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        @endif
    </div>
@endsection
