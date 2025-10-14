<header class="flex items-center justify-between p-4 bg-white border-b">
    <button @click="sidebarOpen = true" class="md:hidden text-gray-500 focus:outline-none">
        <i class="bi bi-list text-2xl"></i>
    </button>
    <div class="flex items-center ml-auto">
        @guest
            <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900">Login</a>
        @else
            <div x-data="{ dropdownOpen: false }" class="relative">
                <button @click="dropdownOpen = !dropdownOpen"
                    class="flex items-center space-x-2 relative focus:outline-none">
                    <span class="hidden md:inline">{{ Auth::user()->name }}</span>
                    <i class="bi bi-person-circle text-2xl"></i>
                </button>
                <div x-show="dropdownOpen" @click.away="dropdownOpen = false"
                    class="absolute right-0 mt-2 w-48 bg-white rounded-md overflow-hidden shadow-xl z-10" x-cloak>
                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profil</a>
                    <a href="{{ route('logout') }}"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        Logout
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                </div>
            </div>
        @endguest
    </div>
</header>
