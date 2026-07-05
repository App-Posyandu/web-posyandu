@extends('dashboard.layouts.dashboard')
@section('title', 'Manajemen Database')
@section('content')
<div class="w-full mx-auto sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl p-4 sm:p-6 lg:p-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div class="w-full flex flex-col items-start gap-4">
                <h2 class="text-2xl font-bold text-gray-800 whitespace-nowrap">Database System</h2>
            </div>
        </div>

        <div class="text-gray-900">
            <div class="hidden md:block w-full max-w-full overflow-x-auto">
                <table class="min-w-full whitespace-nowrap divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Tabel</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Baris</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($tableList as $table)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $table['name'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="px-2 py-1 bg-gray-100 rounded text-gray-700 font-mono">{{ number_format($table['rows']) }}</span> baris
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
                                    <a href="{{ route('admin.database.show', $table['name']) }}" class="inline-flex items-center text-pink-600 hover:text-pink-900 bg-pink-50 hover:bg-pink-100 px-3 py-1 rounded-md transition-colors font-semibold">
                                        <i class="bi bi-table mr-2"></i> Lihat Data
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile View -->
            <div class="block md:hidden space-y-4 mt-4">
                @foreach ($tableList as $table)
                    <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-pink-50 to-purple-50 px-4 py-3 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-gray-900">{{ $table['name'] }}</h3>
                            </div>
                        </div>
                        <div class="px-4 py-3 space-y-3">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-24 text-xs font-medium text-gray-500">Jumlah Data</div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-900">{{ number_format($table['rows']) }} baris</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-2 mt-2 pt-2 border-t border-gray-200 bg-gray-50 px-4 sm:px-6 pb-4">
                            <a href="{{ route('admin.database.show', $table['name']) }}" class="w-full sm:w-auto px-3 py-2 bg-pink-500 text-white rounded-md text-sm font-medium text-center hover:bg-pink-600 transition">
                                <i class="bi bi-table mr-1"></i> Lihat Data
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
