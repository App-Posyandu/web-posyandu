@include('layouts.partials.header-new')
<x-guest-layout>
    <form method="POST" action="{{ route('ajuan.store.permohonan') }}">
        @csrf
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-8">Formulir Permohonan</h2>

        <div class="mb-6">
            <label for="bidang_pelayanan" class="block font-medium text-sm text-gray-700">Ubah bidang pelayanan</label>
            <select id="bidang_pelayanan" onchange="window.location.href=this.value;"
                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                @foreach ($allBidangs as $itemBidang)
                    <option value="{{ route('ajuan.create', $itemBidang->slug) }}" @selected($bidang->slug == $itemBidang->slug)>
                        {{ $itemBidang->nama_bidang }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="space-y-4">
            @foreach ($items as $item)
                <label class="flex items-center">
                    <input type="checkbox" name="permohonan_items[]" value="{{ $item }}"
                        class="rounded border-gray-300 text-pink-600 shadow-sm focus:ring-pink-500">
                    <span class="ms-3 text-gray-700">{{ $item }}</span>
                </label>
            @endforeach
        </div>

        <div class="flex items-center justify-end mt-8 space-x-4">
            <a href="{{ route('dashboard') }}"
                class="py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Kembali</a>
            <button type="submit"
                class="inline-flex items-center px-8 py-2 bg-pink-500 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-pink-600">Selanjutnya</button>
        </div>
    </form>
</x-guest-layout>
