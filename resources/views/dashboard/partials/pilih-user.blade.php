@extends('dashboard.layouts.dashboard')
@section('title', 'Buat Ajuan Atas Nama')
@section('content')

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Buat Ajuan Atas Nama
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">

                <div class="p-6 text-gray-900" x-data="pilihUserState">
                    <h3 class="text-xl font-semibold mb-4">Pilih Masyarakat</h3>
                    <p class="text-sm text-gray-600 mb-6">Pilih masyarakat yang akan Anda bantu buatkan pengajuan. Hanya
                        masyarakat yang sudah terverifikasi yang akan muncul di daftar ini.</p>

                    <div class="relative mb-4">
                        <input type="text" x-model="search" placeholder="Cari nama atau NIK masyarakat..."
                            class="block w-full border-gray-300 rounded-md shadow-sm pl-10 focus:border-pink-500 focus:ring-pink-500">
                        <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>

                    <div class="space-y-3 max-h-96 overflow-y-auto border p-4 rounded-md">

                        <template x-for="user in filteredUsers" :key="user.id">
                            <a :href="`{{ route('dashboard.partials.pilih-user') }}?user_id=${user.id}`"
                                class="block p-4 bg-gray-50 hover:bg-pink-100 rounded-md transition duration-150">
                                <div class="font-semibold text-pink-600" x-text="user.name"></div>
                                <div class="text-sm text-gray-500" x-text="`NIK: ${user.nik}`"></div>
                            </a>
                        </template>

                        <p x-show="filteredUsers.length === 0" class="text-gray-500">
                            Tidak ada masyarakat yang cocok dengan pencarian Anda.
                        </p>
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
