@extends('dashboard.layouts.dashboard')
@section('title', 'Administrasi Ajuan')
@section('content')
    <div class="w-full lg:mx-8 mt-6 px-6 py-8 bg-white shadow-md overflow-hidden sm:rounded-lg z-10">
        <form method="POST" action="{{ route('ajuan.store.administrasi') }}" enctype="multipart/form-data"
            id="form-administrasi">
            @csrf
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-8">Administrasi Ajuan</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-6 items-start" x-data="{
                ktp_mode: '{{ $userKtp ? 'claimed' : 'upload' }}',
                ktp_preview: '{{ $userKtp ?? '' }}',
                ktp_filename: '{{ $userKtp ? 'KTP Terdaftar' : 'Pilih file' }}',
                kk_mode: '{{ $userKk ? 'claimed' : 'upload' }}',
                kk_preview: '{{ $userKk ?? '' }}',
                kk_filename: '{{ $userKk ? 'KK Terdaftar' : 'Pilih file' }}'
            }">
                <input type="hidden" name="ktp_mode" accept="image/*,application/pdf" x-bind:value="ktp_mode">
                <input type="hidden" name="kk_mode" accept="image/*,application/pdf" x-bind:value="kk_mode">

                @foreach ($items as $key => $label)
                    @if ($key === 'ktp')
                        <div>
                            <label for="ktp"
                                class="block font-medium text-base md:text-lg text-gray-700 mb-1">{{ $label }}</label>
                            <div class="relative">
                                <div
                                    class="w-full flex items-center px-3 py-2 bg-white text-gray-500 rounded-md shadow-sm border border-gray-300">
                                    <span x-text="ktp_filename" class="truncate"></span>
                                </div>
                                <label for="ktp"
                                    class="absolute inset-y-0 right-0 flex items-center px-4 bg-pink-500 text-white rounded-r-md cursor-pointer hover:bg-pink-600">
                                    <x-untitledui-upload class="w-5 h-5" />
                                </label>
                                <input id="ktp" class="hidden" type="file" name="ktp"
                                    accept="image/*,application/pdf"
                                    @change="ktp_mode = 'upload';
                                           ktp_filename = $event.target.files[0].name;
                                           ktp_preview = URL.createObjectURL($event.target.files[0])">
                            </div>

                            <!-- Preview Box dengan Icon -->
                            <div x-show="ktp_preview" x-transition class="mt-3 relative">
                                <div class="flex items-center gap-2 mb-2 text-gray-600">
                                    <i class="fa-solid fa-eye text-pink-500"></i>
                                    <span class="text-sm font-medium">Preview</span>
                                </div>
                                <div
                                    class="w-full h-40 flex items-center justify-center border-2 border-dashed border-pink-300 rounded-lg bg-gray-50 p-2">
                                    <img :src="ktp_preview" class="max-h-full max-w-full object-contain rounded"
                                        alt="Preview KTP">
                                </div>
                            </div>

                            @if ($userKtp)
                                <button type="button"
                                    @click="ktp_mode = 'claimed'; ktp_filename = 'KTP Terdaftar'; ktp_preview = '{{ $userKtp }}'"
                                    class="mt-2 text-base text-green-600 underline hover:text-green-700 transition transform duration-300"
                                    :class="{ 'font-bold text-lg': ktp_mode == 'claimed' }">
                                    <i class="fa-solid fa-check-circle"></i> Gunakan KTP Terdaftar
                                </button>
                            @endif
                            <x-input-error :messages="$errors->get('ktp')" class="mt-2" />
                        </div>
                    @elseif ($key === 'kk')
                        <div>
                            <label for="kk"
                                class="block font-medium text-base md:text-lg text-gray-700 mb-1">{{ $label }}</label>
                            <div class="relative">
                                <div
                                    class="w-full flex items-center px-3 py-2 bg-white text-gray-500 rounded-md shadow-sm border border-gray-300">
                                    <span x-text="kk_filename" class="truncate"></span>
                                </div>
                                <label for="kk"
                                    class="absolute inset-y-0 right-0 flex items-center px-4 bg-pink-500 text-white rounded-r-md cursor-pointer hover:bg-pink-600">
                                    <x-untitledui-upload class="w-5 h-5" />
                                </label>
                                <input id="kk" class="hidden" type="file" name="kk"
                                    accept="image/*,application/pdf"
                                    @change="kk_mode = 'upload';
                                           kk_filename = $event.target.files[0].name;
                                           kk_preview = URL.createObjectURL($event.target.files[0])">
                            </div>

                            <!-- Preview Box dengan Icon -->
                            <div x-show="kk_preview" x-transition class="mt-3 relative">
                                <div class="flex items-center gap-2 mb-2 text-gray-600">
                                    <i class="fa-solid fa-eye text-pink-500"></i>
                                    <span class="text-sm font-medium">Preview</span>
                                </div>
                                <div
                                    class="w-full h-40 flex items-center justify-center border-2 border-dashed border-pink-300 rounded-lg bg-gray-50 p-2">
                                    <img :src="kk_preview" class="max-h-full max-w-full object-contain rounded"
                                        alt="Preview KK">
                                </div>
                            </div>

                            @if ($userKk)
                                <button type="button"
                                    @click="kk_mode = 'claimed'; kk_filename = 'KK Terdaftar'; kk_preview = '{{ $userKk }}'"
                                    class="mt-2 text-base text-green-600 underline hover:text-green-700 transition transform duration-300"
                                    :class="{ 'font-bold text-lg': kk_mode == 'claimed' }">
                                    <i class="fa-solid fa-check-circle"></i> Gunakan KK Terdaftar
                                </button>
                            @endif
                            <x-input-error :messages="$errors->get('kk')" class="mt-2" />
                        </div>
                    @else
                        <div x-data="{ fileName: '', filePreview: '' }" class="flex flex-col h-full">
                            <label for="{{ $key }}"
                                class="block font-medium text-sm md:text-lg text-gray-700 mb-1 h-full">{{ $label }}</label>
                            <div class="relative">
                                <div
                                    class="w-full flex items-center px-3 py-2 bg-white text-gray-500 rounded-md shadow-sm border border-gray-300">
                                    <span x-text="fileName || 'Pilih file'" class="truncate"></span>
                                </div>
                                <label for="{{ $key }}"
                                    class="absolute inset-y-0 right-0 flex items-center px-4 bg-pink-500 text-white rounded-r-md cursor-pointer hover:bg-pink-600">
                                    <x-untitledui-upload class="w-5 h-5" />
                                </label>
                                <input id="{{ $key }}" class="hidden" type="file" name="{{ $key }}"
                                    accept="image/*,application/pdf"
                                    @change="fileName = $event.target.files[0] ? $event.target.files[0].name : '';
                            filePreview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : ''" />

                            </div>

                            <div x-show="filePreview" x-transition class="mt-3 relative">
                                <div class="flex items-center gap-2 mb-2 text-gray-600">
                                    <i class="fa-solid fa-eye text-pink-500"></i>
                                    <span class="text-sm font-medium">Preview</span>
                                </div>
                                <div
                                    class="w-full h-40 flex items-center justify-center border-2 border-dashed border-pink-300 rounded-lg bg-gray-50 p-2">
                                    <img :src="filePreview" class="max-h-full max-w-full object-contain rounded"
                                        alt="Preview">
                                </div>
                            </div>

                            <x-input-error :messages="$errors->get($key)" class="mt-2" />
                        </div>
                    @endif
                @endforeach
            </div>

            <div class="block mt-6">
                <label class="flex items-center">
                    <input type="checkbox" name="agreement" required
                        class="rounded border-gray-300 text-pink-600 shadow-sm focus:ring-pink-500">
                    <span class="ms-2 text-base md:text-lg text-gray-600">Dengan ini saya ajukan formulir permohonan ini
                        dengan data
                        sebenar-benarnya.</span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-8 space-x-4">
                <a href="{{ url()->previous() }}" id="kembali-administrasi"
                    class="py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm md:text-base font-medium text-gray-700 bg-white hover:bg-gray-50">Kembali</a>
                <button type="button" id="submit-pengajuan"
                    class="inline-flex items-center px-8 py-2 bg-pink-500 border border-transparent rounded-md font-semibold text-sm md:text-base text-white hover:bg-pink-600">
                    Kirim
                </button>
            </div>
        </form>
        @push('scripts')
            <script>
                let isSubmitting = false;

                window.addEventListener('beforeunload', function(e) {
                    if (isSubmitting) return undefined;

                    e.preventDefault();
                    e.returnValue = '';
                    return '';
                });

                document.addEventListener('DOMContentLoaded', function() {

                    const links = document.querySelectorAll('a:not([id="kembali-administrasi"])');

                    links.forEach(link => {
                        const href = link.getAttribute('href');
                        if (!href || href === '#' || href.startsWith('javascript:')) {
                            return;
                        }

                        link.addEventListener('click', function(e) {
                            if (isSubmitting) return;

                            e.preventDefault();
                            const targetUrl = this.href;

                            Swal.fire({
                                title: 'Keluar dari Pengajuan?',
                                text: 'Data yang sudah Anda upload akan hilang jika belum dikirim.',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: '<i class="fa-solid fa-sign-out-alt"></i> Ya, keluar',
                                cancelButtonText: '<i class="fa-solid fa-times"></i> Tetap di sini',
                                confirmButtonColor: '#dc2626',
                                cancelButtonColor: '#6b7280',
                                reverseButtons: true
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    isSubmitting = true;
                                    window.location.href = targetUrl;
                                }
                            });
                        });
                    });

                    const btnKembali = document.getElementById('kembali-administrasi');
                    if (btnKembali) {
                        btnKembali.addEventListener('click', function(e) {
                            e.preventDefault();

                            Swal.fire({
                                title: 'Kembali ke Halaman Sebelumnya?',
                                text: 'Data yang belum disimpan akan hilang.',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: '<i class="fa-solid fa-arrow-left"></i> Ya, kembali',
                                cancelButtonText: '<i class="fa-solid fa-times"></i> Batal',
                                confirmButtonColor: '#ec4899',
                                cancelButtonColor: '#6b7280',
                                reverseButtons: true
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    isSubmitting = true;
                                    window.location.href = this.href;
                                }
                            });
                        });
                    }

                    const btnKirim = document.getElementById('submit-pengajuan');
                    const form = document.getElementById('form-administrasi');

                    if (btnKirim && form) {
                        btnKirim.addEventListener('click', function(e) {
                            e.preventDefault();

                            Swal.fire({
                                title: 'Apakah data yang dikirim sudah benar?',
                                text: 'Pastikan semua dokumen sudah sesuai.',
                                icon: 'question',
                                showCancelButton: true,
                                confirmButtonText: '<i class="fa-solid fa-paper-plane"></i> Ya, Kirim',
                                cancelButtonText: '<i class="fa-solid fa-times"></i> Batal',
                                confirmButtonColor: '#ec4899',
                                cancelButtonColor: '#6b7280',
                                reverseButtons: true
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    isSubmitting = true;
                                    form.submit();
                                }
                            });
                        });
                    }
                });
            </script>
        @endpush
    </div>
@endsection
