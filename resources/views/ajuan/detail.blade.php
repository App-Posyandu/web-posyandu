@extends('dashboard.layouts.dashboard')
@section('title', 'Detail Pengajuan')
@section('content')
    <div class="flex-grow gap-5 flex-col flex items-center justify-center py-12">
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
        @if (auth()->user()->role == 'kader')
            @if ($ajuan->status_pengajuan === 'Diproses' && !$ajuan->sudah_verifikasi)
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl p-8 w-full max-w-4xl" x-data="{ step: 1, kunjungan_lapangan: '0' }">

                    <form method="POST" action="{{ route('ajuan.verify', $ajuan) }}">
                        @csrf
                        @method('PATCH')
                        <div x-show="step === 1" x-transition>
                            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Tahap 1: Verifikasi
                                Dokumen
                            </h2>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div>
                                    <h3 class="font-semibold mb-4 border-b pb-2">Detail Permohonan Diajukan
                                    </h3>
                                    <div class="space-y-3">
                                        @forelse ($ajuan->formulir_items ?? [] as $item)
                                            <label
                                                class="flex items-center justify-between p-3 rounded-md bg-gray-50 border">
                                                <span class="text-sm text-gray-700 pr-4">{{ $item }}</span>
                                                <input type="checkbox" name="verified_formulir_items[]"
                                                    value="{{ $item }}"
                                                    class="h-5 w-5 rounded border-gray-400 text-pink-600 shadow-sm focus:ring-pink-500">
                                            </label>
                                        @empty
                                            <p class="text-gray-500 text-sm">Tidak ada item permohonan yang
                                                dipilih.
                                            </p>
                                        @endforelse
                                        {{-- @foreach ($templateData['formulir_items'] as $item)
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
                                                                <input type="checkbox"
                                                                    class="h-5 w-5 rounded border-gray-300 text-pink-600 shadow-sm focus:ring-pink-500"
                                                                    @checked($isLainnyaChecked)>
                                                                <span
                                                                    class="ms-3 text-sm font-semibold text-gray-700">{{ $item }}</span>
                                                            </label>
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
                                                                @checked(in_array($item, $ajuan->formulir_items ?? []))>
                                                            <span
                                                                class="ms-3 text-sm text-gray-700">{{ $item }}</span>
                                                        </label>
                                                    @endif
                                                @endforeach --}}
                                    </div>
                                </div>

                                <div>
                                    <h3 class="font-semibold mb-4 border-b pb-2">Dokumen Administrasi Terlampir
                                    </h3>
                                    <div class="space-y-3">
                                        @forelse ($ajuan->administrasi_items ?? [] as $key => $path)
                                            @php
                                                $label =
                                                    $templateData['administrasi_items'][$key] ??
                                                    ucfirst(str_replace('_', ' ', $key));
                                            @endphp
                                            <div class="p-3 rounded-md bg-gray-50 border">
                                                <label class="flex items-center justify-between">
                                                    <span class="text-sm text-gray-700">{{ $label }}</span>
                                                    <input type="checkbox"
                                                        name="verified_administrasi_items[{{ $key }}]"
                                                        value="1"
                                                        class="h-5 w-5 rounded border-gray-400 text-pink-600 shadow-sm focus:ring-pink-500">
                                                </label>
                                                <a href="{{ route('ajuan.dokumen.download', ['ajuan' => $ajuan, 'key' => $key]) }}"
                                                    target="_blank" class="text-xs text-blue-600 hover:underline ml-1">
                                                    Lihat/Unduh Dokumen
                                                </a>
                                            </div>
                                        @empty
                                            <p class="text-gray-500 text-sm">Tidak ada dokumen yang diunggah.
                                            </p>
                                        @endforelse
                                        {{-- @foreach ($templateData['administrasi_items'] as $key => $label)
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
                                                @endforeach --}}
                                    </div>
                                </div>
                            </div>
                            <div class="mt-6">
                                <label for="catatan_tolak" class="block font-medium text-sm text-gray-700">Catatan (Wajib
                                    jika
                                    ditolak)</label>
                                <textarea id="catatan_tolak" name="catatan" rows="3"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                    placeholder="Tambahkan catatan jika pengajuan ditolak..."></textarea>
                            </div>
                            <div class="flex items-center justify-end mt-8 space-x-4">
                                <button type="submit" name="tolak_langsung" value="Ditolak"
                                    class="py-2 px-6 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                    Tolak Langsung
                                </button>

                                <button type="button" @click="step = 2"
                                    class="inline-flex items-center px-6 py-2 bg-green-600 text-white font-semibold text-sm rounded-md hover:bg-green-700">
                                    Setujui Verifikasi
                                </button>
                            </div>
                        </div>

                        <div x-show="step === 2" x-transition:enter.duration.500ms>
                            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Tahap 2: Tindak
                                Lanjut &
                                Keputusan</h2>

                            <input type="hidden" name="sudah_verifikasi" value="1">

                            <div class="mt-6 border-t pt-6">
                                <h3 class="font-semibold mb-4 text-gray-700">Perlu Kunjungan Lapangan?</h3>
                                <div class="space-y-3">
                                    <label class="flex items-center p-3 border rounded-md">
                                        <input type="radio" name="kunjungan_lapangan" value="1"
                                            x-model="kunjungan_lapangan" class="h-5 w-5 ...">
                                        <span class="ms-3 text-sm text-gray-700">Ya, perlu kunjungan
                                            lapangan</span>
                                    </label>
                                    <label class="flex items-center p-3 border rounded-md">
                                        <input type="radio" name="kunjungan_lapangan" value="0"
                                            x-model="kunjungan_lapangan" class="h-5 w-5 ...">
                                        <span class="ms-3 text-sm text-gray-700">Tidak, tidak perlu kunjungan
                                            lapangan</span>
                                    </label>
                                </div>
                            </div>

                            <div x-show="kunjungan_lapangan === '0'" class="mt-6">
                                <label for="status" class="block font-medium text-sm text-gray-700">Status
                                    Pengajuan Akhir</label>
                                <select id="status" name="status"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="Disetujui">Setujui Pengajuan</option>
                                    <option value="Ditolak">Tolak Pengajuan</option>
                                </select>
                            </div>

                            <div class="mt-6">
                                <label for="catatan_final" class="block font-medium text-sm text-gray-700">Catatan Akhir
                                    (Opsional)</label>
                                <textarea id="catatan_final" name="catatan" rows="3"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="Tambahkan catatan..."></textarea>
                            </div>

                            <div class="flex items-center justify-end mt-8 space-x-4">
                                <button type="button" @click="step = 1"
                                    class="py-2 px-4 bg-gray-200 text-gray-800 rounded-md text-sm font-semibold hover:bg-gray-300">
                                    Kembali
                                </button>

                                <button type="submit"
                                    class="inline-flex items-center px-6 py-2 bg-pink-500 text-white font-semibold text-sm rounded-md hover:bg-pink-600">
                                    {{-- Teks tombol berubah secara dinamis --}}
                                    <span x-show="kunjungan_lapangan === '0'">Simpan Keputusan Akhir</span>
                                    <span x-show="kunjungan_lapangan === '1'">Simpan (Lanjut Kunjungan)</span>
                                </button>
                            </div>
                        </div>
                        {{-- <div class="mt-8 border-t pt-6">
                                    <h3 class="font-semibold mb-4 text-gray-700">Tindak Lanjut</h3>
                                    <div class="flex flex-col space-y-3">
                                        <label class="flex items-center">
                                            <input type="checkbox" name="sudah_verifikasi" value="1"
                                                @checked(old('sudah_verifikasi', $ajuan->sudah_verifikasi))
                                                class="rounded border-gray-300 text-pink-600 shadow-sm focus:ring-pink-500 h-5 w-5">
                                            <span class="ms-3 text-sm text-gray-700">Sudah Verifikasi</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="kunjungan_lapangan" value="1"
                                                @checked(old('kunjungan_lapangan', $ajuan->kunjungan_lapangan))
                                                class="rounded border-gray-300 text-pink-600 shadow-sm focus:ring-pink-500 h-5 w-5">
                                            <span class="ms-3 text-sm text-gray-700">Perlu Kunjungan Lapangan</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="mt-8 border-t pt-6 space-y-4">
                                    <div>
                                        <label for="catatan"
                                            class="block font-medium text-sm text-gray-700">Catatan</label>
                                        <textarea id="catatan" name="catatan" rows="3" required
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                            placeholder="Tambahkan catatan jika pengajuan ditolak..."></textarea>
                                    </div>
                                    <div>
                                        <label for="status_pengajuan"
                                            class="block font-medium text-sm text-gray-700">Ubah
                                            Status Pengajuan</label>
                                        <select id="status_pengajuan" name="status_pengajuan"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                            <option value="Disetujui">Setujui Pengajuan</option>
                                            <option value="Ditolak">Tolak Pengajuan</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="flex items-center justify-end mt-6">
                                    <button type="submit"
                                        class="inline-flex items-center px-6 py-2 bg-pink-600 text-white font-semibold text-sm rounded-md hover:bg-pink-700">
                                        Verifikasi
                                    </button>
                                </div> --}}
                    </form>
                </div>
            @elseif ($ajuan->status_pengajuan === 'Diproses' && $ajuan->sudah_verifikasi && $ajuan->kunjungan_lapangan)
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl p-8 w-full max-w-4xl">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Tahap 3: Keputusan Pasca Kunjungan
                        Lapangan
                    </h2>
                    <p class="text-center text-sm text-gray-600 mb-6">Pengajuan ini sudah diverifikasi dan kunjungan
                        lapangan
                        telah ditandai. Silakan masukkan keputusan akhir setelah kunjungan dilakukan.</p>

                    <form method="POST" action="{{ route('ajuan.verify', $ajuan) }}">
                        @csrf
                        @method('PATCH')

                        <div class="space-y-4">
                            <div>
                                <label for="finalize_status" class="block font-medium text-sm text-gray-700">Status
                                    Pengajuan
                                    Akhir</label>
                                {{-- Kita gunakan nama 'finalize_status' agar tidak bentrok dengan input 'status' di Formulir A --}}
                                <select id="finalize_status" name="finalize_status"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                    <option value="" disabled selected>Pilih Keputusan...</option>
                                    <option value="Disetujui">Setujui Pengajuan</option>
                                    <option value="Ditolak">Tolak Pengajuan</option>
                                </select>
                            </div>

                            <div>
                                <label for="catatan_final_visit" class="block font-medium text-sm text-gray-700">Catatan
                                    Akhir
                                    (Wajib jika ditolak)</label>
                                <textarea id="catatan_final_visit" name="catatan" rows="3"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                    placeholder="Tambahkan catatan akhir pasca kunjungan..."></textarea>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-8">
                            <button type="submit"
                                class="inline-flex items-center px-6 py-2 bg-pink-500 text-white font-semibold text-sm rounded-md hover:bg-pink-600">
                                Simpan Keputusan Akhir
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        @endif
    </div>
@endsection
