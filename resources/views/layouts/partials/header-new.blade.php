<header class="w-full max-w-7xl mx-auto py-4 px-6 flex justify-between items-center">
    <div>
        <a href="/">
            <div class="flex flex-row items-center gap-4">
                <img src={{ asset('assets/image/logo/logo_sapaposyandu.png') }} alt="Logo Sapaposyandu" class="h-12">
                <div class="flex flex-col">
                    <h1 class="text-2xl font-bold text-pink-500 uppercase">Sapa Posyandu</h1>
                    <p class="text-sm text-gray-600">Sistem Aplikasi Pengelolaan Pos <br> Pelayanan Terpadu</p>
                </div>
            </div>
        </a>
    </div>
    <div class="sm:flex sm:items-center sm:ms-6 gap-5">
        @include('dashboard.partials.notification-dropdown')
        <x-dropdown align="right" width="48">
            <x-slot name="trigger">
                <button
                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                    <div>{{ auth()->user()->name }}</div>
                    <div class="ms-1"><i class="bi bi-chevron-down"></i></div>
                </button>
            </x-slot>
            <x-slot name="content">
                <x-dropdown-link :href="route('dashboard')">{{ __('Beranda') }}</x-dropdown-link>
                @if (auth()->user()->role === 'admin' ||
                        auth()->user()->role === 'kader' ||
                        auth()->user()->role === 'kabid' ||
                        auth()->user()->role === 'ketua-kader')
                    <x-dropdown-link :href="route('admin.users.index')">{{ __('Users') }}</x-dropdown-link>
                @endif
                @if (auth()->user()->role === 'kabid')
                    <x-dropdown-link :href="route('admin.posyandu.index')">{{ __('Posyandu') }}</x-dropdown-link>
                @endif
                <x-dropdown-link :href="route('buku_saku.index')">{{ __('Dokumen') }}</x-dropdown-link>
                <x-dropdown-link :href="route('ajuan.index')">{{ __('Lihat Pengajuan') }}</x-dropdown-link>
                <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-dropdown-link>
                </form>
            </x-slot>
        </x-dropdown>
    </div>
</header>
