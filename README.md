# SAPA Posyandu

**SAPA Posyandu** (Sistem Aplikasi Pengelolaan Pos Pelayanan Terpadu) adalah sebuah sistem informasi berbasis web yang dirancang untuk mengelola administrasi, pengajuan layanan masyarakat tingkat desa hingga kabupaten, serta memberikan kemudahan monitoring operasional Posyandu secara berjenjang.

## 🚀 Fitur Utama

Berdasarkan struktur internal sistem, aplikasi ini memiliki fitur-fitur krusial sebagai berikut:

1. **Manajemen Pengajuan Layanan (AjuanController)**
   - Fitur utama bagi masyarakat untuk mengajukan permohonan layanan publik (baik berupa bantuan administrasi maupun pengajuan lainnya).
   - Dilengkapi dengan *tracking system* menggunakan QR Code atau Kode Tracking yang dapat diakses publik tanpa harus login.
   - Sistem *workflow approval* berjenjang (dari Pemdes, Kades, hingga Posyandu/Bidang terkait).
2. **Dashboard Analytics & Monitoring (DashboardController)**
   - Visualisasi data (berbasis `Chart.js`) yang interaktif dan dapat difilter berdasarkan tahun atau wilayah.
   - Penerapan Data Isolation (Multi-Tenancy) di mana tiap role hanya melihat metrik statistik wilayah atau bidang yang menjadi wewenangnya saja.
3. **Pusat Informasi & Buku Saku (BukuSakuController)**
   - Direktori digital penyediaan dokumen edukasi atau Buku Saku Posyandu yang bisa didownload langsung sesuai dengan standar nama dokumen aslinya.
4. **Manajemen Pengguna & Otorisasi (UserController & ProfileController)**
   - Mengelola aktivasi dan penonaktifan akun secara hierarkis.
   - Integrasi Single Sign-On (SSO) menggunakan Google Login (berbasis `Laravel Socialite`).
5. **Cetak & Pelaporan (LaporanController & DOMPDF)**
   - Generate laporan dalam bentuk dokumen PDF dengan kapabilitas export data ke Excel menggunakan pustaka bawaan `Maatwebsite Excel`.
6. **Data Terpusat Wilayah (DatabaseController & API)**
   - Sinkronisasi dinamis data geografis/wilayah (Kabupaten, Kecamatan, Desa) untuk keakuratan form pengajuan.

---

## 🛠️ Tech Stack

Berdasarkan *dependencies* dalam `composer.json` dan `package.json`, proyek ini mengandalkan:

**Backend & Framework:**
- **PHP** ^8.2
- **Laravel Framework** ^12.0
- **Livewire** ^3.6 (Untuk UI Interaktif sisi server)
- **Barryvdh Laravel DOMPDF** ^3.1 (Pembuat PDF Laporan)
- **Maatwebsite Excel** ^3.1 (Eksport/Import Data Excel)
- **Laravel Socialite** ^5.23 (OAuth Login seperti Google)

**Frontend & UI:**
- **Node.js** & **Vite** ^7.3.1 (Modul Bundler)
- **Tailwind CSS** ^3.1.0 (Utility-first CSS)
- **Alpine.js** ^3.4.2 (Logika interaksi UI ringan, state UI)
- **Chart.js** ^4.5.1 (Visualisasi data dan dashboard)
- **SweetAlert2** (Pop-up notifikasi yang elegan)

---

## 📂 Struktur Folder Utama

