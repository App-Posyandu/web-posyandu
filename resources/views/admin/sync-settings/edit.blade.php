@extends('dashboard.layouts.dashboard')
@section('title', 'Pengaturan Sinkronisasi')
@section('content')
<div class="w-full mx-auto sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl p-4 sm:p-6 lg:p-8">

        <div class="px-4 sm:px-0 mb-4">
            @include('components.all-notifications')
        </div>

        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <i class="bi bi-arrow-repeat text-indigo-600"></i> Pengaturan Sinkronisasi
            </h2>
            <p class="text-sm text-gray-500 mt-1">
                Aktifkan untuk mengizinkan operator-desa di kabupaten/kota terkait melakukan sinkronisasi username kader.
            </p>
        </div>

        @if ($isSuperAdmin)
            <div class="mb-6 max-w-2xl">
                <label class="block font-semibold text-gray-800 mb-1">Pilih Kabupaten/Kota</label>
                <select onchange="window.location.href='{{ route('admin.sync-settings.edit') }}?kabupaten=' + encodeURIComponent(this.value)"
                    class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    @forelse ($kabupatenOptions as $opt)
                        <option value="{{ $opt }}" {{ $opt === $kabupaten ? 'selected' : '' }}>{{ $opt }}</option>
                    @empty
                        <option value="">(Belum ada operator-desa)</option>
                    @endforelse
                </select>
            </div>
        @else
            <p class="mb-6 text-sm text-gray-600">Kabupaten/Kota: <strong>{{ $kabupaten }}</strong></p>
        @endif

        @if ($kabupaten === '')
            <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg text-amber-800 text-sm max-w-2xl">
                Belum ada operator-desa yang bisa diatur. Pastikan sudah ada akun operator-desa di suatu kabupaten/kota.
            </div>
        @else
        <form method="POST" action="{{ route('admin.sync-settings.update') }}" class="max-w-2xl">
            @csrf
            @method('PUT')
            @if ($isSuperAdmin)
                <input type="hidden" name="kabupaten" value="{{ $kabupaten }}">
            @endif

            {{-- Toggle aktivasi --}}
            <div class="mb-6 p-4 border border-gray-200 rounded-lg flex items-start justify-between gap-4">
                <div>
                    <label for="is_active" class="font-semibold text-gray-800">Aktifkan Sinkronisasi</label>
                    <p class="text-xs text-gray-500 mt-1">
                        Jika aktif, operator-desa akan mendapat pemberitahuan dan tombol Sinkronisasi.
                        Mengaktifkan ulang akan me-reset status menjadi "belum sinkron" untuk semua operator-desa.
                    </p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer mt-1">
                    <input type="checkbox" id="is_active" name="is_active" value="1"
                        class="sr-only peer" {{ optional($directive)->is_active ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-gray-300 rounded-full peer peer-checked:bg-indigo-600
                        peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-0.5
                        after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                </label>
            </div>

            {{-- Pesan broadcast --}}
            <div class="mb-6">
                <label for="message" class="block font-semibold text-gray-800 mb-1">Pesan Pemberitahuan (Broadcast)</label>
                <textarea id="message" name="message" rows="4" maxlength="1000"
                    class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Contoh: Mohon lakukan sinkronisasi data kader agar username mengikuti format terbaru.">{{ old('message', optional($directive)->message) }}</textarea>
                <p class="text-xs text-gray-500 mt-1">Pesan ini akan tampil sebagai notifikasi di dashboard operator-desa.</p>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit"
                    class="px-5 py-2 bg-indigo-600 text-white rounded-md text-sm font-semibold hover:bg-indigo-700 transition">
                    <i class="bi bi-save mr-1"></i> Simpan Pengaturan
                </button>
                @if (optional($directive)->is_active)
                    <span class="inline-flex items-center gap-1 text-sm text-green-700 bg-green-50 px-3 py-1 rounded-full border border-green-200">
                        <i class="bi bi-check-circle-fill"></i> Sedang Aktif
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 text-sm text-gray-600 bg-gray-50 px-3 py-1 rounded-full border border-gray-200">
                        <i class="bi bi-slash-circle"></i> Nonaktif
                    </span>
                @endif
            </div>
        </form>

        {{-- Status operator-desa --}}
        <div class="mt-8 max-w-2xl">
            <h3 class="font-semibold text-gray-800 mb-3">Status Operator-Desa ({{ $operators->count() }})</h3>
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="text-left px-4 py-2">Nama</th>
                            <th class="text-left px-4 py-2">Desa</th>
                            <th class="text-left px-4 py-2">Status Sinkron</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($operators as $op)
                            @php
                                $done = $directive && $directive->activated_at && $op->sync_done_at
                                    && $op->sync_done_at->gte($directive->activated_at);
                            @endphp
                            <tr>
                                <td class="px-4 py-2 text-gray-800">{{ $op->name }}</td>
                                <td class="px-4 py-2 text-gray-600">{{ $op->desa ?? '-' }}</td>
                                <td class="px-4 py-2">
                                    @if (!$directive || !$directive->is_active)
                                        <span class="text-gray-400">—</span>
                                    @elseif ($done)
                                        <span class="text-green-700"><i class="bi bi-check-circle-fill"></i> Sudah sinkron</span>
                                    @else
                                        <span class="text-amber-600"><i class="bi bi-clock"></i> Belum sinkron</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-4 text-center text-gray-500">Belum ada operator-desa di kabupaten ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection
