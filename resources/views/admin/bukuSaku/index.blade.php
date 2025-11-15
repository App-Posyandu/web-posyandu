@extends('dashboard.layouts.dashboard')
@section('title', 'Buku Saku')
@section('content')
    <div class="w-full max-w-7xl mx-auto min-h-[70vh]" x-data="{ showModal: false, pdfUrl: '', pdfTitle: '' }" @keydown.escape.window="showModal = false">

        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Buku Saku') }}
            </h2>
        </x-slot>

        <!-- Modal -->
        <div x-show="showModal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/75" x-transition>
            <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl h-5/6 flex flex-col" @click.away="showModal = false">
                <div class="flex justify-between items-center p-4 border-b">
                    <h3 class="text-lg font-semibold" x-text="pdfTitle">Preview Dokumen</h3>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 text-3xl">&times;</button>
                </div>
                <div class="flex-grow p-4">
                    <iframe :src="pdfUrl" width="100%" height="100%" frameborder="0"></iframe>
                </div>
            </div>
        </div>

        <!-- Daftar Buku -->
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">

                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold text-gray-800">Daftar Dokumen</h2>
                            @can('create', \App\Models\BukuSaku::class)
                                <a href="{{ route('buku_saku.create') }}"
                                    class="px-4 py-2 bg-pink-500 text-white rounded-md text-sm font-semibold hover:bg-pink-600">
                                    <i class="bi bi-plus-circle-fill mr-2"></i>Unggah Baru
                                </a>
                            @endcan
                        </div>

                        @include('components.all-notifications')

                        <div class="space-y-4">
                            @forelse ($bukuSaku as $buku)
                                <div class="p-4 border rounded-lg flex flex-col sm:flex-row justify-between items-start">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">{{ $buku->title }}</h3>
                                        <p class="text-sm text-gray-600">{{ $buku->description }}</p>
                                        <p class="text-xs text-gray-400 mt-1">
                                            Diunggah oleh: {{ $buku->user->name }} pada
                                            {{ $buku->created_at->format('d M Y') }}
                                        </p>
                                    </div>
                                    <div class="flex-shrink-0 flex gap-2 mt-4 sm:mt-0">
                                        <button
                                            @click="showModal = true; pdfUrl = '{{ route('buku_saku.stream', $buku) }}'; pdfTitle = '{{ $buku->title }}'"
                                            class="px-3 py-1 bg-blue-500 text-white rounded-md text-xs hover:bg-blue-600">
                                            Preview
                                        </button>

                                        <a href="{{ Illuminate\Support\Facades\Storage::url($buku->file_path) }}"
                                            download="{{ $buku->title }}.pdf"
                                            class="px-3 py-1 bg-green-500 text-white rounded-md text-xs hover:bg-green-600">
                                            Download
                                        </a>

                                        @can('update', $buku)
                                            <a href="{{ route('buku_saku.edit', $buku) }}"
                                                class="px-3 py-1 bg-yellow-500 text-white rounded-md text-xs hover:bg-yellow-600">
                                                Edit
                                            </a>
                                        @endcan

                                        @can('delete', $buku)
                                            <form action="{{ route('buku_saku.destroy', $buku) }}" method="POST"
                                                onsubmit="return confirm('Anda yakin ingin menghapus file ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="px-3 py-1 bg-red-500 text-white rounded-md text-xs hover:bg-red-600">
                                                    Hapus
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </div>
                            @empty
                                <p class="text-gray-500">Belum ada buku saku yang diunggah.</p>
                            @endforelse
                        </div>

                        <div class="mt-6">
                            {{ $bukuSaku->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
