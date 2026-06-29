    {{-- PWA Install Banner --}}
    <div id="pwa-install-banner"
        class="w-full relative z-[60] {{ $maxWidth ?? 'max-w-7xl' }} {{ $paddingClass ?? 'px-4 sm:px-6 lg:px-8' }} mx-auto hidden pb-6 mt-4"
        role="complementary" aria-label="Install aplikasi">
        <div class="bg-white {{ $roundedClass ?? 'rounded-xl' }} shadow-md border border-gray-100 px-4 py-4 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <img src="/assets/image/logo/app/logo_192x192.png"
                    alt="Sapa Posyandu" class="w-12 h-12 rounded-xl flex-shrink-0">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate">Sapa Posyandu</p>
                    <p class="text-xs text-gray-500">Install aplikasi untuk akses lebih mudah</p>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">

                <button id="pwa-install-btn"
                    class="bg-pink-500 hover:bg-pink-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors shadow-sm">
                    Install
                </button>
            </div>
        </div>
    </div>

    <script>
        // PWA Install Banner
        let deferredPrompt = null;
        const banner  = document.getElementById('pwa-install-banner');
        const btnInstall  = document.getElementById('pwa-install-btn');

        window.addEventListener('beforeinstallprompt', function (e) {
            e.preventDefault();
            deferredPrompt = e;

            banner.classList.remove('hidden');
        });

        if(btnInstall) {
            btnInstall.addEventListener('click', async function () {
                if (!deferredPrompt) return;
                banner.classList.add('hidden');
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                deferredPrompt = null;
            });
        }



        // Sembunyikan banner jika sudah terinstall
        window.addEventListener('appinstalled', function () {
            if(banner) banner.classList.add('hidden');
            deferredPrompt = null;
        });
    </script>
