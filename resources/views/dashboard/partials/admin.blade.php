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
                            class="{{ $bidangColors[$bidang] ?? 'bg-gray-500' }} text-white p-4 md:py-4 md:px-8 rounded-lg shadow-md flex items-center gap-4">
                            <span class="text-6xl font-bold">{{ $total }}</span>
                            <div class="flex flex-col gap-2">
                                <img src="{{ $icons[\Illuminate\Support\Str::slug(str_replace('Bidang ', '', $bidang))] ??
                                    asset('assets/image/icon/bidang/default.svg') }}"
                                    alt="{{ $bidang }} icon" class="w-8 h-8 mx-3">
                                <span class="ml-3 font-semibold">{{ $bidang }}</span>
                            </div>
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

                    <div class="flex items-center gap-3">
                        <form action="{{ route('ajuan.index') }}" method="GET"
                            class="flex flex-col md:flex-row items-center gap-2 w-full md:w-auto">
                            <select name="status" onchange="this.form.submit()"
                                class="border-gray-300 rounded-md shadow-sm text-sm w-full md:w-auto">
                                <option value="">Semua Status</option>
                                <option value="Diproses" @selected(request('status') == 'Diproses')>Diproses</option>
                                <option value="Disetujui" @selected(request('status') == 'Disetujui')>Disetujui</option>
                                <option value="Ditolak" @selected(request('status') == 'Ditolak')>Ditolak</option>
                            </select>

                            {{-- Search Input dengan Tombol Submit Terintegrasi --}}
                            <div class="relative w-full md:w-auto">
                                <input type="text" name="search" placeholder="Cari berdasarkan nama..."
                                    value="{{ request('search') }}"
                                    class="w-full md:w-64 pl-4 pr-10 py-2 border border-gray-300 rounded-md text-sm focus:ring-pink-500 focus:border-pink-500">
                                <button type="submit" class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <i class="bi bi-search text-gray-400"></i>
                                </button>
                            </div>

                            @if (request('search') || request('status'))
                                <a href="{{ route('dashboard') }}"
                                    class="text-sm text-gray-600 hover:text-gray-900">Reset</a>
                            @endif
                        </form>
                        <button id="exportExcelBtn"
                            class="flex items-center px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">
                            <i class="bi bi-file-earmark-excel-fill mr-2"></i> Export to Excel
                        </button>

                    </div>
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

        @php
            use Illuminate\Support\Facades\Auth;
            $desaUser = optional(optional(Auth::user()->posyandu)->desa);
        @endphp

        const userRole = "{{ Auth::user()->role }}";
        const userDesa = "{{ Auth::user()?->posyandu?->desa ?? '' }}";

        document.getElementById('exportExcelBtn').addEventListener('click', function() {
            // HTML untuk select bidang (selalu muncul)
            let bidangSelectHTML = `
            <div>
                <label class="block font-semibold mb-1 text-gray-700">Pilih Bidang:</label>
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

            // HTML untuk select desa (hanya tampil jika role = kabid)
            let desaSelectHTML = "";
            @if (Auth::user()->role === 'kabid')
                desaSelectHTML = `
                <div>
                    <label class="block font-semibold mb-1 text-gray-700">Pilih Desa:</label>
                    <select id="selectDesa" class="w-full border rounded-md p-2" required>
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
                title: '<h2 class="text-xl font-bold text-gray-800 mb-2">Pilih Bidang & Desa untuk Di-Export</h2>',
                html: `
                <div class="space-y-4">
                    ${bidangSelectHTML}
                    ${desaSelectHTML}
                    <button id="confirmExportBtn"
                        class="swal2-confirm swal2-styled !bg-emerald-600 hover:!bg-emerald-700 w-full py-4 rounded-md text-white font-bold shadow-lg">
                        Export Data
                    </button>
                </div>
            `,
                showConfirmButton: false,
                showCancelButton: true,
                cancelButtonText: 'Batal',
                width: 600,
                background: '#f9fafb',
                customClass: {
                    popup: 'rounded-md md:rounded-2xl shadow-lg p-4 md:p-6',
                    cancelButton: 'bg-white outline outline-red-500 mt-4 text-red-500 hover:text-red-600 font-medium hover:bg-red-500 hover:text-white text-base md:text-lg py-2 px-6'
                }
            });

            document.addEventListener('click', function handler(e) {
                if (e.target && e.target.id === 'confirmExportBtn') {
                    let bidang = null;
                    let desa = null;

                    if (userRole === 'ketua-kader') {
                        bidang = document.getElementById('selectBidang').value;
                        desa = userDesa; // otomatis dari database
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection