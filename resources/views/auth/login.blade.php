<x-guest-layout>
    <div x-data="{ activeTab: 'login' }">
        <div class="flex border-b border-gray-200 mb-6">
            <button @click="activeTab = 'login'"
                :class="activeTab === 'login' ? 'border-pink-500 text-pink-600' :
                    'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                class="flex-1 py-4 px-1 text-center border-b-2 font-medium text-sm transition-colors">
                <i class="bi bi-box-arrow-in-right mr-2"></i>
                Login
            </button>
            <button @click="activeTab = 'track'"
                :class="activeTab === 'track' ? 'border-pink-500 text-pink-600' :
                    'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                class="flex-1 py-4 px-1 text-center border-b-2 font-medium text-sm transition-colors">
                <i class="bi bi-search mr-2"></i>
                Lacak Pengajuan
            </button>
        </div>
        <div x-show="activeTab === 'login'" x-transition>

            <x-auth-session-status class="mb-4" :status="session('status')" />

            {{-- ✅ MODAL BUKU PANDUAN --}}
            <div id="guideModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title"
                role="dialog" aria-modal="true">
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    {{-- Background overlay --}}
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

                    {{-- Modal panel --}}
                    <div
                        class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div
                                    class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                    {{-- Icon Book --}}
                                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                                        </path>
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                        Buku Panduan Pengguna
                                    </h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500 mb-4">
                                            Selamat datang di <strong>SAPA POSYANDU</strong>! Sebelum memulai, silakan
                                            lihat
                                            buku panduan untuk membantu Anda menggunakan sistem ini.
                                        </p>

                                        {{-- Preview PDF --}}
                                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                            <div class="flex items-center justify-between mb-3">
                                                <div class="flex items-center space-x-2">
                                                    <svg class="w-8 h-8 text-red-500" fill="currentColor"
                                                        viewBox="0 0 20 20">
                                                        <path
                                                            d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z">
                                                        </path>
                                                    </svg>
                                                    <div>
                                                        <p class="text-sm font-semibold text-gray-900">
                                                            Panduan_SAPA_POSYANDU.pdf
                                                        </p>
                                                        <p class="text-xs text-gray-500">Buku panduan lengkap sistem</p>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Embedded PDF Preview --}}
                                            <div class="relative w-full" style="height: 400px;">
                                                <iframe
                                                    src="https://drive.google.com/file/d/18TIpDP-BjaseFDf0q32GpEMyzM7E_wJi/preview"
                                                    class="w-full h-full rounded border border-gray-300"
                                                    allow="autoplay">
                                                </iframe>
                                            </div>
                                        </div>

                                        {{-- Download Button --}}
                                        <div class="mt-4 flex items-center justify-center space-x-3">
                                            <a href="https://drive.google.com/uc?export=download&id=18TIpDP-BjaseFDf0q32GpEMyzM7E_wJi"
                                                target="_blank"
                                                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                    </path>
                                                </svg>
                                                Download Panduan
                                            </a>
                                            <a href="https://drive.google.com/file/d/18TIpDP-BjaseFDf0q32GpEMyzM7E_wJi/view"
                                                target="_blank"
                                                class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-md transition">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14">
                                                    </path>
                                                </svg>
                                                Buka di Tab Baru
                                            </a>
                                        </div>

                                        {{-- Checkbox: Jangan tampilkan lagi --}}
                                        <div class="mt-4 flex items-center">
                                            <input id="dontShowAgain" type="checkbox"
                                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            <label for="dontShowAgain" class="ml-2 block text-sm text-gray-700">
                                                Jangan tampilkan lagi
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="button" onclick="closeGuideModal()"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-pink-600 text-base font-medium text-white hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Mengerti, Lanjutkan
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-center space-x-4 mb-6">
                <img src={{ asset('assets/image/logo/logo_kebumen.png') }} alt="Logo Kebumen" class="h-16">
                <img src={{ asset('assets/image/logo/logo_posyandu.png') }} alt="Logo Posyandu" class="h-16">
                <img src={{ asset('assets/image/logo/logo_sapaposyandu.png') }} alt="Logo Posyandu" class="h-16">
                <img src={{ asset('assets/image/logo/logo_telkom_university.png') }} alt="Logo Posyandu" class="h-16">
            </div>

            <div class="text-center mb-6">
                <h1 class="text-3xl font-bold text-gray-800">Selamat Datang di SAPA POSYANDU</h1>
                <p class="text-pink-500 text-xl font-semibold">Pelayanan Elektronik Posyandu</p>
            </div>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div>
                    <x-input-label for="login" :value="__('Email / No. Telepon')" />
                    <x-text-input id="login" class="block mt-1 w-full" type="text" name="login"
                        :value="old('login')" required autofocus autocomplete="username"
                        placeholder="Masukkan email atau nomor telepon" />
                    <x-input-error :messages="$errors->get('login')" class="mt-2" />
                </div>

                <div class="mt-4">
                    <x-input-label for="password" :value="__('Password')" />
                    <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required
                        autocomplete="current-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="mt-4 flex justify-between items-center">
                    <label for="remember_me" class="inline-flex items-center">
                        <input id="remember_me" type="checkbox"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            name="remember">
                        <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                    </label>
                    <div>
                        @if (\Illuminate\Support\Facades\Route::has('password.request'))
                            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                href="{{ route('password.request') }}">
                                {{ __('Forgot your password?') }}
                            </a>
                        @endif
                    </div>
                </div>

                <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-md">
                    <p class="text-xs text-blue-800">
                        💡 <strong>Tips:</strong> Anda bisa login menggunakan:
                    </p>
                    <ul class="text-xs text-blue-700 mt-1 ml-4 list-disc">
                        <li>Email: contoh@email.com</li>
                        <li>No. Telepon: 08123456789</li>
                    </ul>
                </div>

                <div class="mt-6">
                    <button type="submit"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm md:text-lg font-medium text-white bg-pink-500 hover:bg-pink-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500">
                        {{ __('Login') }}
                    </button>
                </div>
            </form>

            <div class="flex items-center my-4">
                <hr class="flex-grow border-gray-300">
                <span class="mx-4 text-gray-500 text-base">Atau</span>
                <hr class="flex-grow border-gray-300">
            </div>

            <a href="{{ route('google.login') }}"
                class="w-full flex gap-2 items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm md:text-lg font-medium text-gray-700 bg-white hover:bg-gray-50">
                <img width="20" src="{{ asset('assets/image/icon/Google.png') }}" alt="Google Icon">
                Login with Google
            </a>

            <div class="text-center mt-6">
                <p class="text-sm md:text-base text-gray-600">
                    Belum punya akun?
                    <a class="font-semibold text-pink-500 hover:text-pink-700" href="{{ route('register') }}">
                        Buat akun baru
                    </a>
                </p>
            </div>

            <div class="my-4 text-center">
                <button type="button" onclick="openGuideModal()"
                    class="inline-flex items-center px-4 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 text-sm font-medium rounded-lg transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                        </path>
                    </svg>
                    Lihat Buku Panduan
                </button>
            </div>
        </div>
        <div x-show="activeTab === 'track'" x-transition>
            <div class="mb-6 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-pink-100 rounded-full mb-4">
                    <i class="bi bi-search text-3xl text-pink-600"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Lacak Pengajuan Anda</h3>
                <p class="text-sm text-gray-600">
                    Masukkan kode tracking yang tertera di bukti pengajuan Anda
                </p>
            </div>

            {{-- ✅ FORM TRACKING (Manual Input Only) --}}
            <form method="GET" action="{{ route('ajuan.track.show') }}" class="space-y-4">
                <div>
                    <label for="tracking_code" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="bi bi-upc-scan mr-1"></i>
                        Kode Tracking
                    </label>
                    <input type="text" id="tracking_code" name="code" required
                        placeholder="Contoh: PGJ-202501-AB123"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500 text-center font-mono text-lg uppercase"
                        maxlength="20" pattern="[A-Z0-9-]+" oninput="this.value = this.value.toUpperCase()">
                    <p class="mt-2 text-xs text-gray-500">
                        <i class="bi bi-info-circle mr-1"></i>
                        Kode tracking terdapat di bukti pengajuan yang Anda terima
                    </p>
                </div>

                {{-- Error Message if tracking code not found (from session) --}}
                @if (session('tracking_error'))
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                        <div class="flex items-start">
                            <i class="bi bi-exclamation-triangle-fill text-red-500 mr-3 mt-0.5"></i>
                            <div class="text-sm text-red-700">
                                <p class="font-semibold">Kode Tidak Ditemukan</p>
                                <p class="mt-1">{{ session('tracking_error') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <button type="submit"
                    class="w-full bg-pink-600 text-white py-3 rounded-lg hover:bg-pink-700 transition-colors font-semibold">
                    <i class="bi bi-search mr-2"></i>
                    Lacak Pengajuan
                </button>
            </form>

            {{-- ✅ INFO CARD --}}
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start">
                    <i class="bi bi-lightbulb-fill text-blue-500 mr-3 mt-0.5 text-xl"></i>
                    <div class="text-sm text-blue-700">
                        <p class="font-semibold mb-2">Cara Menggunakan:</p>
                        <ol class="list-decimal list-inside space-y-1 ml-2">
                            <li>Ambil bukti pengajuan yang Anda terima dari petugas</li>
                            <li>Lihat kode tracking di bagian atas bukti</li>
                            <li>Masukkan kode tersebut pada kolom di atas</li>
                            <li>Klik "Lacak Pengajuan" untuk melihat status</li>
                        </ol>
                    </div>
                </div>
            </div>

            {{-- ✅ CONTACT INFO --}}
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600 mb-2">Belum memiliki kode tracking?</p>
                <p class="text-sm text-gray-800">
                    <i class="bi bi-info-circle-fill text-pink-500 mr-1"></i>
                    Kode tracking akan Anda dapatkan setelah mengajukan permohonan
                </p>
                <p class="text-xs text-gray-500 mt-2">
                    Hubungi posyandu terdekat atau
                    <a href="tel:+62123456789" class="text-pink-600 hover:underline font-medium">
                        +62 123 456 789
                    </a>
                </p>
            </div>
        </div>
    </div>

    {{-- ✅ JAVASCRIPT --}}
    <script>
        function openScanner() {
            // Placeholder untuk QR Scanner
            // Nanti bisa diintegrasikan dengan library seperti html5-qrcode
            alert(
                'Fitur scan QR Code akan segera tersedia!\n\nSaat ini, silakan masukkan kode pengajuan secara manual.'
            );

            // TODO: Implementasi QR Scanner
            // Example:
            // const html5QrCode = new Html5Qrcode("qr-reader");
            // html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess);
        }

        // Auto-focus on tracking code if track tab is active from session
        if (session('track_error')) {
            document.addEventListener('DOMContentLoaded', function() {
                Alpine.store('activeTab', 'track');
            });
        }

        // Check if user has seen guide before (using localStorage for guest)
        const hasSeenGuide = localStorage.getItem('hasSeenGuide');

        // Show modal on first visit
        if (!hasSeenGuide) {
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(() => {
                    document.getElementById('guideModal').classList.remove('hidden');
                }, 500); // Delay 500ms agar smooth
            });
        }

        function openGuideModal() {
            document.getElementById('guideModal').classList.remove('hidden');
        }

        function closeGuideModal() {
            const dontShowAgain = document.getElementById('dontShowAgain').checked;

            if (dontShowAgain) {
                localStorage.setItem('hasSeenGuide', 'true');
            }

            document.getElementById('guideModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('guideModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeGuideModal();
            }
        });

        // Close with ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeGuideModal();
            }
        });
    </script>
</x-guest-layout>
