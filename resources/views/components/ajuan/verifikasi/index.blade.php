<x-guest-layout>
    <form method="POST" action="{{ route('ajuan.store.final') }}">
        @csrf
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-8">Verifikasi Administrasi Ajuan</h2>

        <div class="space-y-4">
            @foreach ($data['administrasi_items'] as $key => $label)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                    <span class="text-gray-700">{{ $label }}</span>
                    @if (isset($data['uploaded_files'][$key]))
                        <i class="bi bi-check-square-fill text-green-500 text-xl"></i>
                    @else
                        <i class="bi bi-x-square-fill text-red-500 text-xl"></i>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="flex items-center justify-end mt-8 space-x-4">
            <a href="{{ url()->previous() }}"
                class="py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Kembali</a>
            <button type="submit"
                class="inline-flex items-center px-8 py-2 bg-pink-500 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-pink-600">Kirim</button>
        </div>
    </form>
</x-guest-layout>