```text
sapa-posyandu/
├── app/                    # Logika inti aplikasi (Backend Laravel)
│   ├── Console/            # Perintah artisan kustom
│   ├── Exports/            # Logika export data (Excel)
│   ├── Http/
│   │   ├── Controllers/    # Menampung seluruh controller (AjuanController, DashboardController, dll)
│   │   ├── Middleware/     # Proteksi hak akses (Role-based access control, Auth)
│   │   ├── Requests/       # Validasi form request
│   │   └── Resources/      # Transformasi data API
│   ├── Imports/            # Logika import data (Excel)
│   ├── Livewire/           # Komponen reaktif backend (Pencarian, Tabel Dinamis, Form)
│   ├── Models/             # Entitas Database ORM (User, Posyandu, Pengajuan, BidangPengajuan, dll)
│   ├── Notifications/      # Class untuk mengirim notifikasi dalam aplikasi/email
│   ├── Policies/           # Otorisasi tingkat lanjut untuk resource tertentu
│   ├── Providers/          # Service provider aplikasi (Route, Auth, Event, dll)
│   ├── Support/            # Fungsi bantuan/helpers kustom
│   └── View/               # Komponen view khusus
├── bootstrap/              # File cache dan inisialisasi framework
├── config/                 # Konfigurasi global sistem (auth, database, mail, app, logging, cors, dll)
├── database/               # Struktur dan dummy data database
│   ├── factories/          # Blueprint untuk generate data palsu (faker)
│   ├── migrations/         # Skema struktur tabel database (users, pengajuans, posyandus, dll)
│   └── seeders/            # Pengisi data awal database (Akun default, Wilayah, Pengaturan Sistem)
├── lang/                   # File bahasa untuk terjemahan aplikasi
├── public/                 # Folder publik yang bisa diakses langsung via browser
│   ├── assets/             # Aset statis berupa gambar, ikon statis, CSS eksternal
│   ├── build/              # Hasil compile frontend dari Vite (manifest, css, js)
│   └── index.php           # Entry point utama aplikasi
├── resources/              # Aset belum ter-compile dan template tampilan
│   ├── css/                # File Tailwind CSS (app.css)
│   ├── js/                 # File JavaScript utama dan inisiasi Alpine.js (app.js)
│   └── views/              # Template Blade Laravel
│       ├── admin/          # Tampilan manajemen data oleh admin
│       ├── ajuan/          # Halaman permohonan, form detail, dan tracking pengajuan
│       ├── auth/           # Tampilan login, register, dan lupa password
│       ├── components/     # Komponen UI Blade yang bisa dipakai ulang (modal, button, badge)
│       ├── dashboard/      # Tampilan dashboard statistik (termasuk partials per role)
│       ├── layouts/        # Template struktur kerangka halaman utama (app.blade.php, guest.blade.php)
│       ├── livewire/       # File view khusus untuk komponen Livewire
│       ├── posyandu/       # Halaman profil dan manajemen posyandu
│       └── profile/        # Pengaturan profil pengguna
├── routes/                 # Definisi endpoint rute URL
│   ├── api.php             # Rute untuk API publik/eksternal
│   ├── channels.php        # Broadcasting event (WebSockets)
│   ├── console.php         # Rute perintah artisan CLI
│   └── web.php             # Rute utama aplikasi web (Login, Dashboard, Ajuan, dll)
├── storage/                # Folder penyimpanan file sistem dan user
│   ├── app/                # Berkas yang disimpan aplikasi
│   │   └── public/         # File media terpublikasi, tempat upload lampiran pengajuan & buku saku (perlu storage:link)
│   ├── framework/          # Cache framework, view ter-compile, dan session
│   └── logs/               # Catatan log error harian Laravel (laravel.log)
├── tests/                  # Direktori unit testing dan feature testing
├── .env                    # Variabel environment sistem (kredensial DB, Mail, API URL, dll)
├── composer.json           # Definisi daftar package backend (PHP)
├── package.json            # Definisi daftar package frontend (Node.js)
├── tailwind.config.js      # Konfigurasi custom class Tailwind CSS dan lokasi file view
└── vite.config.js          # Pengaturan bundler Vite untuk compile CSS/JS
```

---

## ⚙️ Panduan Instalasi (Development Setup)

Ikuti langkah-langkah standar berikut untuk menjalankan project ini secara lokal:

1. **Clone Repository**
   ```bash
   git clone <repo-url>
   cd e-posyandu
   ```
2. **Install Dependensi PHP & Node.js**
   ```bash
   composer install
   npm install
   ```
3. **Konfigurasi Environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *(Penting: Buka file `.env` dan atur parameter `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` sesuai dengan konfigurasi lokal Anda.)*
4. **Migrasi Database & Seeder**
   ```bash
   php artisan migrate --seed
   ```
5. **Konfigurasi Storage Link (Untuk Media / File Pengajuan)**
   ```bash
   php artisan storage:link
   ```
6. **Compile Frontend & Jalankan Server**
   ```bash
   npm run build
   # Kemudian jalankan Laravel Development Server
   php artisan serve
   ```

---

## 👥 Role & Hak Akses (RBAC)

