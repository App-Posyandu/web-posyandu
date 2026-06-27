@extends('dashboard.layouts.dashboard')
@section('title', 'Administrasi Ajuan')
@section('content')
    <div class="w-full lg:mx-8 mt-6 px-6 py-8 bg-white shadow-md overflow-hidden sm:rounded-lg z-10">
        <form method="POST" action="{{ route('ajuan.store.administrasi') }}" enctype="multipart/form-data"
            id="form-administrasi">
            @csrf
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-2">Administrasi Ajuan</h2>
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded-lg">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-blue-500 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd" />
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-blue-800 mb-1">Ketentuan Upload Dokumen:</p>
                        <ul class="text-xs text-blue-700 space-y-1">
                            <li>• Format file: <strong>JPG, JPEG, PNG</strong></li>
                            <li>• Ukuran maksimal: <strong>2 MB (2048 KB)</strong></li>
                            <li>• Pastikan dokumen terlihat jelas dan tidak buram</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-6 items-start" x-data="{
                ktp_mode: '{{ $userKtp ? 'claimed' : 'upload' }}',
                ktp_preview: '{{ $userKtp ?? '' }}',
                ktp_filename: '{{ $userKtp ? 'KTP Terdaftar' : 'Pilih file' }}',
                ktp_size: 0,
                kk_mode: '{{ $userKk ? 'claimed' : 'upload' }}',
                kk_preview: '{{ $userKk ?? '' }}',
                kk_filename: '{{ $userKk ? 'KK Terdaftar' : 'Pilih file' }}',
                kk_size: 0,
                formatSize(bytes) {
                    if (bytes === 0) return '0 Bytes';
                    const k = 1024;
                    const sizes = ['Bytes', 'KB', 'MB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
                }
            }">
                <input type="hidden" name="ktp_mode" accept="image/*,application/pdf" x-bind:value="ktp_mode">
                <input type="hidden" name="kk_mode" accept="image/*,application/pdf" x-bind:value="kk_mode">

                @foreach ($items as $key => $label)
                    @if ($key === 'ktp')
                        <div>
                            <label for="ktp" class="block font-medium text-base md:text-lg text-gray-700 mb-1">
                                {{ $label }}<span class="text-red-600">*</span>
                            </label>
                            <p class="text-xs text-gray-500 mb-2">
                                <i class="fa-solid fa-info-circle text-blue-500"></i>
                                Format: JPG/JPEG/PNG • Max: 2 MB
                            </p>
                            <div class="relative">
                                <div
                                    class="w-full flex items-center px-3 py-2 bg-white text-gray-500 rounded-md shadow-sm border border-gray-300">
                                    <span x-text="ktp_filename" class="truncate flex-1"></span>
                                    <span x-show="ktp_size > 0" x-text="'(' + formatSize(ktp_size) + ')'"
                                        class="text-xs ml-2"></span>
                                </div>
                                <label for="ktp"
                                    class="absolute inset-y-0 right-0 flex items-center px-4 bg-pink-500 text-white rounded-r-md cursor-pointer hover:bg-pink-600">
                                    <x-untitledui-upload class="w-5 h-5" />
                                </label>
                                <input id="ktp" class="hidden" type="file" name="ktp"
                                    accept="image/jpeg,image/jpg,image/png" capture="environment"
                                    @change="
                                        let file = $event.target.files[0];
                                        if (file) {
                                            if (file.size > 2048000) {
                                                Swal.fire({
                                                    title: 'Mengkompresi File...',
                                                    text: 'Mohon tunggu sebentar',
                                                    allowOutsideClick: false,
                                                    didOpen: () => Swal.showLoading()
                                                });
                                                try {
                                                    file = await compressImage(file, 2);
                                                    const dataTransfer = new DataTransfer();
                                                    dataTransfer.items.add(file);
                                                    $event.target.files = dataTransfer.files;
                                                } catch (e) {
                                                    console.error('Compression failed', e);
                                                }
                                                Swal.close();
                                            }
                                            if (file.size > 2048000) {
                                                alert('Ukuran file masih terlalu besar meskipun sudah dikompresi! Maksimal 2 MB.');
                                                $event.target.value = '';
                                                return;
                                            }
                                            ktp_mode = 'upload';
                                            ktp_filename = file.name;
                                            ktp_size = file.size;
                                            ktp_preview = URL.createObjectURL(file);
                                        }
                                    ">
                            </div>

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
                                <div x-data="{ ktpToast: false }">
                                    <button type="button"
                                        @click="ktp_mode = 'claimed'; ktp_filename = 'KTP Terdaftar'; ktp_preview = '{{ $userKtp }}'; ktp_size = 0; ktpToast = true; setTimeout(() => ktpToast = false, 3000)"
                                        class="mt-2 text-base text-green-600 underline hover:text-green-700 transition transform duration-300"
                                        :class="{ 'font-bold text-lg': ktp_mode == 'claimed' }">
                                        <i class="fa-solid fa-check-circle"></i> Gunakan KTP Terdaftar
                                    </button>
                                    <div x-show="ktpToast" x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="opacity-0 translate-y-1"
                                        x-transition:enter-end="opacity-100 translate-y-0"
                                        x-transition:leave="transition ease-in duration-200"
                                        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                                        class="mt-2 flex items-center gap-2 px-3 py-2 bg-green-50 border border-green-300 text-green-700 text-sm rounded-lg">
                                        <i class="fa-solid fa-circle-check text-green-500"></i>
                                        <span>KTP terdaftar berhasil digunakan!</span>
                                    </div>
                                </div>
                            @endif
                            <x-input-error :messages="$errors->get('ktp')" class="mt-2" />
                        </div>
                    @elseif ($key === 'kk')
                        <div>
                            <label for="kk" class="block font-medium text-base md:text-lg text-gray-700 mb-1">
                                {{ $label }}<span class="text-red-600">*</span>
                            </label>
                            <p class="text-xs text-gray-500 mb-2">
                                <i class="fa-solid fa-info-circle text-blue-500"></i>
                                Format: JPG/JPEG/PNG • Max: 2 MB
                            </p>
                            <div class="relative">
                                <div
                                    class="w-full flex items-center px-3 py-2 bg-white text-gray-500 rounded-md shadow-sm border border-gray-300">
                                    <span x-text="kk_filename" class="truncate flex-1"></span>
                                    <span x-show="kk_size > 0" x-text="'(' + formatSize(kk_size) + ')'"
                                        class="text-xs ml-2"></span>
                                </div>
                                <label for="kk"
                                    class="absolute inset-y-0 right-0 flex items-center px-4 bg-pink-500 text-white rounded-r-md cursor-pointer hover:bg-pink-600">
                                    <x-untitledui-upload class="w-5 h-5" />
                                </label>
                                <input id="kk" class="hidden" type="file" name="kk"
                                    accept="image/jpeg,image/jpg,image/png" capture="environment"
                                    @change="
                                        let file = $event.target.files[0];
                                        if (file) {
                                            if (file.size > 2048000) {
                                                Swal.fire({
                                                    title: 'Mengkompresi File...',
                                                    text: 'Mohon tunggu sebentar',
                                                    allowOutsideClick: false,
                                                    didOpen: () => Swal.showLoading()
                                                });
                                                try {
                                                    file = await compressImage(file, 2);
                                                    const dataTransfer = new DataTransfer();
                                                    dataTransfer.items.add(file);
                                                    $event.target.files = dataTransfer.files;
                                                } catch (e) {
                                                    console.error('Compression failed', e);
                                                }
                                                Swal.close();
                                            }
                                            if (file.size > 2048000) {
                                                alert('Ukuran file masih terlalu besar meskipun sudah dikompresi! Maksimal 2 MB.');
                                                $event.target.value = '';
                                                return;
                                            }
                                            kk_mode = 'upload';
                                            kk_filename = file.name;
                                            kk_size = file.size;
                                            kk_preview = URL.createObjectURL(file);
                                        }
                                    ">
                            </div>

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
                                <div x-data="{ kkToast: false }">
                                    <button type="button"
                                        @click="kk_mode = 'claimed'; kk_filename = 'KK Terdaftar'; kk_preview = '{{ $userKk }}'; kk_size = 0; kkToast = true; setTimeout(() => kkToast = false, 3000)"
                                        class="mt-2 text-base text-green-600 underline hover:text-green-700 transition transform duration-300"
                                        :class="{ 'font-bold text-lg': kk_mode == 'claimed' }">
                                        <i class="fa-solid fa-check-circle"></i> Gunakan KK Terdaftar
                                    </button>
                                    <div x-show="kkToast" x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="opacity-0 translate-y-1"
                                        x-transition:enter-end="opacity-100 translate-y-0"
                                        x-transition:leave="transition ease-in duration-200"
                                        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                                        class="mt-2 flex items-center gap-2 px-3 py-2 bg-green-50 border border-green-300 text-green-700 text-sm rounded-lg">
                                        <i class="fa-solid fa-circle-check text-green-500"></i>
                                        <span>KK terdaftar berhasil digunakan!</span>
                                    </div>
                                </div>
                            @endif
                            <x-input-error :messages="$errors->get('kk')" class="mt-2" />
                        </div>
                    @else
                        <div x-data="{ fileName: '', filePreview: '', fileSize: 0 }" class="flex flex-col h-full">
                            <label for="{{ $key }}"
                                class="block font-medium text-sm md:text-lg text-gray-700 mb-1 h-full">
                                {{ $label }}<span class="text-red-600">*</span>
                            </label>
                            <p class="text-xs text-gray-500 mb-2">
                                <i class="fa-solid fa-info-circle text-blue-500"></i>
                                Format: JPG/JPEG/PNG • Max: 2 MB
                            </p>
                            <div class="relative">
                                <div
                                    class="w-full flex items-center px-3 py-2 bg-white text-gray-500 rounded-md shadow-sm border border-gray-300">
                                    <span x-text="fileName || 'Pilih file'" class="truncate flex-1"></span>
                                    <span x-show="fileSize > 0" x-text="'(' + (fileSize / 1024).toFixed(0) + ' KB)'"
                                        class="text-xs ml-2"></span>
                                </div>
                                <label for="{{ $key }}"
                                    class="absolute inset-y-0 right-0 flex items-center px-4 bg-pink-500 text-white rounded-r-md cursor-pointer hover:bg-pink-600">
                                    <x-untitledui-upload class="w-5 h-5" />
                                </label>
                                <input id="{{ $key }}" class="hidden" type="file"
                                    name="{{ $key }}" accept="image/jpeg,image/jpg,image/png" capture="environment"
                                    @change="
                                        let file = $event.target.files[0];
                                        if (file) {
                                            if (file.size > 2048000) {
                                                Swal.fire({
                                                    title: 'Mengkompresi File...',
                                                    text: 'Mohon tunggu sebentar',
                                                    allowOutsideClick: false,
                                                    didOpen: () => Swal.showLoading()
                                                });
                                                try {
                                                    file = await compressImage(file, 2);
                                                    const dataTransfer = new DataTransfer();
                                                    dataTransfer.items.add(file);
                                                    $event.target.files = dataTransfer.files;
                                                } catch (e) {
                                                    console.error('Compression failed', e);
                                                }
                                                Swal.close();
                                            }
                                            if (file.size > 2048000) {
                                                alert('Ukuran file masih terlalu besar meskipun sudah dikompresi! Maksimal 2 MB.');
                                                $event.target.value = '';
                                                return;
                                            }
                                            fileName = file.name;
                                            fileSize = file.size;
                                            filePreview = URL.createObjectURL(file);
                                        }
                                    " />
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
                        dengan data sebenar-benarnya.</span>
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
                async function compressImage(file, maxSizeMB = 2) {
                    return new Promise((resolve, reject) => {
                        const reader = new FileReader();
                        reader.readAsDataURL(file);
                        reader.onload = event => {
                            const img = new Image();
                            img.src = event.target.result;
                            img.onload = () => {
                                const canvas = document.createElement('canvas');
                                let width = img.width;
                                let height = img.height;
                                
                                const maxDim = 1920;
                                if (width > maxDim || height > maxDim) {
                                    if (width > height) {
                                        height = Math.round((height *= maxDim / width));
                                        width = maxDim;
                                    } else {
                                        width = Math.round((width *= maxDim / height));
                                        height = maxDim;
                                    }
                                }
                                
                                canvas.width = width;
                                canvas.height = height;
                                const ctx = canvas.getContext('2d');
                                ctx.drawImage(img, 0, 0, width, height);
                                
                                let quality = 0.8;
                                const maxSizeBytes = maxSizeMB * 1024 * 1024;
                                
                                const attemptCompress = (q) => {
                                    canvas.toBlob(blob => {
                                        if (blob.size <= maxSizeBytes || q <= 0.2) {
                                            const newFile = new File([blob], file.name, {
                                                type: 'image/jpeg',
                                                lastModified: Date.now()
                                            });
                                            resolve(newFile);
                                        } else {
                                            attemptCompress(q - 0.15);
                                        }
                                    }, 'image/jpeg', q);
                                };
                                
                                attemptCompress(quality);
                            };
                            img.onerror = error => reject(error);
                        };
                        reader.onerror = error => reject(error);
                    });
                }

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

                            const agreementCheckbox = form.querySelector('input[name="agreement"]');
                            if (agreementCheckbox && !agreementCheckbox.checked) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Pernyataan Belum Dicentang',
                                    text: 'Silakan centang pernyataan terlebih dahulu sebelum mengirim pengajuan.',
                                    confirmButtonColor: '#dc2626'
                                });
                                return;
                            }

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
                                    if (typeof form.requestSubmit === 'function') {
                                        form.requestSubmit();
                                    } else {
                                        form.submit();
                                    }
                                }
                            });
                        });
                    }
                });
            </script>
        @endpush
    </div>
@endsection
