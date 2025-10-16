<aside class="flex flex-col bg-gray-800 text-gray-300 transition-all duration-300 ease-in-out"
    :class="sidebarOpen ? 'w-64' : 'w-20'">
    <div class="flex items-center justify-center h-16 bg-gray-900">
        <span class="text-white font-bold" :class="!sidebarOpen && 'hidden'">E-POSYANDU</span>
        <i class="bi bi-heart-pulse-fill text-2xl text-white" :class="sidebarOpen && 'hidden'"></i>
    </div>
    <nav class="flex-1 px-4 py-4 space-y-2">
        <a href="/dashboard" class="flex items-center px-2 py-2 text-gray-100 bg-gray-700 rounded-md">
            <i class="bi bi-speedometer2 text-lg w-12 text-center"></i>
            <span :class="!sidebarOpen && 'hidden'">Dashboard</span>
        </a>
        <a href="#" class="flex items-center px-2 py-2 text-gray-300 hover:bg-gray-700 rounded-md">
            <i class="bi bi-clipboard2-plus-fill text-lg w-12 text-center"></i>
            <span :class="!sidebarOpen && 'hidden'">Pengajuan</span>
        </a>
        <a href="#" class="flex items-center px-2 py-2 text-gray-300 hover:bg-gray-700 rounded-md">
            <i class="bi bi-file-earmark-bar-graph-fill text-lg w-12 text-center"></i>
            <span :class="!sidebarOpen && 'hidden'">Laporan</span>
        </a>
        <a href="#" class="flex items-center px-2 py-2 text-gray-300 hover:bg-gray-700 rounded-md">
            <i class="bi bi-people-fill text-lg w-12 text-center"></i>
            <span :class="!sidebarOpen && 'hidden'">Data Warga</span>
        </a>
    </nav>
</aside>
