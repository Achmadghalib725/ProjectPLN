# E-Dispatch & Tool Management System

E-Dispatch & Tool Management System adalah aplikasi manajemen gudang dan surat jalan untuk pengiriman dan peminjaman barang antar gudang. Fokusnya adalah kontrol stok, alur persetujuan yang jelas, dan pelacakan status per peran.

## Daftar isi
- Gambaran umum
- Fitur
- Peran dan akses
- Alur surat jalan (ringkas)
- Teknologi
- Prasyarat
- Quick start
- Konfigurasi .env penting
- Data contoh
- Perintah berguna
- Testing

## Gambaran umum
Aplikasi ini membantu operasional gudang dengan fitur stok, pergerakan, surat jalan, dan approval multi peran. Surat jalan dapat dipantau statusnya sampai selesai, termasuk proses penerimaan dan pengembalian.

## Fitur
- Master data gudang, item, kategori, satuan, dan PIC.
- Stok per gudang, mutasi, dan riwayat pergerakan.
- Surat jalan: draft, approval, pengiriman, pemeriksaan security, penerimaan, dan pengembalian.
- QR code, PDF surat jalan, lampiran, serta tanda tangan digital.
- Notifikasi, aktivitas, dan dashboard per peran.
- Export Excel untuk rekap dan daftar surat jalan.

## Peran dan akses
- Admin: kelola user, item, PIC, master data, dan rekap.
- Operator gudang: kelola stok, buat surat jalan, dan proses pengembalian.
- Manager: menyetujui atau menolak surat jalan dari gudang yang dikelola.
- Security: verifikasi surat jalan via QR dan konfirmasi terima atau tolak.
- Penerima: melihat dan menerima surat jalan sesuai divisi atau jabatan.

## Alur surat jalan (ringkas)
```
Draft
  -> Menunggu persetujuan
  -> Disetujui
  -> Dikirim
  -> Diperiksa (security)
  -> Diterima
  -> (Opsional) Pengembalian
  -> Selesai
```
Catatan: status detail dapat berbeda sesuai skenario, role, dan jenis transaksi.

## Teknologi
- Laravel 12, PHP 8.2
- Database relasional (MySQL/MariaDB/PostgreSQL sesuai .env)
- Redis (opsional) untuk cache, queue, dan broadcast
- Vite, Tailwind CSS, Alpine.js
- Dompdf, Laravel Excel, Simple QR Code
- Laravel Reverb + Echo (opsional untuk realtime)

## Prasyarat
- PHP 8.2+ dengan ekstensi umum Laravel (pdo, mbstring, openssl, tokenizer, xml)
- Composer
- Node.js + npm
- Database server

## Quick start
```
composer run setup
composer run dev
```

Jika ingin manual:
1. Install dependency backend:
   ```bash
   composer install
   ```
2. Siapkan `.env`:
   - Salin dari `.env.example` jika tersedia, atau buat `.env` sendiri.
3. Generate key dan migrate:
   ```bash
   php artisan key:generate
   php artisan migrate
   ```
4. (Opsional) seed data contoh:
   ```bash
   php artisan db:seed
   ```
5. Install dependency frontend:
   ```bash
   npm install
   ```
6. Jalankan aplikasi:
   ```bash
   php artisan serve
   npm run dev
   ```

## Konfigurasi .env penting
Contoh isian inti yang perlu disesuaikan:
```
APP_NAME=E-Dispatch & Tool Management System
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=project_pln
DB_USERNAME=root
DB_PASSWORD=

CACHE_STORE=file
QUEUE_CONNECTION=sync
BROADCAST_CONNECTION=log
```
Jika memakai Redis dan realtime, atur `CACHE_STORE`, `QUEUE_CONNECTION`, dan `BROADCAST_CONNECTION` sesuai kebutuhan.

## Data contoh
Jalankan `php artisan db:seed` untuk membuat akun demo berikut (password: `password`):
- admin
- manager
- budi
- siti
- agus
- rizal

## Perintah berguna
- Generate signature hash surat jalan:
  ```bash
  php artisan surat-jalan:generate-hash --id=123
  ```
  Gunakan `--force` untuk regenerate hash yang sudah ada.
- Build frontend production:
  ```bash
  npm run build
  ```

## Testing
```bash
composer test
```
