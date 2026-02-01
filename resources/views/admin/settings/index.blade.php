@extends('dashboard.layouts.dashboard')
@section('title', 'Pengaturan Sistem')
@section('content')
    <div class="w-full mx-auto sm:px-6 lg:px-4">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">

                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                            <i class="bi bi-gear-fill text-pink-500"></i>
                            Pengaturan Sistem
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">
                            Konfigurasi sistem auto-reject dan pengaturan lainnya
                        </p>
                    </div>
                </div>

                @if (session('success'))
                    <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
                        <div class="flex items-start">
                            <i class="bi bi-check-circle-fill text-green-500 mr-3 mt-0.5 text-xl"></i>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if (session('warning'))
                    <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-lg">
                        <div class="flex items-start">
                            <i class="bi bi-exclamation-triangle-fill text-yellow-500 mr-3 mt-0.5 text-xl"></i>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-yellow-800">{{ session('warning') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                        <div class="flex items-start">
                            <i class="bi bi-x-circle-fill text-red-500 mr-3 mt-0.5 text-xl"></i>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-red-800 mb-2">Terjadi kesalahan:</p>
                                <ul class="list-disc list-inside text-sm text-red-700">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <form action="{{ route('admin.settings.update') }}" method="POST" x-data="settingsForm()">
                    @csrf
                    @method('PUT')

                    <div class="border-b border-gray-200 mb-6">
                        <nav class="-mb-px flex space-x-8">
                            @foreach ($groupedSettings as $category => $categorySettings)
                                <button type="button" @click="activeTab = '{{ $category }}'"
                                    :class="activeTab === '{{ $category }}' ? 'border-pink-500 text-pink-600' :
                                        'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                    class="py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                                    @if ($category === 'revision')
                                        <i class="bi bi-clock-history mr-2"></i>
                                        Revisi & Auto-Reject
                                    @else
                                        <i class="bi bi-gear mr-2"></i>
                                        {{ ucfirst($category) }}
                                    @endif
                                </button>
                            @endforeach
                        </nav>
                    </div>

                    @foreach ($groupedSettings as $category => $categorySettings)
                        <div x-show="activeTab === '{{ $category }}'" x-transition class="space-y-6">

                            @if ($category === 'revision')
                                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg mb-6">
                                    <div class="flex items-start">
                                        <i class="bi bi-info-circle-fill text-blue-500 mr-3 mt-0.5"></i>
                                        <div class="text-sm text-blue-700">
                                            <p class="font-semibold mb-1">Pengaturan Auto-Reject</p>
                                            <p>Sistem akan otomatis menolak pengajuan jika masyarakat tidak merevisi dalam
                                                batas waktu yang ditentukan.</p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                @foreach ($categorySettings as $setting)
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-5">
                                        <div class="flex items-start justify-between mb-3">
                                            <div class="flex-1">
                                                <label for="setting_{{ $setting->key }}"
                                                    class="block text-sm font-semibold text-gray-800 mb-1">
                                                    {{ $setting->label }}
                                                </label>
                                                <p class="text-xs text-gray-600">
                                                    {{ $setting->description }}
                                                </p>
                                            </div>

                                            <span
                                                class="ml-3 px-2 py-1 text-xs font-medium rounded-full
                                                @if ($setting->type === 'integer') bg-blue-100 text-blue-800
                                                @elseif($setting->type === 'boolean') bg-green-100 text-green-800
                                                @else bg-gray-100 text-gray-800 @endif">
                                                {{ $setting->type }}
                                            </span>
                                        </div>

                                        @if ($setting->type === 'boolean')
                                            <div
                                                class="flex items-center justify-between mt-3 p-3 bg-white rounded border border-gray-200">
                                                <span class="text-sm font-medium text-gray-700">
                                                    Status: <span
                                                        x-text="settings['{{ $setting->key }}'] === 'true' ? 'Aktif' : 'Nonaktif'"
                                                        :class="settings['{{ $setting->key }}'] === 'true' ?
                                                            'text-green-600' : 'text-gray-500'"></span>
                                                </span>
                                                <label class="relative inline-flex items-center cursor-pointer">
                                                    <input type="hidden" name="settings[{{ $setting->key }}]"
                                                        :value="settings['{{ $setting->key }}']">
                                                    <input type="checkbox"
                                                        :checked="settings['{{ $setting->key }}'] === 'true'"
                                                        @change="settings['{{ $setting->key }}'] = $event.target.checked ? 'true' : 'false'"
                                                        class="sr-only peer">
                                                    <div
                                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-pink-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-pink-600">
                                                    </div>
                                                </label>
                                            </div>
                                        @elseif($setting->type === 'integer')
                                            <div class="flex items-center gap-2 mt-3">
                                                <button type="button" @click="decrementSetting('{{ $setting->key }}')"
                                                    class="px-3 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                                                    <i class="bi bi-dash-lg"></i>
                                                </button>

                                                <input type="number" id="setting_{{ $setting->key }}"
                                                    name="settings[{{ $setting->key }}]"
                                                    x-model="settings['{{ $setting->key }}']" min="1" max="999"
                                                    required
                                                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500 text-center font-mono text-lg font-semibold">

                                                <button type="button" @click="incrementSetting('{{ $setting->key }}')"
                                                    class="px-3 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                                                    <i class="bi bi-plus-lg"></i>
                                                </button>
                                            </div>
                                        @else
                                            <input type="text" id="setting_{{ $setting->key }}"
                                                name="settings[{{ $setting->key }}]" value="{{ $setting->value }}"
                                                class="mt-3 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                                        @endif

                                        @if ($setting->updatedByUser)
                                            <div class="mt-3 pt-3 border-t border-gray-200">
                                                <p class="text-xs text-gray-500">
                                                    <i class="bi bi-clock-history mr-1"></i>
                                                    Diubah oleh <strong>{{ $setting->updatedByUser->name }}</strong>
                                                    pada {{ $setting->updated_at->format('d M Y H:i') }}
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            @if ($category === 'revision')
                                <div
                                    class="bg-gradient-to-r from-pink-50 to-purple-50 border-2 border-pink-200 rounded-lg p-6 mt-6">
                                    <h4 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                                        <i class="bi bi-eye-fill text-pink-500"></i>
                                        Preview Konfigurasi Auto-Reject
                                    </h4>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div class="bg-white rounded-lg p-4 shadow-sm">
                                            <div class="flex items-center gap-3">
                                                <div
                                                    class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                                    <i class="bi bi-calendar-check text-blue-600 text-xl"></i>
                                                </div>
                                                <div>
                                                    <p class="text-xs text-gray-600">Batas Waktu Revisi</p>
                                                    <p class="text-xl font-bold text-gray-800">
                                                        <span x-text="settings['auto_reject_days']"></span> Hari
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="bg-white rounded-lg p-4 shadow-sm">
                                            <div class="flex items-center gap-3">
                                                <div
                                                    class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                                    <i class="bi bi-arrow-repeat text-purple-600 text-xl"></i>
                                                </div>
                                                <div>
                                                    <p class="text-xs text-gray-600">Maksimal Revisi</p>
                                                    <p class="text-xl font-bold text-gray-800">
                                                        <span x-text="settings['max_revision_count']"></span>x
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="bg-white rounded-lg p-4 shadow-sm">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-full flex items-center justify-center"
                                                    :class="settings['enable_auto_reject'] === 'true' ? 'bg-green-100' :
                                                        'bg-gray-100'">
                                                    <i class="text-xl"
                                                        :class="settings['enable_auto_reject'] === 'true' ?
                                                            'bi bi-check-circle-fill text-green-600' :
                                                            'bi bi-x-circle-fill text-gray-600'"></i>
                                                </div>
                                                <div>
                                                    <p class="text-xs text-gray-600">Status Auto-Reject</p>
                                                    <p class="text-xl font-bold"
                                                        :class="settings['enable_auto_reject'] === 'true' ?
                                                            'text-green-600' : 'text-gray-600'"
                                                        x-text="settings['enable_auto_reject'] === 'true' ? 'Aktif' : 'Nonaktif'">
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4 p-4 bg-white rounded-lg border border-gray-200">
                                        <p class="text-sm text-gray-700">
                                            <strong>Contoh Kasus:</strong>
                                            Jika pengajuan diminta revisi hari ini, masyarakat memiliki waktu hingga
                                            <strong class="text-pink-600">
                                                <span x-text="settings['auto_reject_days']"></span> hari kerja
                                            </strong>
                                            untuk merevisi. Jika tidak, pengajuan akan
                                            <strong class="text-red-600">otomatis ditolak</strong>
                                            <span x-show="settings['enable_auto_reject'] === 'false'"
                                                class="text-gray-500">
                                                (saat ini fitur auto-reject nonaktif)
                                            </span>.
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach

                    <div class="mt-8 flex justify-between items-center pt-6 border-t border-gray-200">
                        <form action="{{ route('admin.settings.reset') }}" method="POST" id="form-reset-settings">
                            @csrf
                            <button type="submit"
                                onclick="return confirm('Yakin ingin reset semua pengaturan ke nilai default?')"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition flex items-center gap-2">
                                <i class="bi bi-arrow-clockwise"></i>
                                Reset ke Default
                            </button>
                        </form>

                        <div class="flex gap-3">
                            <a href="{{ route('dashboard') }}"
                                class="px-6 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                                Batal
                            </a>
                            <button type="submit"
                                class="px-6 py-2 bg-pink-600 text-white rounded-lg hover:bg-pink-700 transition flex items-center gap-2">
                                <i class="bi bi-save"></i>
                                Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function settingsForm() {
                return {
                    activeTab: 'revision',
                    settings: {
                        @foreach ($settings as $setting)
                            '{{ $setting->key }}': '{{ $setting->value }}',
                        @endforeach
                    },

                    incrementSetting(key) {
                        const current = parseInt(this.settings[key]);
                        if (current < 999) {
                            this.settings[key] = (current + 1).toString();
                        }
                    },

                    decrementSetting(key) {
                        const current = parseInt(this.settings[key]);
                        if (current > 1) {
                            this.settings[key] = (current - 1).toString();
                        }
                    }
                }
            }
        </script>
    @endpush
@endsection
