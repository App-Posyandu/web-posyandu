@section('content')
    <div class="flex flex-col gap-6">
        @if (!$isVerified)
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded-lg mb-6" role="alert">
                <div class="flex">
                    <div class="py-1"><i class="bi bi-shield-lock-fill mr-3"></i></div>
                    <div>
                        <p class="font-bold">Akun Belum Terverifikasi</p>
                        <p class="text-sm">Akun Anda sedang menunggu verifikasi dari atasan. Anda belum dapat mengelola data
                            apa pun.</p>
                    </div>
                </div>
            </div>
        @endif
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
                                class="w-full md:w-64 pl-10 pr-4 py-2">
                        </div>
                    </form>
                </div>

                @include('ajuan.table', ['semuaAjuan' => $semuaAjuan])

                <div class="mt-4 flex justify-between items-center text-sm text-gray-600">
                    {{ $semuaAjuan->links() }}
                </div>

            </div>
        </div>
    </div>
    <script>
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
