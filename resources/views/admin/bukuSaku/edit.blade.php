<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Ubah Buku Saku') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8 text-gray-900">
                    <form method="POST" action="{{ route('buku-saku.update', $bukuSaku) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')

                        <h2 class="text-2xl font-bold text-center text-gray-800 mb-8">Formulir Ubah Buku Saku</h2>

                        <div class="space-y-4">
                            <div>
                                <x-input-label for="title" :value="__('Judul Buku Saku')" />
                                <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title', $bukuSaku->title)" required autofocus />
                                <x-input-error :messages="$errors->get('title')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="description" :value="__('Deskripsi Singkat (Opsional)')" />
                                <textarea id="description" name="description" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" rows="3">{{ old('description', $bukuSaku->description) }}</textarea>
                                <x-input-error :messages="$errors->get('description')" class="mt-2" />
                            </div>

                            <div x-data="{
                                // URL awal diisi dengan file yang sudah ada
                                previewUrl: '{{ Storage::url($bukuSaku->file_path) }}'
                            }">
                                <x-input-label for="file" :value="__('File PDF (Opsional)')" />

                                <div class="mt-2 w-full h-80 border-2 border-dashed border-gray-300 rounded-md">
                                    <iframe x-show="previewUrl" :src="previewUrl" width="100%" height="100%" class="rounded-md"></iframe>
                                    <div x-show="!previewUrl" class="p-4 text-center text-gray-500">
                                        Tidak ada file PDF.
                                    </div>
                                </div>

                                <div class="mt-2">
                                    <label for="file" class="text-sm text-gray-700">Pilih file baru untuk mengganti (biarkan kosong jika tidak ingin diubah):</label>
                                    <input id="file" class="block mt-1 w-full border border-gray-300 rounded-md p-2" type="file" name="file" accept="application/pdf"
                                           @change="
                                               // Jika ada file baru, hapus preview lama (jika blob)
                                               if (previewUrl.startsWith('blob:')) { URL.revokeObjectURL(previewUrl); }
                                               // Tampilkan preview file PDF yang baru dipilih
                                               previewUrl = event.target.files.length ? URL.createObjectURL(event.target.files[0]) : '';
                                           " />
                                </div>
                                <x-input-error :messages="$errors->get('file')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-8 gap-4">
                            <a href="{{ route('buku-saku.index') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md text-sm font-semibold hover:bg-gray-300">Batal</a>
                            <x-primary-button>
                                {{ __('Simpan Perubahan') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
