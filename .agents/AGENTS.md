# Docker Environment Rules
- Project Laravel ini berjalan di dalam environment Docker menggunakan docker-compose.
- Jika ada instalasi package PHP (Composer) atau NPM, berikan perintah dengan prefix Docker. Contoh: `docker compose exec app composer require...` atau `docker compose exec app npm run build`.
- Semua perintah artisan (seperti `php artisan migrate`, `php artisan db:seed`) harus ditulis dengan format eksekusi di dalam container (contoh: `docker compose exec app php artisan ...`).
- PENTING: Controller utama pengajuan adalah `AjuanController.php`. Semua modifikasi backend untuk fitur pengajuan harus merujuk dan memodifikasi file ini (bukan PengajuanController).
- Backend menggunakan library PHP GD bawaan untuk kompresi gambar (seperti method `compressAndStoreImage`). Jika butuh update library OS, berikan instruksi update `Dockerfile` untuk install ekstensi gd (`libpng-dev`, `libjpeg-dev`, dsb) lalu instruksikan pengguna untuk menjalankan `docker compose build`.
