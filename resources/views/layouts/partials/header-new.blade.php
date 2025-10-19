<header class="w-full max-w-7xl mx-auto py-4 px-6 flex justify-between items-center">
    <div>
        <a href="/">
            <h1 class="text-2xl font-bold text-pink-600">EPOSY</h1>
            <p class="text-sm text-gray-600">Pelayanan Elektronik Posyandu</p>
        </a>
    </div>
    <div class="flex items-center space-x-4">
        <img src={{ asset('assets/image/logo/logo_kebumen.png') }} alt="Logo Kebumen" class="h-12">
        <img src={{ asset('assets/image/logo/logo_posyandu.png') }} alt="Logo Posyandu" class="h-12">
    </div>
    <div class="hidden sm:flex sm:items-center sm:ms-6">
        <x-dropdown align="right" width="48">
            <x-slot name="trigger">
                <button
                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                    <div>{{ Auth::user()->name }}</div>
                    <div class="ms-1"><i class="bi bi-chevron-down"></i></div>
                </button>
            </x-slot>
            <x-slot name="content">
                <x-dropdown-link :href="route('dashboard')">{{ __('Beranda') }}</x-dropdown-link>
                @if (Auth::user()->role === 'admin' ||
                        Auth::user()->role === 'kader' ||
                        Auth::user()->role === 'kabid' ||
                        Auth::user()->role === 'ketua-kader')
                    <x-dropdown-link :href="route('admin.users.index')">{{ __('Masyarakat') }}</x-dropdown-link>
                @endif
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
