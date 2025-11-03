@section('content')
    <div class="max-w-4xl w-full mx-auto sm:px-6 lg:px-8">
        @if (session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6 max-w-4xl w-full mx-auto"
                role="alert">
                <div class="flex">
                    <div class="py-1"><i class="bi bi-exclamation-triangle-fill mr-3"></i></div>
                    <div>
                        <p class="font-bold">Akses Ditolak</p>
                        <p class="text-sm">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-2xl p-8">

            <h2 class="text-2xl font-bold text-center text-gray-800 mb-8">
                Pilih Ajuan Layanan
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach ($allBidangs as $bidang)
                    <a href="{{ route('ajuan.create', $bidang->slug) }}"
                        class="block p-6 text-center text-8xl text-white font-semibold rounded-lg shadow-md transform hover:scale-105 transition-transform duration-200"
                        style="background-color: {{ $colors[$loop->index % count($colors)] }}">
                        <div class="flex flex-row items-center gap-4 ">
                            <img src="{{ $icons[$bidang->slug] ?? asset('assets/image/icon/bidang/default.svg') }}"
                                alt="{{ $bidang->nama_bidang }} icon" class="w-8 h-8 ">
                            <span>{{ $bidang->nama_bidang }}</span>
                        </div>
                    </a>
                @endforeach

            </div>
        </div>
    </div>
@endsection
