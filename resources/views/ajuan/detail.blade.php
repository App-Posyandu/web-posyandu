@extends('dashboard.layouts.dashboard')
@section('title', 'Detail Pengajuan')
@section('content')
    <div x-data="{
        showModal: false,
        fileUrl: '',
        fileTitle: '',
        fileType: '',
        isLoadingModal: false
    }" class="flex-grow gap-5 flex-col flex items-center justify-center py-12">
        <div class="w-full mx-8">
            <div class="bg-white overflow-hidden shadow-xl md:mx-8 sm:rounded-2xl p-4 md:p-8">

                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Detail Pengajuan</h2>
                        <p class="text-sm text-gray-500">Diajukan pada:
                            {{ \Carbon\Carbon::parse($ajuan->tanggal_permohonan ?? $ajuan->created_at)->format('d F Y') }}
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('ajuan.index') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md text-sm font-semibold hover:bg-gray-300">Kembali</a>

                        @if (auth()->user()->role === 'masyarakat')
                            {{-- Tombol Ubah muncul jika Ditolak ATAU ada permintaan revisi --}}
                            @if (
                                $ajuan->status_pengajuan === 'Ditolak' ||
                                    ($ajuan->status_pengajuan === 'Diproses' && $ajuan->revision_requested_at))

                                {{-- Cek apakah masih dalam masa revisi (5 hari kerja) --}}
                                @php
                                    $requestedDate = \Carbon\Carbon::parse($ajuan->revision_requested_at);

                                    // ✅ FIX: Pakai SystemSetting dari database, bukan config()
                                    $debugMode = \App\Models\SystemSetting::get('revision_debug_mode', false);
                                    $debugMinutes = \App\Models\SystemSetting::get('revision_debug_minutes', 5);
                                    $productionDays = \App\Models\SystemSetting::get('auto_reject_days', 5);

                                    if ($debugMode) {
                                        $revisionDeadline = $requestedDate->copy()->addMinutes($debugMinutes);
                                    } else {
                                        $revisionDeadline = $requestedDate->copy()->addWeekdays($productionDays);
                                    }

                                    $isExpired = now()->greaterThan($revisionDeadline);
                                @endphp

                                @if ($ajuan->revision_requested_at && $isExpired)
                                    {{-- Jika sudah expired, tampilkan peringatan --}}
                                    <div class="px-4 py-2 bg-red-100 text-red-800 rounded-md text-sm font-semibold">
                                        Masa Revisi Berakhir
                                    </div>
                                @else
                                    {{-- Tampilkan tombol Ubah jika belum expired --}}
                                    <a href="{{ route('ajuan.edit', $ajuan) }}"
                                        class="px-4 py-2 bg-yellow-500 text-white rounded-md text-sm font-semibold hover:bg-yellow-600">
                                        Ubah
                                    </a>

                                    @if ($ajuan->revision_requested_at && $revisionDeadline)
                                        <span class="px-3 py-2 bg-orange-100 text-orange-800 rounded-md text-xs">
                                            Batas: {{ $revisionDeadline->format('d M Y') }}
                                        </span>
                                    @endif
                                @endif
                            @endif
                        @endif
                    </div>
                </div>

                {{-- Status Progress Bar --}}
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        @php
                            $steps = [
                                ['name' => 'Verifikasi', 'completed' => $ajuan->sudah_verifikasi],
                                ['name' => 'Kunjungan', 'completed' => $ajuan->kunjungan_lapangan],
                                ['name' => 'Ketua Posyandu', 'completed' => $ajuan->approved_by_ketua],
                                ['name' => 'Kepala Desa', 'completed' => $ajuan->approved_by_kades],
                            ];
                        @endphp

                        @foreach ($steps as $index => $step)
                            <div class="flex items-center {{ $loop->last ? '' : 'flex-1' }}">
                                <div class="flex flex-col items-center">
                                    <div
                                        class="w-10 h-10 rounded-full flex items-center justify-center {{ $step['completed'] ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-500' }}">
                                        @if ($step['completed'])
                                            <i class="bi bi-check-lg font-bold"></i>
                                        @else
                                            {{ $index + 1 }}
                                        @endif
                                    </div>
                                    <span
                                        class="text-xs mt-2 text-center {{ $step['completed'] ? 'text-green-600 font-semibold' : 'text-gray-500' }}">
                                        {{ $step['name'] }}
                                    </span>
                                </div>
                                @if (!$loop->last)
                                    <div class="flex-1 h-1 mx-2 {{ $step['completed'] ? 'bg-green-500' : 'bg-gray-200' }}">
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Alert Permintaan Revisi --}}
                @if (auth()->user()->role === 'masyarakat' && $ajuan->status_pengajuan === 'Diproses' && $ajuan->revision_requested_at)
                    @php
                        $requestedDate = \Carbon\Carbon::parse($ajuan->revision_requested_at);

                        // ✅ FIX: Pakai SystemSetting dari database, bukan config()
                        $enableAutoReject = \App\Models\SystemSetting::get('enable_auto_reject', true);
                        $debugMode = \App\Models\SystemSetting::get('revision_debug_mode', false);
                        $debugMinutes = \App\Models\SystemSetting::get('revision_debug_minutes', 5);
                        $productionDays = \App\Models\SystemSetting::get('auto_reject_days', 5);

                        if ($debugMode) {
                            $revisionDeadline = $requestedDate->copy()->addMinutes($debugMinutes);
                        } else {
                            $revisionDeadline = $requestedDate->copy()->addWeekdays($productionDays);
                        }

                        $isExpired = now()->greaterThan($revisionDeadline);
                    @endphp

                    {{-- ✅ FIX: Countdown SELALU tampil sampai habis, pesan dan behavior beda berdasarkan enableAutoReject --}}
                    {{-- Countdown Realtime --}}
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
                        enableAutoReject: {{ $enableAutoReject ? 'true' : 'false' }},
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
                            if (this.rejecting) return;
                            this.rejecting = true;

                            if (this.enableAutoReject) {
                                // ✅ Auto-reject AKTIF: redirect ke show() untuk trigger server-side reject
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
                                    window.location.href = '{{ route('ajuan.show', $ajuan) }}';
                                });
                            } else {
                                // ✅ Auto-reject NONAKTIF: cuma info, gak diapa-apain
                                // Countdown tetap jalan sampai habis, tapi tidak trigger reject
                            }
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

                                {{-- ✅ Countdown Display - selalu tampil sampai habis --}}
                                <div x-show="!expired">
                                    <p class="text-sm text-orange-700 mt-2">
                                        Sisa waktu untuk merevisi:
                                    </p>

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

                                {{-- ✅ Pesan setelah expired - berbeda based on enableAutoReject --}}
                                <div x-show="expired && !rejecting">
                                    <template x-if="enableAutoReject">
                                        <div class="text-red-600 mt-2">
                                            <p class="text-sm font-semibold">⚠️ Waktu revisi telah habis!</p>
                                            <p class="text-xs mt-1">Pengajuan akan otomatis ditolak...</p>
                                        </div>
                                    </template>
                                    <template x-if="!enableAutoReject">
                                        <div class="text-gray-600 mt-2 bg-gray-50 p-3 rounded border border-gray-200">
                                            <p class="text-sm font-semibold">⏱️ Waktu revisi telah habis</p>
                                            <p class="text-xs mt-1">Fitur auto-reject saat ini <strong>nonaktif</strong>,
                                                pengajuan tidak akan ditolak secara otomatis.</p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- @elseif ($ajuan->revision_count > 0)
                    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="bi bi-info-circle-fill text-yellow-500 text-lg"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-800">
                                    Ini adalah revisi ke-<strong>{{ $ajuan->revision_count }}</strong> dari pengajuan
                                    @if (auth()->user()->role === 'masyarakat')
                                        Anda
                                    @else
                                        {{ $ajuan->user?->name ?? 'Pengguna Telah Dihapus' }}
                                    @endif.
                                </p>
                            </div>
                        </div>
                    </div> --}}
                @endif

                {{-- Info Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-6 border-t border-b py-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nama Pemohon</dt>
                        <dd class="mt-1 text-gray-900 font-semibold">
                            {{ $ajuan->user?->name ?? 'Pengguna Telah Dihapus' }}</dd>
                        <dd class="text-xs text-gray-500">RW {{ $ajuan->user?->rw ?? '-' }} / RT
                            {{ $ajuan->user?->rt ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Bidang Layanan</dt>
                        <dd class="mt-1 text-gray-900 font-semibold">
                            {{ $ajuan->bidang->nama_bidang ?? 'Bidang' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status Pengajuan</dt>
                        <dd class="mt-1">
                            @if ($ajuan->status_pengajuan == 'Disetujui')
                                <span
                                    class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Disetujui</span>
                            @elseif ($ajuan->status_pengajuan == 'Ditolak')
                                <span
                                    class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>
                            @elseif ($ajuan->status_pengajuan == 'Sesuai')
                                <span
                                    class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Sesuai</span>
                            @elseif ($ajuan->status_pengajuan == 'Diajukan ke Desa')
                                <span
                                    class="px-3 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">Diajukan
                                    ke Desa</span>
                            @else
                                <span
                                    class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Diproses</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Revisi</dt>
                        <dd class="mt-1">
                            @if ($ajuan->revision_count > 0)
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">
                                    {{ $ajuan->revision_count }}x Revisi
                                </span>
                            @else
                                <span class="text-sm text-gray-500">Belum Ada Revisi</span>
                            @endif
                        </dd>
                    </div>
                </div>

                {{-- Deskripsi --}}
                <div class="mt-6">
                    <h3 class="font-semibold mb-2">Deskripsi Permohonan</h3>
                    <p class="text-gray-700 bg-gray-50 p-4 rounded-md">{{ $ajuan->deskripsi_pengajuan }}</p>
                </div>

                {{-- Tindak Lanjut --}}
                @if ($ajuan->tindak_lanjut)
                    <div class="mt-6">
                        <h3 class="font-semibold mb-2">Tindak Lanjut</h3>
                        <p class="text-gray-700 bg-blue-50 p-4 rounded-md border-l-4 border-blue-500">
                            {{ $ajuan->tindak_lanjut }}</p>
                    </div>
                @endif

                {{-- Detail Permohonan & Dokumen --}}
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <h3 class="font-semibold mb-2">Detail Permohonan Dipilih</h3>
                        <ul class="space-y-2">
                            @forelse ($ajuan->formulir_items as $item)
                                <li class="flex items-center text-gray-700">
                                    <i class="bi bi-check-square-fill text-green-500 mr-3"></i>
                                    <span>{{ $item }}</span>
                                </li>
                            @empty
                                <li class="text-gray-500">Tidak ada item permohonan yang dipilih.</li>
                            @endforelse
                        </ul>
                    </div>
                    <div>
                        <h3 class="font-semibold mb-2">Dokumen Terlampir</h3>
                        <ul class="space-y-2">
                            @forelse ($ajuan->administrasi_items as $key => $path)
                                <li class="flex items-center">
                                    <a href="{{ route('ajuan.dokumen.download', ['ajuan' => $ajuan, 'key' => $key]) }}"
                                        target="_blank" class="text-blue-600 hover:underline flex items-center">
                                        <i class="bi bi-file-earmark-arrow-down-fill text-blue-500 mr-2"></i>
                                        <span>{{ ucfirst(str_replace('_', ' ', $key)) }}</span>
                                    </a>
                                </li>
                            @empty
                                <li class="text-gray-500">Tidak ada dokumen yang diunggah.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                {{-- Foto Kunjungan --}}
                @if ($ajuan->foto_kunjungan && count($ajuan->foto_kunjungan) > 0)
                    <div class="mt-6">
                        <h3 class="font-semibold mb-2">Foto Kunjungan Lapangan</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            @foreach ($ajuan->foto_kunjungan as $foto)
                                <div class="relative group cursor-pointer"
                                    @click="showModal=true; fileUrl='{{ route('ajuan.foto-kunjungan', ['ajuan' => $ajuan, 'index' => $loop->index]) }}'; fileType='image';">
                                    <img src="{{ route('ajuan.foto-kunjungan', ['ajuan' => $ajuan, 'index' => $loop->index]) }}" alt="Foto Kunjungan"
                                        class="w-full h-32 object-cover rounded-lg border shadow-sm hover:scale-105 transition">
                                    <div
                                        class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition rounded-lg flex items-center justify-center">
                                        <i class="bi bi-eye text-white opacity-0 group-hover:opacity-100 text-2xl"></i>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Riwayat --}}
                <div class="mt-8">
                    <h3 class="font-semibold mb-4">Riwayat Pengajuan</h3>
                    <div class="border-l-2 border-gray-200 pl-6">
                        @forelse ($ajuan->histories->sortByDesc('created_at') as $history)
                            <div class="mb-6">
                                <div class="flex items-center mb-1">
                                    <div class="bg-pink-500 w-4 h-4 rounded-full -ml-[33px] mr-4 border-4 border-white">
                                    </div>
                                    <p class="font-bold text-gray-800">{{ $history->status }}</p>
                                    @if ($history->action_by_role)
                                        <span class="ml-2 px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                            {{ ucfirst($history->action_by_role) }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-500 ml-5">
                                    {{ \Carbon\Carbon::parse($history->created_at)->format('d F Y, H:i') }}</p>
                                @if ($history->catatan)
                                    <p class="mt-2 text-sm text-gray-600 bg-gray-50 p-3 rounded-md ml-5">
                                        {{ $history->catatan }}</p>
                                @endif
                            </div>
                        @empty
                            <p class="text-gray-500">Belum ada riwayat untuk pengajuan ini.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Preview Dokumen --}}
        <div x-show="showModal" x-cloak x-transition.opacity
            class="fixed inset-0 bg-black/60 flex items-center justify-center z-50" @click.self="showModal = false">
            <div class="bg-white rounded-xl shadow-2xl w-11/12 md:w-3/4 h-[85vh] relative overflow-hidden flex flex-col">
                <button @click="showModal=false"
                    class="absolute top-3 right-3 text-gray-600 hover:text-black text-2xl font-bold z-20">✕</button>
                <h2 class="text-lg font-semibold text-center py-3 border-b" x-text="fileTitle"></h2>
                <div class="flex-1 flex items-center justify-center bg-gray-100 relative">
                    <div x-show="isLoadingModal"
                        class="absolute inset-0 flex flex-col items-center justify-center bg-gray-100/80 z-10">
                        <div class="w-12 h-12 border-4 border-t-pink-500 border-gray-200 rounded-full animate-spin"></div>
                        <p class="text-gray-600 mt-3">Memuat dokumen...</p>
                    </div>
                    <iframe x-show="fileType === 'pdf' || fileType === 'pdf_path'" :src="fileUrl"
                        @load="isLoadingModal=false" class="w-full h-full border-0 rounded-b-xl"></iframe>
                    <img x-show="fileType === 'image'" :src="fileUrl" @load="isLoadingModal = false"
                        class="max-h-full max-w-full object-contain rounded-b-xl">
                </div>
            </div>
        </div>

        {{-- KADER: Verifikasi Step 1-2 --}}
        @if (auth()->user()->role === 'kader' && $ajuan->status_pengajuan === 'Diproses')
            <div class="w-full mx-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl p-4 md:p-8 mx-0 md:mx-8"
                    x-data="{
                        step1Open: {{ !$ajuan->sudah_verifikasi ? 'true' : 'false' }},
                        step2Open: {{ $ajuan->sudah_verifikasi && !$ajuan->kunjungan_lapangan ? 'true' : 'false' }},
                        toggleStep(step) {
                            if (step === 1) {
                                this.step1Open = !this.step1Open;
                                this.step2Open = false;
                            } else if (step === 2 && {{ $ajuan->sudah_verifikasi ? 'true' : 'false' }}) {
                                this.step1Open = false;
                                this.step2Open = !this.step2Open;
                            }
                        }
                    }">

                    <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Proses Verifikasi Pengajuan</h2>

                    {{-- STEP 1: Verifikasi Dokumen --}}
                    <div
                        class="mb-4 border rounded-lg overflow-hidden {{ $ajuan->sudah_verifikasi ? 'bg-green-50 border-green-300' : 'bg-white border-gray-300' }}">
                        <button @click="toggleStep(1)" type="button"
                            class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-gray-50 transition-colors duration-200">
                            <div class="flex items-center space-x-3">
                                @if ($ajuan->sudah_verifikasi)
                                    <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                @else
                                    <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z"
                                            clip-rule="evenodd" />
                                    </svg>
                                @endif
                                <span class="font-semibold text-lg">
                                    Tahap 1: Verifikasi Dokumen
                                    @if ($ajuan->sudah_verifikasi)
                                        <span class="text-sm text-green-600 ml-2">(Selesai)</span>
                                    @else
                                        <span class="text-sm text-blue-600 ml-2">(Aktif)</span>
                                    @endif
                                </span>
                            </div>
                            <svg class="w-5 h-5 transition-transform duration-300"
                                :class="step1Open ? 'rotate-180' : 'rotate-0'" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>

                        <div x-show="step1Open" x-transition style="display: none;">
                            @php
                                $step1History = $ajuan->histories
                                    ->where('status', 'Menunggu Kunjungan')
                                    ->sortByDesc('created_at')
                                    ->first();
                            @endphp
                            <div class="px-6 py-4 border-t">
                                <form method="POST" action="{{ route('ajuan.verify', $ajuan) }}"
                                    id="form-ajuan-verify">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="verification_step" value="1">

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                        <div>
                                            <h3 class="font-semibold mb-4 border-b pb-2">Detail Permohonan</h3>
                                            <div class="space-y-3">
                                                @forelse ($ajuan->formulir_items ?? [] as $item)
                                                    <label
                                                        class="flex items-center justify-between p-3 rounded-md bg-gray-50 border">
                                                        <span
                                                            class="text-sm text-gray-700 pr-4">{{ $item }}</span>
                                                        <input type="checkbox" name="verified_formulir_items[]"
                                                            value="{{ $item }}"
                                                            {{ $ajuan->sudah_verifikasi ? 'checked disabled' : '' }}
                                                            class="h-5 w-5 rounded border-gray-400 text-pink-600">
                                                    </label>
                                                @empty
                                                    <p class="text-gray-500 text-sm">Tidak ada item permohonan.</p>
                                                @endforelse
                                            </div>
                                        </div>

                                        <div>
                                            <h3 class="font-semibold mb-4 border-b pb-2">Dokumen Administrasi</h3>
                                            <div class="space-y-3">
                                                @forelse ($ajuan->administrasi_items ?? [] as $key => $path)
                                                    @php
                                                        $label =
                                                            $templateData['administrasi_items'][$key] ??
                                                            ucfirst(str_replace('_', ' ', $key));
                                                        $streamUrl = route('ajuan.dokumen.stream', [
                                                            'ajuan' => $ajuan,
                                                            'key' => $key,
                                                        ]);
                                                        $fileType = \Illuminate\Support\Str::endsWith($path, ['.pdf'])
                                                            ? 'pdf'
                                                            : 'image';
                                                    @endphp
                                                    <div class="p-3 rounded-md bg-gray-50 border">
                                                        <label class="flex items-center justify-between">
                                                            <span class="text-sm text-gray-700">{{ $label }}</span>
                                                            <input type="checkbox"
                                                                name="verified_administrasi_items[{{ $key }}]"
                                                                value="1"
                                                                {{ $ajuan->sudah_verifikasi ? 'checked disabled' : '' }}
                                                                class="h-5 w-5 rounded border-gray-400 text-pink-600">
                                                        </label>
                                                        <div class="mt-2 flex items-center space-x-4">
                                                            <button type="button"
                                                                class="px-3 py-1 text-sm text-blue-600 border border-blue-600 rounded hover:bg-blue-50"
                                                                @click="showModal=true; fileUrl='{{ $streamUrl }}'; fileTitle='{{ $label }}'; fileType='{{ $fileType }}';">
                                                                Lihat Dokumen
                                                            </button>
                                                            <a href="{{ route('ajuan.dokumen.download', ['ajuan' => $ajuan, 'key' => $key]) }}"
                                                                class="text-xs text-green-600 hover:underline">Unduh</a>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <p class="text-gray-500 text-sm">Tidak ada dokumen.</p>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-6">
                                        <label for="keputusan_step1"
                                            class="block font-medium text-sm text-gray-700 mb-2">Keputusan</label>
                                        @php
                                            $maxRevisionCount = \App\Models\SystemSetting::get('max_revision_count', 3);
                                            $canRevise = $ajuan->revision_count < $maxRevisionCount;
                                        @endphp
                                        <select name="keputusan" id="keputusan_step1" required
                                            class="block w-full border-gray-300 rounded-md shadow-sm"
                                            {{ $ajuan->sudah_verifikasi ? 'disabled' : '' }}>
                                            <option value="">Pilih Keputusan</option>
                                            <option value="lanjut">Lanjut ke Kunjungan Lapangan</option>
                                            <option value="revisi" {{ !$canRevise ? 'disabled' : '' }}>
                                                Minta Revisi ({{ $ajuan->revision_count }}/{{ $maxRevisionCount }})
                                                @if (!$canRevise)
                                                    - Maksimal tercapai
                                                @endif
                                            </option>
                                            <option value="tolak">Tolak (Posyandu Salah)</option>
                                        </select>
                                    </div>

                                    <div class="mt-4">
                                        <label for="catatan_step1"
                                            class="block font-medium text-sm text-gray-700 mb-2">Catatan</label>
                                        <textarea id="catatan_step1" name="catatan" rows="3" {{ $ajuan->sudah_verifikasi ? 'disabled' : '' }}
                                            class="block w-full border-gray-300 rounded-md shadow-sm"
                                            placeholder="Berikan catatan jika ada revisi atau penolakan...">{{ $step1History?->catatan }}</textarea>
                                        @error('catatan')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    @if (!$ajuan->sudah_verifikasi)
                                        <div class="flex justify-end mt-6">
                                            <button type="submit" id="submit-verifikasi"
                                                class="px-6 py-2 bg-green-600 text-white font-semibold rounded-md hover:bg-green-700">
                                                Simpan Verifikasi
                                            </button>
                                        </div>
                                    @endif
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- STEP 2: Kunjungan Lapangan --}}
                    <div
                        class="mb-4 border rounded-lg overflow-hidden {{ $ajuan->kunjungan_lapangan ? 'bg-green-50 border-green-300' : ($ajuan->sudah_verifikasi ? 'bg-white border-gray-300' : 'bg-gray-100 border-gray-200') }}">
                        <button @click="toggleStep(2)" type="button" {{ !$ajuan->sudah_verifikasi ? 'disabled' : '' }}
                            class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-gray-50 transition-colors {{ !$ajuan->sudah_verifikasi ? 'cursor-not-allowed opacity-50' : '' }}">
                            <div class="flex items-center space-x-3">
                                @if ($ajuan->kunjungan_lapangan)
                                    <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                @elseif ($ajuan->sudah_verifikasi)
                                    <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z"
                                            clip-rule="evenodd" />
                                    </svg>
                                @else
                                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd" />
                                    </svg>
                                @endif
                                <span class="font-semibold text-lg">
                                    Tahap 2: Kunjungan Lapangan
                                    @if ($ajuan->kunjungan_lapangan)
                                        <span class="text-sm text-green-600 ml-2">(Selesai)</span>
                                    @elseif ($ajuan->sudah_verifikasi)
                                        <span class="text-sm text-blue-600 ml-2">(Aktif)</span>
                                    @else
                                        <span class="text-sm text-gray-400 ml-2">(Terkunci)</span>
                                    @endif
                                </span>
                            </div>
                            <svg class="w-5 h-5 transition-transform" :class="step2Open ? 'rotate-180' : 'rotate-0'"
                                fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>

                        @if ($ajuan->sudah_verifikasi)
                            <div x-show="step2Open" x-transition style="display: none;">
                                <div class="px-6 py-4 border-t">
                                    <form method="POST" action="{{ route('ajuan.verify', $ajuan) }}"
                                        enctype="multipart/form-data" id="form-kunjunganlapangan">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="verification_step" value="2">

                                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">

                                            <p class="text-sm text-blue-800">
                                                Kunjungan lapangan adalah tahap wajib. Anda dapat upload foto kunjungan
                                                (opsional).
                                            </p>
                                        </div>
                                        <div class="mb-6">
                                            <label class="block font-medium text-sm text-gray-700 mb-2">Upload Foto
                                                Kunjungan (Opsional)</label>
                                            <input type="file" name="foto_kunjungan[]" multiple accept="image/*"
                                                {{ $ajuan->kunjungan_lapangan ? 'disabled' : '' }}
                                                class="block w-full border-gray-300 rounded-md shadow-sm">
                                            <p class="text-xs text-gray-500 mt-1">Anda dapat upload beberapa foto sekaligus
                                            </p>
                                        </div>

                                        <div class="mb-6">
                                            <label class="block font-medium text-sm text-gray-700 mb-2">Catatan
                                                Kunjungan (Required)</label>
                                            <textarea name="catatan_kunjungan" rows="4" {{ $ajuan->kunjungan_lapangan ? 'disabled' : '' }}
                                                class="block w-full border-gray-300 rounded-md shadow-sm" placeholder="Hasil kunjungan lapangan..."></textarea>
                                            @error('catatan_kunjungan')
                                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        @if (!$ajuan->kunjungan_lapangan)
                                            <div class="flex justify-end">
                                                <button type="submit" id="confirm-kunjunganlapangan"
                                                    class="px-6 py-2 bg-green-600 text-white font-semibold rounded-md hover:bg-green-700">
                                                    Selesai Kunjungan
                                                </button>
                                            </div>
                                        @endif
                                    </form>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Info: Menunggu Ketua Posyandu --}}
                    @if ($ajuan->kunjungan_lapangan && !$ajuan->approved_by_ketua)
                        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4">
                            <p class="text-sm text-yellow-800">
                                Kunjungan lapangan telah selesai. Menunggu persetujuan dari <strong>Ketua Posyandu</strong>.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- KETUA POSYANDU: Approval Step 3 --}}
        @if (auth()->user()->role === 'ketua-posyandu' && $ajuan->kunjungan_lapangan && !$ajuan->approved_by_ketua)
            <div class="w-full mx-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl p-4 md:p-8 mx-0 md:mx-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Persetujuan Ketua Posyandu</h2>

                    <form method="POST" action="{{ route('ajuan.verify', $ajuan) }}" id="form-keputusan-ketua">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="verification_step" value="3">

                        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
                            <p class="text-sm text-blue-800">
                                Kunjungan lapangan telah selesai. Silakan berikan keputusan Anda terkait pengajuan ini.
                            </p>
                        </div>

                        <div class="mb-6">
                            <label class="block font-medium text-sm text-gray-700 mb-2">Keputusan</label>
                            <select name="keputusan" id="keputusan_step3" required
                                class="block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Pilih Keputusan</option>
                                <option value="ditindaklanjuti">Ditindaklanjuti (Lanjutkan ke Pemdes)</option>
                                <option value="tidak-ditindaklanjuti">Tidak Ditindaklanjuti</option>
                            </select>
                        </div>

                        <div class="mb-6">
                            <label class="block font-medium text-sm text-gray-700 mb-2">Catatan</label>
                            <textarea id="catatan_step3" name="catatan" rows="4"
                                class="block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" id="submit-keputusan-ketua"
                                class="px-6 py-2 bg-pink-600 text-white rounded-md hover:bg-pink-700">
                                Simpan Keputusan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- KETUA POSYANDU: Submit ke Pemdes --}}
        @if (
            auth()->user()->role === 'ketua-posyandu' &&
                $ajuan->approved_by_ketua &&
                !$ajuan->submitted_to_desa &&
                $ajuan->status_pengajuan === 'Diproses')
            <div class="w-full mx-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl p-4 md:p-8 mx-0 md:mx-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Kirim ke Pemdes</h2>

                    <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
                        <p class="text-sm text-green-800">
                            Pengajuan telah disetujui. Klik tombol di bawah untuk mengirim ke Pemdes.
                        </p>
                    </div>

                    <form method="POST" id="form-ke-pemdes" action="{{ route('ajuan.submit-to-pemdes', $ajuan) }}">
                        @csrf
                        <div class="flex justify-end gap-2">
                            <a href="/ajuan/cetak/{{ $ajuan->id }}" target="_blank"
                                class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                <i class="bi bi-printer-fill mr-2"></i> Cetak Rekomendasi
                            </a>
                            <button type="submit" id="submit-ke-pemdes"
                                class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                                <i class="bi bi-send-fill mr-2"></i> Kirim ke Pemdes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        {{-- KADES: Approval Final --}}
        @if (auth()->user()->role === 'kades' && $ajuan->submitted_to_desa && $ajuan->status_pengajuan === 'Diproses')
            <div class="w-full mx-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl p-4 md:p-8 mx-0 md:mx-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Persetujuan Kepala Desa</h2>

                    <form method="POST" id="form-keputusan-kades" action="{{ route('ajuan.kades-approval', $ajuan) }}">
                        @csrf
                        <div class="bg-purple-50 border-l-4 border-purple-500 p-4 mb-6">
                            <p class="text-sm text-purple-800">
                                Pengajuan telah diajukan oleh Ketua Posyandu. Silakan berikan keputusan akhir.
                            </p>
                        </div>

                        <div class="mb-6">
                            <label class="block font-medium text-sm text-gray-700 mb-2">Keputusan</label>
                            <select name="keputusan" id="keputusan_kades" required
                                class="block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Pilih Keputusan</option>
                                <option value="diajukan">Diajukan (Lanjutkan ke Pemdes)</option>
                                <option value="tidak-diajukan">Tidak Diajukan</option>
                            </select>
                        </div>

                        <div class="mb-6">
                            <label class="block font-medium text-sm text-gray-700 mb-2">Tindak Lanjut Rekomendasi (Wajib)</label>
                            <textarea name="tindak_lanjut" id="tindaklanjut_kades" rows="4"
                                class="block w-full border-gray-300 rounded-md shadow-sm"
                                placeholder="Deskripsikan tindak lanjut yang perlu dilakukan..."></textarea>
                            @error('tindak_lanjut')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label class="block font-medium text-sm text-gray-700 mb-2">Catatan (Opsional)</label>
                            <textarea name="catatan" id="catatan_kades" rows="4"
                                class="block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                        </div>

                        <div class="flex justify-end gap-2">
                            <a href="/ajuan/cetak/{{ $ajuan->id }}" target="_blank"
                                class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                <i class="bi bi-printer-fill mr-2"></i> Cetak
                            </a>
                            <button type="submit" id="submit-kades"
                                class="px-6 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">
                                Simpan Keputusan Akhir
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                // ===== STEP 1: Verifikasi Dokumen =====
                const formVerifikasi = document.getElementById('form-ajuan-verify');
                const btnSubmitVerifikasi = document.getElementById('submit-verifikasi');

                if (btnSubmitVerifikasi && formVerifikasi) {
                    btnSubmitVerifikasi.addEventListener('click', function(e) {
                        e.preventDefault();

                        const keputusan = document.getElementById('keputusan_step1').value;
                        const catatan = document.getElementById('catatan_step1').value;

                        // Validasi keputusan harus dipilih
                        if (!keputusan) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Perhatian!',
                                text: 'Silakan pilih keputusan terlebih dahulu.',
                                confirmButtonColor: '#dc2626'
                            });
                            return;
                        }

                        // Validasi catatan untuk revisi dan tolak
                        if ((keputusan === 'revisi' || keputusan === 'tidak-ditindaklanjuti' || keputusan ===
                                'tidak-diajukan') && !catatan.trim()) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Catatan Diperlukan!',
                                text: 'Silakan berikan catatan untuk keputusan ' + (keputusan ===
                                    'revisi' ? 'revisi' : keputusan === 'tidak-ditindaklanjuti' ?
                                    'tidak-ditindaklanjuti' : 'tidak-diajukan') + '.',
                                confirmButtonColor: '#dc2626'
                            });
                            return;
                        }
                        // Tentukan pesan konfirmasi berdasarkan keputusan
                        let title, text, icon, confirmButtonText;

                        if (keputusan === 'lanjut') {
                            title = 'Lanjut ke Kunjungan Lapangan?';
                            text = 'Dokumen akan diverifikasi dan dilanjutkan ke tahap kunjungan lapangan.';
                            icon = 'info';
                            confirmButtonText = 'Ya, Lanjutkan';
                        } else if (keputusan === 'revisi') {
                            title = 'Minta Revisi?';
                            text = 'Pengajuan akan dikembalikan ke pemohon untuk dilakukan revisi.';
                            icon = 'warning';
                            confirmButtonText = 'Ya, Minta Revisi';
                        } else if (keputusan === 'tolak') {
                            title = 'Tolak Pengajuan?';
                            text = 'Pengajuan akan ditolak. Tindakan ini tidak dapat dibatalkan.';
                            icon = 'error';
                            confirmButtonText = 'Ya, Tolak';
                        }

                        Swal.fire({
                            title: title,
                            text: text,
                            icon: icon,
                            showCancelButton: true,
                            confirmButtonColor: keputusan === 'tolak' ? '#dc2626' : '#16a34a',
                            cancelButtonColor: '#6b7280',
                            confirmButtonText: confirmButtonText,
                            cancelButtonText: 'Batal',
                            reverseButtons: true
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Tampilkan loading
                                Swal.fire({
                                    title: 'Memproses...',
                                    text: 'Mohon tunggu sebentar',
                                    allowOutsideClick: false,
                                    allowEscapeKey: false,
                                    didOpen: () => {
                                        Swal.showLoading();
                                    }
                                });

                                // Submit form
                                formVerifikasi.submit();
                            }
                        });
                    });
                }

                // ===== STEP 2: Kunjungan Lapangan =====
                const formKunjungan = document.getElementById('form-kunjunganlapangan');
                const btnKunjungan = document.getElementById('confirm-kunjunganlapangan');

                if (btnKunjungan && formKunjungan) {
                    btnKunjungan.addEventListener('click', function(e) {
                        e.preventDefault();

                        const catatanKunjungan = formKunjungan.querySelector(
                            'textarea[name="catatan_kunjungan"]');

                        // Validasi catatan kunjungan (required)
                        if (!catatanKunjungan.value.trim()) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Catatan Diperlukan!',
                                text: 'Silakan isi catatan hasil kunjungan lapangan.',
                                confirmButtonColor: '#dc2626'
                            });
                            return;
                        }

                        Swal.fire({
                            title: 'Selesaikan Kunjungan Lapangan?',
                            html: `
                    <p>Kunjungan lapangan akan ditandai selesai dan pengajuan akan dilanjutkan ke Ketua Posyandu.</p>
                    <div class="mt-3 p-3 bg-blue-50 rounded text-sm text-left">
                        <strong>Catatan:</strong><br>
                        ${catatanKunjungan.value}
                    </div>
                `,
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#16a34a',
                            cancelButtonColor: '#6b7280',
                            confirmButtonText: 'Ya, Selesaikan',
                            cancelButtonText: 'Batal',
                            reverseButtons: true
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Tampilkan loading
                                Swal.fire({
                                    title: 'Menyimpan Data...',
                                    text: 'Mohon tunggu sebentar',
                                    allowOutsideClick: false,
                                    allowEscapeKey: false,
                                    didOpen: () => {
                                        Swal.showLoading();
                                    }
                                });

                                // Submit form
                                formKunjungan.submit();
                            }
                        });
                    });
                }

                // Step 3
                const formKeputusanKetua = document.getElementById('form-keputusan-ketua');
                const btnKeputusanKetua = document.getElementById('submit-keputusan-ketua');

                if (btnKeputusanKetua && formKeputusanKetua) {
                    btnKeputusanKetua.addEventListener('click', function(e) {
                        e.preventDefault();

                        const keputusan = document.getElementById('keputusan_step3').value;
                        const catatan = document.getElementById('catatan_step3').value;

                        // Validasi keputusan harus dipilih
                        if (!keputusan) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Perhatian!',
                                text: 'Silakan pilih keputusan terlebih dahulu.',
                                confirmButtonColor: '#dc2626'
                            });
                            return;
                        }

                        // Validasi catatan untuk revisi dan tolak
                        if ((keputusan === 'revisi' || keputusan === 'tidak-ditindaklanjuti' || keputusan ===
                                'tidak-diajukan') && !catatan.trim()) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Catatan Diperlukan!',
                                text: 'Silakan berikan catatan untuk keputusan ' + (keputusan ===
                                    'revisi' ? 'revisi' : keputusan === 'tidak-ditindaklanjuti' ?
                                    'tidak ditindaklanjuti' : 'tidak diajukan') + '.',
                                confirmButtonColor: '#dc2626'
                            });
                            return;
                        }

                        let title, text, icon, confirmButtonText;

                        if (keputusan === 'ditindaklanjuti') {
                            title = 'Tidak lanjuti Pengajuan?';
                            text = 'Pengajuan akan ditindaklanjuti dan dilanjutkan ke pemdes.';
                            icon = 'info';
                            confirmButtonText = 'Ya, Lanjutkan';
                        } else if (keputusan === 'tidak-ditindaklanjuti') {
                            title = 'Tidak ditindaklanjuti Pengajuan?';
                            text = 'Pengajuan akan ditolak. Tindakan ini tidak dapat dibatalkan.';
                            icon = 'warning';
                            confirmButtonText = 'Ya, Tidak Ditindaklanjuti';
                        }

                        Swal.fire({
                            title: title,
                            text: text,
                            icon: icon,
                            showCancelButton: true,
                            confirmButtonColor: keputusan === 'tolak' ? '#dc2626' : '#16a34a',
                            cancelButtonColor: '#6b7280',
                            confirmButtonText: confirmButtonText,
                            cancelButtonText: 'Batal',
                            reverseButtons: true
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Tampilkan loading
                                Swal.fire({
                                    title: 'Memproses...',
                                    text: 'Mohon tunggu sebentar',
                                    allowOutsideClick: false,
                                    allowEscapeKey: false,
                                    didOpen: () => {
                                        Swal.showLoading();
                                    }
                                });

                                // Submit form
                                formKeputusanKetua.submit();
                            }
                        });

                    });
                }

                // Submit ke Pemdes
                const formKePemdes = document.getElementById('form-ke-pemdes');
                const btnKePemdes = document.getElementById('submit-ke-pemdes');

                if (btnKePemdes && formKePemdes) {
                    btnKePemdes.addEventListener('click', function(e) {
                        e.preventDefault();

                        let title, text, icon, confirmButtonText;

                        title = 'Tidak lanjuti Pengajuan?';
                        text = 'Pengajuan akan ditindaklanjuti dan dilanjutkan ke pemdes.';
                        icon = 'info';
                        confirmButtonText = 'Ya, Lanjutkan';

                        Swal.fire({
                            title: title,
                            text: text,
                            icon: icon,
                            showCancelButton: true,
                            confirmButtonColor: '#16a34a',
                            cancelButtonColor: '#6b7280',
                            confirmButtonText: confirmButtonText,
                            cancelButtonText: 'Batal',
                            reverseButtons: true
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Tampilkan loading
                                Swal.fire({
                                    title: 'Memproses...',
                                    text: 'Mohon tunggu sebentar',
                                    allowOutsideClick: false,
                                    allowEscapeKey: false,
                                    didOpen: () => {
                                        Swal.showLoading();
                                    }
                                });

                                // Submit form
                                formKePemdes.submit();
                            }
                        });

                    });
                }

                // Kades
                const formKeputusanKades = document.getElementById('form-keputusan-kades');
                const btnKeputusanKades = document.getElementById('submit-kades');

                if (btnKeputusanKades && formKeputusanKades) {
                    btnKeputusanKades.addEventListener('click', function(e) {
                        e.preventDefault();

                        const keputusan = document.getElementById('keputusan_kades').value;
                        const tindakLanjut = document.getElementById('tindaklanjut_kades').value;
                        const catatan = document.getElementById('catatan_kades').value;

                        // Validasi keputusan harus dipilih
                        if (!keputusan) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Perhatian!',
                                text: 'Silakan pilih keputusan terlebih dahulu.',
                                confirmButtonColor: '#dc2626'
                            });
                            return;
                        }

                        if (!tindakLanjut) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Perhatian!',
                                text: 'Silakan mengisi tindak lanjut terlebih dahulu.',
                                confirmButtonColor: '#dc2626'
                            });
                            return;
                        }

