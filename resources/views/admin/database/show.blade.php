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
@endsection
