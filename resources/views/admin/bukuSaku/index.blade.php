@extends('admin.layouts.index')
@section('title', 'Buku Saku')
@section('content')
    <div class="min-h-[70vh]">
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Buku Saku') }}
            </h2>
        </x-slot>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                @if (session('success'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg" role="alert">
                        {{ session('success') }}
                    </div>
                @endif

                @if (Auth::user()->role === 'kabid' || Auth::user()->role === 'admin')
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <h2 class="text-2xl font-bold text-gray-800 mb-6">Unggah / Ganti Buku Saku</h2>
                            <form method="POST" action="{{ route('buku-saku.store') }}" enctype="multipart/form-data">
                                @csrf
                                <div class="space-y-4">
                                    <div>
                                        <x-input-label for="title" :value="__('Judul Buku Saku')" />
                                        {{-- Isi value dengan judul lama jika ada --}}
                                        <x-text-input id="title" class="block mt-1 w-full" type="text" name="title"
                                            :value="old('title', $bukuSaku->title ?? '')" required />
                                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-input-label for="description" :value="__('Deskripsi Singkat (Opsional)')" />
                                        <textarea id="description" name="description" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm"
                                            rows="3">{{ old('description', $bukuSaku->description ?? '') }}</textarea>
                                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-input-label for="file" :value="__('File PDF (PDF, Max 10MB)')" />
                                        <input id="file"
                                            class="block mt-1 w-full border border-gray-300 rounded-md p-2" type="file"
                                            name="file" accept="application/pdf" required />
                                        <x-input-error :messages="$errors->get('file')" class="mt-2" />
                                    </div>
                                    <div class="flex justify-end">
                                        <x-primary-button>
                                            {{ __('Unggah & Ganti File') }}
                                        </x-primary-button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">

                        {{-- Cek apakah ada buku saku di database --}}
                        @if ($bukuSaku)
                            <h2 class="text-2xl font-bold text-gray-800 mb-2">{{ $bukuSaku->title }}</h2>
                            <p class="text-sm text-gray-500 mb-4">{{ $bukuSaku->description }}</p>
                            <p class="text-xs text-gray-400 mb-6">
                                Terakhir diperbarui oleh: {{ $bukuSaku->user->name ?? 'N/A' }}
                                ({{ $bukuSaku->updated_at->format('d F Y') }})
                            </p>

                            <div class="flex gap-4 mb-4">
                                <a href="{{ Storage::url($bukuSaku->file_path) }}" download="{{ $bukuSaku->title }}.pdf"
                                    class="px-4 py-2 bg-green-500 text-white rounded-md text-sm font-semibold hover:bg-green-600">
                                    Download PDF
                                </a>
                            </div>

                            <div class="w-full h-[70vh] border rounded-lg overflow-hidden">
                                <iframe src="{{ Storage::url($bukuSaku->file_path) }}" width="100%" height="100%"
                                    frameborder="0">
                                    Browser Anda tidak mendukung preview PDF. Silakan klik "Download PDF".
                                </iframe>
                            </div>
                        @else
                            {{-- Tampilan jika belum ada buku saku sama sekali --}}
                            <p class="text-center text-gray-500 py-10">
                                Buku Saku belum diunggah oleh Kabid.
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
