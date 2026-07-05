@extends('dashboard.layouts.dashboard')
@section('title', 'Data Tabel ' . $table)
@section('content')
<div class="w-full mx-auto sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl p-4 sm:p-6 lg:p-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div class="w-full md:w-1/2 flex flex-col md:flex-row items-start md:items-center gap-4">
                <h2 class="text-2xl font-bold text-gray-800 whitespace-nowrap">
                    <i class="bi bi-table mr-2 text-pink-500"></i>
                    Data Tabel: <span class="font-mono bg-gray-50 px-2 py-1 rounded border">{{ $table }}</span>
                </h2>
            </div>
            <div class="w-full md:w-1/2 flex justify-end">
                <a href="{{ route('admin.database.index') }}" class="whitespace-nowrap px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-sm font-semibold hover:bg-gray-300 transition">
                    <i class="bi bi-arrow-left mr-2"></i> Kembali ke Daftar
                </a>
            </div>
        </div>

        <div class="mb-4 bg-gray-50 p-4 rounded-lg border flex flex-col md:flex-row md:items-center justify-between gap-4">
            <form id="searchTableForm" action="{{ route('admin.database.show', $table) }}" method="GET" class="w-full flex flex-col sm:flex-row gap-2 items-center">
                <select id="searchColumnSelect" name="search_column" class="w-full sm:w-auto px-4 py-2 border rounded-md shadow-sm focus:ring-pink-500 focus:border-pink-500 text-sm">
                    <option value="">-- Pilih Kolom --</option>
                    @foreach ($columns as $column)
                        <option value="{{ $column }}" {{ (isset($searchColumn) && $searchColumn == $column) ? 'selected' : '' }}>
                            {{ $column }}
                        </option>
                    @endforeach
                </select>
                <input id="searchInput" type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari data..." class="w-full sm:w-1/2 px-4 py-2 border rounded-md shadow-sm focus:ring-pink-500 focus:border-pink-500 text-sm">
                <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-pink-500 text-white rounded-md text-sm font-semibold hover:bg-pink-600 transition">
                    <i class="bi bi-search mr-1"></i> Cari
                </button>
                @if(isset($search) && $search !== '')
                    <a href="{{ route('admin.database.show', $table) }}" class="w-full sm:w-auto px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-sm font-semibold hover:bg-gray-300 transition text-center">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="text-gray-900">
            <div class="w-full max-w-full overflow-x-auto border rounded-lg">
                <table class="min-w-full whitespace-nowrap divide-y divide-gray-200 table-auto">
                    <thead class="bg-gray-50">
                        <tr>
                            @foreach ($columns as $column)
                                <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">
                                    {{ $column }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($records as $record)
                            <tr class="hover:bg-gray-50 transition-colors">
                                @foreach ($columns as $column)
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 max-w-xs truncate" title="{{ $record->$column ?? '' }}">
                                        @if($record->$column === null)
                                            <span class="text-gray-400 italic">NULL</span>
                                        @else
                                            {{ \Illuminate\Support\Str::limit((string)$record->$column, 50) }}
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($columns) }}" class="px-6 py-8 text-center text-gray-500 flex flex-col items-center justify-center">
                                    <i class="bi bi-inbox text-4xl mb-3 text-gray-300"></i>
                                    <p>Tabel ini belum memiliki data.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-6">
                {{ $records->links('vendor.pagination.tailwind') }}
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var searchForm = document.getElementById('searchTableForm');
    if(searchForm) {
        searchForm.addEventListener('submit', function(e) {
            var searchColumn = document.getElementById('searchColumnSelect').value;
            var searchInput = document.getElementById('searchInput').value;
            
            if (searchInput.trim() !== '' && searchColumn === '') {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Pilih Kolom',
                    text: 'Kolom pencarian wajib dipilih jika Anda memasukkan kata kunci!',
                    confirmButtonColor: '#ec4899'
                });
            }
        });
    }
});
</script>
@endsection
