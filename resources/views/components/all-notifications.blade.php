@if (session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-6" role="alert">
        <div class="flex">
            <div class="py-1">
                <i class="bi bi-check-circle-fill mr-3"></i>
            </div>
            <div>
                <p class="font-bold">Berhasil</p>
                <p class="text-sm">{{ session('success') }}</p>
            </div>
        </div>
    </div>
@endif

@if (session('error'))
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6" role="alert">
        <div class="flex">
            <div class="py-1">
                <i class="bi bi-exclamation-triangle-fill mr-3"></i>
            </div>
            <div>
                <p class="font-bold">Gagal</p>
                <p class="text-sm">{{ session('error') }}</p>
            </div>
        </div>
    </div>
@endif

@if (session('warning'))
    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded-lg mb-6" role="alert">
        <div class="flex">
            <div class="py-1">
                <i class="bi bi-exclamation-triangle-fill mr-3"></i>
            </div>
            <div>
                <p class="font-bold">Peringatan</p>
                <p class="text-sm">{{ session('warning') }}</p>
            </div>
        </div>
    </div>
@endif
