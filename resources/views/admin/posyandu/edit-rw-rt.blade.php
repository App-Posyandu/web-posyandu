@extends('dashboard.layouts.dashboard')
@section('title', 'Edit Mapping RW/RT - ' . $posyandu->nama_posyandu)

@section('content')
    <div class="w-full max-w-6xl mx-auto sm:px-6 lg:px-8" x-data="rwRtManager()">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-8 text-gray-900">

                <!-- Header -->
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">
                        Kelola RW/RT Posyandu
                    </h2>
                    <p class="text-gray-600 mt-2">
                        {{ $posyandu->nama_posyandu }} - {{ $posyandu->desa }}
                    </p>
                </div>

                <!-- Info Card -->
                <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                    <div class="flex items-start">
                        <i class="bi bi-info-circle-fill text-blue-500 mr-3 mt-0.5"></i>
                        <div class="text-sm text-blue-700">
                            <p class="font-semibold mb-1">Ketentuan:</p>
                            <ul class="list-disc list-inside space-y-1 ml-2">
                                <li><strong>Maksimal 15 RW</strong> per posyandu</li>
                                <li><strong>Maksimal 53 RT</strong> total di seluruh RW</li>
                                <li>Format RW: <code class="bg-white px-2 py-0.5 rounded">RW01</code>, <code
                                        class="bg-white px-2 py-0.5 rounded">RW02</code>, dst</li>
                                <li>Format RT: <code class="bg-white px-2 py-0.5 rounded">RT001</code>, <code
                                        class="bg-white px-2 py-0.5 rounded">RT002</code>, dst</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.posyandu.update-rw-rt', $posyandu) }}">
                    @csrf
                    @method('PUT')

                    <!-- Counter Stats -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-gradient-to-r from-purple-50 to-purple-100 p-4 rounded-lg border border-purple-200">
                            <div class="text-sm text-purple-600 font-semibold">Total RW Aktif</div>
                            <div class="text-3xl font-bold text-purple-700" x-text="rwList.length"></div>
                            <div class="text-xs text-purple-500 mt-1">Maksimal: 15 RW</div>
                        </div>

                        <div class="bg-gradient-to-r from-green-50 to-green-100 p-4 rounded-lg border border-green-200">
                            <div class="text-sm text-green-600 font-semibold">Total RT Terdaftar</div>
                            <div class="text-3xl font-bold text-green-700" x-text="getTotalRt()"></div>
                            <div class="text-xs text-green-500 mt-1">Maksimal: 53 RT</div>
                        </div>

                        <div class="bg-gradient-to-r from-blue-50 to-blue-100 p-4 rounded-lg border border-blue-200">
                            <div class="text-sm text-blue-600 font-semibold">Status</div>
                            <div class="text-xl font-bold" :class="getTotalRt() > 53 ? 'text-red-600' : 'text-blue-700'"
                                x-text="getTotalRt() > 53 ? 'OVER LIMIT!' : 'Valid'">
                            </div>
                        </div>
                    </div>

                    <!-- Manage RW -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="bi bi-plus-circle text-pink-500"></i>
                            Kelola RW (Rukun Warga)
                        </h3>

                        <div class="space-y-3">
                            <template x-for="(rw, index) in rwList" :key="index">
                                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border">
                                    <input type="hidden" :name="'rw_list[' + index + ']'" :value="rw">
                                    <div class="flex-1">
                                        <input type="text" x-model="rwList[index]"
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-pink-500 focus:ring-pink-500"
                                            placeholder="RW01" pattern="RW\d{2}" required>
                                    </div>
                                    <button type="button" @click="removeRw(index)"
                                        class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </template>

                            <button type="button" @click="addRw()" :disabled="rwList.length >= 15"
                                class="w-full px-4 py-2 bg-pink-500 text-white rounded-md hover:bg-pink-600 disabled:bg-gray-300 disabled:cursor-not-allowed">
                                <i class="bi bi-plus-lg mr-2"></i>
                                Tambah RW
                                <span x-show="rwList.length >= 15" class="text-xs">(Maksimal tercapai)</span>
                            </button>
                        </div>
                    </div>

                    <!-- Manage RT per RW -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="bi bi-diagram-3 text-blue-500"></i>
                            Mapping RT ke RW
                        </h3>

                        <div class="space-y-6">
                            <template x-for="rw in rwList" :key="rw">
                                <div class="border border-gray-200 rounded-lg p-4 bg-white">
                                    <div class="flex justify-between items-center mb-3">
                                        <h4 class="font-semibold text-gray-700" x-text="rw"></h4>
                                        <span class="text-sm text-gray-500"
                                            x-text="'RT: ' + (rtMapping[rw] ? rtMapping[rw].length : 0)">
                                        </span>
                                    </div>

                                    <div class="space-y-2">
                                        <template x-for="(rt, rtIndex) in (rtMapping[rw] || [])" :key="rtIndex">
                                            <div class="flex items-center gap-3">
                                                <input type="hidden" :name="'rt_mapping[' + rw + '][' + rtIndex + ']'"
                                                    :value="rt">
                                                <input type="text" x-model="rtMapping[rw][rtIndex]"
                                                    class="flex-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                    placeholder="RT001" pattern="RT\d{3}" required>
                                                <button type="button" @click="removeRt(rw, rtIndex)"
                                                    class="px-3 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </div>
                                        </template>

                                        <button type="button" @click="addRt(rw)" :disabled="getTotalRt() >= 53"
                                            class="w-full px-3 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 disabled:bg-gray-300 disabled:cursor-not-allowed text-sm">
                                            <i class="bi bi-plus mr-2"></i>
                                            Tambah RT di <span x-text="rw"></span>
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <div x-show="rwList.length === 0" class="text-center text-gray-500 py-8">
                                Belum ada RW. Tambahkan RW terlebih dahulu.
                            </div>
                        </div>
                    </div>

                    <!-- Warning jika over limit -->
                    <div x-show="getTotalRt() > 53" class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded">
                        <p class="text-red-700 font-semibold">
                            ⚠️ Total RT melebihi batas! Anda harus mengurangi RT sebelum menyimpan.
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-between mt-8 gap-4">
                        <a href="{{ route('admin.posyandu.index') }}"
                            class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                            Batal
                        </a>
                        <button type="submit" :disabled="getTotalRt() > 53 || rwList.length > 15"
                            class="px-6 py-2 bg-pink-600 text-white rounded-md hover:bg-pink-700 disabled:bg-gray-300 disabled:cursor-not-allowed">
                            💾 Simpan Mapping
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
                    rwList: @json($posyandu->rw_list ?? []),
                    rtMapping: @json($posyandu->rt_mapping ?? []),

                    init() {
                        // Ensure rtMapping keys match rwList
                        this.rwList.forEach(rw => {
                            if (!this.rtMapping[rw]) {
                                this.rtMapping[rw] = [];
                            }
                        });
                    },

                    addRw() {
                        if (this.rwList.length >= 15) {
                            alert('Maksimal 15 RW per posyandu');
                            return;
                        }

                        const nextNum = String(this.rwList.length + 1).padStart(2, '0');
                        const newRw = `RW${nextNum}`;

                        this.rwList.push(newRw);
                        this.rtMapping[newRw] = [];
                    },

                    removeRw(index) {
                        const rw = this.rwList[index];

                        if (confirm(`Hapus ${rw} dan semua RT nya?`)) {
                            delete this.rtMapping[rw];
                            this.rwList.splice(index, 1);
                        }
                    },

                    addRt(rw) {
                        if (this.getTotalRt() >= 53) {
                            alert('Maksimal 53 RT total di seluruh RW');
                            return;
                        }

                        if (!this.rtMapping[rw]) {
                            this.rtMapping[rw] = [];
                        }

                        const nextNum = String(this.rtMapping[rw].length + 1).padStart(3, '0');
                        this.rtMapping[rw].push(`RT${nextNum}`);
                    },

                    removeRt(rw, index) {
                        this.rtMapping[rw].splice(index, 1);
                    },

                    getTotalRt() {
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
