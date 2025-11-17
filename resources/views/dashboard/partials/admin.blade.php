@section('content')
    <div class="flex flex-col gap-4 md:gap-6 px-4 sm:px-6 lg:px-8 w-full max-w-7xl mx-auto">
        @if (!$isVerified)
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-3 md:p-4 rounded-lg" role="alert">
                <div class="flex">
                    <div class="py-1"><i class="bi bi-shield-lock-fill mr-2 md:mr-3"></i></div>
                    <div>
                        <p class="font-bold text-sm md:text-base">Akun Belum Terverifikasi</p>
                        <p class="text-xs md:text-sm">Akun Anda sedang menunggu verifikasi dari atasan. Anda belum dapat
                            mengelola data apa pun.</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Dashboard Cards Section -->
        <div class="bg-white overflow-hidden shadow-xl rounded-lg md:rounded-2xl p-4 md:p-6 lg:p-8 w-full">
            <h2 class="text-xl md:text-2xl font-bold text-gray-800 mb-4 md:mb-6">Dashboard Ajuan Pelayanan</h2>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6">
                <!-- Cards Grid -->
                <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-3 md:gap-4">
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
                            class="{{ $bidangColors[$bidang] ?? 'bg-gray-500' }} text-white p-3 md:p-4 lg:py-4 lg:px-8 rounded-lg shadow-md flex items-center gap-3 md:gap-4">
                            <span class="text-4xl md:text-5xl lg:text-6xl font-bold">{{ $total }}</span>
                            <div class="flex flex-col gap-1 md:gap-2">
                                <img src="{{ $icons[\Illuminate\Support\Str::slug(str_replace('Bidang ', '', $bidang))] ?? asset('assets/image/icon/bidang/default.svg') }}"
                                    alt="{{ $bidang }} icon" class="w-6 h-6 md:w-8 md:h-8 mx-2 md:mx-3">
                                <span
                                    class="ml-2 md:ml-3 text-xs md:text-sm lg:text-base font-semibold leading-tight">{{ $bidang }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pie Chart -->
                <div class="bg-white p-3 md:p-4 rounded-lg" x-data="pieChartData" x-init="drawChart()">
                    <canvas x-ref="pieChart"></canvas>
                </div>
            </div>
        </div>

        <!-- List Pengajuan Section -->
        <div class="w-full">
            <div class="bg-white overflow-hidden shadow-xl rounded-lg md:rounded-2xl p-4 md:p-6 lg:p-8">

                <!-- Header with Search and Export -->
                <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-4 md:mb-6">
                    <h2 class="text-xl md:text-2xl font-bold text-gray-800">List Pengajuan</h2>

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 md:gap-3 w-full md:w-auto">
                        <form action="{{ route('ajuan.index') }}" method="GET"
                            class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full md:w-auto">

                            <!-- Filter Status -->
                            <select name="status" onchange="this.form.submit()"
                                class="border-gray-300 rounded-md shadow-sm text-sm w-full sm:w-auto px-3 py-2">
                                <option value="">Semua Status</option>
                                <option value="Diproses" @selected(request('status') == 'Diproses')>Diproses</option>
                                <option value="Disetujui" @selected(request('status') == 'Disetujui')>Disetujui</option>
                                <option value="Ditolak" @selected(request('status') == 'Ditolak')>Ditolak</option>
                            </select>

                            <!-- Search Input -->
                            <div class="relative w-full sm:w-auto">
                                <input type="text" name="search" placeholder="Cari berdasarkan nama..."
                                    value="{{ request('search') }}"
                                    class="w-full sm:w-64 pl-4 pr-10 py-2 border border-gray-300 rounded-md text-sm focus:ring-pink-500 focus:border-pink-500">
                                <button type="submit" class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <i class="bi bi-search text-gray-400"></i>
                                </button>
                            </div>

                            <!-- Reset Link -->
                            @if (request('search') || request('status'))
                                <a href="{{ route('dashboard') }}"
                                    class="text-sm text-center sm:text-left text-gray-600 hover:text-gray-900 py-2 sm:py-0">Reset</a>
                            @endif
                        </form>

                        <!-- Export Button -->
                        <button id="exportExcelBtn"
                            class="flex items-center justify-center px-4 py-2 bg-green-500 text-white text-sm md:text-base rounded-md hover:bg-green-600 whitespace-nowrap">
                            <i class="bi bi-file-earmark-excel-fill mr-2"></i>
                            <span class="hidden sm:inline">Export to Excel</span>
                            <span class="sm:hidden">Export</span>
                        </button>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto -mx-4 md:mx-0">
                    <div class="inline-block min-w-full align-middle">
                        @include('ajuan.table', ['semuaAjuan' => $semuaAjuan])
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-4 flex justify-between items-center text-xs md:text-sm text-gray-600">
                    {{ $semuaAjuan->links() }}
                </div>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('pieChartData', () => ({
                chart: null,

                drawChart() {
                    if (typeof Chart === 'undefined') {
                        console.error('Chart.js belum loaded! Retry dalam 100ms...');
                        setTimeout(() => this.drawChart(), 100);
                        return;
                    }

                    const ctx = this.$refs.pieChart;
                    if (!ctx) {
                        console.error('Canvas element tidak ditemukan!');
                        return;
                    }

                    if (this.chart) {
                        this.chart.destroy();
                    }

                    const rawLabels = @json(array_keys($ajuanCounts->toArray() ?? []));
                    const rawData = @json(array_values($ajuanCounts->toArray() ?? []));

                    const filteredData = rawLabels.map((label, index) => ({
                        label: label.replace('Bidang ', ''),
                        value: rawData[index]
                    })).filter(item => item.value > 0);

                    const labels = filteredData.length > 0 ?
                        filteredData.map(item => item.label) : ['Tidak Ada Data'];
                    const data = filteredData.length > 0 ?
                        filteredData.map(item => item.value) : [1];

                    const bidangColors = {
                        'Perumahan Rakyat': 'rgb(59, 130, 246)',
                        'Pendidikan': 'rgb(251, 146, 60)',
                        'Kesehatan': 'rgb(236, 72, 153)',
                        'Sosial': 'rgb(251, 113, 133)',
                        'Pekerjaan Umum': 'rgb(34, 197, 94)',
                        'Trantibumlinmas': 'rgb(234, 179, 8)'
                    };

                    const colors = labels.map(label => bidangColors[label] || 'rgb(209, 213, 219)');

                    // Responsive font sizes
                    const isMobile = window.innerWidth < 640;
                    const fontSize = isMobile ? 9 : 11;
                    const legendPadding = isMobile ? 10 : 15;

                    this.chart = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: labels,
                            datasets: [{
                                data: data,
                                backgroundColor: colors,
                                borderWidth: 2,
                                borderColor: '#fff',
                                hoverOffset: 10
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        padding: legendPadding,
                                        font: {
                                            size: fontSize
                                        },
                                        boxWidth: 12,
                                        usePointStyle: true
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.label || '';
                                            let value = context.parsed || 0;
                                            let total = context.dataset.data.reduce((a,
                                                b) => a + b, 0);
                                            let percentage = ((value * 100) / total)
                                                .toFixed(1);
                                            return ` ${label}: ${value} (${percentage}%)`;
                                        }
                                    }
                                },
                                datalabels: {
                                    color: '#fff',
                                    font: {
                                        weight: 'bold',
                                        size: isMobile ? 12 : 14
                                    },
                                    formatter: (value, ctx) => {
                                        let sum = ctx.chart.data.datasets[0].data.reduce((a,
                                            b) => a + b, 0);
                                        let percentage = ((value * 100) / sum).toFixed(1);
                                        return percentage > 5 ? percentage + '%' : '';
                                    }
                                }
                            }
                        }
                    });

                    console.log('✅ Chart berhasil dibuat dengan ' + labels.length + ' bidang!', this
                        .chart);
                },

                destroy() {
                    if (this.chart) {
                        this.chart.destroy();
                    }
                }
            }));
        });

        @php
            use Illuminate\Support\Facades\Auth;
            $desaUser = optional(optional(Auth::user()->posyandu)->desa);
        @endphp

        const userRole = "{{ Auth::user()->role }}";
        const userDesa = "{{ Auth::user()?->posyandu?->desa ?? '' }}";

        document.getElementById('exportExcelBtn').addEventListener('click', function() {
            let bidangSelectHTML = `
            <div>
                <label class="block font-semibold mb-1 text-gray-700 text-sm md:text-base">Pilih Bidang:</label>
                <select id="selectBidang" class="w-full border rounded-md p-2 text-sm md:text-base" required>
                    <option value="" disabled selected>Pilih Bidang</option>
                    <option value="all">Semua Bidang</option>
                    <option value="Bidang Perumahan Rakyat">Bidang Perumahan Rakyat</option>
                    <option value="Bidang Pendidikan">Bidang Pendidikan</option>
                    <option value="Bidang Kesehatan">Bidang Kesehatan</option>
                    <option value="Bidang Sosial">Bidang Sosial</option>
                    <option value="Bidang Pekerjaan Umum">Bidang Pekerjaan Umum</option>
                    <option value="Bidang Trantibumlinmas">Bidang Trantibumlinmas</option>
                </select>
            </div>
        `;

            let desaSelectHTML = "";
            @if (Auth::user()->role === 'kabid')
                desaSelectHTML = `
                <div>
                    <label class="block font-semibold mb-1 text-gray-700 text-sm md:text-base">Pilih Desa:</label>
                    <select id="selectDesa" class="w-full border rounded-md p-2 text-sm md:text-base" required>
                        <option value="" disabled selected>Pilih Desa</option>
                        <option value="all">Semua Desa</option>
                        @foreach ($desas as $desa)
                            <option value="{{ $desa }}">{{ strtoupper($desa) }}</option>
                        @endforeach
                    </select>
                </div>
            `;
            @endif

            Swal.fire({
                title: '<h2 class="text-lg md:text-xl font-bold text-gray-800 mb-2">Pilih Bidang & Desa untuk Di-Export</h2>',
                html: `
                <div class="space-y-3 md:space-y-4">
                    ${bidangSelectHTML}
                    ${desaSelectHTML}
                    <button id="confirmExportBtn"
                        class="swal2-confirm swal2-styled !bg-emerald-600 hover:!bg-emerald-700 w-full py-3 md:py-4 rounded-md text-white text-sm md:text-base font-bold shadow-lg">
                        Export Data
                    </button>
                </div>
            `,
                showConfirmButton: false,
                showCancelButton: true,
                cancelButtonText: 'Batal',
                width: window.innerWidth < 640 ? '95%' : 600,
                background: '#f9fafb',
                customClass: {
                    popup: 'rounded-lg md:rounded-2xl shadow-lg p-4 md:p-6',
                    cancelButton: 'bg-white outline outline-red-500 mt-3 md:mt-4 text-red-500 hover:text-red-600 text-sm md:text-base font-medium hover:bg-red-500 hover:text-white py-2 px-4 md:px-6'
                }
            });

            document.addEventListener('click', function handler(e) {
                if (e.target && e.target.id === 'confirmExportBtn') {
                    let bidang = null;
                    let desa = null;

                    if (userRole === 'ketua-kader') {
                        bidang = document.getElementById('selectBidang').value;
                        desa = userDesa;
                    } else if (userRole === 'kabid') {
                        bidang = document.getElementById('selectBidang').value;
                        desa = document.getElementById('selectDesa')?.value || '';
                    }

                    if (!bidang) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Bidang Belum Dipilih!',
                            text: 'Silakan pilih bidang terlebih dahulu sebelum export data.',
                            confirmButtonColor: '#f87171',
                            confirmButtonText: 'OK'
                        });
                        return;
                    } else if (!desa) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Desa Belum Dipilih!',
                            text: 'Silakan pilih desa terlebih dahulu sebelum export data.',
                            confirmButtonColor: '#f87171',
                            confirmButtonText: 'OK'
                        });
                        return;
                    }
                    exportData(bidang, desa);
                    document.removeEventListener('click', handler);
                }
            });
        });

        function exportData(bidang, desa) {
            if (userRole === 'ketua-kader') {
                const url = `/admin/export/${encodeURIComponent(bidang)}/${userDesa}`;
                window.location.href = url;
            } else if (bidang === 'all' && userRole === 'kabid') {
                window.location.href = `/admin/export-all/${desa}`;
            } else {
                const url = `/admin/export/${encodeURIComponent(bidang)}/${encodeURIComponent(desa)}`;
                window.location.href = url;
            }
        }
    </script>
@endsection
