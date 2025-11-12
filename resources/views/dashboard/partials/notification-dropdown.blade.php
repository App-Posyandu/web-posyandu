{{-- Ini adalah komponen dropdown untuk lonceng notifikasi --}}
<div x-data="{ open: false }" class="relative">

    {{-- Tombol Lonceng --}}
    <button @click="open = !open" class="relative text-gray-500 hover:text-gray-700 focus:outline-none">
        <i class="bi bi-bell-fill text-xl"></i>
        {{-- Tampilkan titik merah jika ada notifikasi baru --}}
        @if (auth()->user()->unreadNotifications->count() > 0)
            <span class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-pink-500 ring-2 ring-white"></span>
        @endif
    </button>

    {{-- Panel Dropdown --}}
    <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl z-50 overflow-hidden" style="display: none;">

        <div class="p-4 flex justify-between items-center border-b">
            <h3 class="font-semibold text-gray-700">Notifikasi</h3>
            @if (auth()->user()->unreadNotifications->count() > 0)
                <form action="{{ route('notifications.markAsRead') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-xs text-blue-500 hover:underline">Tandai semua dibaca</button>
                </form>
            @endif
        </div>

        <div class="max-h-96 overflow-y-auto">
            @forelse (auth()->user()->unreadNotifications as $notification)
                <a href="{{ $notification->data['url'] ?? '#' }}" class="block px-4 py-3 hover:bg-gray-50 border-b">
                    <p class="font-semibold text-sm text-gray-800">{{ $notification->data['title'] }}</p>
                    <p class="text-sm text-gray-600">{{ $notification->data['message'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                </a>
            @empty
                <p class="text-gray-500 text-sm text-center py-4">Tidak ada notifikasi baru.</p>
            @endforelse

            {{-- Link untuk melihat semua notifikasi (jika Anda membuat halaman 'semua notifikasi') --}}
            @if (auth()->user()->notifications->count() > 5)
                <a href="#" class="block text-center py-2 text-sm text-blue-500 hover:bg-gray-100">
                    Lihat Semua Notifikasi
                </a>
            @endif
        </div>
    </div>
</div>
