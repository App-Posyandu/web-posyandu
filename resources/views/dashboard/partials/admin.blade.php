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

                exportData() {
                    const userRole = "{{ auth()->user()->role }}";
                    const userPosyanduDesa = "{{ auth()->user()?->posyandu?->desa ?? '' }}";
                    const userDesa = "{{ auth()->user()->desa ?? '' }}";
                    const userKecamatan = "{{ auth()->user()->kecamatan ?? '' }}";
                    const userKabupaten = "{{ auth()->user()->kabupaten ?? '' }}";
                    const bidangKabid = "{{ auth()->user()?->bidang?->nama_bidang ?? '' }}";

                    if (userRole === 'ketua-posyandu') {
                        if (!userPosyanduDesa) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Data Posyandu Tidak Ditemukan',
                                text: 'Posyandu belum ditetapkan di profile Anda.',
                                confirmButtonColor: '#f87171'
                            });
                            return;
                        }

                        const selectedYear = this.selectedYear;
                        this.showKetuaKaderExportModal();
                        return;
                    }

                    if (userRole === 'kades' || userRole === 'bu-kades') {
                        if (!userDesa) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Data Desa Tidak Ditemukan',
                                text: 'Desa belum ditetapkan di profile Anda.',
                                confirmButtonColor: '#f87171'
                            });
                            return;
                        }

                        this.showKadesExportModal();
                        return;
                    }

                    if (userRole === 'admin-kecamatan') {
                        if (!userKecamatan) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Data Kecamatan Tidak Ditemukan',
                                text: 'Kecamatan belum ditetapkan di profile Anda.',
                                confirmButtonColor: '#f87171'
                            });
                            return;
                        }

                        this.showAdminKecamatanExportModal();
                        return;
                    }

                    if (userRole === 'kabid') {
                        if (!bidangKabid) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Bidang Kabid Belum Diset',
                                text: 'Bidang kabid belum tersedia. Silakan lengkapi data bidang di profil.',
                                confirmButtonColor: '#f87171'
                            });
                            return;
                        }

                        this.showKabidExportModal();
                        return;
                    }

                    if (['ketua-timpembina-posyandu', 'admin-kabupaten', 'admin'].includes(userRole)) {
                        window.location.href =
                            `/admin/export-all-bidang-desa?year=${this.selectedYear}`;
                        return;
                    }
                    window.location.href = `/admin/export-all-bidang-desa?year=${this.selectedYear}`;
                },

                showKetuaKaderExportModal() {
                    const selectedYear = this.selectedYear;
                    const userDesa = "{{ auth()->user()?->posyandu?->desa ?? '' }}";

                    Swal.fire({
                        title: '<h2 class="text-lg md:text-xl font-bold text-gray-800 mb-2">Export Data Pengajuan</h2>',
                        html: `
            <div class="space-y-6">
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-md">
                    <div class="flex items-start">
                        <i class="bi bi-info-circle-fill text-blue-500 mr-2 mt-0.5"></i>
                        <div class="text-left">
                            <p class="text-sm font-medium text-gray-900">Posyandu Anda</p>
                            <p class="text-xs text-gray-600 mt-1">
                                Export data hanya untuk posyandu Anda di desa: <strong>${userDesa}</strong>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="text-left">
                    <label class="block text-start font-semibold mb-2 text-gray-700">Pilih Bidang:</label>
                    <select id="ketuaKaderBidangSelect" class="w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
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

                <div class="flex justify-between gap-4 mt-6">
                    <button id="cancelKetuaKaderExport"
                        class="bg-gray-500 text-white hover:bg-gray-600 font-medium rounded-md py-3 px-6 w-1/2 shadow transition-colors">
                        Batal
                    </button>
                    <button id="confirmKetuaKaderExport"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-md py-3 px-6 w-1/2 shadow transition-colors">
                        <i class="bi bi-file-earmark-excel mr-2"></i>
                        Export Data
                    </button>
                </div>
            </div>
        `,
                        showConfirmButton: false,
                        showCancelButton: false,
                        width: 600,
                        background: '#f9fafb',
                        customClass: {
                            popup: 'rounded-md md:rounded-2xl shadow-2xl p-6'
                        }
                    });

                    const handleKetuaKaderExport = (e) => {
                        if (e.target.id === 'cancelKetuaKaderExport') {
                            Swal.close();
                            document.removeEventListener('click', handleKetuaKaderExport);
                        }

                        if (e.target.id === 'confirmKetuaKaderExport') {
                            const bidangSelect = document.getElementById('ketuaKaderBidangSelect');
                            const selectedBidang = bidangSelect?.value;

                            if (!selectedBidang) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Bidang Belum Dipilih!',
                                    text: 'Silakan pilih bidang terlebih dahulu.',
                                    confirmButtonColor: '#f87171'
                                });
                                return;
                            }
                            if (selectedBidang === 'all') {
                                window.location.href =
                                    `/admin/export-all/${userDesa}?year=${selectedYear}`;
                            } else {
                                window.location.href =
                                    `/admin/export/${encodeURIComponent(selectedBidang)}/${userDesa}?year=${selectedYear}`;
                            }

                            Swal.close();
                            document.removeEventListener('click', handleKetuaKaderExport);
                        }
                    };

                    document.addEventListener('click', handleKetuaKaderExport);
                },

                showKadesExportModal() {
                    const selectedYear = this.selectedYear;
                    const userDesa = "{{ auth()->user()->desa ?? '' }}";

                    Swal.fire({
                        title: '<h2 class="text-lg md:text-xl font-bold text-gray-800 mb-2">Export Data Pengajuan</h2>',
                        html: `
            <div class="space-y-6">
                <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-md">
                    <div class="flex items-start">
                        <i class="bi bi-info-circle-fill text-green-500 mr-2 mt-0.5"></i>
                        <div class="text-left">
                            <p class="text-sm font-medium text-gray-900">Desa Anda</p>
                            <p class="text-xs text-gray-600 mt-1">
                                Export data untuk semua bidang di desa: <strong>${userDesa}</strong>
                            </p>
                        </div>
                    </div>
                </div>

                <p class="text-sm text-gray-600 text-center">
                    Data akan diekspor untuk semua bidang di desa Anda
                </p>

                <div class="flex justify-between gap-4 mt-6">
                    <button id="cancelKadesExport"
                        class="bg-gray-500 text-white hover:bg-gray-600 font-medium rounded-md py-3 px-6 w-1/2 shadow transition-colors">
                        Batal
                    </button>
                    <button id="confirmKadesExport"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-md py-3 px-6 w-1/2 shadow transition-colors">
                        <i class="bi bi-file-earmark-excel mr-2"></i>
                        Export Data
                    </button>
                </div>
            </div>
        `,
                        showConfirmButton: false,
                        showCancelButton: false,
                        width: 600,
                        background: '#f9fafb',
                        customClass: {
                            popup: 'rounded-md md:rounded-2xl shadow-2xl p-6'
                        }
                    });

                    const handleKadesExport = (e) => {
                        if (e.target.id === 'cancelKadesExport') {
                            Swal.close();
                            document.removeEventListener('click', handleKadesExport);
                        }

                        if (e.target.id === 'confirmKadesExport') {
                            window.location.href =
                                `/admin/export-all/${userDesa}?year=${selectedYear}`;
                            Swal.close();
                            document.removeEventListener('click', handleKadesExport);
                        }
                    };

                    document.addEventListener('click', handleKadesExport);
                },

                showAdminKecamatanExportModal() {
                    const selectedYear = this.selectedYear;
                    const userKecamatan = "{{ auth()->user()->kecamatan ?? '' }}";
                    const desas = this.desas;

                    Swal.fire({
                        title: '<h2 class="text-lg md:text-xl font-bold text-gray-800 mb-2">Export Data Pengajuan</h2>',
                        html: `
            <div class="space-y-6">
                <div class="bg-purple-50 border-l-4 border-purple-500 p-4 rounded-md">
                    <div class="flex items-start">
                        <i class="bi bi-info-circle-fill text-purple-500 mr-2 mt-0.5"></i>
                        <div class="text-left">
                            <p class="text-sm font-medium text-gray-900">Kecamatan Anda</p>
                            <p class="text-xs text-gray-600 mt-1">
                                Export data untuk semua bidang dan desa di kecamatan: <strong>${userKecamatan}</strong>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="text-left">
                    <label class="block text-start font-semibold mb-2 text-gray-700">Pilih Desa:</label>
                    <select id="adminKecamatanDesaSelect" class="w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        <option value="" disabled selected>Pilih Desa</option>
                        <option value="all" class="font-bold">📊 Semua Desa di ${userKecamatan}</option>
                        <optgroup label="Desa Spesifik:">
                            ${desas.map(d => `<option value="${d}">${d}</option>`).join('')}
                        </optgroup>
                    </select>
                </div>

                <div class="flex justify-between gap-4 mt-6">
                    <button id="cancelAdminKecamatanExport"
                        class="bg-gray-500 text-white hover:bg-gray-600 font-medium rounded-md py-3 px-6 w-1/2 shadow transition-colors">
                        Batal
                    </button>
                    <button id="confirmAdminKecamatanExport"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-md py-3 px-6 w-1/2 shadow transition-colors">
                        <i class="bi bi-file-earmark-excel mr-2"></i>
                        Export Data
                    </button>
                </div>
            </div>
        `,
                        showConfirmButton: false,
                        showCancelButton: false,
                        width: 600,
                        background: '#f9fafb',
                        customClass: {
                            popup: 'rounded-md md:rounded-2xl shadow-2xl p-6'
                        }
                    });

                    const handleAdminKecamatanExport = (e) => {
                        if (e.target.id === 'cancelAdminKecamatanExport') {
                            Swal.close();
                            document.removeEventListener('click', handleAdminKecamatanExport);
                        }

                        if (e.target.id === 'confirmAdminKecamatanExport') {
                            const desaSelect = document.getElementById('adminKecamatanDesaSelect');
                            const selectedDesa = desaSelect?.value;

                            if (!selectedDesa) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Desa Belum Dipilih!',
                                    text: 'Silakan pilih desa terlebih dahulu.',
                                    confirmButtonColor: '#f87171'
                                });
                                return;
                            }

                            if (selectedDesa === 'all') {
                                window.location.href =
                                    `/admin/export-all/${selectedDesa}?year=${selectedYear}`;
                            } else {
                                window.location.href =
                                    `/admin/export-all/${selectedDesa}?year=${selectedYear}`;
                            }

                            Swal.close();
                            document.removeEventListener('click', handleAdminKecamatanExport);
                        }
                    };

                    document.addEventListener('click', handleAdminKecamatanExport);
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

                showKabidExportModal() {
                    const bidangKabid = "{{ auth()->user()->bidang?->nama_bidang ?? '' }}";
                    const kabupatenKabid = "{{ auth()->user()->kabupaten ?? '' }}";
                    const desas = this.desas;

                    Swal.fire({
                        title: '<h2 class="text-lg md:text-xl font-bold text-gray-800 mb-2">Export Data Pengajuan</h2>',
                        html: `
            <div class="space-y-6">
                <div class="bg-pink-50 border-l-4 border-pink-500 p-4 rounded-md">
                    <div class="flex items-start">
                        <i class="bi bi-info-circle-fill text-pink-500 mr-2 mt-0.5"></i>
                        <div class="text-left">
                            <p class="text-sm font-medium text-gray-900">Bidang Anda</p>
                            <p class="text-xs text-gray-600 mt-1">
                                Export data untuk bidang: <strong>${bidangKabid}</strong>
                            </p>
                            <p class="text-xs text-gray-600">
                                Wilayah: <strong>${kabupatenKabid}</strong>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="text-left">
                    <label class="block text-start font-semibold mb-2 text-gray-700">Pilih Desa:</label>
                    <select id="kabidDesaSelect" class="w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                        <option value="" disabled selected>Pilih Desa</option>
                        <option value="all" class="font-bold">Semua Desa di ${kabupatenKabid}</option>
                        <optgroup label="Desa Spesifik:">
                            ${desas.map(d => `<option value="${d}">${d}</option>`).join('')}
                        </optgroup>
                    </select>
                    <p class="text-xs text-gray-500 mt-2">
                        <i class="bi bi-lightbulb"></i>
                        Pilih "Semua Desa" untuk export seluruh data di ${kabupatenKabid}
                    </p>
                </div>

                <div class="flex justify-between gap-4 mt-6">
                    <button id="cancelKabidExport"
                        class="bg-gray-500 text-white hover:bg-gray-600 font-medium rounded-md py-3 px-6 w-1/2 shadow transition-colors">
                        Batal
                    </button>
                    <button id="confirmKabidExport"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-md py-3 px-6 w-1/2 shadow transition-colors">
                        <i class="bi bi-file-earmark-excel mr-2"></i>
                        Export Data
                    </button>
                </div>
            </div>
        `,
                        showConfirmButton: false,
                        showCancelButton: false,
                        width: 600,
                        background: '#f9fafb',
                        customClass: {
                            popup: 'rounded-md md:rounded-2xl shadow-2xl p-6'
                        }
                    });

                    const handleKabidExport = (e) => {
                        if (e.target.id === 'cancelKabidExport') {
                            Swal.close();
                            document.removeEventListener('click', handleKabidExport);
                        }

                        if (e.target.id === 'confirmKabidExport') {
                            const desaSelect = document.getElementById('kabidDesaSelect');
                            const selectedDesa = desaSelect?.value;

                            if (!selectedDesa) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Desa Belum Dipilih!',
                                    text: 'Silakan pilih desa terlebih dahulu.',
                                    confirmButtonColor: '#f87171'
                                });
                                return;
                            }

                            const selectedYear = this.selectedYear;

                            if (selectedDesa === 'all') {
                                window.location.href =
                                    `/admin/export/${encodeURIComponent(bidangKabid)}?year=${selectedYear}`;
                            } else {
                                window.location.href =
                                    `/admin/export/${encodeURIComponent(bidangKabid)}/${encodeURIComponent(selectedDesa)}?year=${selectedYear}`;
                            }

                            Swal.close();
                            document.removeEventListener('click', handleKabidExport);
                        }
                    };

                    document.addEventListener('click', handleKabidExport);
                }
            }));
        });

        @php
            use Illuminate\Support\Facades\Auth;
            $desaUser = optional(optional(Auth::user()->posyandu)->desa);
        @endphp

        const userRole = "{{ Auth::user()->role }}";
        const userDesa = "{{ Auth::user()?->posyandu?->desa ?? '' }}";
        const bidangKabid = "{{ Auth::user()?->bidang?->nama_bidang ?? '' }}";

        const exportExcelBtn = document.getElementById('exportExcelBtn');
        if (exportExcelBtn) {
            exportExcelBtn.addEventListener('click', function() {
                let bidangSelectHTML = "";

                if (userRole !== 'kabid') {
                    bidangSelectHTML = `
            <div class="flex flex-col justify-start">
                <label class="block text-start font-semibold mb-1 text-gray-700">Pilih Bidang:</label>
                <select id="selectBidang" class="w-full border rounded-md p-2" required>
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
                } else {
                    bidangSelectHTML = `
            <div class="bg-pink-50 border-l-4 border-pink-500 p-4 rounded-md">
                <div class="flex items-start">
                    <i class="bi bi-info-circle-fill text-pink-500 mr-2 mt-0.5"></i>
                    <div>
                        <p class="text-sm font-medium text-gray-900">Bidang Anda</p>
                        <p class="text-xs text-gray-600 mt-1">
                            Export data untuk bidang: <strong>${bidangKabid}</strong>
                        </p>
                    </div>
                </div>
            </div>
        `;
                }

                let desaSelectHTML = "";

                @if (Auth::user()->role === 'kabid')
                    const desas = Alpine.$data(document.querySelector('[x-data="dashboardFilter"]'))?.desas ?? [];

                    desaSelectHTML = `
            <div class="relative text-left">
                <label class="block text-start font-semibold mb-1 text-gray-700">Pilih Desa:</label>

                <input type="hidden" id="desaValue">

                <div class="relative">
                    <input
                        type="text"
                        id="desaSearch"
                        placeholder="Cari desa atau pilih 'Semua Desa'..."
                        class="block w-full border border-gray-300 rounded-md p-2"
                        autocomplete="off"
                    >
                    <button type="button" id="toggleDesaDropdown"
                        class="absolute inset-y-0 right-0 flex items-center px-3">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                </div>

                <div id="desaDropdownList"
                    class="hidden absolute z-50 mt-1 w-full bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
                    <div id="desaOptions"></div>
                </div>
            </div>
        `;
                @endif

                Swal.fire({
                    title: '<h2 class="text-lg md:text-xl font-bold text-gray-800 mb-2">Export Data Pengajuan</h2>',
                    html: `
            <div class="space-y-8">
                ${bidangSelectHTML}
                ${desaSelectHTML}
                <div class="flex justify-between gap-4 mt-6">
                    <button id="cancelExportBtn"
                        class="bg-red-500 text-white hover:bg-red-700 font-medium rounded-md py-3 px-6 w-1/2 shadow">
                        Batal
                    </button>
                    <button id="confirmExportBtn"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-md py-3 px-6 w-1/2 shadow">
                        Export Data
                    </button>
                </div>
            </div>
        `,
                    showConfirmButton: false,
                    showCancelButton: false,
                    width: 600,
                    background: '#f9fafb',
                    customClass: {
                        popup: 'rounded-md md:rounded-2xl shadow-lg p-4 md:p-6'
                    },
                    didOpen: () => {
                        @if (Auth::user()->role === 'kabid')
                            const desas = ['all', ...(Alpine.$data(document.querySelector(
                                '[x-data="dashboardFilter"]'))?.desas ?? [])];
                            const searchInput = document.getElementById('desaSearch');
                            const dropdownList = document.getElementById('desaDropdownList');
                            const desaOptions = document.getElementById('desaOptions');
                            const toggleBtn = document.getElementById('toggleDesaDropdown');
                            const desaValue = document.getElementById('desaValue');

                            function renderOptions(filter = '') {
                                const filtered = filter ?
                                    desas.filter(d => d.toLowerCase().includes(filter.toLowerCase())) :
                                    desas;

                                if (filtered.length === 0) {
                                    desaOptions.innerHTML =
                                        '<div class="px-4 py-2 text-gray-500 text-sm">Tidak ada hasil</div>';
                                    return;
                                }

                                desaOptions.innerHTML = filtered.map(desa => {
                                    const displayText = desa === 'all' ?
                                        '<strong>Semua Desa</strong>' :
                                        desa;

                                    return `<div class="desa-option px-4 py-2 cursor-pointer hover:bg-indigo-50 ${desa === 'all' ? 'bg-indigo-50 border-b-2 border-indigo-200' : ''}" data-value="${desa}">
                            ${displayText}
                        </div>`;
                                }).join('');

                                document.querySelectorAll('.desa-option').forEach(option => {
                                    option.addEventListener('click', function() {
                                        const value = this.getAttribute('data-value');
                                        searchInput.value = value === 'all' ?
                                            'Semua Desa' :
                                            value;
                                        desaValue.value = value;
                                        dropdownList.classList.add('hidden');
                                    });
                                });
                            }

                            renderOptions();

                            searchInput.addEventListener('focus', () => {
                                dropdownList.classList.remove('hidden');
                            });

                            searchInput.addEventListener('input', (e) => {
                                renderOptions(e.target.value);
                                dropdownList.classList.remove('hidden');
                            });

                            toggleBtn.addEventListener('click', (e) => {
                                e.stopPropagation();
                                dropdownList.classList.toggle('hidden');
                            });

                            document.addEventListener('click', (e) => {
                                if (!searchInput.contains(e.target) && !dropdownList.contains(e
                                        .target) && !toggleBtn.contains(e.target)) {
                                    dropdownList.classList.add('hidden');
                                }
                            });
                        @endif
                    }
                });

                document.addEventListener('click', function handler(e) {
                    if (e.target.id === 'cancelExportBtn') {
                        Swal.close();
                        document.removeEventListener('click', handler);
                    }

                    if (e.target.id === 'confirmExportBtn') {
                        let bidang = null;
                        let desa = null;

                        if (userRole === 'kabid') {
                            bidang = bidangKabid;
                        } else if (userRole === 'ketua-posyandu') {
                            bidang = document.getElementById('selectBidang')?.value;
                            desa = userDesa;
                        } else {
                            bidang = document.getElementById('selectBidang')?.value;
                        }

                        if (userRole === 'kabid') {
                            desa = document.getElementById('desaValue')?.value || '';
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
                        }

                        if (userRole === 'kabid' && !desa) {
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
                        Swal.close();
                    }
                });
            });
        }

        function exportData(bidang, desa) {
            const selectedYear = '{{ $selectedYear }}';

            if (userRole === 'ketua-posyandu') {
                const url = `/admin/export/${encodeURIComponent(bidang)}/${userDesa}?year=${selectedYear}`;
                window.location.href = url;
            } else if (userRole === "kabid") {
                if (desa === 'all') {
                    window.location.href =
                        `/admin/export-all-bidang-desa?year=${selectedYear}&bidang=${encodeURIComponent(bidang)}`;
                } else {
                    const url =
                        `/admin/export/${encodeURIComponent(bidang)}/${encodeURIComponent(desa)}?year=${selectedYear}`;
                    window.location.href = url;
                }
            } else if (bidang === 'all' && userRole === 'ketua-timpembina-posyandu') {
                window.location.href = `/admin/export-all/${desa}?year=${selectedYear}`;
            } else {
                const url = `/admin/export/${encodeURIComponent(bidang)}/${encodeURIComponent(desa)}?year=${selectedYear}`;
                window.location.href = url;
            }
        }
    </script>
@endsection