Aplikasi ini menggunakan sistem hierarki akses dan *Data Isolation* tingkat lanjut yang didefinisikan secara ketat dalam *Database Migration*, *Middleware*, dan *Policies*. Terdapat **11 Role (Peran)** yang diakomodasi:

**1. Tingkat Master (Global Access)**
- **admin**: Memiliki akses paripurna. Dapat mengelola pengaturan sistem utama, memanajemen semua *user* lintas wilayah, memodifikasi master data Posyandu, serta melihat/mengekspor seluruh laporan tanpa batasan wilayah.
- **ketua-timpembina-posyandu**: Memiliki akses eksekutif tingkat atas. Dapat melihat seluruh laporan, analitik, dan detail pengajuan secara global, namun tidak memiliki wewenang untuk memodifikasi master data pengaturan sistem atau akun *user*.

**2. Tingkat Bidang & Kabupaten (Isolasi Data Kabupaten/Bidang)**
- **admin-kabupaten**: Mengelola *user*, master data Posyandu, dan memonitor laporan agregat khusus di lingkup wilayah kabupaten miliknya saja. Tidak dapat mengelola pengaturan sistem *root*.
- **kabid (Kepala Bidang)**: Mengawasi dan melihat laporan analitik serta pengajuan khusus pada **Bidang** dan **Kabupaten** yang menjadi tanggung jawabnya. Dapat memilih *user* untuk mendaftarkan permohonan administrasi secara administratif dari sisi dinas.

**3. Tingkat Kecamatan (Isolasi Data Kecamatan)**
- **admin-kecamatan**: Mengawasi, mengelola master data posyandu, memanajemen akun (seperti kader/operator), serta melihat laporan rekap khusus di lingkup wilayah kecamatannya sendiri.

**4. Tingkat Desa / Kelurahan (Isolasi Data Desa)**
- **kades (Kepala Desa)**: Penentu keputusan administratif desa. Memiliki hak akses eksekusi khusus untuk menyetujui atau menolak permohonan warga (*Kades Approval*) sebelum dilanjutkan ke dinas/kabid. Juga berhak memantau laporan seluruh posyandu di desanya.
- **operator-desa**: Admin teknis tingkat desa yang bertugas mengelola akun pengguna, mengatur *mapping* data posyandu di wilayah desa tersebut, dan memonitor data laporan desa.
- **bu-kades**: Berperan sebagai pengawas tingkat desa (*Viewer-Only*). Dapat melihat statistik, memantau laporan, dan progres pengajuan seluruh posyandu di desanya tanpa hak eksekusi persetujuan atau modifikasi data.

**5. Tingkat Posyandu (Isolasi Data Posyandu)**
- **ketua-posyandu**: Pemimpin unit posyandu. Memiliki wewenang esensial untuk memverifikasi dokumen warga dan meneruskannya ke tingkat desa (*Submit to Pemdes*). Berhak memverifikasi akun kader di posyandunya serta melihat semua permohonan yang masuk ke unitnya.
- **kader**: Pelaksana teknis lapangan. Mengelola/mengaktifkan akun masyarakat, membantu pembuatan draf pengajuan, dan memiliki batasan hanya bisa mengakses data pengajuan yang *bidang*-nya sama dan berada di posyandu tempat ia ditugaskan.

**6. Tingkat Pengguna Umum**
- **masyarakat**: Pengguna publik biasa. Hanya memiliki hak akses untuk membuat pengajuan permohonan baru, mengunggah revisi bila ditolak/dikembalikan, melacak status permohonannya sendiri, serta mencetak bukti *tracking*.

---

## ⚠️ Panduan Developer & Aturan Proyek

Harap diperhatikan bagi para developer, **WAJIB** mengikuti panduan di bawah ini saat melakukan *commit* atau pengembangan fitur:

- UI wajib responsif (Desktop menggunakan tabel, Mobile menggunakan layout Card).
- Wajib pakai helper `asset('storage/...')` dan hapus string `'public/'` saat memanggil gambar dari database.
- Wajib menjalankan `php artisan view:clear` jika perubahan file view (Blade) tidak memantul di browser.
- Z-index modal (terutama AlpineJS) minimal menggunakan `z-[9999]`.
- Nama file download harus sesuai judul asli dokumen, dilarang menggunakan fungsi slug/hash.