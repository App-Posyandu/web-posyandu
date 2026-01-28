@extends('dashboard.layouts.dashboard')
@section('title', 'Ubah Pengajuan')
@section('content')
    <div class="flex-grow flex items-center justify-center py-12">
        <div class="w-full max-w-4xl mx-4 md:mx-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl p-8">
                <form method="POST" action="{{ route('ajuan.update', $ajuan) }}" enctype="multipart/form-data"
                    id="form-edit-ajuan">
                    @csrf
                    @method('PATCH')

                    <h2 class="text-2xl font-bold text-center text-gray-800 mb-2">Ubah Pengajuan</h2>
                    <p class="text-center text-sm text-gray-500 mb-8">
                        Bidang: <strong class="text-pink-600">{{ $ajuan->bidang->nama_bidang }}</strong>
                    </p>

                    @if ($ajuan->revision_requested_at)
                        @php
                            $requestedDate = \Carbon\Carbon::parse($ajuan->revision_requested_at);

                            // ✅ Gunakan config yang sama dengan command
                            $debugMode = config('revision.debug_mode', false);
                            $debugMinutes = config('revision.deadline.debug_minutes');
                            $productionDays = config('revision.deadline.days', 5);

                            if ($debugMode && $debugMinutes) {
                                $revisionDeadline = $requestedDate->copy()->addMinutes($debugMinutes);
                            } else {
                                $revisionDeadline = $requestedDate->copy()->addDays($productionDays);
                            }

                            $isExpired = now()->greaterThan($revisionDeadline);
                        @endphp

                        @if ($isExpired)
                            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-exclamation-triangle-fill text-red-500 text-lg"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-semibold text-red-800">Masa Revisi Telah Berakhir</h3>
                                        <p class="text-sm text-red-700 mt-1">
                                            Batas waktu revisi telah habis pada
                                            {{ $revisionDeadline->format('d F Y, H:i') }} WIB.
                                            Pengajuan ini akan otomatis ditolak.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @else
                            {{-- Countdown Realtime dengan Auto-Reject --}}
                            <div class="bg-orange-50 border-l-4 border-orange-500 p-4 mb-6" x-data="{
                                deadline: new Date('{{ $revisionDeadline->toIso8601String() }}').getTime(),
                                now: Date.now(),
                                days: 0,
                                hours: 0,
                                minutes: 0,
                                seconds: 0,
                                expired: false,
                                rejecting: false,
                                debugMode: {{ $debugMode ? 'true' : 'false' }},
                                updateCountdown() {
                                    this.now = Date.now();
                                    const distance = this.deadline - this.now;

                                    if (distance < 0 && !this.expired) {
                                        this.expired = true;
                                        this.handleExpired();
                                        return;
                                    }

                                    this.days = Math.floor(distance / (1000 * 60 * 60 * 24));
                                    this.hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                                    this.minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                                    this.seconds = Math.floor((distance % (1000 * 60)) / 1000);
                                },
                                handleExpired() {
                                    // ✅ Ketika countdown habis, trigger auto-reject
                                    if (this.rejecting) return;

                                    this.rejecting = true;

                                    // Show alert
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Waktu Revisi Habis!',
                                        html: 'Masa revisi telah berakhir.<br>Pengajuan akan otomatis ditolak.',
                                        allowOutsideClick: false,
                                        showConfirmButton: false,
                                        timer: 3000,
                                        timerProgressBar: true,
                                        didOpen: () => {
                                            Swal.showLoading();
                                        }
                                    }).then(() => {
                                        // Redirect ke halaman detail untuk trigger auto-reject
                                        window.location.href = '{{ route('ajuan.show', $ajuan) }}';
                                    });
                                }
                            }"
                                x-init="updateCountdown();
                                const interval = setInterval(() => {
                                    updateCountdown();
                                    if (expired) {
                                        clearInterval(interval);
                                    }
                                }, 1000);">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-clock-fill text-orange-500 text-lg"></i>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        {{-- Debug Mode Indicator --}}
                                        @if ($debugMode && $debugMinutes)
                                            <div
                                                class="mb-2 bg-yellow-100 border border-yellow-300 rounded px-3 py-1 text-xs text-yellow-800">
                                                🐛 <strong>DEBUG MODE:</strong> Deadline {{ $debugMinutes }} menit
                                            </div>
                                        @endif

                                        <p class="text-sm font-semibold text-orange-800">
                                            Revisi Diminta ({{ $ajuan->revision_count }}x)
                                        </p>

                                        <div x-show="!expired">
                                            <p class="text-sm text-orange-700 mt-2">
                                                Sisa waktu untuk merevisi:
                                            </p>

                                            {{-- Countdown Display --}}
                                            <div class="mt-3 flex flex-wrap gap-2">
                                                <div
                                                    class="bg-white rounded-lg px-3 py-2 border border-orange-200 min-w-[70px] text-center">
                                                    <div class="text-2xl font-bold text-orange-600" x-text="days"></div>
                                                    <div class="text-xs text-gray-600">Hari</div>
                                                </div>
                                                <div
                                                    class="bg-white rounded-lg px-3 py-2 border border-orange-200 min-w-[70px] text-center">
                                                    <div class="text-2xl font-bold text-orange-600" x-text="hours"></div>
                                                    <div class="text-xs text-gray-600">Jam</div>
                                                </div>
                                                <div
                                                    class="bg-white rounded-lg px-3 py-2 border border-orange-200 min-w-[70px] text-center">
                                                    <div class="text-2xl font-bold text-orange-600" x-text="minutes"></div>
                                                    <div class="text-xs text-gray-600">Menit</div>
                                                </div>
                                                <div
                                                    class="bg-white rounded-lg px-3 py-2 border border-orange-200 min-w-[70px] text-center">
                                                    <div class="text-2xl font-bold text-orange-600" x-text="seconds"></div>
                                                    <div class="text-xs text-gray-600">Detik</div>
                                                </div>
                                            </div>

                                            <p class="text-xs text-orange-600 mt-3">
                                                <i class="bi bi-calendar-check"></i>
                                                Batas waktu: {{ $revisionDeadline->format('d F Y, H:i') }} WIB
                                            </p>
                                        </div>

                                        <div x-show="expired && !rejecting" class="text-red-600 mt-2">
                                            <p class="text-sm font-semibold">⚠️ Waktu revisi telah habis!</p>
                                            <p class="text-xs mt-1">Mengalihkan ke halaman detail...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @elseif ($ajuan->revision_count > 0)
                        {{-- Badge revisi tanpa countdown --}}
                        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-info-circle-fill text-yellow-500 text-lg"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-800">
                                        Ini adalah revisi ke-<strong>{{ $ajuan->revision_count }}</strong> dari pengajuan
                                        Anda.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Catatan dari Kader --}}
                    @php
                        $latestRevisionHistory = $ajuan
                            ->histories()
                            ->where('status', 'Revisi Diminta')
                            ->orderBy('created_at', 'desc')
                            ->first();
                    @endphp

                    @if ($latestRevisionHistory && $latestRevisionHistory->catatan)
                        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-chat-left-text-fill text-red-500 text-lg"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-semibold text-red-800">Catatan dari Kader:</p>
                                    <p class="text-sm text-red-700 mt-2 bg-white p-3 rounded border border-red-200">
                                        {{ $latestRevisionHistory->catatan }}
                                    </p>
                                    <p class="text-xs text-red-600 mt-2">
                                        <i class="bi bi-clock-fill"></i>
                                        {{ \Carbon\Carbon::parse($latestRevisionHistory->created_at)->format('d F Y, H:i') }}
                                        WIB
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Hidden input untuk bidang --}}
                    <input type="hidden" name="bidang_pelayanan" value="{{ $ajuan->bidang->slug }}">

                    {{-- Info Bidang yang Dipilih --}}
                    <div class="mb-6 bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <label class="block font-medium text-sm text-gray-700 mb-2">
                            <i class="bi bi-briefcase-fill text-pink-500"></i> Bidang Layanan
                        </label>
                        <div class="flex items-center">
                            <span class="text-lg font-semibold text-gray-800">{{ $ajuan->bidang->nama_bidang }}</span>
                            <span class="ml-3 px-3 py-1 bg-pink-100 text-pink-800 text-xs rounded-full">
                                Tidak dapat diubah
                            </span>
                        </div>
                    </div>

                    {{-- Item Permohonan --}}
                    <div class="mb-6">
                        <label class="block font-medium text-base text-gray-700 mb-3">
                            <i class="bi bi-list-check text-pink-500"></i> Pilih Item Permohonan <span
                                class="text-red-600">*</span>
                        </label>
                        <p class="text-sm text-gray-500 mb-4">Centang semua item yang sesuai dengan kebutuhan Anda</p>

                        <div class="space-y-3">
                            @foreach ($templateData['formulir_items'] as $item)
                                @if ($item === 'Lainnya...')
                                    @php
                                        $lainnyaItem = collect($ajuan->formulir_items ?? [])->first(
                                            fn($i) => str_starts_with($i, 'Lainnya: '),
                                        );
                                        $isLainnyaChecked = !is_null($lainnyaItem);
                                        $lainnyaTextValue = $isLainnyaChecked
                                            ? str_replace('Lainnya: ', '', $lainnyaItem)
                                            : '';
                                    @endphp

                                    <div x-data="{ checked: {{ $isLainnyaChecked ? 'true' : 'false' }} }"
                                        class="p-4 border-2 rounded-lg bg-gradient-to-r from-pink-50 to-purple-50 hover:shadow-md transition">
                                        <label class="flex items-center cursor-pointer">
                                            <input type="checkbox" x-model="checked" name="permohonan_items[]"
                                                value="Lainnya..."
                                                class="h-5 w-5 rounded border-gray-300 text-pink-600 shadow-sm focus:ring-pink-500">
                                            <span class="ml-3 text-gray-700 font-semibold">{{ $item }}</span>
                                        </label>
                                        <div x-show="checked" x-transition class="mt-3">
                                            <input type="text" name="lainnya_text"
                                                class="block w-full border-gray-300 focus:border-pink-500 focus:ring-pink-500 rounded-md shadow-sm"
                                                placeholder="Silakan ketik permohonan Anda..."
                                                value="{{ old('lainnya_text', $lainnyaTextValue) }}"
                                                x-bind:required="checked" x-bind:disabled="!checked" />
                                            <p class="text-xs text-gray-500 mt-1">
                                                <i class="bi bi-info-circle"></i> Jelaskan permohonan lainnya dengan detail
                                            </p>
                                        </div>
                                    </div>
                                @else
                                    <label
                                        class="flex items-center p-3 border-2 rounded-lg hover:bg-gray-50 hover:border-pink-300 cursor-pointer transition">
                                        <input type="checkbox" name="permohonan_items[]" value="{{ $item }}"
                                            @checked(in_array($item, old('permohonan_items', $ajuan->formulir_items ?? [])))
                                            class="h-5 w-5 rounded border-gray-300 text-pink-600 shadow-sm focus:ring-pink-500">
                                        <span class="ml-3 text-gray-700">{{ $item }}</span>
                                    </label>
                                @endif
                            @endforeach
                        </div>
                        @error('permohonan_items')
                            <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Deskripsi Pengajuan --}}
                    <div class="mt-6">
                        <label for="deskripsi_pengajuan" class="block font-medium text-base text-gray-700 mb-2">
                            <i class="bi bi-file-text-fill text-pink-500"></i> Deskripsi Pengajuan <span
                                class="text-red-600">*</span>
                        </label>
                        <textarea name="deskripsi_pengajuan" id="deskripsi_pengajuan" rows="5"
                            class="block mt-1 w-full border-gray-300 focus:border-pink-500 focus:ring-pink-500 rounded-md shadow-sm" required
                            placeholder="Jelaskan detail permohonan Anda dengan lengkap...">{{ old('deskripsi_pengajuan', $ajuan->deskripsi_pengajuan) }}</textarea>
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="bi bi-info-circle"></i> Minimal 10 karakter. Jelaskan kebutuhan Anda dengan detail.
                        </p>
                        @error('deskripsi_pengajuan')
                            <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Dokumen Administrasi --}}
                    <div class="mt-8">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">
                            <i class="bi bi-folder-fill text-pink-500"></i> Dokumen Administrasi
                        </h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Upload ulang dokumen yang perlu diperbaiki. Dokumen yang tidak diubah akan tetap menggunakan
                            file lama.
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach ($templateData['administrasi_items'] as $key => $label)
                                <div x-data="{
                                    previewUrl: '{{ isset($ajuan->administrasi_items[$key]) ? (Illuminate\Support\Str::startsWith($ajuan->administrasi_items[$key], 'data:') ? $ajuan->administrasi_items[$key] : Illuminate\Support\Facades\Storage::url($ajuan->administrasi_items[$key])) : '' }}',
                                    fileName: '{{ isset($ajuan->administrasi_items[$key]) ? 'File saat ini' : 'Belum diunggah' }}',
                                    fileSize: 0,
                                    formatSize(bytes) {
                                        if (bytes === 0) return '';
                                        const k = 1024;
                                        const sizes = ['Bytes', 'KB', 'MB'];
                                        const i = Math.floor(Math.log(bytes) / Math.log(k));
                                        return '(' + Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i] + ')';
                                    }
                                }"
                                    class="border-2 rounded-lg p-4 hover:border-pink-300 transition">

                                    <h4 class="font-semibold mb-2 text-gray-700 flex items-center">
                                        <i class="bi bi-file-earmark-arrow-up text-pink-500 mr-2"></i>
                                        {{ $label }}
                                        @if (!in_array($key, ['ktp', 'kk', 'kartu_bpjs']))
                                            <span class="text-red-600 ml-1">*</span>
                                        @endif
                                    </h4>

                                    {{-- Status dokumen saat ini --}}
                                    @if (isset($ajuan->administrasi_items[$key]))
                                        <div
                                            class="mb-3 bg-green-50 border border-green-200 rounded p-2 text-xs text-green-700">
                                            <i class="bi bi-check-circle-fill"></i> Dokumen tersedia
                                        </div>
                                    @else
                                        <div
                                            class="mb-3 bg-yellow-50 border border-yellow-200 rounded p-2 text-xs text-yellow-700">
                                            <i class="bi bi-exclamation-circle-fill"></i> Belum diunggah
                                        </div>
                                    @endif

                                    {{-- Preview Area --}}
                                    <div
                                        class="mt-2 w-full h-40 flex items-center justify-center border-2 border-dashed border-gray-300 rounded-md bg-gray-50 overflow-hidden">
                                        <img x-show="previewUrl" :src="previewUrl"
                                            class="max-h-full max-w-full object-contain rounded"
                                            alt="Preview {{ $label }}">
                                        <div x-show="!previewUrl" class="text-center text-gray-400">
                                            <i class="bi bi-image text-4xl mb-2"></i>
                                            <p class="text-xs">Tidak ada preview</p>
                                        </div>
                                    </div>

                                    {{-- File Input --}}
                                    <div class="mt-3">
                                        <div class="relative">
                                            <div
                                                class="w-full flex items-center px-3 py-2 bg-white text-gray-600 rounded-md shadow-sm border border-gray-300">
                                                <span x-text="fileName" class="truncate flex-1 text-sm"></span>
                                                <span x-show="fileSize > 0" x-text="formatSize(fileSize)"
                                                    class="text-xs ml-2 text-gray-500"></span>
                                            </div>
                                            <label for="{{ $key }}"
                                                class="absolute inset-y-0 right-0 flex items-center px-4 bg-pink-500 text-white rounded-r-md cursor-pointer hover:bg-pink-600 transition">
                                                <i class="bi bi-upload"></i>
                                            </label>
                                        </div>

                                        <input type="file" id="{{ $key }}" name="{{ $key }}"
                                            accept="image/jpeg,image/jpg,image/png" class="hidden"
                                            @change="
                                                const file = $event.target.files[0];
                                                if (file) {
                                                    if (file.size > 2048000) {
                                                        alert('⚠️ Ukuran file terlalu besar! Maksimal 2 MB.');
                                                        $event.target.value = '';
                                                        fileName = '{{ isset($ajuan->administrasi_items[$key]) ? 'File saat ini' : 'Belum diunggah' }}';
                                                        fileSize = 0;
                                                        return;
                                                    }
                                                    fileName = file.name;
                                                    fileSize = file.size;
                                                    previewUrl = URL.createObjectURL(file);
                                                }
                                            ">

                                        <p class="text-xs text-gray-500 mt-2">
                                            <i class="bi bi-info-circle text-blue-500"></i>
                                            Format: JPG, PNG • Max: 2MB
                                        </p>
                                    </div>

                                    @error($key)
                                        <p class="text-red-500 text-xs mt-2">
                                            <i class="bi bi-exclamation-triangle-fill"></i> {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Buttons --}}
                    <div class="flex items-center justify-end mt-8 space-x-4">
                        <a href="{{ route('ajuan.show', $ajuan) }}"
                            class="py-2 px-6 border-2 border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-400 transition">
                            <i class="bi bi-x-circle mr-1"></i> Batal
                        </a>
                        <button type="submit" id="submit-revisi"
                            class="inline-flex items-center px-8 py-2 bg-gradient-to-r from-pink-500 to-purple-500 border border-transparent rounded-md font-semibold text-sm text-white hover:from-pink-600 hover:to-purple-600 focus:outline-none focus:ring-2 focus:ring-pink-500 shadow-lg transition">
                            <i class="bi bi-check-circle-fill mr-2"></i>
                            Simpan & Ajukan Kembali
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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
                const form = document.getElementById('form-edit-ajuan');
                const submitBtn = document.getElementById('submit-revisi');

                if (submitBtn && form) {
                    submitBtn.addEventListener('click', function(e) {
                        e.preventDefault();

                        Swal.fire({
                            title: 'Simpan Perubahan?',
                            html: 'Pengajuan yang telah direvisi akan dikirim kembali untuk diverifikasi.<br><small class="text-gray-500">Pastikan semua data sudah benar.</small>',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: '<i class="bi bi-send-fill"></i> Ya, Kirim',
                            cancelButtonText: '<i class="bi bi-x-circle"></i> Periksa Lagi',
                            confirmButtonColor: '#ec4899',
                            cancelButtonColor: '#6b7280',
                            reverseButtons: true,
                            customClass: {
                                confirmButton: 'shadow-lg',
                                cancelButton: 'shadow-lg'
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                Swal.fire({
                                    title: 'Mengirim...',
                                    html: 'Mohon tunggu sebentar',
                                    allowOutsideClick: false,
                                    didOpen: () => {
                                        Swal.showLoading();
                                    }
                                });

                                isSubmitting = true;
                                form.submit();
                            }
                        });
                    });
                }

                const links = document.querySelectorAll('a:not([href*="ajuan.show"])');
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
                            title: 'Keluar dari Halaman Revisi?',
                            text: 'Perubahan yang belum disimpan akan hilang.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: '<i class="bi bi-box-arrow-right"></i> Ya, Keluar',
                            cancelButtonText: '<i class="bi bi-x-circle"></i> Tetap di Sini',
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
            });
        </script>
    @endpush
@endsection
