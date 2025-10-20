<x-guest-layout> {{-- Atau layout utama Anda --}}
    <form method="POST" action="{{ route('ajuan.update', $ajuan) }}" enctype="multipart/form-data">
        @csrf
        @method('PATCH')

        <h2 class="text-2xl font-bold text-center text-gray-800 mb-8">Ubah Pengajuan</h2>

        <div class="space-y-4">
            @foreach ($templateData['formulir_items'] as $item)
                @if ($item === 'Lainnya...')
                    @php
                        $lainnyaItem = collect($ajuan->formulir_items ?? [])->first(
                            fn($i) => str_starts_with($i, 'Lainnya: '),
                        );
                        $isLainnyaChecked = !is_null($lainnyaItem);
                        $lainnyaTextValue = $isLainnyaChecked ? str_replace('Lainnya: ', '', $lainnyaItem) : '';
                    @endphp

                    <div x-data="{ checked: {{ $isLainnyaChecked ? 'true' : 'false' }} }" class="p-4 border rounded-md">
                        <label class="flex items-center">
                            <input type="checkbox" x-model="checked" name="permohonan_items[]" value="Lainnya..."
                                class="rounded border-gray-300 text-pink-600 shadow-sm focus:ring-pink-500">
                            <span class="ms-3 text-gray-700 font-semibold">{{ $item }}</span>
                        </label>
                        <div x-show="checked" x-transition class="mt-2">
                            <x-text-input type="text" name="lainnya_text" class="block w-full"
                                placeholder="Silakan ketik permohonan Anda..."
                                value="{{ old('lainnya_text', $lainnyaTextValue) }}" x-bind:disabled="!checked" />
                        </div>
                    </div>

                @else
                    <label class="flex items-center">
                        <input type="checkbox" name="permohonan_items[]" value="{{ $item }}"
                            @checked(in_array($item, old('permohonan_items', $ajuan->formulir_items ?? []))) class="rounded border-gray-300 text-pink-600 ...">
                        <span class="ms-3 text-gray-700">{{ $item }}</span>
                    </label>
                @endif
            @endforeach
        </div>

        {{-- Deskripsi (sudah terisi data lama) --}}
        <div class="mt-6">
            <label for="deskripsi_pengajuan" class="block font-medium text-sm text-gray-700">Deskripsi Pengajuan</label>
            <textarea name="deskripsi_pengajuan" rows="4"
                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                required>{{ old('deskripsi_pengajuan', $ajuan->deskripsi_pengajuan) }}</textarea>
        </div>

        {{-- Administrasi (sudah ada preview) --}}
        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-8">
            @foreach ($templateData['administrasi_items'] as $key => $label)
                <div x-data="{ previewUrl: '{{ isset($ajuan->administrasi_items[$key]) ? Storage::url($ajuan->administrasi_items[$key]) : '' }}' }">
                    <h3 class="font-semibold mb-2">{{ $label }}</h3>
                    <div class="mt-2 w-full h-32 flex items-center justify-center border-2 ...">
                        <img x-show="previewUrl" :src="previewUrl" class="max-h-full ...">
                        <span x-show="!previewUrl" class="text-gray-400">Belum diunggah</span>
                    </div>
                    <input type="file" name="{{ $key }}" class="mt-2"
                        @change="previewUrl = URL.createObjectURL($event.target.files[0])">
                </div>
            @endforeach
        </div>

        <div class="flex items-center justify-end mt-8 space-x-4">
            <a href="{{ route('ajuan.show', $ajuan) }}"
                class="py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Batal</a>
            <button type="submit"
                class="inline-flex items-center px-8 py-2 bg-pink-500 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-pink-600">Simpan
                Perubahan & Ajukan Kembali</button>
        </div>
    </form>
</x-guest-layout>
