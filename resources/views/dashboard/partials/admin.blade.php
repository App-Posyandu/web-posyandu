@section('content')
    <div x-data="dashboardFilter" x-init="init()"
        class="flex flex-col gap-4 md:gap-6 px-4 sm:px-6 lg:px-8 w-full mx-auto">
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

        {{-- ✅ YEAR FILTER SECTION --}}
        <div class="bg-white overflow-hidden shadow-xl rounded-lg md:rounded-2xl p-4 md:p-6 w-full">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                {{-- Info Current Year --}}
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

                {{-- Year Selector --}}
                <div class="flex items-center gap-3">
                    <label for="yearFilter" class="text-sm font-medium text-gray-700 whitespace-nowrap">
                        Pilih Tahun:
                    </label>
                    <form method="GET" action="{{ route('dashboard') }}" id="yearFilterForm" class="flex gap-2">
                        {{-- Preserve existing filters --}}
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

                        {{-- Reset Button (jika bukan tahun berjalan) --}}
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

            {{-- Statistics Summary --}}
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

            {{-- Loading Indicator --}}
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

        {{-- Info Message (Data Historis) --}}
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

        <!-- Dashboard Cards Section -->
        @if (auth()->user()->role !== 'kabid')
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
        @endif

        {{-- List Pengajuan Section --}}
        <div class="w-full">
            <div class="bg-white overflow-hidden shadow-xl rounded-lg md:rounded-2xl p-4 md:p-6 lg:p-8">
                {{-- Header with Search and Export --}}
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
                        {{-- Archive Toggle --}}
                        <button @click.prevent="toggleArchive($event)" type="button"
                            class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium bg-white text-gray-700 hover:bg-gray-50 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4">
                                </path>
                            </svg>
                            <span x-text="showArchived ? 'Lihat Pengajuan Aktif' : 'Lihat Arsip'"></span>
                        </button>
                        {{-- Filter Status --}}
                        <select x-model="filterStatus" @change="loadDashboardData()"
                            class="border-gray-300 rounded-md shadow-sm text-sm w-full sm:w-auto px-3 py-2">
                            <option value="">Semua Status</option>
                            <option value="Diproses">Diproses</option>
                            <option value="Disetujui">Disetujui</option>
                            <option value="Ditolak">Ditolak</option>
                        </select>

                        {{-- Search Input --}}
                        <div class="relative w-full sm:w-auto">
                            <input type="text" x-model="searchQuery" @input.debounce.500ms="loadDashboardData()"
                                placeholder="Cari berdasarkan nama..."
                                class="w-full sm:w-64 pl-4 pr-10 py-2 border border-gray-300 rounded-md text-sm focus:ring-pink-500 focus:border-pink-500">
                            <i class="bi bi-search absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400"></i>
                        </div>

                        {{-- Reset Link --}}
                        <button x-show="searchQuery || filterStatus" @click="resetFilters()"
                            class="text-sm text-center sm:text-left text-gray-600 hover:text-gray-900 py-2 sm:py-0">
                            Reset
                        </button>

                        {{-- Export Button --}}
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

                {{-- Table --}}
                <div class="overflow-x-auto -mx-4 md:mx-0">
                    <div class="inline-block min-w-full align-middle">
                        {{-- Loading State --}}
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

                        {{-- Table Content --}}
                        <div x-show="!loading" x-html="tableHtml" @click="handlePagination($event)"></div>
                    </div>
                </div>

                {{-- Pagination --}}
                <div class="mt-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 text-xs md:text-sm text-gray-600"
                    @click="handlePagination($event)">

                    {{-- Info (Kiri) --}}
                    <div x-show="!loading" class="text-gray-600">
                        <template x-if="paginationInfo.total > 0">
                            <span>
                                Showing
                                <span class="font-medium" x-text="paginationInfo.from"></span>
                                to
                                <span class="font-medium" x-text="paginationInfo.to"></span>
                                of
                                <span class="font-medium" x-text="paginationInfo.total"></span>
                                results
                            </span>
                        </template>
                        <template x-if="paginationInfo.total === 0">
                            <span>Tidak ada data</span>
                        </template>
                    </div>

                    {{-- Pagination Links (Kanan) --}}
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

                // Buat slug untuk icon
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
            Alpine.data('dashboardFilter', () => ({
                // State
                currentYear: {{ $currentYear }},
                selectedYear: {{ $selectedYear }},
                availableYears: @json($availableYears),
                loading: false,
                isVerified: {{ $isVerified ? 'true' : 'false' }},
                filterStatus: '{{ request('status') }}',
                searchQuery: '{{ request('search') }}',
                showArchived: {{ $showArchived ?? false ? 'true' : 'false' }},

                // Data
                bidangData: @json($chartData),

                statistics: {
                    total: {{ $ajuanCounts->sum() }},
                    disetujui: {{ $semuaAjuan->where('status_pengajuan', 'Disetujui')->count() }},
                    diproses: {{ $semuaAjuan->where('status_pengajuan', 'Diproses')->count() }},
                    ditolak: {{ $semuaAjuan->where('status_pengajuan', 'Ditolak')->count() }},
                },

                paginationInfo: {
                    from: 0,
                    to: 0,
                    total: 0
                },

                tableHtml: '',
                paginationHtml: '',
                chart: null,

                // Methods
                init() {
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
                    if (!this.isVerified) return;

                    this.loading = true;

                    try {
                        const params = new URLSearchParams({
                            year: this.selectedYear,
                            search: this.searchQuery,
                            status: this.filterStatus,
                            archived: this.showArchived ? '1' : '0',
                            ajax: '1',
                            page: page
                        });

                        console.log('Loading dashboard data with params:', params.toString());

                        const response = await fetch(`{{ route('dashboard') }}?${params}`);
                        const data = await response.json();

                        // Update bidang data
                        this.bidangData = data.bidangData;
                        this.statistics = data.statistics;
                        this.tableHtml = data.tableHtml;
                        this.paginationHtml = data.paginationHtml;

                        this.paginationInfo = data.paginationInfo;

                        // Update chart
                        this.updateChart();

                    } catch (error) {
                        console.error('Error loading dashboard data:', error);
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
                },

                exportData() {
                    const userRole = "{{ auth()->user()->role }}";
                    const userPosyanduDesa = "{{ auth()->user()?->posyandu?->desa ?? '' }}";
                    const userDesa = "{{ auth()->user()->desa ?? '' }}";
                    const userKecamatan = "{{ auth()->user()->kecamatan ?? '' }}";
                    const userKabupaten = "{{ auth()->user()->kabupaten ?? '' }}";
                    const bidangKabid = "{{ auth()->user()?->bidang?->nama_bidang ?? '' }}";

                    // ✅ KETUA KADER: Hanya bisa export data posyandu mereka sendiri
                    if (userRole === 'ketua-kader') {
                        if (!userPosyanduDesa) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Data Posyandu Tidak Ditemukan',
                                text: 'Posyandu belum ditetapkan di profile Anda.',
                                confirmButtonColor: '#f87171'
                            });
                            return;
                        }

                        // ✅ Direct export ke posyandu mereka saja
                        const selectedYear = this.selectedYear;
                        // Gunakan nama bidang dari modal atau default ke all
                        this.showKetuaKaderExportModal();
                        return;
                    }

                    // ✅ KADES: Hanya bisa export untuk desa mereka (semua bidang)
                    if (userRole === 'kades') {
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

                    // ✅ ADMIN KECAMATAN: Hanya bisa export untuk desa & bidang di kecamatan mereka
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

                    // ✅ KABID: Hanya bisa export bidang mereka saja (semua desa & kecamatan)
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

                    // ✅ KETUA POSYANDU, ADMIN KABUPATEN, ADMIN: Export semua data
                    if (['ketua-posyandu', 'admin-kabupaten', 'admin'].includes(userRole)) {
                        // Export semua bidang & desa
                        window.location.href = `/admin/export-all-bidang-desa?year=${this.selectedYear}`;
                        return;
                    }

                    // Default: export semua
                    window.location.href = `/admin/export-all-bidang-desa?year=${this.selectedYear}`;
                },

                // ✅ Modal untuk KETUA KADER
                showKetuaKaderExportModal() {
                    const selectedYear = this.selectedYear;
                    const userDesa = "{{ auth()->user()?->posyandu?->desa ?? '' }}";

                    Swal.fire({
                        title: '<h2 class="text-lg md:text-xl font-bold text-gray-800 mb-2">Export Data Pengajuan</h2>',
                        html: `
            <div class="space-y-6">
                <!-- Info Posyandu -->
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

                <!-- Pilih Bidang -->
                <div class="text-left">
                    <label class="block text-start font-semibold mb-2 text-gray-700">Pilih Bidang:</label>
                    <select id="ketuaKaderBidangSelect" class="w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="" disabled selected>Pilih Bidang</option>
                        <option value="all">📊 Semua Bidang</option>
                        <option value="Bidang Perumahan Rakyat">Bidang Perumahan Rakyat</option>
                        <option value="Bidang Pendidikan">Bidang Pendidikan</option>
                        <option value="Bidang Kesehatan">Bidang Kesehatan</option>
                        <option value="Bidang Sosial">Bidang Sosial</option>
                        <option value="Bidang Pekerjaan Umum">Bidang Pekerjaan Umum</option>
                        <option value="Bidang Trantibumlinmas">Bidang Trantibumlinmas</option>
                    </select>
                </div>

                <!-- Buttons -->
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

                            // Export ke posyandu mereka dengan bidang tertentu
                            if (selectedBidang === 'all') {
                                window.location.href = `/admin/export-all/${userDesa}?year=${selectedYear}`;
                            } else {
                                window.location.href = `/admin/export/${encodeURIComponent(selectedBidang)}/${userDesa}?year=${selectedYear}`;
                            }

                            Swal.close();
                            document.removeEventListener('click', handleKetuaKaderExport);
                        }
                    };

                    document.addEventListener('click', handleKetuaKaderExport);
                },

                // ✅ Modal untuk KADES
                showKadesExportModal() {
                    const selectedYear = this.selectedYear;
                    const userDesa = "{{ auth()->user()->desa ?? '' }}";

                    Swal.fire({
                        title: '<h2 class="text-lg md:text-xl font-bold text-gray-800 mb-2">Export Data Pengajuan</h2>',
                        html: `
            <div class="space-y-6">
                <!-- Info Desa -->
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

                <!-- Buttons -->
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
                            // Export semua bidang untuk desa mereka
                            window.location.href = `/admin/export-all/${userDesa}?year=${selectedYear}`;
                            Swal.close();
                            document.removeEventListener('click', handleKadesExport);
                        }
                    };

                    document.addEventListener('click', handleKadesExport);
                },

                // ✅ Modal untuk ADMIN KECAMATAN
                showAdminKecamatanExportModal() {
                    const selectedYear = this.selectedYear;
                    const userKecamatan = "{{ auth()->user()->kecamatan ?? '' }}";
                    const desas = @json($desas ?? []);

                    Swal.fire({
                        title: '<h2 class="text-lg md:text-xl font-bold text-gray-800 mb-2">Export Data Pengajuan</h2>',
                        html: `
            <div class="space-y-6">
                <!-- Info Kecamatan -->
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

                <!-- Pilih Desa -->
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

                <!-- Buttons -->
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

                            // Export dengan desa tertentu atau semua desa
                            if (selectedDesa === 'all') {
                                window.location.href = `/admin/export-all/${selectedDesa}?year=${selectedYear}`;
                            } else {
                                window.location.href = `/admin/export-all/${selectedDesa}?year=${selectedYear}`;
                            }

                            Swal.close();
                            document.removeEventListener('click', handleAdminKecamatanExport);
                        }
                    };

                    document.addEventListener('click', handleAdminKecamatanExport);
                },

                handlePagination(event) {
                    const link = event.target.tagName === 'A' ? event.target : event.target.closest('a');
                    if (!link) return;

                    // Hanya tangani klik pada pagination agar link Detail/Cetak tetap normal
                    const isPagination = link.closest('.pagination');
                    if (!isPagination) return;

                    event.preventDefault();

                    const url = new URL(link.href);
                    const page = url.searchParams.get('page') || 1;

                    this.loadDashboardData(page);
                },

                // ✅ METHOD BARU: Modal khusus untuk Kabid
                showKabidExportModal() {
                    const bidangKabid = "{{ auth()->user()->bidang?->nama_bidang ?? '' }}";
                    const kabupatenKabid = "{{ auth()->user()->kabupaten ?? '' }}";
                    const desas = @json($desas ?? []);

                    Swal.fire({
                        title: '<h2 class="text-lg md:text-xl font-bold text-gray-800 mb-2">Export Data Pengajuan</h2>',
                        html: `
            <div class="space-y-6">
                <!-- Info Bidang -->
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

                <!-- Pilih Desa -->
                <div class="text-left">
                    <label class="block text-start font-semibold mb-2 text-gray-700">Pilih Desa:</label>
                    <select id="kabidDesaSelect" class="w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                        <option value="" disabled selected>Pilih Desa</option>
                        <option value="all" class="font-bold">📊 Semua Desa di ${kabupatenKabid}</option>
                        <optgroup label="Desa Spesifik:">
                            ${desas.map(d => `<option value="${d}">${d}</option>`).join('')}
                        </optgroup>
                    </select>
                    <p class="text-xs text-gray-500 mt-2">
                        <i class="bi bi-lightbulb"></i>
                        Pilih "Semua Desa" untuk export seluruh data di ${kabupatenKabid}
                    </p>
                </div>

                <!-- Buttons -->
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

                    // Event listeners
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

                            // ✅ Export dengan parameter yang benar
                            const selectedYear = this.selectedYear;

                            if (selectedDesa === 'all') {
                                // Export semua desa di kabupatennya untuk bidangnya
                                window.location.href =
                                    `/admin/export/${encodeURIComponent(bidangKabid)}?year=${selectedYear}`;
                            } else {
                                // Export desa tertentu untuk bidangnya
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
        const bidangKabid = "{{ Auth::user()?->bidang?->nama_bidang ?? '' }}"; // ✅ Fixed: nama_bidang bukan name

        document.getElementById('exportExcelBtn').addEventListener('click', function() {
            // ✅ KABID: Tidak perlu pilih bidang (sudah ada di profil)
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
                // ✅ KABID: Tampilkan info bidang yang sudah ditetapkan
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
                const desas = @json($desas);

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
                    // Initialize dropdown desa dengan vanilla JS
                    @if (Auth::user()->role === 'kabid')
                        const desas = ['all', ...
                            @json($desas)
                        ]; // ✅ Tambahkan opsi 'all'
                        const searchInput = document.getElementById('desaSearch');
                        const dropdownList = document.getElementById('desaDropdownList');
                        const desaOptions = document.getElementById('desaOptions');
                        const toggleBtn = document.getElementById('toggleDesaDropdown');
                        const desaValue = document.getElementById('desaValue');

                        // Fungsi untuk render options
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

                            // Event listener untuk setiap option
                            document.querySelectorAll('.desa-option').forEach(option => {
                                option.addEventListener('click', function() {
                                    const value = this.getAttribute('data-value');
                                    searchInput.value = value === 'all' ? 'Semua Desa' :
                                        value;
                                    desaValue.value = value;
                                    dropdownList.classList.add('hidden');
                                });
                            });
                        }

                        // Initial render
                        renderOptions();

                        // Show dropdown on focus
                        searchInput.addEventListener('focus', () => {
                            dropdownList.classList.remove('hidden');
                        });

                        // Filter saat typing
                        searchInput.addEventListener('input', (e) => {
                            renderOptions(e.target.value);
                            dropdownList.classList.remove('hidden');
                        });

                        // Toggle dropdown
                        toggleBtn.addEventListener('click', (e) => {
                            e.stopPropagation();
                            dropdownList.classList.toggle('hidden');
                        });

                        // Close dropdown saat click di luar
                        document.addEventListener('click', (e) => {
                            if (!searchInput.contains(e.target) && !dropdownList.contains(e
                                    .target) && !toggleBtn.contains(e.target)) {
                                dropdownList.classList.add('hidden');
                            }
                        });
                    @endif
                }
            });

            // Event listener untuk tombol
            document.addEventListener('click', function handler(e) {
                if (e.target.id === 'cancelExportBtn') {
                    Swal.close();
                    document.removeEventListener('click', handler);
                }

                if (e.target.id === 'confirmExportBtn') {
                    let bidang = null;
                    let desa = null;

                    // ✅ HANDLE BIDANG
                    if (userRole === 'kabid') {
                        // Kabid: Gunakan bidang dari profil
                        bidang = bidangKabid;
                    } else if (userRole === 'ketua-kader') {
                        // Ketua Kader: Bisa pilih bidang dari dropdown
                        bidang = document.getElementById('selectBidang')?.value;
                        desa = userDesa;
                    } else {
                        // Role lain: Pilih dari dropdown
                        bidang = document.getElementById('selectBidang')?.value;
                    }

                    // ✅ HANDLE DESA
                    if (userRole === 'kabid') {
                        desa = document.getElementById('desaValue')?.value || '';
                    }

                    // ✅ VALIDASI
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

        function exportData(bidang, desa) {
            const selectedYear = '{{ $selectedYear }}';

            if (userRole === 'ketua-kader') {
                // Ketua Kader: Export bidang tertentu di desanya
                const url = `/admin/export/${encodeURIComponent(bidang)}/${userDesa}?year=${selectedYear}`;
                window.location.href = url;
            } else if (userRole === "kabid") {
                // ✅ KABID: Export bidangnya di desa tertentu atau semua desa
                if (desa === 'all') {
                    // Export semua desa untuk bidangnya
                    window.location.href =
                        `/admin/export-all-bidang-desa?year=${selectedYear}&bidang=${encodeURIComponent(bidang)}`;
                } else {
                    // Export desa tertentu untuk bidangnya
                    const url =
                        `/admin/export/${encodeURIComponent(bidang)}/${encodeURIComponent(desa)}?year=${selectedYear}`;
                    window.location.href = url;
                }
            } else if (bidang === 'all' && userRole === 'ketua-posyandu') {
                // Ketua Posyandu: Export semua bidang
                window.location.href = `/admin/export-all/${desa}?year=${selectedYear}`;
            } else {
                // Role lain: Export bidang tertentu di desa tertentu
                const url = `/admin/export/${encodeURIComponent(bidang)}/${encodeURIComponent(desa)}?year=${selectedYear}`;
                window.location.href = url;
            }
        }
    </script>
@endsection
