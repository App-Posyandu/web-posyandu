@section('content')
    <div class="flex flex-col gap-6">

        <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl p-8 space-y-12 w-full">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Dashboard Ajuan Pelayanan</h2>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @php
                        $bidangColors = [
                            'Bidang Perumahan Rakyat' => 'bg-blue-500',
                            'Bidang Pendidikan' => 'bg-orange-500',
                            'Bidang Kesehatan' => 'bg-pink-500',
                            'Bidang Sosial' => 'bg-rose-500',
                            'Bidang Pekerjaan Umum' => 'bg-green-500',
                            'Bidang Trantibumlinmas' => 'bg-yellow-500',
                        ];
                    @endphp
                    @foreach ($ajuanCounts as $bidang => $total)
                        <div
                            class="{{ $bidangColors[$bidang] ?? 'bg-gray-500' }} text-white p-4 rounded-lg shadow-md flex items-center">
                            <span class="text-5xl font-bold">{{ $total }}</span>
                            <span class="ml-3 font-semibold">{{ $bidang }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="bg-white p-4 rounded-lg" x-data="pieChartData" x-init="drawChart()">
                    <canvas id="ajuanPieChart"></canvas>
                </div>
            </div>
        </div>

        <div class="w-full max-w-7xl mx-auto ">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl p-8">

                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">List Pengajuan</h2>
                    <form action="{{ route('dashboard') }}" method="GET">
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3"><i
                                    class="bi bi-search text-gray-400"></i></span>
                            <input type="text" name="search" placeholder="Search" value="{{ request('search') }}"
                                class="w-full md:w-64 pl-10 pr-4 py-2 ...">
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nama Pengaju</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Bidang</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Deskripsi Permohonan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tindak Lanjut Pengajuan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status Pengajuan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($semuaAjuan as $ajuan)
                                <tr>
                                    <td class="px-6 py-4 ...">
                                        {{ ($semuaAjuan->currentPage() - 1) * $semuaAjuan->perPage() + $loop->iteration }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $ajuan->user?->name ?? 'Pengguna Dihapus' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $ajuan->bidang->nama_bidang }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ Str::limit($ajuan->deskripsi_pengajuan ?? 'Tidak ada deskripsi', 50) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if ($ajuan->status == 'Disetujui')
                                            <span
                                                class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Sudah
                                                Verifikasi</span>
                                        @elseif($ajuan->status == 'Ditolak')
                                            <span
                                                class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                                        @else
                                            <span
                                                class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-yellow-700">Diproses</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if ($ajuan->status == 'Disetujui')
                                            <span
                                                class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Sudah
                                                Verifikasi</span>
                                        @elseif($ajuan->status == 'Ditolak')
                                            <span
                                                class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
                                        @else
                                            <span
                                                class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-yellow-700">Diproses</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex space-x-2">
                                        <a href="#"
                                            class="flex items-center px-3 py-1 bg-blue-500 text-white rounded-md text-xs hover:bg-blue-600"><i
                                                class="bi bi-eye-fill mr-1"></i> Detail</a>
                                        @if (Auth::user()->role == 'kader' || Auth::user()->role == 'kabid')
                                            <a href="#"
                                                class="flex items-center px-3 py-1 bg-yellow-500 text-white rounded-md text-xs hover:bg-yellow-600"><i
                                                    class="bi bi-pencil-fill mr-1"></i> Ubah</a>
                                        @endif
                                        <a href="#"
                                            class="flex items-center px-3 py-1 bg-green-500 text-white rounded-md text-xs hover:bg-green-600"><i
                                                class="bi bi-printer-fill mr-1"></i> Cetak</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">Tidak ada
                                        data pengajuan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 flex justify-between items-center text-sm text-gray-600">
                    {{ $semuaAjuan->links() }}
                </div>

            </div>
        </div>
    </div>
    <script>
        console.log("PIEEEE")
        const ctx = document.getElementById('ajuanPieChart');

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: @json($ajuanCounts->keys()),
                datasets: [{
                    label: 'Jumlah Pengajuan',
                    data: @json($ajuanCounts->values()),
                    backgroundColor: @json($colors),
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                // Potong bagian tengah doughnut chart
                cutout: '60%',
                plugins: {
                    // Pengaturan legenda (sudah ada)
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            padding: 20
                        }
                    },
                },
                datalabels: {
                    // Fungsi untuk mengubah nilai menjadi persentase
                    formatter: (value, ctx) => {
                        let sum = 0;
                        let dataArr = ctx.chart.data.datasets[0].data;
                        dataArr.map(data => {
                            sum += data;
                        });
                        let percentage = (value * 100 / sum).toFixed(0) + "%";
                        return percentage;
                    },
                    // Atur warna dan ukuran font
                    color: '#fff',
                    font: {
                        weight: 'bold',
                        size: 14,
                    }
                }
            }
        });
    </script>
@endsection