/*                         if (!catatan) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Perhatian!',
                                text: 'Silakan mengisi tindak lanjut terlebih dahulu.',
                                confirmButtonColor: '#dc2626'
                            });
                            return;
                        } */

                        // Validasi catatan untuk revisi dan tolak
                        if ((keputusan === 'revisi' || keputusan === 'tidak-ditindaklanjuti' || keputusan ===
                                'tidak-diajukan') && !catatan.trim()) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Catatan Diperlukan!',
                                text: 'Silakan berikan catatan untuk keputusan ' + (keputusan ===
                                    'revisi' ? 'revisi' : keputusan === 'tidak-ditindaklanjuti' ?
                                    'tidak-ditindaklanjuti' : 'tidak-diajukan') + '.',
                                confirmButtonColor: '#dc2626'
                            });
                            return;
                        }

                        let title, text, icon, confirmButtonText;

                        if (keputusan === 'diajukan') {
                            title = 'Ajukan Pengajuan?';
                            text = 'Pengajuan akan diajukan dan disetujui.';
                            icon = 'info';
                            confirmButtonText = 'Ya, Lanjutkan';
                        } else if (keputusan === 'tidak-diajukan') {
                            title = 'Tidak ajukan Pengajuan?';
                            text = 'Pengajuan akan ditolak. Tindakan ini tidak dapat dibatalkan.';
                            icon = 'warning';
                            confirmButtonText = 'Ya, Tidak diajukan';
                        }

                        Swal.fire({
                            title: title,
                            text: text,
                            icon: icon,
                            showCancelButton: true,
                            confirmButtonColor: keputusan === 'tolak' ? '#dc2626' : '#16a34a',
                            cancelButtonColor: '#6b7280',
                            confirmButtonText: confirmButtonText,
                            cancelButtonText: 'Batal',
                            reverseButtons: true
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Tampilkan loading
                                Swal.fire({
                                    title: 'Memproses...',
                                    text: 'Mohon tunggu sebentar',
                                    allowOutsideClick: false,
                                    allowEscapeKey: false,
                                    didOpen: () => {
                                        Swal.showLoading();
                                    }
                                });

                                // Submit form
                                formKeputusanKades.submit();
                            }
                        });

                    });
                }
            });
        </script>
    @endpush
@endsection
