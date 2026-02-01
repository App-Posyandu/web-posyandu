@extends('dashboard.layouts.dashboard')
@section('title', 'Kelola RW/RT - ' . $posyandu->nama_posyandu)

@section('content')
    <div class="w-full max-w-7xl mx-auto sm:px-6 lg:px-8" x-data="rwRtManager()">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-8 text-gray-900">

                <!-- Header -->
                <div class="mb-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">Kelola RW/RT Posyandu</h2>
                            <p class="text-gray-600 mt-2">
                                {{ $posyandu->nama_posyandu }} - {{ $posyandu->desa }}, {{ $posyandu->kecamatan }}
                            </p>
                        </div>
                        <a href="{{ route('admin.posyandu.index') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                            ← Kembali
                        </a>
                    </div>
                </div>

                <!-- Info Card -->
                <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                    <div class="flex items-start">
                        <i class="bi bi-info-circle-fill text-blue-500 mr-3 mt-0.5 text-xl"></i>
                        <div class="text-sm text-blue-700">
                            <p class="font-semibold mb-2">Informasi Sistem RW/RT</p>
                            <ul class="list-disc list-inside space-y-1 ml-2">
                                <li><strong>RW tersedia di kabupaten:</strong> RW01 sampai RW15 (total 15 RW)</li>
                                <li><strong>RT tersedia di kabupaten:</strong> RT001 sampai RT053 (total 53 RT)</li>
                                <li>Pilih RW/RT mana saja yang <strong>dilayani oleh posyandu ini</strong></li>
                                <li>User yang register akan otomatis memilih dari RW/RT yang sudah Anda tentukan</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.posyandu.save-rw-rt', $posyandu) }}">
                    @csrf

                    <!-- Counter Stats -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div class="bg-gradient-to-r from-purple-50 to-purple-100 p-4 rounded-lg border border-purple-200">
                            <div class="text-sm text-purple-600 font-semibold">RW yang Dilayani Posyandu Ini</div>
                            <div class="text-3xl font-bold text-purple-700">
                                <span x-text="selectedRwList.length"></span>
                            </div>
                            <div class="text-xs text-purple-500 mt-1">dari 15 RW yang ada di kabupaten</div>
                        </div>

                        <div class="bg-gradient-to-r from-green-50 to-green-100 p-4 rounded-lg border border-green-200">
                            <div class="flex justify-between items-center">
                                <div class="text-sm text-green-600 font-semibold">RT yang Dilayani Posyandu Ini</div>
                                <button type="button" @click="$nextTick(() => {})"
                                    class="text-xs px-2 py-1 bg-green-500 text-white rounded hover:bg-green-600">
                                    Refresh
                                </button>
                            </div>
                            <div class="text-3xl font-bold text-green-700">
                                <span x-text="getTotalSelectedRt()"></span>
                            </div>
                            <div class="text-xs text-green-500 mt-1">dari 53 RT yang ada di kabupaten</div>
                            <div class="mt-3 p-2 bg-white rounded text-xs">
                                <div class="font-mono text-gray-600">
                                    Debug:
                                    <span x-text="'Total: ' + getTotalSelectedRt()"></span>
                                    <template x-for="rw in selectedRwList" :key="rw">
                                        <span class="ml-2" x-text="rw + ': ' + (rtMapping[rw] || []).length"></span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pilih RW -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="bi bi-check-square text-pink-500"></i>
                            Pilih RW yang Dilayani Posyandu
                        </h3>

                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <div class="grid grid-cols-3 md:grid-cols-5 gap-3">
                                <template x-for="rw in allRwOptions" :key="rw">
                                    <label class="flex items-center gap-2 p-3 bg-white rounded-lg border cursor-pointer hover:border-pink-300 transition"
                                        :class="{ 'border-pink-500 bg-pink-50': selectedRwList.includes(rw) }">
                                        <input type="checkbox" :value="rw"
                                            x-model="selectedRwList"
                                            class="w-4 h-4 text-pink-600 rounded focus:ring-pink-500">
                                        <span class="text-sm font-medium text-gray-700" x-text="rw"></span>
                                    </label>
                                </template>
                            </div>
                            <p class="text-xs text-gray-500 mt-3">
                                <i class="bi bi-info-circle text-blue-500"></i>
                                Centang RW mana saja yang dilayani oleh posyandu ini
                            </p>
                        </div>
                    </div>

                    <!-- Mapping RT per RW -->
                    <div class="mb-8" x-show="selectedRwList.length > 0">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="bi bi-diagram-3 text-blue-500"></i>
                            Pilih RT untuk Setiap RW
                        </h3>

                        <div class="space-y-4">
                            <template x-for="rw in selectedRwList" :key="rw">
                                <div class="border border-gray-200 rounded-lg p-4 bg-white">
                                    <div class="flex justify-between items-center mb-3">
                                        <h4 class="font-semibold text-gray-700 text-lg" x-text="rw"></h4>
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm px-2 py-1 bg-blue-100 text-blue-700 rounded font-medium">
                                                <span x-text="(rtMapping[rw] || []).length"></span> / 53 RT
                                            </span>
                                            <button type="button" @click="selectAllRt(rw)"
                                                class="px-3 py-1.5 bg-green-500 text-white rounded-md hover:bg-green-600 text-xs">
                                                <i class="bi bi-check-all mr-1"></i>
                                                Pilih Semua
                                            </button>
                                            <button type="button" @click="clearAllRt(rw)"
                                                class="px-3 py-1.5 bg-gray-500 text-white rounded-md hover:bg-gray-600 text-xs">
                                                <i class="bi bi-x-circle mr-1"></i>
                                                Hapus Semua
                                            </button>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-4 md:grid-cols-8 lg:grid-cols-10 gap-2">
                                        <template x-for="rt in allRtOptions" :key="rt">
                                            <label class="flex items-center justify-center p-2 bg-gray-50 rounded border cursor-pointer hover:border-blue-300 transition text-xs"
                                                :class="{ 'border-blue-500 bg-blue-50': rtMapping[rw] && rtMapping[rw].includes(rt) }">
                                                <input type="checkbox" :value="rt"
                                                    @change="toggleRt(rw, rt)"
                                                    :checked="rtMapping[rw] && rtMapping[rw].includes(rt)"
                                                    class="sr-only">
                                                <span class="font-medium" x-text="rt"></span>
                                            </label>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Hidden inputs untuk submit -->
                    <template x-for="(rw, index) in selectedRwList" :key="index">
                        <input type="hidden" :name="'rw_list[' + index + ']'" :value="rw">
                    </template>

                    <template x-for="rw in selectedRwList" :key="rw">
                        <template x-for="(rt, rtIndex) in (rtMapping[rw] || [])" :key="rtIndex">
                            <input type="hidden" :name="'rt_mapping[' + rw + '][' + rtIndex + ']'" :value="rt">
                        </template>
                    </template>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-between mt-8 pt-6 border-t gap-4">
                        <a href="{{ route('admin.posyandu.index') }}"
                            class="px-6 py-2.5 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                            Batal
                        </a>
                        <button type="submit" :disabled="selectedRwList.length === 0"
                            class="px-8 py-2.5 bg-pink-600 text-white rounded-md hover:bg-pink-700 disabled:bg-gray-300 disabled:cursor-not-allowed shadow-lg">
                            <i class="bi bi-save mr-2"></i>
                            Simpan Mapping RW/RT
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('rwRtManager', () => ({
                    allRwOptions: Array.from({ length: 15 }, (_, i) => `RW${String(i + 1).padStart(2, '0')}`),
                    allRtOptions: Array.from({ length: 53 }, (_, i) => `RT${String(i + 1).padStart(3, '0')}`),

                    selectedRwList: @json($posyandu->rw_list ?? []),
                    rtMapping: @json($posyandu->rt_mapping ?? []),

                    init() {
                        console.log('Alpine.js initialized');
                        console.log('Initial RW List:', this.selectedRwList);
                        console.log('Initial RT Mapping:', this.rtMapping);

                        this.selectedRwList.forEach(rw => {
                            if (!this.rtMapping[rw]) {
                                this.rtMapping[rw] = [];
                            }
                        });

                        this.$watch('selectedRwList', (newVal, oldVal) => {
                            console.log('RW List changed:', newVal);

                            newVal.forEach(rw => {
                                if (!this.rtMapping[rw]) {
                                    this.rtMapping[rw] = [];
                                }
                            });

                            if (oldVal) {
                                oldVal.forEach(rw => {
                                    if (!newVal.includes(rw)) {
                                        delete this.rtMapping[rw];
                                    }
                                });
                            }
                        });

                        this.$watch('rtMapping', (newVal) => {
                            console.log('RT Mapping changed:', newVal);
                            console.log('Total RT:', this.getTotalSelectedRt());
                        }, { deep: true });
                    },

                    selectAllRt(rw) {
                        console.log('Select all RT for', rw);
                        this.rtMapping[rw] = [...this.allRtOptions];
                        this.rtMapping = { ...this.rtMapping };
                    },

                    clearAllRt(rw) {
                        console.log('Clear all RT for', rw);
                        this.rtMapping[rw] = [];
                        this.rtMapping = { ...this.rtMapping };
                    },

                    toggleRt(rw, rt) {
                        if (!this.rtMapping[rw]) {
                            this.rtMapping[rw] = [];
                        }

                        const index = this.rtMapping[rw].indexOf(rt);
                        if (index > -1) {
                            this.rtMapping[rw].splice(index, 1);
                            console.log('Removed', rt, 'from', rw);
                        } else {
                            this.rtMapping[rw].push(rt);
                            console.log('Added', rt, 'to', rw);
                        }
                    },

                    getTotalSelectedRt() {
                        let total = 0;
                        Object.values(this.rtMapping).forEach(rtList => {
                            if (Array.isArray(rtList)) {
                                total += rtList.length;
                            }
                        });
                        return total;
                    }
                }))
            });
        </script>
    @endpush
@endsection
