<x-guest-layout>
    <form method="POST" action="{{ route('ajuan.store.administrasi') }}" enctype="multipart/form-data">
        @csrf
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-8">Administrasi Ajuan</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-6 ">
            @foreach ($items as $key => $label)
                <div x-data="{ fileName: '' }" class="h-full flex flex-col justify-end">
                    <label for="{{ $key }}" class="block font-medium text-sm text-gray-700">{{ $label }}</label>
                    <label for="{{ $key }}" class="mt-1 flex justify-between items-center px-3 py-2 bg-white text-gray-500 rounded-md shadow-sm border border-gray-300 cursor-pointer hover:text-gray-700">
                        <span x-text="fileName || 'Pilih File'" class="truncate"></span>
                        <i class="bi bi-cloud-upload text-pink-500 text-lg"></i>
                    </label>
                    <input id="{{ $key }}" class="hidden" type="file" name="{{ $key }}" @change="fileName = $event.target.files[0] ? $event.target.files[0].name : ''" />
                    <x-input-error :messages="$errors->get($key)" class="mt-2" />
                </div>
            @endforeach
        </div>

        <div class="block mt-6">
            <label class="flex items-center">
                <input type="checkbox" name="agreement" required class="rounded border-gray-300 text-pink-600 shadow-sm focus:ring-pink-500">
                <span class="ms-2 text-sm text-gray-600">Dengan ini saya ajukan formulir permohonan ini dengan data sebenar-benarnya.</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-8 space-x-4">
            <a href="{{ url()->previous() }}" class="py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Kembali</a>
            <button type="submit" class="inline-flex items-center px-8 py-2 bg-pink-500 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-pink-600">Selanjutnya</button>
        </div>
    </form>
</x-guest-layout>
