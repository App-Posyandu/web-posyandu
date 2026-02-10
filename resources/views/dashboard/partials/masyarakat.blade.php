@section('content')
    <div x-data="masyarakatDashboard" x-init="init()"
        class="flex flex-col gap-4 md:gap-6 px-4 sm:px-6 lg:px-8 w-full mx-auto">
        <div class="bg-gradient-to-r from-pink-500 to-fuchsia-500 text-white rounded-lg md:rounded-2xl p-6 shadow-xl">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="flex-shrink-0">
                        <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold">Selamat Datang, {{ auth()->user()->name }}!</h1>
                        <p class="text-indigo-100">Berikut adalah ringkasan pengajuan layanan Anda</p>
                    </div>
                </div>
                <div class="w-full sm:w-auto">
                    <a href="{{ route('dashboard.partials.pilih-layanan') }}"
                        class="w-full sm:w-auto flex items-center justify-center px-6 py-3 bg-white text-pink-500 rounded-lg text-sm font-semibold hover:bg-indigo-50 transition-all duration-150 shadow-lg">
                        <i class="bi bi-plus-circle-fill mr-2"></i>
                        <span>Buat Pengajuan Baru</span>
                    </a>
                </div>
            </div>
        </div>
        <div class="bg-white overflow-hidden shadow-xl rounded-lg md:rounded-2xl p-4 md:p-6 w-full">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-12 h-12 bg-indigo-100 rounded-lg">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-600">Periode Data</h3>
                        <p class="text-lg font-bold text-gray-800">
                            Tahun {{ $selectedYear }}
                            @if ($selectedYear == $currentYear)
                                <span class="ml-2 px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full">
                                    Tahun Berjalan
                                </span>
                            @endif
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <label for="yearFilter" class="text-sm font-medium text-gray-700 whitespace-nowrap">
                        Pilih Tahun:
                    </label>
                    <form method="GET" action="{{ route('dashboard') }}" id="yearFilterForm" class="flex gap-2">
                        @if (request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                        @if (request('status'))
                            <input type="hidden" name="status" value="{{ request('status') }}">
                        @endif

                        <select name="year" id="yearFilter" onchange="this.form.submit()"
                            class="block w-full md:w-auto px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            @foreach ($availableYears as $year)
                                <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                                    {{ $year }}
                                    @if ($year == $currentYear)
                                        (Tahun Ini)
                                    @endif
                                </option>
                            @endforeach
                        </select>

                        @if ($selectedYear != $currentYear)
                            <a href="{{ route('dashboard') }}"
                                class="inline-flex items-center px-4 py-2 bg-gray-500 text-white text-sm rounded-lg hover:bg-gray-600 transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                    </path>
                                </svg>
                                Reset
                            </a>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-xl rounded-lg md:rounded-2xl p-4 md:p-6 w-full">
            <h2 class="text-xl md:text-2xl font-bold text-gray-800 mb-4">Pengajuan Saya</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-indigo-50 rounded-lg p-4 text-center">
                    <p class="text-3xl font-bold text-indigo-600" x-text="myStats.total"></p>
                    <p class="text-sm text-gray-600 mt-1">Total Pengajuan</p>
                </div>
                <div class="bg-green-50 rounded-lg p-4 text-center">
                    <p class="text-3xl font-bold text-green-600" x-text="myStats.disetujui"></p>
                    <p class="text-sm text-gray-600 mt-1">Disetujui</p>
                </div>
                <div class="bg-yellow-50 rounded-lg p-4 text-center">
                    <p class="text-3xl font-bold text-yellow-600" x-text="myStats.diproses"></p>
                    <p class="text-sm text-gray-600 mt-1">Diproses</p>
                </div>
                <div class="bg-red-50 rounded-lg p-4 text-center">
                    <p class="text-3xl font-bold text-red-600" x-text="myStats.ditolak"></p>
                    <p class="text-sm text-gray-600 mt-1">Ditolak</p>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-xl rounded-lg md:rounded-2xl p-4 md:p-6 lg:p-8 w-full">
            @php
                $label = '';
                if (auth()->user()->posyandu && auth()->user()->posyandu->nama_posyandu) {
                    $label = 'Posyandu ' . auth()->user()->posyandu->nama_posyandu;
                }
            @endphp
            <h2 class="text-xl md:text-2xl font-bold text-gray-800 mb-4 md:mb-6">
                Statistik Pengajuan Desa - 
                @if ($label)
                    <span class="text-pink-500">{{ $label }}</span>
                @endif
            </h2>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6">
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

                <div class="bg-white p-3 md:p-4 rounded-lg" x-data="pieChartData" x-init="drawChart()">
                    <canvas x-ref="pieChart"></canvas>
                </div>
            </div>


        </div>

        <div class="w-full">
            <div class="bg-white overflow-hidden shadow-xl rounded-lg md:rounded-2xl p-4 md:p-6 lg:p-8">
                <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-4 md:mb-6">
                    <h2 class="text-xl md:text-2xl font-bold text-gray-800">Riwayat Pengajuan Saya</h2>

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 md:gap-3 w-full md:w-auto">
                        <select x-model="filterStatus" @change="loadDashboardData()"
                            class="border-gray-300 rounded-md shadow-sm text-sm w-full sm:w-auto px-3 py-2">
                            <option value="">Semua Status</option>
                            <option value="Diproses">Diproses</option>
                            <option value="Disetujui">Disetujui</option>
                            <option value="Ditolak">Ditolak</option>
                        </select>

                        <div class="relative w-full sm:w-auto">
                            <input type="text" x-model="searchQuery" @input.debounce.500ms="loadDashboardData()"
                                placeholder="Cari pengajuan..."
                                class="w-full sm:w-64 pl-4 pr-10 py-2 border border-gray-300 rounded-md text-sm focus:ring-pink-500 focus:border-pink-500">
                            <i class="bi bi-search absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400"></i>
                        </div>

                        <button x-show="searchQuery || filterStatus" @click="resetFilters()"
                            class="text-sm text-center sm:text-left text-gray-600 hover:text-gray-900 py-2 sm:py-0">
                            Reset
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto -mx-4 md:mx-0">
                    <div class="inline-block min-w-full align-middle">
                        <div x-show="loading" class="text-center py-12">
                            <div class="inline-flex items-center gap-2 text-gray-600">
                                <svg class="animate-spin h-8 w-8" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                <span>Memuat data...</span>
                            </div>
                        </div>

                        <div x-show="!loading" x-html="tableHtml"></div>
                    </div>
                </div>

                <div class="mt-4 flex justify-between items-center text-xs md:text-sm text-gray-600">
                    <div x-html="paginationHtml"></div>
                </div>
            </div>
        </div>
    </div>

    @php
        $chartData = $ajuanCounts
            ->map(function ($total, $nama) use ($icons) {
                $colorMap = [
                    'Bidang Perumahan Rakyat' => 'bg-blue-500',
                    'Bidang Pendidikan' => 'bg-orange-500',
                    'Bidang Kesehatan' => 'bg-pink-500',
                    'Bidang Sosial' => 'bg-rose-500',
                    'Bidang Pekerjaan Umum' => 'bg-green-500',
                    'Bidang Trantibumlinmas' => 'bg-yellow-500',
                ];

                $slug = \Illuminate\Support\Str::slug(str_replace('Bidang ', '', $nama));
                $icon = $icons[$slug] ?? asset('assets/image/icon/bidang/default.svg');

                return [
                    'name' => $nama,
                    'total' => $total,
                    'color' => $colorMap[$nama] ?? 'bg-gray-500',
                    'icon' => $icon,
                ];
            })
            ->values();
    @endphp

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('masyarakatDashboard', () => ({
                currentYear: {{ $currentYear }},
                selectedYear: {{ $selectedYear }},
                availableYears: @json($availableYears),
                searchQuery: '',
                filterStatus: '',
                loading: false,
                isVerified: true,
                bidangData: @json($chartData),

                statistics: {
                    total: {{ $ajuanCounts->sum() }},
                    disetujui: 0,
                    diproses: 0,
                    ditolak: 0,
                },

                myStats: {
                    total: {{ $myStats['total'] }},
                    disetujui: {{ $myStats['disetujui'] }},
                    diproses: {{ $myStats['diproses'] }},
                    ditolak: {{ $myStats['ditolak'] }},
                },
                paginationInfo: {
                    from: 0,
                    to: 0,
                    total: 0
                },
                tableHtml: '',
                paginationHtml: '',
                chart: null,
                init() {
                    this.drawChart();
                    this.loadDashboardData();
                },

                async loadDashboardData() {
                    this.loading = true;

                    try {
                        const params = new URLSearchParams({
                            year: this.selectedYear,
                            search: this.searchQuery,
                            status: this.filterStatus,
                            ajax: '1'
                        });

                        const response = await fetch(`{{ route('dashboard') }}?${params}`);
                        const data = await response.json();

                        this.bidangData = data.bidangData;
                        this.statistics = data.statistics;
                        this.myStats = data.myStats;
                        this.tableHtml = data.tableHtml;
                        this.paginationHtml = data.paginationHtml;
                        console.log(data)

                        this.paginationInfo = data.paginationInfo;
                        this.updateChart();

                    } catch (error) {
                        console.error('Error loading dashboard data:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                handlePagination(event) {
                    if (event.target.tagName === 'A' || event.target.closest('a')) {
                        event.preventDefault();
                        const link = event.target.tagName === 'A' ? event.target : event.target.closest(
                            'a');
                        const url = new URL(link.href);
                        const page = url.searchParams.get('page') || 1;
                        this.loadDashboardData(page);
                    }
                },

                resetToCurrentYear() {
                    this.selectedYear = this.currentYear;
                    this.loadDashboardData();
                },

                resetFilters() {
                    this.searchQuery = '';
                    this.filterStatus = '';
                    this.loadDashboardData();
                },

                drawChart() {
                    if (typeof Chart === 'undefined') {
                        setTimeout(() => this.drawChart(), 100);
                        return;
                    }

                    const ctx = this.$refs.pieChart;
                    if (!ctx) return;

                    if (this.chart) {
                        this.chart.destroy();
                    }

                    this.updateChart();
                },

                updateChart() {
                    const ctx = this.$refs.pieChart;
                    if (!ctx) return;

                    if (this.chart) {
                        this.chart.destroy();
                    }

                    const filteredData = this.bidangData.filter(item => item.total > 0);
                    const labels = filteredData.length > 0 ?
                        filteredData.map(item => item.name.replace('Bidang ', '')) : ['Tidak Ada Data'];
                    const data = filteredData.length > 0 ?
                        filteredData.map(item => item.total) : [1];

                    const bidangColors = {
                        'Perumahan Rakyat': 'rgb(59, 130, 246)',
                        'Pendidikan': 'rgb(251, 146, 60)',
                        'Kesehatan': 'rgb(236, 72, 153)',
                        'Sosial': 'rgb(251, 113, 133)',
                        'Pekerjaan Umum': 'rgb(34, 197, 94)',
                        'Trantibumlinmas': 'rgb(234, 179, 8)'
                    };

                    const colors = labels.map(label => bidangColors[label] || 'rgb(209, 213, 219)');

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
                                        padding: 15,
                                        font: {
                                            size: 11
                                        },
                                        boxWidth: 12,
                                        usePointStyle: true
                                    }
                                }
                            }
                        }
                    });
                }
            }));

            Alpine.data('pieChartData', () => ({
                chart: null,

                drawChart() {
                    if (typeof Chart === 'undefined') {
                        setTimeout(() => this.drawChart(), 100);
                        return;
                    }

                    const ctx = this.$refs.pieChart;
                    if (!ctx) return;

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
                                }
                            }
                        }
                    });
                },

                destroy() {
                    if (this.chart) {
                        this.chart.destroy();
                    }
                }
            }));
        });
    </script>
@endsection
