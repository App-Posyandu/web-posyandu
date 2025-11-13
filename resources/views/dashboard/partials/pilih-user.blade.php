<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Buat Ajuan Atas Nama
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-xl font-semibold mb-4">Pilih Masyarakat</h3>
                    <p class="text-sm text-gray-600 mb-6">Pilih masyarakat yang akan Anda bantu buatkan pengajuan. Hanya
                        masyarakat yang sudah terverifikasi yang akan muncul di daftar ini.</p>

                    <div class="space-y-3 max-h-96 overflow-y-auto border p-4 rounded-md">
                        @forelse($masyarakatUsers as $user)
                            {{-- Link ini akan memulai alur, menyimpan ID user, dan redirect --}}
                            <a href="{{ route('dashboard.partials.pilih-user', ['user_id' => $user->id]) }}"
                                class="block p-4 bg-gray-50 hover:bg-pink-100 rounded-md transition duration-150">
                                <div class="font-semibold text-pink-600">{{ $user->name }}</div>
                                <div class="text-sm text-gray-500">NIK: {{ $user->nik }}</div>
                            </a>
                        @empty
                            <p class="text-gray-500">Tidak ada masyarakat terverifikasi yang ditemukan.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
