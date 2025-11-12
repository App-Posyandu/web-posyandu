@include('layouts.partials.header-new')
<x-guest-layout>
    <form method="POST" action="{{ route('ajuan.store.permohonan') }}">
        @csrf
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-8">Formulir Permohonan</h2>

        <div class="mb-6">
            <label for="bidang_pelayanan" class="block font-medium text-base md:text-xl text-gray-700">Bidang
                Pelayanan</label>

            @php
                $user = auth()->user();
                $isKader = $user->role === 'kader';
            @endphp
            <select id="bidang_pelayanan" onchange="if(!this.disabled) window.location.href=this.value;"
                class="block mt-1 w-full border-gray-300 rounded-md shadow-sm
                           @if ($isKader) bg-gray-100 cursor-not-allowed @endif"
                @if ($isKader) disabled @endif>

                @if ($isKader && $user->bidang)
                    {{-- Kader hanya akan melihat bidang mereka sendiri --}}
                    <option value="{{ route('ajuan.create', $user->bidang->slug) }}" selected>
                        {{ $user->bidang->nama_bidang }}
                    </option>
                @else
                    {{-- Admin/Masyarakat bisa melihat semua bidang --}}
                    @foreach ($allBidangs as $itemBidang)
                        <option value="{{ route('ajuan.create', $itemBidang->slug) }}" @selected($bidang->slug == $itemBidang->slug)>
                            {{ $itemBidang->nama_bidang }}
                        </option>
                    @endforeach
                @endif
            </select>

            @if ($isKader)
                <p class="mt-1 text-xs text-gray-500">Anda hanya dapat membuat pengajuan untuk bidang tugas Anda.</p>
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
            <a href="{{ route('dashboard') }}"
                class="py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm md:text-base font-medium text-gray-700 bg-white hover:bg-gray-50">Kembali</a>
            <button type="submit"
                class="inline-flex items-center px-8 py-2 bg-pink-500 border border-transparent rounded-md font-semibold text-sm md:text-base text-white hover:bg-pink-600">Selanjutnya</button>
        </div>
    </form>
</x-guest-layout>
