# Administrasi RT

Aplikasi technical test Full Stack Programmer untuk mengelola penghuni, rumah, histori hunian, iuran bulanan, pengeluaran, dashboard arus kas, dan laporan bulanan. Backend dan frontend dipisahkan, menggunakan Laravel 11, React + Vite, dan MySQL tanpa Docker.

## Fitur

- CRUD penghuni dengan upload foto KTP, status tetap/kontrak, telepon, dan status menikah.
- CRUD rumah dengan status dihuni/tidak dihuni serta histori penghuni.
- Generate kewajiban satpam Rp100.000 dan kebersihan Rp15.000 untuk setiap bulan yang memiliki penghuni.
- Pembayaran satu atau beberapa bulan sekaligus dengan histori penghuni penanggung jawab.
- CRUD pengeluaran.
- Dashboard pemasukan, pengeluaran, saldo akhir, dan grafik arus kas 12 bulan.
- Laporan bulanan berisi pembayaran, tagihan lunas/belum lunas, dan pengeluaran.

## Desain Data

ERD lengkap tersedia di [docs/ERD.md](docs/ERD.md). Migration utama ada di `backend/database/migrations/2026_05_31_000000_create_rt_administration_tables.php`.

Keputusan desain utama:

- `house_resident_histories.ended_at = NULL` menandakan penghuni rumah saat ini.
- Satu rumah dan satu penghuni hanya dapat memiliki satu relasi hunian aktif. Service menggunakan transaction dan row lock untuk menjaga konsistensi.
- `monthly_dues` menyimpan snapshot kewajiban per penghuni, rumah, bulan, dan jenis iuran. Histori tidak berubah jika penghuni atau tarif berubah.
- `payments` mencatat uang yang benar-benar diterima. Dashboard memakai cash basis berdasarkan `payments.paid_at`.
- `payment_details` memungkinkan satu pembayaran melunasi banyak bulan dan jenis iuran.

## Prasyarat

- PHP `8.2+` beserta ekstensi umum Laravel: `ctype`, `curl`, `fileinfo`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, dan `xml`.
- Composer `2.x`.
- MySQL `8.x` atau MariaDB yang kompatibel.
- Node.js `20.19+` atau `22.12+` dan npm.

## Instalasi Backend

1. Buat database MySQL:

```sql
CREATE DATABASE rt_administration
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
```

2. Siapkan Laravel:

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
```

Pada PowerShell, gunakan `Copy-Item .env.example .env` sebagai pengganti `cp`.

3. Sesuaikan koneksi MySQL dalam `backend/.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rt_administration
DB_USERNAME=root
DB_PASSWORD=
```

4. Buat tabel, isi master jenis iuran, dan buat symbolic link foto KTP:

```bash
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Backend berjalan di `http://localhost:8000`.

## Instalasi Frontend

Buka terminal kedua:

```bash
cd frontend
npm install
cp .env.example .env
npm run dev
```

Pada PowerShell, gunakan `Copy-Item .env.example .env`. Frontend berjalan di `http://localhost:5173`.

Jika alamat backend berbeda, ubah `frontend/.env`:

```dotenv
VITE_API_URL=http://localhost:8000/api
```

## Menjalankan Test

Test backend memakai SQLite sementara agar tidak mengubah database MySQL lokal:

```bash
cd backend
php artisan test
```

Verifikasi frontend:

```bash
cd frontend
npm run lint
npm run build
```

## REST API

| Method | Endpoint | Kegunaan |
| --- | --- | --- |
| `GET` | `/api/dashboard?year=2026` | Summary dan grafik 12 bulan |
| `GET` | `/api/reports/monthly?month=2026-05` | Laporan detail bulanan |
| `GET` | `/api/fee-types` | Master jenis iuran |
| `GET, POST` | `/api/residents` | Daftar dan tambah penghuni |
| `GET, PATCH, DELETE` | `/api/residents/{id}` | Detail, ubah, hapus penghuni |
| `GET, POST` | `/api/houses` | Daftar dan tambah rumah |
| `GET, PATCH, DELETE` | `/api/houses/{id}` | Detail histori, ubah, hapus rumah |
| `POST` | `/api/houses/{id}/occupancies` | Tetapkan penghuni aktif |
| `DELETE` | `/api/houses/{id}/occupancies/current` | Akhiri periode hunian |
| `GET` | `/api/monthly-dues?month=2026-05&status=unpaid` | Daftar tagihan |
| `POST` | `/api/monthly-dues/generate` | Generate tagihan bulan tertentu |
| `GET, POST` | `/api/payments` | Histori dan pencatatan pembayaran |
| `GET` | `/api/payments/{id}` | Detail pembayaran |
| `GET, POST` | `/api/expenses` | Daftar dan tambah pengeluaran |
| `GET, PUT, DELETE` | `/api/expenses/{id}` | Detail, ubah, hapus pengeluaran |

Contoh pembayaran satpam dan kebersihan untuk tiga bulan:

```json
{
  "resident_id": 1,
  "house_id": 1,
  "start_month": "2026-05",
  "month_count": 3,
  "fee_type_ids": [1, 2],
  "paid_at": "2026-05-31",
  "notes": "Pembayaran Mei sampai Juli"
}
```

## Struktur Backend

```text
backend/
|-- app/
|   |-- Enums/                  # Nilai status yang terkontrol
|   |-- Http/Controllers/Api/   # REST controller tipis
|   |-- Http/Requests/          # Validasi request terpusat
|   |-- Models/                 # Model dan relasi Eloquent
|   |-- Repositories/           # Akses data dan query laporan
|   `-- Services/               # Aturan bisnis dan transaction
|-- database/migrations/        # Struktur tabel
|-- database/seeders/           # Master satpam dan kebersihan
|-- routes/api.php              # Daftar endpoint
`-- tests/Feature/              # Test alur administrasi
```

## Struktur Frontend

```text
frontend/src/
|-- api/client.js               # Fetch client dan normalisasi error
|-- components/                 # Layout, modal, dan header reusable
|-- pages/                      # Halaman per fitur
|-- utils/format.js             # Format Rupiah dan tanggal
|-- App.jsx                     # Navigasi halaman
`-- App.css                     # Design system sederhana
```

## Validasi dan Upload KTP

- Foto KTP wajib saat tambah penghuni dan opsional saat update.
- Format yang diterima: JPG, JPEG, PNG, atau WebP dengan ukuran maksimal 2 MB.
- File disimpan pada disk `public` dalam folder `storage/app/public/ktp-photos`.
- Jalankan `php artisan storage:link` agar URL `/storage/...` dapat diakses frontend.
- Jika operasi database gagal, file baru dibersihkan agar tidak meninggalkan orphan file.

## Catatan Deployment

- Set `APP_ENV=production`, `APP_DEBUG=false`, dan kredensial database yang kuat.
- Batasi `FRONTEND_URL` ke origin frontend production.
- Jalankan `php artisan config:cache && php artisan route:cache`.
- Aplikasi technical test ini ditujukan untuk administrasi internal satu RT. Tambahkan autentikasi dan otorisasi sebelum mengekspos API ke internet publik.
- Ringkasan fitur beserta screenshot hasil implementasi tersedia di [docs/RINGKASAN-FITUR.md](docs/RINGKASAN-FITUR.md).
