# wthme_app

`wthme_app` adalah aplikasi manajemen acara berbasis Laravel dan FastAPI yang dirancang untuk kebutuhan panitia, peserta, kesehatan, logistik, dan absensi. Repositori ini berisi dua bagian utama:

- `web-wthme/`: aplikasi web Laravel 13 dengan dashboard admin, panitia, peserta, eksport/import data, tugas, mentoring, kas, notulensi, Gantt chart, dan fitur event lainnya.
- `face-api/`: backend face recognition FastAPI untuk pendaftaran wajah dan verifikasi absensi menggunakan InsightFace + FAISS.

## Fitur Utama

- Otentikasi pengguna dengan peran: admin, panitia, peserta.
- Manajemen panitia dan peserta, termasuk import Excel dan template.
- Absensi menggunakan QR code, form manual, dan face gate.
- Modul P3K/logistik untuk pengumpulan barang, validasi, stok, dan rekap per kelompok.
- Tugas peserta dengan upload file, status, download, dan export.
- Mentoring logbook dengan rekap global dan export.
- Kas keuangan dengan input, penghapusan, dan export.
- Kesehatan dengan pembaruan warna pita.
- Notulensi meeting dengan penyimpanan dokumen dan export.
- Gantt chart untuk perencanaan tugas.
- Quest Meet, Quest Lab, foto kekeluargaan, dan capture moment.
- Leaderboard dan broadcast informasi kepada peserta.
- Ekspor data ke Excel/Word via `maatwebsite/excel`, `phpoffice/phpword`, dan `barryvdh/laravel-dompdf`.

## Arsitektur

- `web-wthme/` adalah aplikasi Laravel lengkap.
- `face-api/` adalah microservice FastAPI yang menyediakan endpoint pendaftaran wajah.
- `face-api/main.py` memproses gambar wajah, membuat embedding, dan membangun index FAISS.
- `face-api/Dockerfile` memungkinkan menjalankan service face recognition dalam container.

## Prasyarat

- PHP 8.3+
- Composer
- Node.js + npm
- Python 3.10+
- Docker (opsional, khusus untuk `face-api` jika ingin mengemas service)
- Database yang kompatibel dengan Laravel (MySQL, PostgreSQL, SQLite, dsb.)

## Instalasi Web Laravel

1. Masuk ke folder aplikasi Laravel:

```bash
cd web-wthme
```

2. Instal dependensi PHP dan Node:

```bash
composer install
npm install
```

3. Siapkan environment:

```bash
cp .env.example .env
php artisan key:generate
```

4. Jalankan migrasi database:

```bash
php artisan migrate
```

5. Buat build aset front-end:

```bash
npm run build
```

6. Jalankan server pengembangan:

```bash
php artisan serve
```

## Menjalankan Face API

### Opsi 1: Lokal

1. Masuk ke folder `face-api`:

```bash
cd ../face-api
```

2. Instal dependensi Python:

```bash
pip install -r requirements.txt
```

3. Jalankan server:

```bash
uvicorn main:app --host 0.0.0.0 --port 8001
```

### Opsi 2: Docker

1. Bangun image:

```bash
docker build -t wthme-face-api .
```

2. Jalankan container:

```bash
docker run -p 8001:8001 wthme-face-api
```

## Struktur Folder

- `web-wthme/`: core aplikasi Laravel.
- `face-api/`: service face recognition.
- `web-wthme/routes/web.php`: daftar rute aplikasi untuk admin, panitia, peserta, export, p3k, absensi, dan fitur event.
- `web-wthme/app/Http/Controllers/`: kontroler aplikasi utama.
- `web-wthme/resources/views/`: tampilan Blade.
- `web-wthme/database/migrations/`: struktur database.

## Catatan Tambahan

- `face-api` menggunakan model `buffalo_sc` dari InsightFace dan menyimpan embedding wajah di `face-api/encodings/` secara sementara.
- `face-api` sekarang menghubungkan ke MySQL menggunakan kredensial `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD`.
- Jika ada file data wajah lama di `face-api/encodings/*.json`, service akan mencoba memigrasikannya ke database saat startup.
- `face-api` menyediakan route pendaftaran wajah `POST /api-face/register/{user_id}`.
- Pastikan folder `face-api/encodings` memiliki izin tulis.
- Untuk pengembangan Laravel, `npm run dev` dapat digunakan bersama `php artisan serve`.

## Lisensi

Proyek ini mengikuti lisensi MIT sesuai konfigurasi `web-wthme/composer.json`.
