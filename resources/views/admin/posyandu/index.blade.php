@extends('dashboard.layouts.dashboard')
@section('title', 'Manajemen Posyandu')
@section('content')
    <div class="w-full mx-auto sm:px-6 lg:px-4">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            @if (session('created_kaders'))
                <div
                    class="mb-6 bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-300 rounded-xl shadow-lg p-6">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                        </div>

                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-green-800 mb-3">
                                Posyandu & 6 Akun Kader Berhasil Dibuat!
                            </h3>

                            <div class="bg-white rounded-lg p-4 mb-4">
                                <p class="text-sm text-gray-700 mb-3">
                                    <strong>PENTING:</strong> Salin dan simpan credentials di bawah ini.
                                    Informasi ini hanya ditampilkan sekali!
                                </p>

                                <div class="space-y-3">
                                    @foreach (session('created_kaders') as $index => $kaderInfo)
                                        <div class="border-l-4 border-blue-500 bg-blue-50 p-3 rounded">
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1">
                                                    <p class="font-semibold text-gray-800">
                                                        {{ $index + 1 }}. Kader {{ $kaderInfo['bidang'] }}
                                                    </p>
                                                    <div class="mt-2 space-y-1 text-sm">
                                                        <div class="flex items-center gap-2">
                                                            <span class="text-gray-600 w-32">Email:</span>
                                                            <code class="bg-white px-2 py-1 rounded border text-gray-800">
                                                                {{ $kaderInfo['email'] }}
                                                            </code>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <span class="text-gray-600 w-32">Password:</span>
                                                            <code
                                                                class="bg-white px-2 py-1 rounded border text-red-600 font-bold">
                                                                {{ $kaderInfo['password'] }}
                                                            </code>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Copy Button --}}
                                                <button onclick="copyKaderCredentials({{ $index }})"
                                                    class="ml-4 px-3 py-2 bg-blue-500 text-white text-xs rounded hover:bg-blue-600 transition">
                                                    Copy
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="bg-amber-50 border border-amber-300 rounded-lg p-4">
                                <p class="text-sm text-amber-800">
                                    <strong>Catatan:</strong>
                                </p>
                                <ul class="list-disc list-inside text-sm text-amber-700 mt-2 space-y-1">
                                    <li>Semua kader sudah <strong>terverifikasi</strong> dan <strong>aktif</strong></li>
                                    <li>Password default: <code class="bg-white px-2 py-1 rounded">password123</code></li>
                                    <li>Kader dapat login menggunakan <strong>email</strong> atau <strong>nomor
                                            telepon</strong></li>
                                    <li>Ketua Posyandu dapat <strong>melengkapi data</strong> kader (NIK, tanggal lahir, dll)
                                    </li>
                                    <li>Ketua Posyandu dapat <strong>reset password</strong> kader jika diperlukan</li>
                                </ul>
                            </div>

                            {{-- Action Buttons --}}
                            <div class="mt-4 flex gap-3">
                                <a href="{{ route('admin.posyandu.print-credentials', session('posyandu_id')) }}" target="_blank"
                                    class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                                        </path>
                                    </svg>
                                    Print Credentials (PDF)
                                </a>

                                <button onclick="downloadKaderCredentials()"
                                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                        </path>
                                    </svg>
                                    Download as TXT
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <div class="p-6 text-gray-900">

                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">List Posyandu</h2>

                    @if (in_array(auth()->user()->role, ['admin', 'operator-desa']))
                        <a href="{{ route('admin.posyandu.create') }}"
                            class="px-4 py-2 bg-pink-500 text-white rounded-md text-sm font-semibold hover:bg-pink-600">
                            Tambah Posyandu
                        </a>
                    @else
                        <div class="text-sm text-gray-500">
                            <i class="bi bi-info-circle mr-1"></i>
                            Anda mengelola: <strong>{{ auth()->user()->posyandu->nama_posyandu ?? '-' }}</strong>
                        </div>
                    @endif
                </div>

                @include('components.all-notifications')

                @if (auth()->user()->role === 'operator-desa')
                    <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                        <div class="flex items-start">
                            <i class="bi bi-info-circle-fill text-blue-500 mr-3 mt-0.5"></i>
                            <div class="text-sm text-blue-700">
                                <p class="font-semibold mb-1">Informasi untuk Operator Desa</p>
                                <ul class="list-disc list-inside space-y-1 ml-2">
                                    <li>Anda hanya dapat melihat dan mengelola <strong>1 posyandu</strong> yang ditugaskan
                                        kepada Anda</li>
                                    <li>Anda bertanggung jawab untuk setup <strong>RW/RT</strong> yang dilayani posyandu
                                    </li>
                                    <li>Setelah setup RW/RT, user dapat memilih RW/RT saat registrasi</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    No
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nama Posyandu
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Ketua Posyandu
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Lokasi
                                </th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Jumlah RW
                                </th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Jumlah RT
                                </th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($posyandus as $index => $posyandu)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $posyandus->firstItem() + $index }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $posyandu->nama_posyandu }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($posyandu->ketuaKader)
                                            <div class="text-sm text-gray-900">{{ $posyandu->ketuaKader->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $posyandu->ketuaKader->email }}</div>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Belum ada</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">{{ $posyandu->desa }}</div>
                                        <div class="text-xs text-gray-500">{{ $posyandu->kecamatan }},
                                            {{ $posyandu->kabupaten }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-purple-100 text-purple-800">
                                            {{ count($posyandu->rw_list ?? []) }} RW
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @php
                                            $totalRt = 0;
                                            if (is_array($posyandu->rt_mapping)) {
                                                foreach ($posyandu->rt_mapping as $rtList) {
                                                    if (is_array($rtList)) {
                                                        $totalRt += count($rtList);
                                                    }
                                                }
                                            }
                                        @endphp
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">
                                            {{ $totalRt }} RT
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                        <div class="flex items-center justify-center gap-2 flex-wrap">
                                            <button type="button"
                                                onclick="showDetailPosyandu('{{ $posyandu->nama_posyandu }}', '{{ $posyandu->desa }}', {{ json_encode($posyandu->rw_list ?? []) }}, {{ json_encode($posyandu->rt_mapping ?? []) }})"
                                                class="px-3 py-2 bg-green-500 text-white text-xs font-semibold rounded hover:bg-green-600 transition">
                                                Lihat
                                            </button>
                                            <a href="{{ route('admin.posyandu.edit', $posyandu) }}"
                                                class="px-3 py-2 bg-blue-500 text-white text-xs font-semibold rounded hover:bg-blue-600 transition">
                                                Edit
                                            </a>
                                            <form method="POST"
                                                action="{{ route('admin.posyandu.destroy', $posyandu) }}"
                                                id="delete-form-{{ $posyandu->id }}"
                                                class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" 
                                                    onclick="confirmDeletePosyandu(event, '{{ $posyandu->id }}')"
                                                    class="px-3 py-2 bg-red-500 text-white text-xs font-semibold rounded hover:bg-red-600 transition">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500 text-sm">
                                        Belum ada data posyandu.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $posyandus->links() }}
                </div>
            </div>
        </div>
    </div>
    {{-- Modal Detail RW/RT --}}
    <div x-data="{
        show: false,
        data: { nama: '', desa: '', rw_list: [], rt_mapping: {} }
    }" x-show="show" @open-detail.window="show = true; data = $event.detail"
        class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">

        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" @click="show = false"></div>

        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div
                class="relative bg-white rounded-xl shadow-2xl max-w-2xl w-full p-6 overflow-hidden transition-all transform">
                {{-- Header --}}
                <div class="flex justify-between items-center border-b pb-3 mb-4">
                    <h3 class="text-xl font-bold text-gray-800">
                        Detail Wilayah: <span x-text="data.nama" class="text-pink-600"></span>
                    </h3>
                    <button @click="show = false" class="text-gray-400 hover:text-gray-600">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                {{-- Content --}}
                <div class="max-h-[60vh] overflow-y-auto pr-2">
                    <p class="text-sm text-gray-600 mb-4 italic">
                        Daftar RW dan RT yang terdaftar di Desa <span x-text="data.desa"
                            class="font-semibold text-gray-800"></span>
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <template x-for="rw in data.rw_list" :key="rw">
                            <div class="border rounded-lg bg-gray-50 p-3">
                                <div class="flex items-center gap-2 border-b border-gray-200 pb-2 mb-2">
                                    <span class="bg-pink-100 text-pink-700 px-2 py-0.5 rounded text-xs font-bold"
                                        x-text="rw"></span>
                                    <span class="text-xs text-gray-500 font-medium uppercase tracking-wider">Rukun
                                        Warga</span>
                                </div>

                                <div class="flex flex-wrap gap-2">
                                    <template x-for="rt in (data.rt_mapping[rw] || [])" :key="rt">
                                        <span
                                            class="bg-white border border-gray-300 text-gray-700 px-2 py-1 rounded-md text-xs shadow-sm"
                                            x-text="rt"></span>
                                    </template>
                                    <template x-if="!(data.rt_mapping[rw] || []).length">
                                        <span class="text-xs text-gray-400 italic">Tidak ada RT terdaftar</span>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    <template x-if="!data.rw_list.length">
                        <div class="text-center py-10">
                            <i class="bi bi-exclamation-triangle text-amber-500 text-4xl mb-3"></i>
                            <p class="text-gray-500">Belum ada data RW/RT untuk posyandu ini.</p>
                        </div>
                    </template>
                </div>

                {{-- Footer --}}
                <div class="mt-6 flex justify-end">
                    <button @click="show = false"
                        class="px-5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
        <script>
            function showDetailPosyandu(nama, desa, rwList, rtMapping) {
                let contentHtml = `<div class="text-left mt-4">
        <p class="text-sm text-gray-600 mb-4 italic text-center">Wilayah pelayanan di Desa <strong>${desa}</strong></p>
        <div class="grid grid-cols-1 gap-3" style="max-height: 400px; overflow-y: auto; padding: 5px;">`;

                if (rwList && rwList.length > 0) {
                    rwList.forEach(rw => {
                        const rts = rtMapping[rw] || [];
                        contentHtml += `
                <div class="border rounded-lg p-3 bg-gray-50 border-gray-200">
                    <div class="flex items-center gap-2 mb-2 border-b pb-1">
                        <span class="bg-pink-500 text-white px-2 py-0.5 rounded text-xs font-bold">${rw}</span>
                        <span class="text-xs font-semibold text-gray-500 uppercase">Rukun Warga</span>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        ${rts.map(rt => `<span class="bg-white border border-gray-300 text-gray-700 px-2 py-1 rounded text-xs shadow-sm">${rt}</span>`).join('')}
                        ${rts.length === 0 ? '<span class="text-xs text-gray-400 italic">Tidak ada RT</span>' : ''}
                    </div>
                </div>`;
                    });
                } else {
                    contentHtml += `<div class="text-center py-8 text-gray-500">Belum ada data RW/RT terdaftar.</div>`;
                }

                contentHtml += `</div></div>`;

                Swal.fire({
                    title: `<span class="text-xl font-bold text-gray-800">Detail: ${nama}</span>`,
                    html: contentHtml,
                    width: '600px',
                    confirmButtonText: 'Tutup',
                    confirmButtonColor: '#ec4899',
                    customClass: {
                        popup: 'rounded-2xl shadow-xl',
                        title: 'border-b pb-4'
                    },
                    showCloseButton: true
                });
            }
        </script>
        <script>
            function confirmDeletePosyandu(event, id) {
                event.preventDefault();
                console.log('Delete function called with ID:', id);
                Swal.fire({
                    title: 'Hapus Posyandu?',
                    text: 'Yakin ingin menghapus posyandu ini?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formId = 'delete-form-' + id;
                        console.log('Submitting form with ID:', formId);
                        document.getElementById(formId).submit();
                    }
                });
                return false;
            }
        </script>
        <script>
            function downloadKaderCredentials() {
                const kaders = @json(session('created_kaders'));

                if (!kaders || kaders.length === 0) {
                    alert('Data credentials tidak tersedia');
                    return;
                }

                let content = '==============================================\n';
                content += '   CREDENTIALS KADER AUTO-GENERATED\n';
                content += '==============================================\n\n';
                content += 'Tanggal: ' + new Date().toLocaleString('id-ID') + '\n';
                content += '----------------------------------------------\n\n';

                kaders.forEach((kader, i) => {
                    content += (i + 1) + '. Kader ' + kader.bidang + '\n';
                    content += '   Email    : ' + kader.email + '\n';
                    content += '   Password : ' + kader.password + '\n';
                    content += '----------------------------------------------\n';
                });

                content += '\n';
                content += 'PENTING:\n';
                content += '- Simpan file ini dengan aman!\n';
                content += '- Password default: password123\n';
                content += '- Kader dapat login menggunakan email atau nomor telepon\n';
                content += '- Ketua Posyandu wajib meminta kader untuk mengganti password\n';
                content += '\n';
                content += '==============================================\n';

                const blob = new Blob([content], {
                    type: 'text/plain;charset=utf-8'
                });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'kader_credentials_' + Date.now() + '.txt';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);
            }
        </script>
    @endpush
@endsection
