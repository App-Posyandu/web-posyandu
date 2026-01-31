@extends('dashboard.layouts.dashboard')
@section('title', 'Data Kecamatan')
@section('content')
<div class="w-full mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">

                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">List Kecamatan</h2>
                    <a href="{{ route('admin.kecamatan.create') }}"
                        class="px-4 py-2 bg-pink-500 text-white rounded-md text-sm font-semibold hover:bg-pink-600">
                        Tambah Kecamatan
                    </a>
                </div>

                @include('components.all-notifications')

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase">No</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase">Nama Kecamatan
                                </th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase">Kabupaten</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($kecamatans as $item)
                                <tr>
                                    <td class="px-6 py-4">{{ $loop->iteration + $kecamatans->firstItem() - 1 }}</td>
                                    <td class="px-6 py-4 font-medium">{{ $item->nama_kecamatan }}</td>
                                    <td class="px-6 py-4">{{ $item->kabupaten->nama_lengkap ?? '-' }}</td>
                                    <td class="px-6 py-4 flex space-x-2">
                                        <a href="{{ route('admin.kecamatan.edit', $item) }}"
                                            class="px-3 py-1 bg-yellow-500 text-white rounded-md text-xs hover:bg-yellow-600">Ubah</a>
                                        <form action="{{ route('admin.kecamatan.destroy', $item) }}" method="POST"
                                            onsubmit="return confirm('Yakin hapus?');">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="px-3 py-1 bg-red-500 text-white rounded-md text-xs hover:bg-red-600">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">Tidak ada data kecamatan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $kecamatans->links() }}</div>
            </div>
        </div>
    </div>
@endsection
