<header class="w-full top-0 z-50 bg-white md:bg-transparent" x-data="{ mobileMenuOpen: false }">
    <div class="max-w-screen-2xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
            <div class="flex-shrink-0">
                <a href="/" class="flex items-center gap-2 sm:gap-4">
                    <img src="{{ asset('assets/image/logo/logo_sapaposyandu.png') }}" alt="Logo Sapaposyandu"
                        class="h-10 sm:h-12 w-auto">
                    <div class="hidden sm:flex flex-col">
                        <h1 class="text-xl sm:text-2xl font-bold text-pink-500 uppercase">Sapa Posyandu</h1>
                        <p class="text-xs sm:text-sm text-gray-600 leading-tight">
                            Sistem Aplikasi Pengelolaan Pos <br class="hidden sm:block"> Pelayanan Terpadu
                        </p>
                    </div>
                    <div class="flex sm:hidden flex-col">
                        <h1 class="text-base font-bold text-pink-500 uppercase">Sapa Posyandu</h1>
                    </div>
                </a>
            </div>
            <div class="hidden md:flex items-center gap-3 lg:gap-5">
                @include('dashboard.partials.notification-dropdown')

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div class="max-w-[150px] truncate">{{ auth()->user()->name }}</div>
                            <div class="ms-1"><i class="bi bi-chevron-down"></i></div>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link :href="route('dashboard')">
                            <i class="bi bi-house-door mr-2"></i>
                            {{ __('Beranda') }}
                        </x-dropdown-link>

                        @if (in_array(auth()->user()->role, [
                                'admin',
                                'kader',
                                'admin-kabupaten',
                                'ketua-kader',
                                'operator-desa',
                            ]))
                            <x-dropdown-link :href="route('admin.users.index')">
                                <i class="bi bi-people mr-2"></i>
                                {{ __('Users') }}
                            </x-dropdown-link>
                        @endif

                        {{-- Menu untuk Ketua Kader --}}
                        @if (auth()->user()->role === 'ketua-kader')
                            <x-dropdown-link :href="route('ketua-kader.takeover')"
                                class="{{ request()->routeIs('ketua-kader.takeover*') ? 'active' : '' }}">
                                <i class="bi bi-key-fill mr-2"></i>
                                {{ __('Ambil Alih Kader') }}
                            </x-dropdown-link>
                        @endif

                        @if (in_array(auth()->user()->role, ['admin', 'kabid', 'ketua-posyandu', 'admin-kabupaten', 'operator-desa']))
                            <x-dropdown-link :href="route('admin.posyandu.index')">
                                <i class="bi bi-building mr-2"></i>
                                {{ __('Posyandu') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('admin.kecamatan.index')">
                                <i class="bi bi-geo-alt mr-2"></i>
                                {{ __('Kecamatan') }}
                            </x-dropdown-link>
                        @endif

                        <x-dropdown-link :href="route('buku_saku.index')">
                            <i class="bi bi-file-earmark-text mr-2"></i>
                            {{ __('Dokumen') }}
                        </x-dropdown-link>

                        <x-dropdown-link :href="route('ajuan.index')">
                            <i class="bi bi-clipboard-check mr-2"></i>
                            {{ __('Lihat Pengajuan') }}
                        </x-dropdown-link>

                        {{-- ✅ MENU SETTINGS (Only for admin-kabupaten) --}}
                        @if (in_array(auth()->user()->role, ['admin', 'admin-kabupaten', 'admin-kecamatan', 'operator-desa']))
                            <div class="border-t border-gray-100 my-1"></div>
                            <x-dropdown-link :href="route('admin.settings.index')"
                                class="{{ request()->routeIs('admin.settings.*') ? 'bg-pink-50 text-pink-600' : '' }}">
                                <i class="bi bi-gear-fill mr-2"></i>
                                {{ __('Pengaturan Sistem') }}
                            </x-dropdown-link>
                        @endif

                        <div class="border-t border-gray-100 my-1"></div>

                        <x-dropdown-link :href="route('profile.edit')">
                            <i class="bi bi-person-circle mr-2"></i>
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();"
                                class="text-red-600 hover:text-red-800 hover:bg-red-50">
                                <i class="bi bi-box-arrow-right mr-2"></i>
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
            <div class="flex md:hidden items-center gap-3">
                @include('dashboard.partials.notification-dropdown')

                <button @click="mobileMenuOpen = !mobileMenuOpen" type="button"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-700 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': mobileMenuOpen, 'inline-flex': !mobileMenuOpen }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !mobileMenuOpen, 'inline-flex': mobileMenuOpen }" class="hidden"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
        <div x-show="mobileMenuOpen" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 transform -translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform -translate-y-2"
            class="md:hidden mt-4 pb-3 border-t border-gray-200" style="display: none;">

            <div class="pt-4 pb-3 border-b border-gray-200">
                <div class="flex items-center px-4">
                    <div class="flex-shrink-0">
                        <div
                            class="h-10 w-10 rounded-full bg-pink-500 flex items-center justify-center text-white font-semibold">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    </div>
                    <div class="ml-3">
                        <div class="text-base font-medium text-gray-800">{{ auth()->user()->name }}</div>
                        <div class="text-sm font-medium text-gray-500">{{ auth()->user()->email }}</div>
                    </div>
                </div>
            </div>

            <div class="pt-2 pb-3 space-y-1">
                <a href="{{ route('dashboard') }}"
                    class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-pink-500 transition duration-150 ease-in-out">
                    <i class="bi bi-house-door mr-2"></i> Beranda
                </a>

                @if (in_array(auth()->user()->role, [
                        'admin',
                        'kader',
                        'admin-kabupaten',
                        'ketua-kader',
                        'admin-kecamatan',
                        'operator-desa',
                    ]))
                    <a href="{{ route('admin.users.index') }}"
                        class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-pink-500 transition duration-150 ease-in-out">
                        <i class="bi bi-people mr-2"></i> Users
                    </a>
                @endif

                @if (auth()->user()->role === 'ketua-kader')
                    <a href="{{ route('ketua-kader.takeover') }}"
                        class="{{ request()->routeIs('ketua-kader.takeover*') ? 'border-pink-500 bg-pink-50' : 'border-transparent' }} block pl-3 pr-4 py-2 border-l-4 text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-pink-500 transition duration-150 ease-in-out">
                        <i class="bi bi-key-fill mr-2"></i> Ambil Alih Kader
                    </a>
                @endif

                @if (in_array(auth()->user()->role, ['admin', 'kabid', 'ketua-posyandu', 'admin-kabupaten', 'operator-desa']))
                    <a href="{{ route('admin.posyandu.index') }}"
                        class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-pink-500 transition duration-150 ease-in-out">
                        <i class="bi bi-building mr-2"></i> Posyandu
                    </a>
                    <a href="{{ route('admin.kecamatan.index') }}"
                        class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-pink-500 transition duration-150 ease-in-out">
                        <i class="bi bi-geo-alt mr-2"></i> Kecamatan
                    </a>
                @endif

                <a href="{{ route('buku_saku.index') }}"
                    class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-pink-500 transition duration-150 ease-in-out">
                    <i class="bi bi-file-earmark-text mr-2"></i> Dokumen
                </a>

                <a href="{{ route('ajuan.index') }}"
                    class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-pink-500 transition duration-150 ease-in-out">
                    <i class="bi bi-clipboard-check mr-2"></i> Lihat Pengajuan
                </a>

                {{-- ✅ MENU SETTINGS MOBILE (Only for admin-kabupaten) --}}
                @if (in_array(auth()->user()->role, ['admin', 'admin-kabupaten', 'admin-kecamatan', 'operator-desa']))
                    <div class="border-t border-gray-200 my-2"></div>
                    <a href="{{ route('admin.settings.index') }}"
                        class="{{ request()->routeIs('admin.settings.*') ? 'border-pink-500 bg-pink-50 text-pink-600' : 'border-transparent text-gray-600' }} block pl-3 pr-4 py-2 border-l-4 text-base font-medium hover:text-gray-800 hover:bg-gray-50 hover:border-pink-500 transition duration-150 ease-in-out">
                        <i class="bi bi-gear-fill mr-2"></i> Pengaturan Sistem
                    </a>
                    <div class="border-t border-gray-200 my-2"></div>
                @endif

                <a href="{{ route('profile.edit') }}"
                    class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-pink-500 transition duration-150 ease-in-out">
                    <i class="bi bi-person-circle mr-2"></i> Profile
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full text-left pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-red-600 hover:text-red-800 hover:bg-red-50 hover:border-red-500 transition duration-150 ease-in-out">
                        <i class="bi bi-box-arrow-right mr-2"></i> Log Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
