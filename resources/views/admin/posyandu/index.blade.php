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
                                ✅ Posyandu & 6 Akun Kader Berhasil Dibuat!
                            </h3>

                            <div class="bg-white rounded-lg p-4 mb-4">
                                <p class="text-sm text-gray-700 mb-3">
                                    <strong>⚠️ PENTING:</strong> Salin dan simpan credentials di bawah ini.
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
                                                            <span class="text-gray-600 w-32">📧 Email:</span>
                                                            <code class="bg-white px-2 py-1 rounded border text-gray-800">
                                                                {{ $kaderInfo['email'] }}
                                                            </code>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <span class="text-gray-600 w-32">🔐 Password:</span>
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
                                                    📋 Copy
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="bg-amber-50 border border-amber-300 rounded-lg p-4">
                                <p class="text-sm text-amber-800">
                                    <strong>📌 Catatan:</strong>
                                </p>
                                <ul class="list-disc list-inside text-sm text-amber-700 mt-2 space-y-1">
                                    <li>Semua kader sudah <strong>terverifikasi</strong> dan <strong>aktif</strong></li>
                                    <li>Password default: <code class="bg-white px-2 py-1 rounded">kader123</code></li>
                                    <li>Kader dapat login menggunakan <strong>email</strong> atau <strong>nomor
                                            telepon</strong></li>
                                    <li>Ketua Kader dapat <strong>melengkapi data</strong> kader (NIK, tanggal lahir, dll)
                                    </li>
                                    <li>Ketua Kader dapat <strong>reset password</strong> kader jika diperlukan</li>
                                </ul>
                            </div>

                            {{-- Action Buttons --}}
                            <div class="mt-4 flex gap-3">
                                <button onclick="printKaderCredentials()"
                                    class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                                        </path>
                                    </svg>
                                    Print Credentials
                                </button>

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

                <script>
                    // Copy credentials untuk satu kader
                    function copyKaderCredentials(index) {
                        const kaders = @json(session('created_kaders'));
                        const kader = kaders[index];

                        const text = `Kader ${kader.bidang}
Email: ${kader.email}
No Telepon: ${kader.no_telepon}
Password: ${kader.password}`;

                        navigator.clipboard.writeText(text).then(() => {
                            alert('Credentials berhasil disalin!');
                        });
                    }

                    // Print semua credentials
                    function printKaderCredentials() {
                        const kaders = @json(session('created_kaders'));
                        let content = '<html><head><title>Kader Credentials</title>';
                        content +=
                            '<style>body{font-family:Arial;padding:20px;}table{width:100%;border-collapse:collapse;}th,td{border:1px solid #ddd;padding:8px;text-align:left;}th{background:#4a5568;color:white;}</style>';
                        content += '</head><body>';
                        content += '<h2>🏥 Credentials Kader Auto-Generated</h2>';
                        content += '<p><strong>Tanggal:</strong> ' + new Date().toLocaleString('id-ID') + '</p>';
                        content +=
                            '<table><thead><tr><th>No</th><th>Bidang</th><th>Email</th><th>No Telepon</th><th>Password</th></tr></thead><tbody>';

                        kaders.forEach((kader, i) => {
                            content += `<tr>
                <td>${i + 1}</td>
                <td>${kader.bidang}</td>
                <td>${kader.email}</td>
                <td>${kader.no_telepon}</td>
                <td><strong>${kader.password}</strong></td>
            </tr>`;
                        });

                        content += '</tbody></table>';
                        content += '<p style="margin-top:20px;"><strong>⚠️ PENTING:</strong> Simpan dokumen ini dengan aman!</p>';
                        content += '</body></html>';

                        const printWindow = window.open('', '_blank');
                        printWindow.document.write(content);
                        printWindow.document.close();
                        printWindow.print();
                    }

                    // Download as TXT
                    function downloadKaderCredentials() {
                        const kaders = @json(session('created_kaders'));
                        let content = '=== CREDENTIALS KADER AUTO-GENERATED ===\n';
                        content += 'Tanggal: ' + new Date().toLocaleString('id-ID') + '\n\n';

                        kaders.forEach((kader, i) => {
                            content += `${i + 1}. Kader ${kader.bidang}\n`;
                            content += `   Email: ${kader.email}\n`;
                            content += `   No Telepon: ${kader.no_telepon}\n`;
                            content += `   Password: ${kader.password}\n\n`;
                        });

                        content += '⚠️ PENTING: Simpan file ini dengan aman!\n';
                        content += 'Password default: kader123\n';

                        const blob = new Blob([content], {
                            type: 'text/plain'
                        });
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = 'kader_credentials_' + Date.now() + '.txt';
                        a.click();
                        window.URL.revokeObjectURL(url);
                    }
                </script>
            @endif
            <div class="p-6 text-gray-900">

                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">List Posyandu</h2>
                    <a href="{{ route('admin.posyandu.create') }}"
                        class="px-4 py-2 bg-pink-500 text-white rounded-md text-sm font-semibold hover:bg-pink-600">
                        Tambah Posyandu
                    </a>
                </div>

                @include('components.all-notifications')

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase">No</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase">Nama Posyandu
                                </th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase">Ketua Kader
                                </th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase">Desa/Kelurahan
                                </th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase">Kecamatan</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase">Kabupaten</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($posyandus as $posyandu)
                                @php
                                    $ketuaKader = $posyandu->users->firstWhere('role', 'ketua-kader');
                                @endphp
                                <tr>
                                    <td class="px-6 py-4">{{ $loop->iteration + $posyandus->firstItem() - 1 }}</td>
                                    <td class="px-6 py-4 font-medium">{{ $posyandu->nama_posyandu }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $ketuaKader?->name ?? 'Belum Ditentukan' }}
                                    </td>
                                    <td>{{ $posyandu->desa ?? '' }}</td>
                                    <td>{{ $posyandu->kecamatan ?? '' }}</td>
                                    <td>{{ $posyandu->kabupaten ?? '' }}</td>

                                    <td class="px-6 py-4 flex space-x-2">
                                        <a href="{{ route('admin.posyandu.edit', $posyandu) }}"
                                            class="flex items-center justify-center w-24 px-3 py-1 bg-yellow-500 text-white rounded-md text-xs hover:bg-yellow-600">Ubah</a>
                                        <form action="{{ route('admin.posyandu.destroy', $posyandu) }}" method="POST"
                                            onsubmit="return confirm('Yakin hapus?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="flex items-center justify-center w-24 px-3 py-1 bg-red-500 text-white rounded-md text-xs hover:bg-red-600">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">Tidak ada data Posyandu.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $posyandus->links() }}</div>
            </div>
        </div>
    </div>
@endsection
