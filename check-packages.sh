#!/bin/bash

# --- KONFIGURASI ---
OUTPUT_FILE="public/Laporan_Teknologi.pdf"
COMPOSER_FILE="composer.json"
PACKAGE_FILE="package.json"

echo "🔍 Memulai analisis package untuk Aplikasi Posyandu..."

# --- 1. DEFINISI LIBRARY YANG DICARI (WHITELIST) ---
# Format: "nama_package|Deskripsi_Singkat"

BACKEND_LIST=(
    "laravel/framework|Core Framework Utama"
    "livewire/livewire|Full-stack Framework untuk UI Dinamis"
    "maatwebsite/excel|Export dan Import Data Excel"
    "phpoffice/phpspreadsheet|Engine Pengolah Spreadsheet"
    "barryvdh/laravel-dompdf|Library Generator PDF"
    "laravel/socialite|Autentikasi Login Pihak Ketiga"
    "sweetalert2/laravel|Notifikasi Popup (Backend)"
)

FRONTEND_LIST=(
    "tailwindcss|Framework CSS Utility-First"
    "chart.js|Library Visualisasi Grafik Data"
    "alpinejs|Interaktivitas JavaScript Ringan"
    "axios|HTTP Client untuk Request API"
    "sweetalert2|Notifikasi Popup (Frontend)"
    "bootstrap-icons|Set Ikon Vektor"
)

# --- 2. FUNGSI EKSTRAKSI VERSI ---
get_version() {
    local package=$1
    local file=$2
    # Mencari string "package": "^1.2.3" dan mengambil versinya saja
    grep -o "\"$package\": *\"[^\"]*\"" "$file" | cut -d '"' -f 4
}

# --- 3. MEMBANGUN DATA HTML ---
HTML_CONTENT="<html><head><style>
    body { font-family: sans-serif; color: #333; padding: 20px; }
    h1 { text-align: center; border-bottom: 2px solid #4a5568; padding-bottom: 10px; }
    h2 { margin-top: 30px; color: #2d3748; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border: 1px solid #cbd5e0; padding: 12px; text-align: left; }
    th { background-color: #f7fafc; color: #4a5568; }
    .badge { background: #edf2f7; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-family: monospace; font-size: 0.9em; }
</style></head><body>"

HTML_CONTENT+="<h1>Laporan Teknologi Aplikasi Posyandu</h1>"
HTML_CONTENT+="<p>Dokumen ini berisi daftar pustaka (library) utama yang digunakan dalam pengembangan sistem.</p>"

# PROSES BACKEND
HTML_CONTENT+="<h2>A. Backend & Framework (PHP/Composer)</h2>"
HTML_CONTENT+="<table><thead><tr><th>Teknologi</th><th>Versi</th><th>Fungsi dalam Aplikasi</th></tr></thead><tbody>"

echo "   📂 Menganalisis Composer..."
for item in "${BACKEND_LIST[@]}"; do
    IFS='|' read -r name desc <<< "$item"
    version=$(get_version "$name" "$COMPOSER_FILE")

    if [ ! -z "$version" ]; then
        HTML_CONTENT+="<tr><td><strong>$name</strong></td><td><span class='badge'>$version</span></td><td>$desc</td></tr>"
        echo "      ✅ Ditemukan: $name ($version)"
    fi
done
HTML_CONTENT+="</tbody></table>"

# PROSES FRONTEND
HTML_CONTENT+="<h2>B. Frontend & UI (Node.js)</h2>"
HTML_CONTENT+="<table><thead><tr><th>Teknologi</th><th>Versi</th><th>Fungsi dalam Aplikasi</th></tr></thead><tbody>"

echo "   📂 Menganalisis Node Modules..."
for item in "${FRONTEND_LIST[@]}"; do
    IFS='|' read -r name desc <<< "$item"
    version=$(get_version "$name" "$PACKAGE_FILE")

    if [ ! -z "$version" ]; then
        HTML_CONTENT+="<tr><td><strong>$name</strong></td><td><span class='badge'>$version</span></td><td>$desc</td></tr>"
        echo "      ✅ Ditemukan: $name ($version)"
    fi
done
HTML_CONTENT+="</tbody></table>"

HTML_CONTENT+="<br><p style='text-align:right; font-size:0.8em; color:#718096;'>Generated automatically via Shell Script on $(date)</p></body></html>"

# --- 4. GENERATE PDF MENGGUNAKAN LARAVEL TINKER ---
# Kita kirim HTML yang sudah dibuat ke PHP Artisan untuk dirender oleh DomPDF
echo "🖨️  Sedang mencetak PDF..."

# Escape tanda kutip ganda untuk PHP string
ESCAPED_HTML=$(echo "$HTML_CONTENT" | sed 's/"/\\"/g')

php artisan tinker --execute="
\$pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML(\"$ESCAPED_HTML\");
\$pdf->setPaper('A4', 'portrait');
\$pdf->save(public_path('Laporan_Teknologi.pdf'));
exit;
"

echo "✅ SELESAI! File PDF tersimpan di:"
echo "   👉 $PWD/$OUTPUT_FILE"
