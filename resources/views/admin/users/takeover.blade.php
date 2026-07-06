@extends('dashboard.layouts.dashboard')

@section('title', 'Kelola Akses Kader')

@section('content')
    <div class="py-12" x-data="takeoverHandler()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success') && session('new_password'))
                <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm relative"
                    role="alert">
                    <p class="font-bold">Berhasil!</p>
                    <p>{{ session('success') }}</p>
                    <div class="mt-2 p-3 bg-white rounded border border-green-200">
                        <p class="text-sm text-gray-600">Password Baru untuk Kader:</p>
                        <p class="text-xl font-mono font-bold text-gray-800 select-all">{{ session('new_password') }}</p>
                        <p class="text-xs text-gray-500 mt-1">*Segera salin dan berikan kepada kader terkait.</p>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl p-4 sm:p-6 lg:p-8">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold text-gray-800">Daftar Kader Posyandu</h3>
                        <span class="text-sm text-gray-500">Total: {{ $kaders->count() }} Kader</span>
                    </div>

                    <div class="w-full overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wide">
                                        Nama / Username</th>
                                    <th
                                        class="px-4 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wide">
                                        Bidang</th>
                                    <th
                                        class="px-4 py-3 text-center text-sm font-medium text-gray-500 uppercase tracking-wide">
                                        Status</th>
                                    <th
                                        class="px-4 py-3 text-center text-sm font-medium text-gray-500 uppercase tracking-wide">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($kaders as $kader)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $kader->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $kader->username ?? '-' }}</div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                {{ $kader->bidang->nama_bidang ?? 'Tidak Ada Bidang' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-center">
                                            @if ($kader->is_active)
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Aktif
                                                </span>
                                            @else
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    Non-Aktif
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-center">
                                            <button
                                                @click="openModal('{{ $kader->id }}', '{{ $kader->name }}', {{ $kader->is_active ? 'true' : 'false' }})"
                                                class="text-indigo-600 hover:text-indigo-900 font-medium text-sm border border-indigo-200 px-3 py-1 rounded hover:bg-indigo-50 transition">
                                                {{ $kader->is_active ? 'Reset Password' : 'Aktifkan & Reset' }}
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-3 text-center text-gray-500 text-sm">
                                            Belum ada data kader di posyandu ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
            </div>
        </div>
        <div x-show="isOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal()"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                    <form method="POST" :action="actionUrl">
                        @csrf

                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div
                                    class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                    <h3 class="text-base font-semibold leading-6 text-gray-900" id="modal-title">
                                        <span
                                            x-text="isActive ? 'Reset Password Kader' : 'Aktifkan & Reset Password'"></span>
                                    </h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500 mb-4">
                                            Anda akan mereset password untuk kader: <strong x-text="targetName"></strong>.
                                        </p>

                                        <div class="mb-4">
                                            <label for="new_password"
                                                class="block text-sm font-medium text-gray-700">Password Baru</label>
                                            <div class="relative mt-1">
                                                <input type="password" name="new_password" id="new_password" required minlength="8"
                                                    oninput="validateTakeoverPassword()"
                                                    class="block w-full pr-10 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                <button type="button" onclick="toggleTakeoverPassword('new_password', this)"
                                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 focus:outline-none"
                                                    tabindex="-1">
                                                    <i class="bi bi-eye text-lg"></i>
                                                </button>
                                            </div>
                                            <span id="takeover_password_hint" class="mt-1 text-xs text-gray-500"><i class="bi bi-info-circle mr-1"></i>Minimal 8 karakter.</span>
                                            <span id="takeover_password_error" class="mt-1 text-xs text-red-600 hidden"><i class="bi bi-exclamation-circle mr-1"></i>Password minimal 8 karakter.</span>
                                        </div>

                                        <div class="mb-4">
                                            <label for="new_password_confirmation"
                                                class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                                            <div class="relative mt-1">
                                                <input type="password" name="new_password_confirmation"
                                                    id="new_password_confirmation" required
                                                    oninput="validateTakeoverPassword()"
                                                    class="block w-full pr-10 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                                <button type="button" onclick="toggleTakeoverPassword('new_password_confirmation', this)"
                                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 focus:outline-none"
                                                    tabindex="-1">
                                                    <i class="bi bi-eye text-lg"></i>
                                                </button>
                                            </div>
                                            <span id="takeover_confirm_hint" class="mt-1 text-xs text-gray-500"><i class="bi bi-info-circle mr-1"></i>Masukkan ulang password yang sama.</span>
                                            <span id="takeover_confirm_error" class="mt-1 text-xs text-red-600 hidden"><i class="bi bi-exclamation-circle mr-1"></i>Password tidak cocok.</span>
                                        </div>

                                        <div class="mb-2">
                                            <label for="reason" class="block text-sm font-medium text-gray-700">Alasan
                                                Reset (Wajib)</label>
                                            <textarea name="reason" id="reason" rows="2" required placeholder="Contoh: Kader lupa password..."
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                            <p class="mt-1 text-xs text-gray-500"><i class="bi bi-info-circle mr-1"></i>Wajib diisi, maks. 500 karakter.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-2">
                            <button type="submit"
                                class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto">
                                Proses Reset
                            </button>
                            <button type="button" @click="closeModal()"
                                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('takeoverHandler', () => ({
                    isOpen: false,
                    targetName: '',
                    actionUrl: '',
                    isActive: true,

                    openModal(id, name, isActiveStatus) {
                        this.targetName = name;
                        this.isActive = isActiveStatus;
                        this.actionUrl = `{{ url('/ketua-posyandu/takeover') }}/${id}/reset`;
                        this.isOpen = true;
                    },

                    closeModal() {
                        this.isOpen = false;
                        this.targetName = '';
                        this.actionUrl = '';
                    }
                }))
            })
        </script>
        <script>
            function toggleTakeoverPassword(id, btn) {
                const input = document.getElementById(id);
                const icon = btn.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.className = 'bi bi-eye-slash text-lg';
                } else {
                    input.type = 'password';
                    icon.className = 'bi bi-eye text-lg';
                }
            }

            function validateTakeoverPassword() {
                const pw = document.getElementById('new_password');
                const confirm = document.getElementById('new_password_confirmation');

                const pwHint = document.getElementById('takeover_password_hint');
                const pwErr = document.getElementById('takeover_password_error');
                const cfHint = document.getElementById('takeover_confirm_hint');
                const cfErr = document.getElementById('takeover_confirm_error');

                if (pw.value.length > 0 && pw.value.length < 8) {
                    pwHint.classList.add('hidden');
                    pwErr.classList.remove('hidden');
                } else {
                    pwHint.classList.remove('hidden');
                    pwErr.classList.add('hidden');
                }

                if (confirm.value.length > 0 && confirm.value !== pw.value) {
                    cfHint.classList.add('hidden');
                    cfErr.classList.remove('hidden');
                } else {
                    cfHint.classList.remove('hidden');
                    cfErr.classList.add('hidden');
                }
            }
        </script>
    @endpush
@endsection
