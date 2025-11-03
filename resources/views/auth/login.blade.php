<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="flex items-center justify-center space-x-4 mb-6">
        <img src={{ asset('assets/image/logo/logo_kebumen.png') }} alt="Logo Kebumen" class="h-16">
        <img src={{ asset('assets/image/logo/logo_posyandu.png') }} alt="Logo Posyandu" class="h-16">
        <img src={{ asset('assets/image/logo/logo_sapaposyandu.png') }} alt="Logo Posyandu" class="h-16">
        <img src={{ asset('assets/image/logo/logo_telkom_university.png') }} alt="Logo Posyandu" class="h-16">
    </div>

    <div class="text-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Selamat Datang di SAPA POSYANDU</h1>
        <p class="text-pink-500 text-xl font-semibold">Pelayanan Elektronik Posyandu</p>
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email atau nomor telephone')" class="font-semibold text-lg md:text-xl" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')"
                required autofocus autocomplete="username" placeholder="Masukkan email atau nomor telephone" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4" x-data="{ show: false }">
            <x-input-label for="password" :value="__('Password')" class="font-semibold text-lg md:text-xl" />
            <div class="relative">
                <x-text-input id="password" class="block mt-1 w-full" x-bind:type="show ? 'text' : 'password'"
                    name="password" required autocomplete="current-password" placeholder="Masukkan password" />
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center text-sm leading-5">
                    <button type="button" @click="show = !show" class="text-gray-500">
                        <i class="bi" :class="show ? 'bi-eye-slash' : 'bi-eye'"></i>
                    </button>
                </div>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>


        <div class="text-right mt-2">
            @if (Illuminate\Support\Facades\Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md"
                    href="{{ route('password.request') }}">
                    {{ __('Lupa password') }}
                </a>
            @endif
        </div>

        <div class="mt-6">
            <button type="submit"
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm md:text-lg font-medium text-white bg-pink-500 hover:bg-pink-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500">
                {{ __('Login') }}
            </button>
        </div>
    </form>

    <div class="flex items-center my-4">
        <hr class="flex-grow border-gray-300">
        <span class="mx-4 text-gray-500 text-base">Atau</span>
        <hr class="flex-grow border-gray-300">
    </div>

    <a href="{{ route('google.login') }}"
        class="w-full flex gap-2 items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm md:text-lg font-medium text-gray-700 bg-white hover:bg-gray-50">
        <img width="20" src="{{ asset('assets/image/icon/Google.png') }}" alt="Google Icon">
        Login with Google
    </a>

    <div class="text-center mt-6">
        <p class="text-sm md:text-base text-gray-600">
            Belum punya akun?
            <a class="font-semibold text-pink-500 hover:text-pink-700" href="{{ route('register') }}">
                Buat akun baru
            </a>
        </p>
    </div>
</x-guest-layout>
