<aside class="hidden md:flex w-64 flex-col bg-gray-800 text-white">
    <div class="h-16 flex items-center justify-center text-xl font-bold border-b border-gray-700">
        <i class="bi bi-heart-pulse-fill mr-2"></i> Posyandu Ceria
    </div>
    <nav class="flex-1 px-4 py-4 space-y-2">
        <a href="#" class="flex items-center px-4 py-2 text-gray-100 hover:bg-gray-700 rounded-md"><i
                class="bi bi-speedometer2 mr-3"></i> Dashboard</a>

        @if (Auth::check() && in_array(Auth::user()->role, ['kader', 'kabid']))
            <a href="#" class="flex items-center px-4 py-2 text-gray-100 hover:bg-gray-700 rounded-md"><i
                    class="bi bi-clipboard2-plus-fill mr-3"></i> Input Penimbangan</a>
        @endif

        @if (Auth::check() && Auth::user()->role == 'kabid')
            <a href="#" class="flex items-center px-4 py-2 text-gray-100 hover:bg-gray-700 rounded-md"><i
                    class="bi bi-file-earmark-bar-graph-fill mr-3"></i> Laporan</a>
            <a href="#" class="flex items-center px-4 py-2 text-gray-100 hover:bg-gray-700 rounded-md"><i
                    class="bi bi-people-fill mr-3"></i> Manajemen User</a>
        @endif
    </nav>
</aside>

<div x-show="sidebarOpen" @click.away="sidebarOpen = false" class="fixed inset-0 bg-black bg-opacity-50 z-20 md:hidden"
    x-cloak>
    <aside class="w-64 h-full bg-gray-800 text-white">
        <div class="h-16 flex items-center justify-center text-xl font-bold border-b border-gray-700">
            Menu
        </div>
        <nav class="flex-1 px-4 py-4 space-y-2">
            <a href="#" class="flex items-center px-4 py-2 text-gray-100 hover:bg-gray-700 rounded-md"><i
                    class="bi bi-speedometer2 mr-3"></i> Dashboard</a>
        </nav>
    </aside>
</div>
