@extends('dashboard.layouts.dashboard')
@section('title', 'Buat Ajuan Atas Nama')
@section('content')

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Buat Ajuan Atas Nama
        </h2>
    </x-slot>

    <div class="py-12 w-full">
        <div class="mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">

                <div class="p-6 text-gray-900" x-data="pilihUserState">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-xl font-semibold">Pilih Masyarakat</h3>
                            <p class="text-sm text-gray-600 mt-1">
                                Pilih masyarakat yang akan Anda bantu buatkan pengajuan
                            </p>
                        </div>


                    </div>

                    <div class="relative mb-4">
                        <input type="text" x-model="search" placeholder="Cari nama atau NIK masyarakat..."
                            class="block w-full border-gray-300 rounded-md shadow-sm pl-10 focus:border-pink-500 focus:ring-pink-500">
                        <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>

                    <div class="space-y-3 max-h-96 overflow-y-auto border border-gray-200 p-4 rounded-md">

                        <template x-for="user in filteredUsers" :key="user.id">
                            <a :href="`{{ route('dashboard.partials.pilih-user') }}?user_id=${user.id}`"
                                class="block p-4 bg-gray-50 hover:bg-pink-100 rounded-md transition duration-150 border border-transparent hover:border-pink-300">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="font-semibold text-pink-600" x-text="user.name"></div>

                                        <div class="text-sm text-gray-500">
                                            <span class="font-medium">NIK:</span>
                                            <span x-text="user.nik || 'Belum ada NIK'"></span>
                                        </div>

                                        <div class="text-xs text-gray-400 mt-1">
                                            <span class="font-medium">Posyandu:</span>
                                            <span
                                                x-text="user.posyandu && user.posyandu.nama_posyandu
                        ? user.posyandu.nama_posyandu
                        : 'Belum terdaftar'">
                                            </span>
                                        </div>

                                        <div class="text-xs text-gray-400 mt-0.5"
                                            x-show="user.posyandu && user.posyandu.desa">
                                            <span class="font-medium">Desa:</span>
                                            <span x-text="user.posyandu?.desa || '-'"></span>
                                        </div>
                                    </div>

                                    <i class="bi bi-chevron-right text-gray-400 ml-4"></i>
                                </div>
                            </a>
                        </template>

                        <div x-show="filteredUsers.length === 0 && search !== ''" class="text-center py-8">
                            <i class="bi bi-search text-4xl text-gray-300 mb-3"></i>
                            <p class="text-gray-500 mb-4">Tidak ada masyarakat yang cocok dengan pencarian Anda.</p>

                            @if (in_array(auth()->user()->role, ['admin', 'kader']))
                                <a href="{{ route('admin.users.create', ['source' => 'pilih-user']) }}"
                                    class="inline-flex items-center px-4 py-2 bg-pink-500 text-white rounded-md hover:bg-pink-600 transition-colors duration-150 text-sm font-semibold">
                                    <i class="bi bi-plus-circle mr-2"></i>
                                    Buat User Baru
                                </a>
                            @endif
                        </div>

                        <div x-show="users.length === 0" class="text-center py-12">
                            <i class="bi bi-people text-6xl text-gray-300 mb-4"></i>
                            <h4 class="text-lg font-semibold text-gray-700 mb-2">Belum Ada Masyarakat Terdaftar</h4>
                            <p class="text-gray-500 mb-6">Silakan buat user masyarakat terlebih dahulu untuk melanjutkan.
                            </p>

                            @if (in_array(auth()->user()->role, ['admin','kader']))
                                <a href="{{ route('admin.users.create', ['source' => 'pilih-user']) }}"
                                    class="inline-flex items-center px-6 py-3 bg-pink-500 text-white rounded-md hover:bg-pink-600 transition-colors duration-150 font-semibold">
                                    <i class="bi bi-plus-circle mr-2"></i>
                                    Buat User Masyarakat
                                </a>
                            @endif
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('pilihUserState', () => ({
                search: '',
                users: @json($masyarakatUsers),
                get filteredUsers() {
                    const s = this.search.toLowerCase();
                    return this.search === '' ?
                        this.users :
                        this.users.filter(u =>
                            u.name.toLowerCase().includes(s) ||
                            (u.nik && u.nik.toLowerCase().includes(s))
                        );
                }
            }));
        });
    </script>

@endsection
