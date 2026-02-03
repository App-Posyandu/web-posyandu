@extends('dashboard.layouts.dashboard')
@section('title', 'Dokumen')
@section('content')
    <div class="w-full mx-auto min-h-[70vh]" x-data="{ 
        showModal: false, 
        pdfUrl: '', 
        pdfTitle: '',
        closeModal() {
            this.showModal = false;
            this.pdfUrl = '';
            this.pdfTitle = '';
        }
    }" @keydown.escape.window="closeModal()">

        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dokumen') }}
            </h2>
        </x-slot>

        <div x-show="showModal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/75" x-transition>
            <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl h-5/6 flex flex-col" @click.away="closeModal()">
                <div class="flex justify-between items-center p-4 border-b">
                    <h3 class="text-lg font-semibold" x-text="pdfTitle">Preview Dokumen</h3>
                    <button @click="closeModal()" class="text-gray-400 hover:text-gray-600 text-3xl">&times;</button>
                </div>
                <div class="flex-grow p-4">
                    <iframe x-show="pdfUrl" :src="pdfUrl" width="100%" height="100%" frameborder="0"></iframe>
                </div>
            </div>
        </div>

        <div class="py-12">
            <div class=" w-full mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">

                        {{-- Guidebook Management Container - Only for Admin --}}
                        @if (auth()->user()->role === 'admin')
                            @php
                                $currentGuidebook = \App\Models\BukuSaku::guidebook()->first();
                            @endphp
                            <div class="mb-8 p-6 bg-gradient-to-r from-indigo-50 to-blue-50 border-2 border-indigo-200 rounded-lg">
                                <div class="flex items-start gap-4">
                                    <div class="flex-shrink-0">
                                        <svg class="w-8 h-8 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-grow">
                                        <h3 class="text-lg font-bold text-indigo-900 mb-2">
                                            <i class="bi bi-bookmark-star mr-2"></i>Pengaturan Guidebook Halaman Login
                                        </h3>
                                        <p class="text-sm text-indigo-700 mb-4">
                                            Kelola panduan pengguna yang akan ditampilkan di halaman login. Anda dapat upload, preview, atau menghapus guidebook.
                                        </p>
                                        
                                        @if ($currentGuidebook)
                                            <div class="bg-white rounded-md p-4 border-l-4 border-green-500 mb-4">
                                                <p class="text-xs font-semibold text-gray-600 mb-3">GUIDEBOOK AKTIF SAAT INI:</p>
                                                <div class="flex items-start justify-between gap-4">
                                                    <div class="flex-grow">
                                                        <p class="font-semibold text-gray-900">{{ $currentGuidebook->title }}</p>
                                                        <p class="text-xs text-gray-600">{{ $currentGuidebook->description }}</p>
                                                        <p class="text-xs text-gray-500 mt-2">Diunggah: {{ $currentGuidebook->created_at->format('d M Y H:i') }}</p>
                                                    </div>
                                                    <div class="flex gap-2 flex-wrap justify-end">
                                                        <button
                                                            @click="showModal = true; pdfUrl = '{{ route('buku_saku.stream-file', $currentGuidebook) }}'; pdfTitle = '{{ $currentGuidebook->title }}'"
                                                            class="px-3 py-1 bg-blue-500 hover:bg-blue-600 text-white rounded-md text-xs font-semibold whitespace-nowrap">
                                                            <i class="bi bi-eye mr-1"></i>Preview
                                                        </button>
                                                        <form action="{{ route('buku_saku.remove-guidebook', $currentGuidebook) }}" method="POST" style="display: inline;">
                                                            @csrf
                                                            <button type="submit" 
                                                                class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded-md text-xs font-semibold whitespace-nowrap">
                                                                <i class="bi bi-trash mr-1"></i>Hapus
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        <div class="bg-white rounded-md p-4 border-l-4 {{ $currentGuidebook ? 'border-blue-500' : 'border-yellow-500' }}">
                                            <p class="text-xs font-semibold text-gray-600 mb-3">{{ $currentGuidebook ? 'GANTI' : 'UPLOAD' }} GUIDEBOOK BARU:</p>
                                            <form action="{{ route('buku_saku.upload-guidebook') }}" method="POST" enctype="multipart/form-data" id="guidebookUploadForm">
                                                @csrf
                                                <div class="space-y-3">
                                                    <div>
                                                        <label for="guidebook_file" class="block text-sm font-medium text-gray-700 mb-2">
                                                            Pilih File PDF
                                                        </label>
                                                        <div class="relative">
                                                            <input type="file" id="guidebook_file" name="file" accept="application/pdf" required
                                                                class="block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                                                onchange="updateFileName(this)">
                                                            <p class="text-xs text-gray-500 mt-1">Format: PDF | Ukuran maksimal: 10MB</p>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label for="guidebook_title" class="block text-sm font-medium text-gray-700 mb-2">
                                                            Judul Guidebook
                                                        </label>
                                                        <input type="text" id="guidebook_title" name="title" placeholder="Contoh: Panduan Pengguna SAPA POSYANDU" required
                                                            class="block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                                    </div>
                                                    <div>
                                                        <label for="guidebook_description" class="block text-sm font-medium text-gray-700 mb-2">
                                                            Deskripsi (Opsional)
                                                        </label>
                                                        <textarea id="guidebook_description" name="description" rows="2" placeholder="Deskripsi singkat tentang guidebook ini..."
                                                            class="block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"></textarea>
                                                    </div>
                                                    <div class="flex gap-2 justify-end">
                                                        <button type="button" onclick="document.getElementById('guidebook_file').value = ''; document.getElementById('guidebook_title').value = ''; document.getElementById('guidebook_description').value = '';"
                                                            class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-md text-sm font-semibold">
                                                            Bersihkan
                                                        </button>
                                                        <button type="submit"
                                                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-sm font-semibold">
                                                            <i class="bi bi-upload mr-1"></i>{{ $currentGuidebook ? 'Ganti' : 'Upload' }} Guidebook
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

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
                                @if (!$buku->is_guidebook)
                                <div class="p-4 border rounded-lg flex flex-col sm:flex-row justify-between items-start">
                                    <div class="flex-grow">
                                        <div class="flex items-center gap-2">
                                            <h3 class="text-lg font-semibold text-gray-900">{{ $buku->title }}</h3>
                                            @if ($buku->is_guidebook)
                                                <span class="inline-block bg-blue-200 text-blue-800 text-xs px-2 py-1 rounded-full font-semibold">
                                                    <i class="bi bi-bookmark-fill mr-1"></i>Guidebook
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-gray-600">{{ $buku->description }}</p>
                                        <p class="text-xs text-gray-400 mt-1">
                                            Diunggah oleh: {{ $buku->user->name }} pada
                                            {{ $buku->created_at->format('d M Y') }}
                                        </p>
                                    </div>
                                    <div class="flex-shrink-0 flex gap-2 mt-4 sm:mt-0 flex-wrap">
                                        <button
                                            @click="showModal = true; pdfUrl = '{{ route('buku_saku.stream-file', $buku) }}'; pdfTitle = '{{ $buku->title }}'"
                                            class="px-3 py-1 bg-blue-500 text-white rounded-md text-xs hover:bg-blue-600">
                                            Preview
                                        </button>

                                        <a href="{{ route('buku_saku.stream-file', $buku) }}"
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
                                                id="delete-form-{{ $buku->id }}" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button"
                                                    onclick="confirmDelete('{{ $buku->id }}', '{{ $buku->title }}')"
                                                    class="px-3 py-1 bg-red-500 text-white rounded-md text-xs hover:bg-red-600">
                                                    Hapus
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </div>
                                @endif
                            @empty
                                <p class="text-gray-500">Belum ada dokumen yang diunggah.</p>
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

    @push('scripts')
    <script>
        function confirmDelete(bukuId, bukuTitle) {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                html: `Anda yakin ingin menghapus dokumen <strong>"${bukuTitle}"</strong> secara permanen?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + bukuId).submit();
                }
            });
        }

        function updateFileName(input) {
            if (input.files && input.files[0]) {
                const fileName = input.files[0].name;
                // Extract title from filename (remove .pdf)
                const titleSuggestion = fileName.replace('.pdf', '').replace(/[-_]/g, ' ');
                const titleInput = document.getElementById('guidebook_title');
                if (!titleInput.value) {
                    titleInput.value = titleSuggestion;
                }
            }
        }

        // Handle guidebook form submission with confirmation
        document.getElementById('guidebookUploadForm')?.addEventListener('submit', function(e) {
            const fileInput = document.getElementById('guidebook_file');
            const titleInput = document.getElementById('guidebook_title');
            
            if (!fileInput.files || !fileInput.files[0]) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'File Belum Dipilih',
                    text: 'Silakan pilih file PDF terlebih dahulu.',
                    confirmButtonColor: '#f59e0b'
                });
                return;
            }

            if (!titleInput.value.trim()) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Judul Belum Diisi',
                    text: 'Silakan isi judul guidebook terlebih dahulu.',
                    confirmButtonColor: '#f59e0b'
                });
                return;
            }

            const fileSize = fileInput.files[0].size / 1024 / 1024; // Convert to MB
            if (fileSize > 10) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'File Terlalu Besar',
                    text: 'Ukuran file maksimal adalah 10MB. File Anda: ' + fileSize.toFixed(2) + 'MB',
                    confirmButtonColor: '#ef4444'
                });
                return;
            }
        });
    </script>
    @endpush
@endsection
