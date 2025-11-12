@extends('dashboard.layouts.dashboard')
@section('title', 'Unggah Buku Saku')
@section('content')
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Unggah Buku Saku Baru') }}
        </h2>
    </x-slot>

    <div class="py-12 w-full">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-8 text-gray-900">
                    <form method="POST" action="{{ route('buku_saku.store') }}" enctype="multipart/form-data">
                        @csrf
                        <h2 class="text-2xl font-bold text-center text-gray-800 mb-8">Formulir Buku Saku Baru</h2>

                        <div class="space-y-4">
                            <div>
                                <x-input-label for="title" :value="__('Judul Buku Saku')" />
                                <x-text-input id="title" class="block mt-1 w-full" type="text" name="title"
                                    :value="old('title')" required autofocus />
                                <x-input-error :messages="$errors->get('title')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="description" :value="__('Deskripsi Singkat (Opsional)')" />
                                <textarea id="description" name="description" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm"
                                    rows="3">{{ old('description') }}</textarea>
                                <x-input-error :messages="$errors->get('description')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="file" :value="__('File PDF')" />
                                <input id="file" class="block mt-1 w-full border border-gray-300 rounded-md p-2"
                                    type="file" name="file" accept="application/pdf" required />
                                <x-input-error :messages="$errors->get('file')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-8 gap-4">
                            <a href="{{ route('buku_saku.index') }}"
                                class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md text-sm font-semibold hover:bg-gray-300">Batal</a>
                            <x-primary-button>
                                {{ __('Simpan') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
