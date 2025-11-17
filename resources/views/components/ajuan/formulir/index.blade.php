@extends('dashboard.layouts.dashboard')

@section('title', 'Formulir Pengajuan')

@section('content')

    <div class="w-full sm:max-w-3xl mt-6 px-6 py-8 bg-white shadow-md overflow-hidden sm:rounded-lg z-10"
        x-data="{
            selectedBidang: '{{ $bidang->slug }}',
            items: @js($items),
            isLoading: false,
            isKader: {{ auth()->user()->role === 'kader' ? 'true' : 'false' }},

            async changeBidang(slug) {
                if (this.isKader) return; // Kader tidak bisa ganti bidang

                this.isLoading = true;
                try {
                    const response = await fetch(`/ajuan/get-items/${slug}`);
                    const data = await response.json();

                    if (data.success) {
                        this.items = data.items;
                        this.selectedBidang = slug;

                        // Reset checkboxes
                        document.querySelectorAll('input[name=\'permohonan_items[]\']').forEach(cb => cb.checked = false);
                        document.getElementById('deskripsi_pengajuan').value = '';
                    }
                } catch (error) {
                    console.error('Error loading items:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal memuat data',
                        text: 'Terjadi kesalahan saat memuat item bidang'
                    });
                } finally {
                    this.isLoading = false;
                }
            }
        }">

        <form method="POST" action="{{ route('ajuan.store.permohonan') }}">
            @csrf

            @php
                $user = auth()->user();
                $isKader = $user->role === 'kader';
            @endphp

            @if ($isKader)
                <h2 class="text-2xl font-bold text-center text-gray-800 mb-8">
                    Formulir Permohonan
                    <span class="text-pink-600">{{ $user->bidang->nama_bidang }}</span>
                </h2>
            @else
                <h2 class="text-2xl font-bold text-center text-gray-800 mb-8">Formulir Permohonan</h2>
            @endif

            <div class="mb-6">
                @if ($isKader)
                    {{-- Hidden input untuk Kader --}}
                    <input type="hidden" name="bidang_pelayanan" value="{{ $user->bidang->slug }}">
                @else
                    {{-- Dropdown untuk Masyarakat - No Reload --}}
                    <div class="relative">
                        <select id="bidang_pelayanan" name="bidang_pelayanan" x-model="selectedBidang"
                            @change="changeBidang($event.target.value)"
                            class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-pink-500 focus:ring-pink-500"
                            :disabled="isLoading">
                            @foreach ($allBidangs as $itemBidang)
                                <option value="{{ $itemBidang->slug }}">
                                    {{ $itemBidang->nama_bidang }}
                                </option>
                            @endforeach
                        </select>

                        {{-- Loading Indicator --}}
                        <div x-show="isLoading"
                            class="absolute inset-0 bg-white/80 flex items-center justify-center rounded-md">
                            <div class="flex items-center space-x-2">
                                <div
                                    class="w-4 h-4 border-2 border-pink-500 border-t-transparent rounded-full animate-spin">
                                </div>
                                <span class="text-sm text-gray-600">Memuat...</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Items Container with Loading State --}}
            <div class="space-y-4" :class="isLoading ? 'opacity-50 pointer-events-none' : ''">
                <template x-for="(item, index) in items" :key="index">
                    <div>
                        {{-- Item "Lainnya..." dengan text input --}}
                        <template x-if="item === 'Lainnya...'">
                            <div x-data="{ checked: false }" class="p-4 border rounded-md">
                                <label class="flex items-center">
                                    <input type="checkbox" x-model="checked" name="permohonan_items[]"
                                        :value="item"
                                        class="rounded border-gray-300 text-pink-600 shadow-sm focus:ring-pink-500">
                                    <span class="ms-3 text-gray-700 text-base md:text-lg font-semibold"
                                        x-text="item"></span>
                                </label>
                                <div x-show="checked" x-transition class="mt-2">
                                    <input type="text" name="lainnya_text"
                                        class="block w-full border-gray-300 focus:border-pink-500 focus:ring-pink-500 rounded-md shadow-sm"
                                        placeholder="Silakan ketik permohonan Anda..." :disabled="!checked">
                                </div>
                            </div>
                        </template>

                        {{-- Item biasa (checkbox) --}}
                        <template x-if="item !== 'Lainnya...'">
                            <label class="flex items-center">
                                <input type="checkbox" name="permohonan_items[]" :value="item"
                                    class="rounded border-gray-300 text-pink-600 shadow-sm focus:ring-pink-500">
                                <span class="ms-3 text-gray-700" x-text="item"></span>
                            </label>
                        </template>
                    </div>
                </template>

                {{-- Deskripsi Pengajuan --}}
                <div class="mt-6">
                    <label for="deskripsi_pengajuan" class="block font-medium text-lg md:text-xl text-gray-700">
                        Deskripsi Pengajuan
                    </label>
                    <textarea id="deskripsi_pengajuan" name="deskripsi_pengajuan"
                        class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        rows="4" placeholder="Jelaskan secara singkat tujuan atau detail pengajuan Anda di sini...">{{ old('deskripsi_pengajuan') }}</textarea>
                    <x-input-error :messages="$errors->get('deskripsi_pengajuan')" class="mt-2" />
                </div>
            </div>

            <div class="flex items-center justify-end mt-8 space-x-4">
                <a href="{{ route('dashboard') }}" id="batal-pengajuan"
                    class="py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm md:text-base font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Batalkan Pengajuan
                </a>
                <button type="submit" :disabled="isLoading"
                    class="inline-flex items-center px-8 py-2 bg-pink-500 border border-transparent rounded-md font-semibold text-sm md:text-base text-white hover:bg-pink-600 disabled:opacity-50 disabled:cursor-not-allowed">
                    Selanjutnya
                </button>
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
