@section('content')
    <div class="w-full mx-auto sm:px-6 lg:px-8">
        <div class="bg-white lg:bg-transparent overflow-hidden sm:shadow-xl lg:shadow-none sm:rounded-2xl p-4 sm:p-6 lg:p-8 min-h-screen sm:min-h-0">
            <div x-data="dashboardFilter" x-init="init()"
                class="flex flex-col gap-4 md:gap-6 w-full mx-auto">
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
                            @else
                                <span class="ml-2 px-2 py-1 text-xs font-semibold text-gray-600 bg-gray-100 rounded-full">
                                    Data Historis
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
                            class="block w-full md:min-w-[180px] px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
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

            <div x-show="!loading && isVerified" class="mt-4 pt-4 border-t border-gray-200">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-indigo-600" x-text="statistics.total"></p>
                        <p class="text-xs text-gray-600">Total Pengajuan</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-green-600" x-text="statistics.disetujui"></p>
                        <p class="text-xs text-gray-600">Disetujui</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-yellow-600" x-text="statistics.diproses"></p>
                        <p class="text-xs text-gray-600">Diproses</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-red-600" x-text="statistics.ditolak"></p>
                        <p class="text-xs text-gray-600">Ditolak</p>
                    </div>
                </div>
            </div>

            <div x-show="loading" class="mt-4 pt-4 border-t border-gray-200 text-center">
                <div class="inline-flex items-center gap-2 text-indigo-600">
                    <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <span class="text-sm">Memuat data...</span>
                </div>
            </div>
        </div>

        <div x-show="selectedYear != currentYear" class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg" x-transition>
            <div class="flex">
                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                        clip-rule="evenodd">
                    </path>
                </svg>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        Anda sedang melihat data tahun <strong x-text="selectedYear"></strong> (data historis).
                        <button @click="resetToCurrentYear()" class="font-semibold underline hover:text-blue-800">
                            Kembali ke tahun <span x-text="currentYear"></span>
                        </button>
                    </p>
                </div>
            </div>
        </div>
        @if (!in_array(auth()->user()->role, ['kader']))
            @php
                $labels = '';

                if (
                    (auth()->user()->role === 'admin-kabupaten' && auth()->user()->kabupaten) ||
                    (auth()->user()->role === 'ketua-timpembina-posyandu' && auth()->user()->kabupaten)
                ) {
                    $labels = 'Kabupaten ' . auth()->user()->kabupaten;
                } elseif (auth()->user()->role === 'admin-kecamatan' && auth()->user()->kecamatan) {
                    $kecLabel = 'Kecamatan ' . auth()->user()->kecamatan;
                    $kabLabel = auth()->user()->kabupaten ? ', ' . auth()->user()->kabupaten : '';
                    $labels = $kecLabel . $kabLabel;
                } elseif (auth()->user()->role === 'kabid') {
                    $bidangLabel = auth()->user()->bidang ? auth()->user()->bidang->nama_bidang : 'Bidang';
                    $kabLabel = auth()->user()->kabupaten ? ' - ' . auth()->user()->kabupaten : '';
                    $labels = $bidangLabel . $kabLabel;
                } elseif (
                    (auth()->user()->role === 'operator-desa' && auth()->user()->desa) ||
                    (auth()->user()->role === 'kades' && auth()->user()->desa) ||
                    (auth()->user()->role === 'bu-kades' && auth()->user()->desa)
                ) {
                    $labels = 'Desa ' . auth()->user()->desa;
                } elseif (auth()->user()->role === 'ketua-posyandu' && auth()->user()->posyandu) {
                    $labels = 'Posyandu ' . auth()->user()->posyandu->nama_posyandu;
                }
            @endphp
            <div class="bg-white overflow-hidden shadow-xl rounded-lg md:rounded-2xl p-4 md:p-6 lg:p-8 w-full">
                <h2 class="text-xl md:text-2xl font-bold text-gray-800 mb-4 md:mb-6">Dashboard Ajuan Pelayanan -
                    <span class="text-pink-500">{{ $labels }}</span>
                </h2>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6">
                    <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-3 md:gap-4">
                        <template x-if="loading">
                            <div class="col-span-2 flex items-center justify-center py-12 text-gray-400">
                                <svg class="animate-spin h-8 w-8 mr-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                Memuat data...
                            </div>
                        </template>
                        <template x-if="!loading">
                            <template x-for="item in bidangData" :key="item.name">
                                <div
                                    :class="item.color +
                                        ' text-white p-3 md:p-4 lg:py-4 lg:px-8 rounded-lg shadow-md flex items-center gap-3 md:gap-4'">
                                    <span class="text-4xl md:text-5xl lg:text-6xl font-bold" x-text="item.total"></span>
                                    <div class="flex flex-col gap-1 md:gap-2">
                                        <img :src="item.icon" :alt="item.name + ' icon'"
                                            class="w-6 h-6 md:w-8 md:h-8 mx-2 md:mx-3">
                                        <span
                                            class="ml-2 md:ml-3 text-xs md:text-sm lg:text-base font-semibold leading-tight"
                                            x-text="item.name"></span>
                                    </div>
                                </div>
                            </template>
                        </template>
                    </div>
                    <div class="bg-white p-3 md:p-4 rounded-lg">
                        <canvas x-ref="pieChart"></canvas>
                    </div>
                </div>
            </div>
        @endif

        <div class="w-full">
            <div class="bg-white overflow-hidden shadow-xl rounded-lg md:rounded-2xl p-4 md:p-6 lg:p-8">
                <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-4 md:mb-6">
                    <h2 class="text-xl md:text-2xl font-bold text-gray-800">
                        List Pengajuan
                        @if ($showArchived ?? false)
                            <span class="ml-3 px-3 py-1 text-sm font-semibold bg-gray-100 text-gray-700 rounded-full">
                                Arsip
                            </span>
                        @else
                            <span class="ml-3 px-3 py-1 text-sm font-semibold bg-green-100 text-green-700 rounded-full">
                                Aktif
                            </span>
                        @endif
                    </h2>

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 md:gap-3 w-full md:w-auto">
                        <button @click.prevent="toggleArchive($event)" type="button"
                            class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium bg-white text-gray-700 hover:bg-gray-50 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4">
                                </path>
                            </svg>
                            <span x-text="showArchived ? 'Lihat Pengajuan Aktif' : 'Lihat Arsip'"></span>
                        </button>
                        <select x-model="filterStatus" @change="loadDashboardData()"
                            class="border-gray-300 rounded-md shadow-sm text-sm w-full sm:w-auto px-3 py-2">
                            <option value="">Semua Status</option>
                            <option value="Diproses">Diproses</option>
                            <option value="Disetujui">Disetujui</option>
                            <option value="Ditolak">Ditolak</option>
                        </select>

                        <div class="relative w-full sm:w-auto">
                            <input type="text" x-model="searchQuery" @input.debounce.500ms="loadDashboardData()"
                                placeholder="Cari nama, bidang, posyandu, atau alamat..."
                                class="w-full sm:w-64 pl-4 pr-10 py-2 border border-gray-300 rounded-md text-sm focus:ring-pink-500 focus:border-pink-500">
                            <i class="bi bi-search absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400"></i>
                        </div>

                        <button x-show="searchQuery || filterStatus" @click="resetFilters()"
                            class="text-sm text-center sm:text-left text-gray-600 hover:text-gray-900 py-2 sm:py-0">
                            Reset
                        </button>

                        <button @click="exportData()"
                            class="flex items-center justify-center px-4 py-2 bg-green-500 text-white text-sm md:text-base rounded-md hover:bg-green-600 whitespace-nowrap">
                            <i class="bi bi-file-earmark-excel-fill mr-2"></i>
                            <span class="hidden sm:inline">Export to Excel</span>
                            <span class="sm:hidden">Export</span>
                        </button>
                    </div>
                </div>

                <div x-show="showArchived" x-transition class="mb-4 bg-gray-50 border-l-4 border-gray-400 p-4 rounded-lg">
                    <div class="flex">
                        <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                clip-rule="evenodd"></path>
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm text-gray-700">
                                Anda sedang melihat <strong>Arsip Pengajuan</strong> yang sudah selesai diproses.
                                Pengajuan dalam arsip tidak dapat diubah.
                            </p>
                        </div>
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

                        <div x-show="!loading" x-html="tableHtml" @click="handlePagination($event)"></div>
                    </div>
                </div>

                <div @click="handlePagination($event)">
                    <div x-html="paginationHtml"></div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('dashboardFilter', () => ({
                currentYear: {{ $currentYear }},
                selectedYear: {{ $selectedYear }},
                availableYears: @json($availableYears),
                loading: false,
                isVerified: {{ $isVerified ? 'true' : 'false' }},
                filterStatus: '{{ request('status') }}',
                searchQuery: '{{ request('search') }}',
                showArchived: false,

                bidangData: [],
                desas: [],

                statistics: {
                    total: 0,
                    disetujui: 0,
                    diproses: 0,
                    ditolak: 0
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
                    console.log('[Dashboard] Initializing admin dashboard');
                    this.drawChart();
                    this.loadDashboardData();
                },

                toggleArchive(event) {
                    if (event) {
                        event.preventDefault();
                        event.stopPropagation();
                    }

                    this.showArchived = !this.showArchived;
                    console.log('Toggled showArchived to:', this.showArchived);
                    this.loadDashboardData();
                },

                async loadDashboardData(page = 1) {
                    if (!this.isVerified) {
                        console.log('[Dashboard] User not verified, skipping data load');
                        return;
                    }

                    this.loading = true;

                    try {
                        const params = new URLSearchParams();
                        params.set('year', String(this.selectedYear));
                        params.set('archived', this.showArchived ? '1' : '0');

                        if (this.searchQuery) {
                            params.set('search', this.searchQuery);
                        }

                        if (this.filterStatus) {
                            params.set('status', this.filterStatus);
                        }

                        if (page > 1) {
                            params.set('page', String(page));
                        }

                        console.log('Loading dashboard data with params:', params.toString());

                        const response = await fetch(
                            `{{ route('dashboard.data') }}?${params.toString()}`, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                }
                            });

                        if (!response.ok) {
                            let errorData = {};

                            try {
                                errorData = await response.json();
                            } catch (parseError) {
                                console.warn('Failed to parse dashboard error response:', parseError);
                            }

                            if (response.status === 400 && errorData?.errors?.year) {
                                this.selectedYear = this.currentYear;
                                await this.loadDashboardData(1);
                            }

                            throw new Error(errorData?.message || `HTTP ${response.status}: ${response.statusText}`);
                        }

                        const data = await response.json();

                        console.log('[Dashboard] Data received:', data);
                        this.bidangData = data.bidangData || [];
                        this.statistics = data.statistics || {
                            total: 0,
                            disetujui: 0,
                            diproses: 0,
                            ditolak: 0
                        };
                        this.tableHtml = data.tableHtml || '';
                        this.paginationHtml = data.paginationHtml || '';
                        this.paginationInfo = data.paginationInfo || {
                            from: 0,
                            to: 0,
                            total: 0
                        };
                        this.desas = data.desas || [];

                        this.$nextTick(() => {
                            this.updateChart();
                        });

                    } catch (error) {
                        console.error('Error loading dashboard data:', error);
                        alert('Gagal memuat data dashboard. Silakan refresh halaman.');
                    } finally {
                        this.loading = false;
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
                    if (!ctx) {
                        console.warn('[Dashboard] Chart canvas not found');
                        return;
                    }

                    if (typeof Chart === 'undefined') {
                        console.warn('[Dashboard] Chart.js not loaded yet, retrying...');
                        setTimeout(() => this.updateChart(), 100);
                        return;
                    }

                    // Destroy existing chart
                    if (this.chart) {
                        this.chart.destroy();
                    }

                    // ✅ Filter data with values > 0
                    const filteredData = this.bidangData.filter(item => item.total > 0);

                    const labels = filteredData.length > 0 ?
                        filteredData.map(item => item.name.replace('Bidang ', '')) : ['Tidak Ada Data'];

                    const data = filteredData.length > 0 ?
                        filteredData.map(item => item.total) : [1];

                    // ✅ Consistent color mapping
                    const bidangColors = {
                        'Perumahan Rakyat': 'rgb(59, 130, 246)',
                        'Pendidikan': 'rgb(251, 146, 60)',
                        'Kesehatan': 'rgb(236, 72, 153)',
                        'Sosial': 'rgb(251, 113, 133)',
                        'Pekerjaan Umum': 'rgb(34, 197, 94)',
                        'Trantibumlinmas': 'rgb(234, 179, 8)'
                    };

                    const colors = labels.map(label => bidangColors[label] || 'rgb(229, 231, 235)');
                    const isMobile = window.innerWidth < 640;

                    // ✅ Create chart
                    this.chart = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: labels,
                            datasets: [{
                                data: data,
                                backgroundColor: colors,
                                borderWidth: 2,
                                borderColor: '#fff',
                                hoverOffset: 10,
                                hoverBorderWidth: 3
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        padding: isMobile ? 10 : 15,
                                        font: {
                                            size: isMobile ? 9 : 11,
                                            family: 'Inter, system-ui, sans-serif'
                                        },
                                        boxWidth: 12,
                                        usePointStyle: true,
                                        generateLabels: function(chart) {
                                            const data = chart.data;
                                            if (data.labels.length && data.datasets
                                                .length) {
                                                return data.labels.map((label, i) => {
                                                    const value = data.datasets[0]
                                                        .data[i];
                                                    const color = data.datasets[0]
                                                        .backgroundColor[i];
                                                    return {
                                                        text: label + ': ' + value,
                                                        fillStyle: color,
                                                        hidden: false,
                                                        index: i
                                                    };
                                                });
                                            }
                                            return [];
                                        }
                                    }
                                },
                                tooltip: {
                                    enabled: true,
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    titleFont: {
                                        size: 12,
                                        weight: 'bold'
                                    },
                                    bodyFont: {
                                        size: 11
                                    },
                                    padding: 12,
                                    displayColors: true,
                                    callbacks: {
                                        label: function(context) {
                                            const label = context.label || '';
                                            const value = context.parsed || 0;
                                            const total = context.dataset.data.reduce((a,
                                                b) => a + b, 0);
                                            const percentage = total > 0 ? ((value /
                                                total) * 100).toFixed(1) : 0;
                                            return label + ': ' + value + ' (' +
                                                percentage + '%)';
                                        }
                                    }
                                }
                            },
                            cutout: '60%',
                            animation: {
                                animateRotate: true,
                                animateScale: true
                            }
                        }
                    });

                    console.log('[Dashboard] Chart updated successfully');
                },

                exportOptions: null,

                async exportData() {
                    const role = "{{ auth()->user()->role }}";
                    const year = this.selectedYear;

                    const type = await this.showExportStep1(role);
                    if (!type) return;

                    if (type === 'all') {
                        window.location.href = `/admin/export?scope=all&year=${year}`;
                        return;
                    }

                    // Lazy-load dropdown options from API
                    if (!this.exportOptions) {
                        try {
                            const resp = await fetch('{{ route("laporan.exportOptions") }}');
                            if (!resp.ok) throw new Error();
                            this.exportOptions = await resp.json();
                        } catch {
                            Swal.fire({ icon: 'error', title: 'Gagal Memuat Data', text: 'Tidak dapat mengambil data filter. Silakan coba lagi.' });
                            return;
                        }
                    }

                    const result2 = await this.showExportStep2(type, year, this.exportOptions, this.desas);
                    if (!result2) return;
                    if (result2.back) { this.exportData(); return; }

                    if (result2.url) { window.location.href = result2.url; return; }

                    // Step 3: posyandu → pilih bidang
                    if (result2.posyanduId) {
                        const result3 = await this.showExportStep3(result2.posyanduId, year);
                        if (!result3) return;
                        if (result3.back) { this.exportData(); return; }
                        if (result3.url) window.location.href = result3.url;
                    }
                },

                async showExportStep1(role) {
                    const TYPE_CONFIG = {
                        admin: [
                            { value: 'all',       label: 'Keseluruhan',   desc: 'Semua data pengajuan',              icon: 'bi-globe' },
                            { value: 'kabupaten', label: 'Per Kabupaten',  desc: 'Data pada kabupaten tertentu',     icon: 'bi-building' },
                            { value: 'kecamatan', label: 'Per Kecamatan',  desc: 'Data pada kecamatan tertentu',     icon: 'bi-building-fill' },
                            { value: 'desa',      label: 'Per Desa',       desc: 'Data pada desa tertentu',          icon: 'bi-house-fill' },
                            { value: 'posyandu',  label: 'Per Posyandu',   desc: 'Data pada posyandu tertentu',      icon: 'bi-hospital' },
                            { value: 'bidang',    label: 'Per Bidang',     desc: 'Data pada bidang layanan tertentu', icon: 'bi-grid-3x3-gap' },
                        ],
                        'admin-kabupaten': [
                            { value: 'all',       label: 'Keseluruhan',   desc: 'Semua data di kabupaten Anda',     icon: 'bi-globe' },
                            { value: 'kecamatan', label: 'Per Kecamatan',  desc: 'Data pada kecamatan tertentu',    icon: 'bi-building-fill' },
                            { value: 'desa',      label: 'Per Desa',       desc: 'Data pada desa tertentu',         icon: 'bi-house-fill' },
                            { value: 'posyandu',  label: 'Per Posyandu',   desc: 'Data pada posyandu tertentu',     icon: 'bi-hospital' },
                            { value: 'bidang',    label: 'Per Bidang',     desc: 'Data pada bidang tertentu',        icon: 'bi-grid-3x3-gap' },
                        ],
                        kabid: [
                            { value: 'all',       label: 'Keseluruhan',   desc: 'Semua data di bidang Anda',        icon: 'bi-globe' },
                            { value: 'kecamatan', label: 'Per Kecamatan',  desc: 'Data pada kecamatan tertentu',    icon: 'bi-building-fill' },
                            { value: 'desa',      label: 'Per Desa',       desc: 'Data pada desa tertentu',         icon: 'bi-house-fill' },
                        ],
                        'admin-kecamatan': [
                            { value: 'all',       label: 'Keseluruhan',   desc: 'Semua data di kecamatan Anda',     icon: 'bi-globe' },
                            { value: 'desa',      label: 'Per Desa',       desc: 'Data pada desa tertentu',         icon: 'bi-house-fill' },
                            { value: 'posyandu',  label: 'Per Posyandu',   desc: 'Data pada posyandu tertentu',     icon: 'bi-hospital' },
                            { value: 'bidang',    label: 'Per Bidang',     desc: 'Data pada bidang tertentu',        icon: 'bi-grid-3x3-gap' },
                        ],
                        kades: [
                            { value: 'all',       label: 'Keseluruhan',   desc: 'Semua data di desa Anda',          icon: 'bi-globe' },
                            { value: 'posyandu',  label: 'Per Posyandu',   desc: 'Data pada posyandu tertentu',     icon: 'bi-hospital' },
                            { value: 'bidang',    label: 'Per Bidang',     desc: 'Data pada bidang tertentu',        icon: 'bi-grid-3x3-gap' },
                        ],
                        'ketua-posyandu': [
                            { value: 'all',       label: 'Keseluruhan',   desc: 'Semua data di posyandu Anda',      icon: 'bi-globe' },
                            { value: 'bidang',    label: 'Per Bidang',     desc: 'Data pada bidang tertentu',        icon: 'bi-grid-3x3-gap' },
                        ],
                    };
                    TYPE_CONFIG['bu-kades']                  = TYPE_CONFIG['kades'];
                    TYPE_CONFIG['ketua-timpembina-posyandu'] = TYPE_CONFIG['admin-kabupaten'];

                    const options = TYPE_CONFIG[role] ?? TYPE_CONFIG['admin'];

                    return new Promise((resolve) => {
                        let settled = false;
                        const done = (val) => { if (!settled) { settled = true; resolve(val); } };

                        const cols = options.length <= 2 ? 'grid-cols-1' : options.length <= 4 ? 'grid-cols-2' : 'grid-cols-2';
                        const cardsHtml = options.map(o => `
                            <label class="export-type-card flex items-center gap-3 p-3 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition-all">
                                <input type="radio" name="exportType" value="${o.value}" class="hidden">
                                <i class="bi ${o.icon} text-blue-500 text-lg flex-shrink-0"></i>
                                <div class="text-left">
                                    <div class="font-semibold text-sm text-gray-800">${o.label}</div>
                                    <div class="text-xs text-gray-500">${o.desc}</div>
                                </div>
                            </label>`).join('');

                        Swal.fire({
                            title: '<h2 class="text-lg font-bold text-gray-800">Export Data Pengajuan</h2>',
                            html: `<p class="text-sm text-gray-500 mb-3 text-left">Pilih jenis data yang ingin diekspor:</p>
                                   <div class="grid ${cols} gap-2 text-left" id="exportTypeGrid">${cardsHtml}</div>
                                   <div class="flex gap-3 mt-5">
                                       <button id="btnCancelStep1" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl py-2.5 text-sm">Batal</button>
                                       <button id="btnNextStep1" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl py-2.5 text-sm">Lanjut &rarr;</button>
                                   </div>`,
                            showConfirmButton: false,
                            width: 520,
                            background: '#fff',
                            customClass: { popup: 'rounded-2xl shadow-2xl p-6' },
                            didOpen: (popup) => {
                                popup.addEventListener('click', (e) => {
                                    const card = e.target.closest('.export-type-card');
                                    if (card) {
                                        popup.querySelectorAll('.export-type-card').forEach(c => c.classList.remove('border-blue-500', 'bg-blue-50'));
                                        card.classList.add('border-blue-500', 'bg-blue-50');
                                        card.querySelector('input[type=radio]').checked = true;
                                    }
                                    if (e.target.id === 'btnCancelStep1') { Swal.close(); done(null); }
                                    if (e.target.id === 'btnNextStep1') {
                                        const sel = popup.querySelector('input[name="exportType"]:checked')?.value;
                                        if (!sel) {
                                            document.getElementById('exportTypeGrid').classList.add('ring-2', 'ring-red-300', 'rounded-xl', 'p-1');
                                            setTimeout(() => document.getElementById('exportTypeGrid')?.classList.remove('ring-2','ring-red-300','rounded-xl','p-1'), 1500);
                                            return;
                                        }
                                        Swal.close(); done(sel);
                                    }
                                });
                            },
                            didDestroy: () => done(null),
                        });
                    });
                },

                async showExportStep2(type, year, options, desas) {
                    const BIDANG_LIST = [
                        'Bidang Perumahan Rakyat', 'Bidang Pendidikan', 'Bidang Kesehatan',
                        'Bidang Sosial', 'Bidang Pekerjaan Umum', 'Bidang Trantibumlinmas',
                    ];
                    const typeLabels = { kabupaten: 'Kabupaten', kecamatan: 'Kecamatan', desa: 'Desa', posyandu: 'Posyandu', bidang: 'Bidang' };

                    let items = [];
                    let isObject = false;
                    switch (type) {
                        case 'kabupaten': items = options.kabupatens ?? []; break;
                        case 'kecamatan': items = options.kecamatans ?? []; break;
                        case 'desa':      items = desas; break;
                        case 'posyandu':  items = options.posyandus ?? []; isObject = true; break;
                        case 'bidang':    items = BIDANG_LIST; break;
                    }

                    const buildOptHtml = (item) => {
                        if (isObject) {
                            const label = item.nama_posyandu ?? '';
                            const sub   = [item.desa, item.kecamatan].filter(Boolean).join(' — ');
                            return `<div class="searchable-opt px-4 py-2.5 cursor-pointer hover:bg-blue-50 border-b border-gray-50 last:border-0" data-value="${item.id}" data-label="${label}">
                                        <div class="text-sm font-medium text-gray-800">${label}</div>
                                        <div class="text-xs text-gray-400">${sub}</div>
                                    </div>`;
                        }
                        return `<div class="searchable-opt px-4 py-2.5 cursor-pointer hover:bg-blue-50 text-sm text-gray-800" data-value="${item}" data-label="${item}">${item}</div>`;
                    };

                    return new Promise((resolve) => {
                        let settled = false;
                        const done = (val) => { if (!settled) { settled = true; resolve(val); } };

                        const listHtml = items.length
                            ? items.map(buildOptHtml).join('')
                            : '<div class="px-4 py-3 text-sm text-gray-400">Tidak ada data tersedia.</div>';

                        Swal.fire({
                            title: `<h2 class="text-lg font-bold text-gray-800">Pilih ${typeLabels[type]}</h2>`,
                            html: `<p class="text-sm text-gray-500 mb-3 text-left">Pilih ${(typeLabels[type]??'').toLowerCase()} yang ingin diekspor:</p>
                                   <div class="relative text-left">
                                       <input type="text" id="sStep2Search" placeholder="Ketik untuk mencari..." autocomplete="off"
                                           class="w-full border border-gray-300 rounded-xl p-3 pr-10 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                       <button type="button" id="sStep2Toggle" class="absolute right-3 top-3 text-gray-400 text-xs">▼</button>
                                       <input type="hidden" id="sStep2Value">
                                       <div id="sStep2List" class="hidden absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-xl max-h-52 overflow-auto">${listHtml}</div>
                                   </div>
                                   <div class="flex gap-3 mt-5">
                                       <button id="btnBackStep2" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl py-2.5 text-sm">&larr; Kembali</button>
                                       <button id="btnConfirmStep2" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl py-2.5 text-sm flex items-center justify-center gap-2">
                                           <i class="bi bi-file-earmark-excel-fill"></i> Export
                                       </button>
                                   </div>`,
                            showConfirmButton: false,
                            width: 480,
                            background: '#fff',
                            customClass: { popup: 'rounded-2xl shadow-2xl p-6' },
                            didOpen: (popup) => {
                                const search = popup.querySelector('#sStep2Search');
                                const list   = popup.querySelector('#sStep2List');
                                const toggle = popup.querySelector('#sStep2Toggle');
                                const hidden = popup.querySelector('#sStep2Value');
                                const allOpts = () => Array.from(list.querySelectorAll('.searchable-opt'));

                                search.addEventListener('focus', () => list.classList.remove('hidden'));
                                toggle.addEventListener('click', e => { e.stopPropagation(); list.classList.toggle('hidden'); });
                                search.addEventListener('input', e => {
                                    const term = e.target.value.toLowerCase();
                                    allOpts().forEach(o => { o.style.display = o.textContent.toLowerCase().includes(term) ? '' : 'none'; });
                                    list.classList.remove('hidden');
                                });
                                list.addEventListener('click', e => {
                                    const opt = e.target.closest('.searchable-opt');
                                    if (opt) { hidden.value = opt.dataset.value; search.value = opt.dataset.label; list.classList.add('hidden'); }
                                });
                                popup.addEventListener('click', e => {
                                    if (!search.contains(e.target) && !list.contains(e.target) && !toggle.contains(e.target)) list.classList.add('hidden');
                                    if (e.target.id === 'btnBackStep2') { Swal.close(); done({ back: true }); }
                                    if (e.target.id === 'btnConfirmStep2') {
                                        const val = hidden.value;
                                        if (!val) { search.classList.add('ring-2','ring-red-400'); setTimeout(() => search.classList.remove('ring-2','ring-red-400'), 1500); return; }
                                        Swal.close();
                                        if (type === 'posyandu') { done({ posyanduId: val }); return; }
                                        done({ url: `/admin/export?scope=${type}&${type}=${encodeURIComponent(val)}&year=${year}` });
                                    }
                                });
                            },
                            didDestroy: () => done(null),
                        });
                    });
                },

                async showExportStep3(posyanduId, year) {
                    const BIDANG_LIST = [
                        { value: 'all',                      label: 'Semua Bidang' },
                        { value: 'Bidang Perumahan Rakyat',  label: 'Bidang Perumahan Rakyat' },
                        { value: 'Bidang Pendidikan',        label: 'Bidang Pendidikan' },
                        { value: 'Bidang Kesehatan',         label: 'Bidang Kesehatan' },
                        { value: 'Bidang Sosial',            label: 'Bidang Sosial' },
                        { value: 'Bidang Pekerjaan Umum',    label: 'Bidang Pekerjaan Umum' },
                        { value: 'Bidang Trantibumlinmas',   label: 'Bidang Trantibumlinmas' },
                    ];

                    return new Promise((resolve) => {
                        let settled = false;
                        const done = (val) => { if (!settled) { settled = true; resolve(val); } };

                        const cardsHtml = BIDANG_LIST.map(b => `
                            <label class="bidang-card flex items-center gap-3 p-3 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-emerald-400 hover:bg-emerald-50 transition-all">
                                <input type="radio" name="posyanduBidang" value="${b.value}" class="hidden">
                                <span class="text-sm font-medium text-gray-800">${b.label}</span>
                            </label>`).join('');

                        Swal.fire({
                            title: '<h2 class="text-lg font-bold text-gray-800">Pilih Bidang</h2>',
                            html: `<p class="text-sm text-gray-500 mb-3 text-left">Pilih bidang yang ingin diekspor dari posyandu ini:</p>
                                   <div class="grid grid-cols-1 gap-2 text-left" id="bidangCardGrid">${cardsHtml}</div>
                                   <div class="flex gap-3 mt-5">
                                       <button id="btnBackStep3" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl py-2.5 text-sm">&larr; Kembali</button>
                                       <button id="btnConfirmStep3" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl py-2.5 text-sm flex items-center justify-center gap-2">
                                           <i class="bi bi-file-earmark-excel-fill"></i> Export
                                       </button>
                                   </div>`,
                            showConfirmButton: false,
                            width: 480,
                            background: '#fff',
                            customClass: { popup: 'rounded-2xl shadow-2xl p-6' },
                            didOpen: (popup) => {
                                popup.addEventListener('click', e => {
                                    const card = e.target.closest('.bidang-card');
                                    if (card) {
                                        popup.querySelectorAll('.bidang-card').forEach(c => c.classList.remove('border-emerald-500', 'bg-emerald-50'));
                                        card.classList.add('border-emerald-500', 'bg-emerald-50');
                                        card.querySelector('input[type=radio]').checked = true;
                                    }
                                    if (e.target.id === 'btnBackStep3') { Swal.close(); done({ back: true }); }
                                    if (e.target.id === 'btnConfirmStep3') {
                                        const bidang = popup.querySelector('input[name="posyanduBidang"]:checked')?.value;
                                        if (!bidang) {
                                            popup.querySelector('#bidangCardGrid').classList.add('ring-2','ring-red-300','rounded-xl','p-1');
                                            setTimeout(() => popup.querySelector('#bidangCardGrid')?.classList.remove('ring-2','ring-red-300','rounded-xl','p-1'), 1500);
                                            return;
                                        }
                                        Swal.close();
                                        done({ url: `/admin/export?scope=posyandu&posyandu_id=${encodeURIComponent(posyanduId)}&bidang=${encodeURIComponent(bidang)}&year=${year}` });
                                    }
                                });
                            },
                            didDestroy: () => done(null),
                        });
                    });
                },

                handlePagination(event) {
                    const link = event.target.closest('a[href]');
                    if (!link) return;

                    const url = new URL(link.href);
                    const page = url.searchParams.get('page');

                    if (page) {
                        event.preventDefault();
                        this.loadDashboardData(parseInt(page));
                    }
                },

            }));
        });

    </script>
@endsection
