<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <i class="bi bi-table mr-2 text-pink-500"></i>
                {{ __('Data Tabel: ') }} <span class="font-mono bg-white px-2 py-1 rounded border">{{ $table }}</span>
            </h2>
            <a href="{{ route('admin.database.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                <i class="bi bi-arrow-left mr-2"></i> Kembali ke Daftar
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-[95%] mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <div class="overflow-x-auto border rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 table-auto">
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
                                                {{ Str::limit((string)$record->$column, 50) }}
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
                    {{ $records->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
